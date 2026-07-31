<?php
/**
 * Institutional content importer (Fase 3, prompt §14 Phase 6). Reads
 * the generated payload shipped under resources/ (never repository
 * paths) and creates or updates WordPress objects idempotently.
 *
 * Never runs on activation, on normal requests, through any public
 * endpoint or during deployment: only the WP-CLI command calls in.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deterministic, idempotent migration engine with drift detection.
 *
 * Identity meta on every migrated object:
 * - _les_source_key         stable key from the payload manifest
 * - _les_source_hash        payload text hash (source-change detection)
 * - _les_migration_version  payload version applied
 * - _les_migration_owned    fields the migration owns
 * - _les_imported_hash      sha256 of the exact content written
 *                           (manual-edit detection)
 */
class Content_Migrator {

	const META_SOURCE_KEY = '_les_source_key';
	const META_SOURCE_HASH = '_les_source_hash';
	const META_VERSION = '_les_migration_version';
	const META_OWNED = '_les_migration_owned';
	const META_IMPORTED_HASH = '_les_imported_hash';

	/**
	 * Decoded payload.
	 *
	 * @var array|null
	 */
	private $payload = null;

	/**
	 * Load and structurally validate the payload file.
	 *
	 * @return true|\WP_Error
	 */
	public function load() {
		if ( null !== $this->payload ) {
			return true;
		}

		$file = REVISTALOGOS_CORE_DIR . 'resources/content-payload.json';

		if ( ! file_exists( $file ) ) {
			return new \WP_Error( 'missing_payload', 'resources/content-payload.json not found. Run tools/generate-content-payload.mjs in the repository and redeploy the plugin.' );
		}

		$data = json_decode( (string) file_get_contents( $file ), true );

		if ( ! is_array( $data ) || empty( $data['entries'] ) || empty( $data['payload_version'] ) ) {
			return new \WP_Error( 'invalid_payload', 'Payload is unreadable or missing entries/payload_version.' );
		}

		foreach ( $data['entries'] as $entry ) {
			foreach ( array( 'source_key', 'post_type', 'slug', 'title', 'status', 'migration_owned', 'content_text_sha256' ) as $required ) {
				if ( ! isset( $entry[ $required ] ) ) {
					return new \WP_Error( 'invalid_entry', sprintf( 'Entry missing %s: %s', $required, wp_json_encode( $entry ) ) );
				}
			}
		}

		$this->payload = $data;

		return true;
	}

	/**
	 * Payload accessor (after load()).
	 *
	 * @return array
	 */
	public function payload() {
		return (array) $this->payload;
	}

	/**
	 * Find a migrated object by its stable source key.
	 *
	 * @param string $source_key Source key.
	 * @param string $post_type  Post type.
	 * @return \WP_Post|null
	 */
	public function find_by_source_key( $source_key, $post_type = 'page' ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => self::META_SOURCE_KEY,
				'meta_value'     => $source_key,
				'no_found_rows'  => true,
			)
		);

		return $posts ? $posts[0] : null;
	}

	/**
	 * Find a migrated attachment by source key.
	 *
	 * @param string $source_key Source key.
	 * @return int Attachment ID or 0.
	 */
	public function find_attachment( $source_key ) {
		$posts = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => self::META_SOURCE_KEY,
				'meta_value'     => $source_key,
				'no_found_rows'  => true,
			)
		);

		return $posts ? $posts[0]->ID : 0;
	}

	/**
	 * Compute the action for one entry: create | update | skip |
	 * conflict (manual edits present) | update-forced.
	 *
	 * @param array $entry Payload entry.
	 * @param bool  $force Whether --force was passed.
	 * @return array{action: string, reason: string, post_id: int}
	 */
	public function plan_entry( $entry, $force = false ) {
		$existing = $this->find_by_source_key( $entry['source_key'], $entry['post_type'] );

		if ( ! $existing ) {
			return array(
				'action'  => 'create',
				'reason'  => 'no object with this source key',
				'post_id' => 0,
			);
		}

		$stored_source_hash   = (string) get_post_meta( $existing->ID, self::META_SOURCE_HASH, true );
		$stored_imported_hash = (string) get_post_meta( $existing->ID, self::META_IMPORTED_HASH, true );
		$current_hash         = hash( 'sha256', (string) $existing->post_content );

		$source_changed = $stored_source_hash !== (string) $entry['content_text_sha256'];
		$manual_edits   = '' !== $stored_imported_hash && $stored_imported_hash !== $current_hash;

		if ( $manual_edits && $source_changed ) {
			return array(
				'action'  => $force ? 'update-forced' : 'conflict',
				'reason'  => 'source changed AND manual WordPress edits detected; --force overwrites migration-owned fields only',
				'post_id' => $existing->ID,
			);
		}

		if ( $manual_edits ) {
			return array(
				'action'  => $force ? 'update-forced' : 'skip',
				'reason'  => 'manual WordPress edits detected; kept (use --force to reassert migration-owned fields)',
				'post_id' => $existing->ID,
			);
		}

		if ( $source_changed ) {
			return array(
				'action'  => 'update',
				'reason'  => 'canonical source changed since last import',
				'post_id' => $existing->ID,
			);
		}

		return array(
			'action'  => 'skip',
			'reason'  => 'unchanged',
			'post_id' => $existing->ID,
		);
	}

	/**
	 * Resolve payload tokens against imported attachments and the
	 * active theme.
	 *
	 * @param string $html Content with tokens.
	 * @return array{html: string, unresolved: string[]}
	 */
	public function resolve_tokens( $html ) {
		$unresolved = array();

		$html = preg_replace_callback(
			'/\{\{les:attachment:([a-z0-9-]+)\}\}/',
			function ( $m ) use ( &$unresolved ) {
				$attachment_id = $this->find_attachment( $m[1] );
				$url           = $attachment_id ? wp_get_attachment_url( $attachment_id ) : '';

				if ( ! $url ) {
					$unresolved[] = $m[0];
					return '#';
				}

				return $url;
			},
			$html
		);

		$html = preg_replace_callback(
			'/\{\{les:theme-asset:([a-z0-9\/.-]+)\}\}/',
			static function ( $m ) {
				return get_template_directory_uri() . '/assets/' . $m[1];
			},
			$html
		);

		return array(
			'html'       => $html,
			'unresolved' => $unresolved,
		);
	}

	/**
	 * Import media seeds into the Media Library. Idempotent by source
	 * key + checksum.
	 *
	 * @param bool $apply Write when true; report when false.
	 * @return array<int, array{key: string, action: string, id: int}>
	 */
	public function import_media( $apply ) {
		$results = array();

		foreach ( (array) ( $this->payload['media'] ?? array() ) as $media ) {
			$existing_id = $this->find_attachment( $media['source_key'] );

			if ( $existing_id ) {
				$stored = (string) get_post_meta( $existing_id, self::META_SOURCE_HASH, true );

				if ( $stored === $media['sha256'] ) {
					$results[] = array(
						'key'    => $media['source_key'],
						'action' => 'skip (unchanged)',
						'id'     => $existing_id,
					);
					continue;
				}
			}

			if ( ! $apply ) {
				$results[] = array(
					'key'    => $media['source_key'],
					'action' => $existing_id ? 'would update' : 'would import',
					'id'     => $existing_id,
				);
				continue;
			}

			$source_file = REVISTALOGOS_CORE_DIR . 'resources/' . $media['file'];

			if ( ! file_exists( $source_file ) ) {
				$results[] = array(
					'key'    => $media['source_key'],
					'action' => 'ERROR: seed file missing',
					'id'     => 0,
				);
				continue;
			}

			if ( hash_file( 'sha256', $source_file ) !== $media['sha256'] ) {
				$results[] = array(
					'key'    => $media['source_key'],
					'action' => 'ERROR: seed checksum mismatch',
					'id'     => 0,
				);
				continue;
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			// Copy to a temp path so media_handle_sideload moves a copy,
			// never the versioned seed.
			$tmp = wp_tempnam( basename( $media['file'] ) );
			copy( $source_file, $tmp );

			$file_array = array(
				'name'     => basename( $media['file'] ),
				'tmp_name' => $tmp,
			);

			$attachment_id = media_handle_sideload( $file_array, 0, $media['title'] );

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				$results[] = array(
					'key'    => $media['source_key'],
					'action' => 'ERROR: ' . $attachment_id->get_error_message(),
					'id'     => 0,
				);
				continue;
			}

			update_post_meta( $attachment_id, self::META_SOURCE_KEY, $media['source_key'] );
			update_post_meta( $attachment_id, self::META_SOURCE_HASH, $media['sha256'] );
			update_post_meta( $attachment_id, self::META_VERSION, (string) $this->payload['payload_version'] );

			$results[] = array(
				'key'    => $media['source_key'],
				'action' => $existing_id ? 'updated' : 'imported',
				'id'     => (int) $attachment_id,
			);
		}

		return $results;
	}

	/**
	 * Import content entries.
	 *
	 * @param bool $apply Write when true.
	 * @param bool $force Overwrite migration-owned fields on conflict.
	 * @return array<int, array<string, mixed>>
	 */
	public function import_entries( $apply, $force = false ) {
		$results = array();

		foreach ( $this->payload['entries'] as $entry ) {
			$plan = $this->plan_entry( $entry, $force );

			if ( in_array( $plan['action'], array( 'skip', 'conflict' ), true ) || ! $apply ) {
				$results[] = array_merge( array( 'key' => $entry['source_key'] ), $plan );
				continue;
			}

			$resolved = $this->resolve_tokens( (string) $entry['content_html'] );

			$postarr = array(
				'post_type'      => $entry['post_type'],
				'post_status'    => $entry['status'],
				'post_name'      => $entry['slug'],
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			);

			$owned = (array) $entry['migration_owned'];

			if ( in_array( 'post_title', $owned, true ) ) {
				$postarr['post_title'] = $entry['title'];
			}

			if ( in_array( 'post_content', $owned, true ) ) {
				$postarr['post_content'] = $resolved['html'];
			}

			if ( $plan['post_id'] ) {
				$postarr['ID'] = $plan['post_id'];
				$post_id       = wp_update_post( wp_slash( $postarr ), true );
			} else {
				$post_id = wp_insert_post( wp_slash( $postarr ), true );
			}

			if ( is_wp_error( $post_id ) ) {
				$results[] = array(
					'key'    => $entry['source_key'],
					'action' => 'ERROR',
					'reason' => $post_id->get_error_message(),
				);
				continue;
			}

			$written = get_post_field( 'post_content', $post_id );

			update_post_meta( $post_id, self::META_SOURCE_KEY, $entry['source_key'] );
			update_post_meta( $post_id, self::META_SOURCE_HASH, $entry['content_text_sha256'] );
			update_post_meta( $post_id, self::META_VERSION, (string) $this->payload['payload_version'] );
			update_post_meta( $post_id, self::META_OWNED, $owned );
			update_post_meta( $post_id, self::META_IMPORTED_HASH, hash( 'sha256', (string) $written ) );

			$results[] = array(
				'key'        => $entry['source_key'],
				'action'     => $plan['post_id'] ? $plan['action'] : 'created',
				'reason'     => $plan['reason'],
				'post_id'    => (int) $post_id,
				'unresolved' => $resolved['unresolved'],
			);
		}

		return $results;
	}

	/**
	 * Apply site settings and menus (idempotent; object IDs, never
	 * hardcoded URLs; owner-managed menus are never overwritten
	 * silently).
	 *
	 * @param bool $apply Write when true.
	 * @param bool $force Recreate menu items when a managed menu diverged.
	 * @return string[] Report lines.
	 */
	public function apply_site_settings( $apply, $force = false ) {
		$report = array();
		$site   = (array) ( $this->payload['site'] ?? array() );

		if ( ! $site ) {
			return array( 'no site settings in payload' );
		}

		// Reading settings pages by source key.
		$front   = $this->find_by_source_key( (string) $site['front_page'] );
		$posts   = $this->find_by_source_key( (string) $site['posts_page'] );
		$privacy = $this->find_by_source_key( (string) $site['privacy_page'] );

		$settings = array(
			'show_on_front'              => $site['show_on_front'],
			'page_on_front'              => $front ? $front->ID : 0,
			'page_for_posts'             => $posts ? $posts->ID : 0,
			'wp_page_for_privacy_policy' => $privacy ? $privacy->ID : 0,
		);

		foreach ( $settings as $option => $value ) {
			$current = get_option( $option );

			if ( (string) $current === (string) $value ) {
				$report[] = sprintf( '%s unchanged (%s)', $option, (string) $value );
				continue;
			}

			if ( 0 === $value || '' === $value ) {
				$report[] = sprintf( '%s: target page missing, left as-is', $option );
				continue;
			}

			if ( $apply ) {
				update_option( $option, $value );
				$report[] = sprintf( '%s set to %s', $option, (string) $value );
			} else {
				$report[] = sprintf( '%s would change %s -> %s', $option, (string) $current, (string) $value );
			}
		}

		foreach ( (array) ( $site['menus'] ?? array() ) as $location => $menu_cfg ) {
			$report = array_merge( $report, $this->apply_menu( $location, (array) $menu_cfg, $apply, $force ) );
		}

		return $report;
	}

	/**
	 * Create or reconcile one nav menu and its location assignment.
	 *
	 * @param string $location Theme menu location.
	 * @param array  $cfg      Menu config from payload.
	 * @param bool   $apply    Write when true.
	 * @param bool   $force    Rebuild items when diverged.
	 * @return string[]
	 */
	private function apply_menu( $location, $cfg, $apply, $force ) {
		$report = array();
		$name   = (string) $cfg['name'];
		$menu   = wp_get_nav_menu_object( $name );

		if ( $menu && ! $force ) {
			$report[] = sprintf( 'menu "%s" exists; left untouched (owner-managed; --force rebuilds)', $name );
		} elseif ( ! $apply ) {
			$report[] = sprintf( 'menu "%s" would be %s', $name, $menu ? 'rebuilt (--force)' : 'created' );
		} else {
			if ( $menu && $force ) {
				// Rebuild deliberately: remove managed items first.
				foreach ( wp_get_nav_menu_items( $menu->term_id ) as $item ) {
					wp_delete_post( $item->ID, true );
				}
				$menu_id = $menu->term_id;
			} else {
				$menu_id = wp_create_nav_menu( $name );

				if ( is_wp_error( $menu_id ) ) {
					return array( sprintf( 'menu "%s" ERROR: %s', $name, $menu_id->get_error_message() ) );
				}
			}

			foreach ( (array) $cfg['items'] as $item ) {
				$parent_id = $this->insert_menu_item( $menu_id, $item, 0 );

				foreach ( (array) ( $item['children'] ?? array() ) as $child ) {
					$this->insert_menu_item( $menu_id, $child, $parent_id );
				}
			}

			$report[] = sprintf( 'menu "%s" %s', $name, $menu ? 'rebuilt' : 'created' );
			$menu     = wp_get_nav_menu_object( $name );
		}

		// Assign the location only when it is empty (never steal an
		// owner-chosen assignment).
		$locations = get_theme_mod( 'nav_menu_locations', array() );

		if ( empty( $locations[ $location ] ) && $menu ) {
			if ( $apply ) {
				$locations[ $location ] = $menu->term_id;
				set_theme_mod( 'nav_menu_locations', $locations );
				$report[] = sprintf( 'location %s assigned to "%s"', $location, $name );
			} else {
				$report[] = sprintf( 'location %s would be assigned to "%s"', $location, $name );
			}
		}

		return $report;
	}

	/**
	 * Insert one menu item (page reference by source key, custom URL,
	 * or the current-issue placeholder resolved by the theme at render
	 * time).
	 *
	 * @param int   $menu_id   Menu ID.
	 * @param array $item      Item config.
	 * @param int   $parent_id Parent menu item ID.
	 * @return int Menu item ID.
	 */
	private function insert_menu_item( $menu_id, $item, $parent_id ) {
		$args = array(
			'menu-item-title'     => (string) $item['title'],
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent_id,
		);

		if ( ! empty( $item['page'] ) ) {
			$page = $this->find_by_source_key( (string) $item['page'] );

			if ( $page ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $page->ID;
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = home_url( '/' );
			}
		} else {
			$url = (string) $item['url'];

			$args['menu-item-type'] = 'custom';
			$args['menu-item-url']  = '#les-current-issue' === $url || preg_match( '#^https?://#', $url )
				? $url
				: home_url( $url );

			if ( ! empty( $item['external'] ) ) {
				$args['menu-item-target'] = '_blank';
			}
		}

		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );

		return is_wp_error( $item_id ) ? 0 : (int) $item_id;
	}

	/**
	 * Verify imported objects against the payload.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function verify() {
		$results = array();

		foreach ( $this->payload['entries'] as $entry ) {
			$existing = $this->find_by_source_key( $entry['source_key'], $entry['post_type'] );

			if ( ! $existing ) {
				$results[] = array(
					'key'    => $entry['source_key'],
					'status' => 'MISSING',
				);
				continue;
			}

			$stored_source   = (string) get_post_meta( $existing->ID, self::META_SOURCE_HASH, true );
			$stored_imported = (string) get_post_meta( $existing->ID, self::META_IMPORTED_HASH, true );
			$current         = hash( 'sha256', (string) $existing->post_content );
			$has_fixture     = '1' === (string) get_post_meta( $existing->ID, '_les_fixture', true );

			if ( $has_fixture ) {
				$status = 'ERROR: canonical object carries fixture marker';
			} elseif ( $stored_source !== (string) $entry['content_text_sha256'] ) {
				$status = 'STALE (payload newer than imported version)';
			} elseif ( $stored_imported !== $current ) {
				$status = 'DRIFTED (manual WordPress edits)';
			} else {
				$status = 'OK';
			}

			$results[] = array(
				'key'    => $entry['source_key'],
				'status' => $status,
			);
		}

		return $results;
	}
}
