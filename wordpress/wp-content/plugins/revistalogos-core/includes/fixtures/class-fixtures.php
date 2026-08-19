<?php
/**
 * Fixture system (prompt §14 Phase 7, ADR 0004): one realistic
 * first-edition-shaped issue with its articles and authors, plus the
 * minimum stubs needed to exercise pagination. Entirely separate from
 * the institutional migration.
 *
 * Every fixture object carries _les_fixture = 1 (removable). Demo
 * fixtures also carry detectable fake identifiers (1234-5678,
 * 10.1234/les.*, 0000-0000-*). The production editorial bootstrap uses
 * the same marker with _les_fixture_kind=bootstrap and empty
 * identifiers (never fake DOI/ORCID/ISSN). Canonical migrated content
 * never carries the marker.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed / verify / teardown, all idempotent, operator-run via WP-CLI
 * only and refused on production without an explicit override.
 */
class Fixtures {

	const MARKER      = '_les_fixture';
	const FIXTURE_KEY = '_les_fixture_key';
	const KIND        = '_les_fixture_kind';

	const KIND_DEMO      = 'demo';
	const KIND_BOOTSTRAP = 'bootstrap';

	const BOOTSTRAP_AUTHOR_KEY  = 'bootstrap-author-1';
	const BOOTSTRAP_ISSUE_KEY   = 'bootstrap-issue-1';
	const BOOTSTRAP_ARTICLE_KEY = 'bootstrap-article-1';

	const FAKE_ISSN        = '1234-5678';
	const FAKE_DOI_PREFIX  = '10.1234/les';
	const FAKE_ORCID_STEM  = '0000-0000-0000-000';

	/**
	 * Guard: fixtures never run on production without explicit override.
	 *
	 * @param bool $allow_production Override flag.
	 * @return true|\WP_Error
	 */
	public static function environment_guard( $allow_production ) {
		if ( 'production' === wp_get_environment_type() && ! $allow_production ) {
			return new \WP_Error( 'production_guard', 'Refusing to touch fixtures on a production environment. Pass --allow-production only if you are certain.' );
		}

		return true;
	}

	/**
	 * Guard for production writes that are not the full demo seed:
	 * bootstrap and teardown. Requires explicit confirmation and backup
	 * evidence. Does not accept --allow-production (that flag is only
	 * the emergency override for the local demo dataset).
	 *
	 * @param array $assoc_args Named CLI args.
	 * @return true|\WP_Error
	 */
	public static function production_write_guard( $assoc_args ) {
		if ( 'production' !== wp_get_environment_type() ) {
			return true;
		}

		if ( ! isset( $assoc_args['apply'] ) ) {
			return true;
		}

		if ( ! isset( $assoc_args['confirm-production'] ) || empty( $assoc_args['backup'] ) ) {
			return new \WP_Error(
				'production_write_guard',
				'Production fixture bootstrap/teardown requires --confirm-production and --backup=<evidence> (pre-write database backup). The full demo seed remains blocked; do not pass --allow-production for this path.'
			);
		}

		return true;
	}

	/**
	 * Fixture dataset definition. Volume/number shaped like the real
	 * first edition (Vol. 1 Nº 1); every identifier is fake by design.
	 *
	 * @return array<string, mixed>
	 */
	private static function dataset() {
		$sections = array( 'Metafísica', 'Ética', 'Epistemología', 'Filosofía de la Religión' );

		$authors = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			$authors[ "author-$i" ] = array(
				'title'      => "Autora de Ejemplo $i",
				'afiliacion' => 'Institución de Ejemplo (fixture)',
				'orcid'      => self::FAKE_ORCID_STEM . $i,
				'bio'        => 'Perfil demostrativo creado por el sistema de fixtures. No es una persona real.',
			);
		}

		$articles = array(
			array(
				'key'      => 'article-editorial',
				'title'    => 'Editorial (fixture)',
				'type'     => 'editorial',
				'section'  => null,
				'authors'  => array( 'author-1' ),
				'pages'    => '5-8',
				'content'  => 'Texto editorial demostrativo generado por fixtures para validar la sección Editorial del número.',
			),
		);

		for ( $i = 1; $i <= 5; $i++ ) {
			$articles[] = array(
				'key'      => "article-$i",
				'title'    => "Artículo de ejemplo $i (fixture)",
				'type'     => ( 3 === $i ) ? 'essay' : ( ( 5 === $i ) ? 'review' : 'article' ),
				'section'  => $sections[ ( $i - 1 ) % count( $sections ) ],
				'authors'  => array( 'author-' . $i, 'author-' . ( ( $i % 6 ) + 1 ) ),
				'pages'    => sprintf( '%d-%d', $i * 10, $i * 10 + 15 ),
				'content'  => 'Cuerpo demostrativo de artículo, generado por fixtures para ejercitar plantillas y consultas.',
			);
		}

		// Pagination stubs: enough articles for a second archive page
		// (default 10/page) and enough news for a second Noticias page.
		$stub_articles = array();
		for ( $i = 1; $i <= 9; $i++ ) {
			$stub_articles[] = array(
				'key'     => "stub-article-$i",
				'title'   => "Artículo stub $i (fixture)",
				'type'    => 'article',
				'section' => $sections[ $i % count( $sections ) ],
				'authors' => array( 'author-' . ( ( $i % 6 ) + 1 ) ),
				'pages'   => '',
				'content' => 'Stub mínimo para ejercitar la paginación de archivos.',
			);
		}

		$news = array();
		for ( $i = 1; $i <= 12; $i++ ) {
			$news[ "news-$i" ] = array(
				'title'   => "Noticia demostrativa $i (fixture)",
				'content' => 'Noticia generada por el sistema de fixtures para validar el índice de Noticias y su paginación.',
			);
		}

		return array(
			'issues'        => array(
				'issue-1'      => array(
					'title'          => 'Primera edición (fixture)',
					'volume_number'  => 1,
					'issue_number'   => 1,
					'year'           => 2026,
					'date_published' => '2026-06-30',
					'issn'           => self::FAKE_ISSN,
					'doi'            => self::FAKE_DOI_PREFIX . '.v1n1',
					'cover'          => 'portada-ejemplo.jpg',
					'pdf'            => 'numero-v12n2-2025.pdf',
					'content'        => 'Número demostrativo con la forma de la primera edición. Todos sus identificadores son ficticios.',
				),
				'issue-stub-1' => array(
					'title'          => 'Número stub A (fixture)',
					'volume_number'  => 0,
					'issue_number'   => 0,
					'year'           => 2025,
					'date_published' => '2025-06-30',
					'issn'           => self::FAKE_ISSN,
					'doi'            => self::FAKE_DOI_PREFIX . '.stub1',
					'cover'          => 'portada-ejemplo.jpg',
					'pdf'            => '',
					'content'        => 'Stub para ejercitar el archivo de números.',
				),
				'issue-stub-2' => array(
					'title'          => 'Número stub B (fixture)',
					'volume_number'  => 0,
					'issue_number'   => 0,
					'year'           => 2024,
					'date_published' => '2024-06-30',
					'issn'           => self::FAKE_ISSN,
					'doi'            => self::FAKE_DOI_PREFIX . '.stub2',
					'cover'          => '',
					'pdf'            => '',
					'content'        => 'Stub para ejercitar el archivo de números.',
				),
			),
			'authors'       => $authors,
			'articles'      => $articles,
			'stub_articles' => $stub_articles,
			'news'          => $news,
		);
	}

	/**
	 * Find a fixture object by its stable fixture key.
	 *
	 * @param string $key Fixture key.
	 * @return \WP_Post|null
	 */
	private static function find( $key ) {
		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => self::FIXTURE_KEY,
				'meta_value'     => $key,
				'no_found_rows'  => true,
			)
		);

		if ( $posts ) {
			return $posts[0];
		}

		// 'any' excludes attachments in get_posts; check them explicitly.
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => self::FIXTURE_KEY,
				'meta_value'     => $key,
				'no_found_rows'  => true,
			)
		);

		return $attachments ? $attachments[0] : null;
	}

	/**
	 * Every fixture post/attachment ID (marker-based).
	 *
	 * @return int[]
	 */
	public static function all_fixture_ids() {
		$args = array(
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => self::MARKER,
			'meta_value'     => '1',
			'no_found_rows'  => true,
		);

		$posts       = get_posts( array_merge( $args, array( 'post_type' => 'any' ) ) );
		$attachments = get_posts( array_merge( $args, array( 'post_type' => 'attachment' ) ) );

		return array_values( array_unique( array_map( 'absint', array_merge( $posts, $attachments ) ) ) );
	}

	/**
	 * Seed the dataset. Idempotent: existing fixture keys are skipped.
	 *
	 * @param bool $apply Write when true.
	 * @return string[] Report lines.
	 */
	public static function seed( $apply ) {
		$report = array();
		$data   = self::dataset();
		$ids    = array();

		// Media first.
		foreach ( array(
			'media-cover' => array( 'portada-ejemplo.jpg', 'Portada demostrativa (fixture)' ),
			'media-issue-pdf' => array( 'numero-v12n2-2025.pdf', 'PDF de número demostrativo (fixture)' ),
			'media-article-pdf' => array( 'articulo-01.pdf', 'PDF de artículo demostrativo (fixture)' ),
		) as $key => $cfg ) {
			$existing = self::find( $key );

			if ( $existing ) {
				$ids[ $key ] = $existing->ID;
				$report[]    = "media $key: exists (id {$existing->ID})";
				continue;
			}

			if ( ! $apply ) {
				$report[] = "media $key: would import {$cfg[0]}";
				continue;
			}

			$source = REVISTALOGOS_CORE_DIR . 'resources/fixtures/' . $cfg[0];

			if ( ! file_exists( $source ) ) {
				$report[] = "media $key: ERROR seed file missing";
				continue;
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$tmp = wp_tempnam( $cfg[0] );
			copy( $source, $tmp );

			$attachment_id = media_handle_sideload(
				array(
					'name'     => $cfg[0],
					'tmp_name' => $tmp,
				),
				0,
				$cfg[1]
			);

			if ( is_wp_error( $attachment_id ) ) {
				$report[] = "media $key: ERROR " . $attachment_id->get_error_message();
				continue;
			}

			update_post_meta( $attachment_id, self::MARKER, '1' );
			update_post_meta( $attachment_id, self::FIXTURE_KEY, $key );
			update_post_meta( $attachment_id, self::KIND, self::KIND_DEMO );
			$ids[ $key ] = (int) $attachment_id;
			$report[]    = "media $key: imported (id $attachment_id)";
		}

		// Authors.
		foreach ( $data['authors'] as $key => $author ) {
			$ids[ $key ] = self::seed_post(
				$key,
				array(
					'post_type'   => Content_Types::AUTHOR,
					'post_title'  => $author['title'],
					'post_status' => 'publish',
				),
				array(
					'afiliacion' => $author['afiliacion'],
					'orcid'      => $author['orcid'],
					'bio'        => $author['bio'],
				),
				array(),
				$apply,
				$report
			);
		}

		// Issues.
		foreach ( $data['issues'] as $key => $issue ) {
			$meta = array(
				'volume_number'  => $issue['volume_number'],
				'issue_number'   => $issue['issue_number'],
				'year'           => $issue['year'],
				'date_published' => $issue['date_published'],
				'issn'           => $issue['issn'],
				'doi'            => $issue['doi'],
			);

			if ( $issue['pdf'] && isset( $ids['media-issue-pdf'] ) ) {
				$meta['pdf_file'] = $ids['media-issue-pdf'];
			}

			$ids[ $key ] = self::seed_post(
				$key,
				array(
					'post_type'    => Content_Types::ISSUE,
					'post_title'   => $issue['title'],
					'post_status'  => 'publish',
					'post_content' => $issue['content'],
				),
				$meta,
				array(),
				$apply,
				$report,
				( $issue['cover'] && isset( $ids['media-cover'] ) ) ? $ids['media-cover'] : 0
			);
		}

		// Articles (first edition + stubs).
		$all_articles = array_merge( $data['articles'], $data['stub_articles'] );

		foreach ( $all_articles as $index => $article ) {
			$issue_key  = 0 === strpos( $article['key'], 'stub-' )
				? ( 0 === $index % 2 ? 'issue-stub-1' : 'issue-stub-2' )
				: 'issue-1';
			$author_ids = array();

			foreach ( $article['authors'] as $author_key ) {
				if ( isset( $ids[ $author_key ] ) && $ids[ $author_key ] ) {
					$author_ids[] = $ids[ $author_key ];
				}
			}

			$meta = array(
				'abstract'         => 'Resumen demostrativo generado por fixtures.',
				'doi'              => self::FAKE_DOI_PREFIX . '.v1n1.a' . ( $index + 1 ),
				'pages'            => $article['pages'],
				'language'         => 'es',
				'publication_date' => '2026-06-30',
				'authors'          => $author_ids,
				'issue'            => isset( $ids[ $issue_key ] ) ? $ids[ $issue_key ] : 0,
			);

			if ( 'article-1' === $article['key'] && isset( $ids['media-article-pdf'] ) ) {
				$meta['pdf_file'] = $ids['media-article-pdf'];
			}

			$taxonomies = array(
				Taxonomies::ARTICLE_TYPE => array( $article['type'] ),
			);

			if ( $article['section'] ) {
				$taxonomies[ Taxonomies::SECTION ] = array( $article['section'] );
			}

			$taxonomies[ Taxonomies::KEYWORD ] = array( 'fixture', 'demostración' );

			$ids[ $article['key'] ] = self::seed_post(
				$article['key'],
				array(
					'post_type'    => Content_Types::ARTICLE,
					'post_title'   => $article['title'],
					'post_status'  => 'publish',
					'post_content' => $article['content'],
				),
				$meta,
				$taxonomies,
				$apply,
				$report
			);
		}

		// News.
		foreach ( $data['news'] as $key => $news_item ) {
			$ids[ $key ] = self::seed_post(
				$key,
				array(
					'post_type'    => 'post',
					'post_title'   => $news_item['title'],
					'post_status'  => 'publish',
					'post_content' => $news_item['content'],
				),
				array(),
				array(),
				$apply,
				$report
			);
		}

		return $report;
	}

	/**
	 * Restricted editorial bootstrap: one draft issue, one draft
	 * article, one draft author. Tagged _les_fixture=1 and
	 * _les_fixture_kind=bootstrap. No fake DOI/ORCID/ISSN, no news,
	 * no demo media, no institutional pages. Idempotent by fixture key.
	 * Never overwrites an existing object (fixture or editorial).
	 *
	 * @param bool  $apply  Write when true.
	 * @param array $author Optional keys: name, affiliation, bio. Never
	 *                      email, ORCID or credentials. Missing name uses
	 *                      a neutral placeholder.
	 * @return string[] Report lines.
	 */
	public static function bootstrap( $apply, $author = array() ) {
		$report = array();

		$name = isset( $author['name'] ) ? trim( (string) $author['name'] ) : '';
		if ( '' === $name ) {
			$name = 'Autor temporal (estructura editorial)';
		}

		$affiliation = isset( $author['affiliation'] ) ? trim( (string) $author['affiliation'] ) : '';
		$bio         = isset( $author['bio'] ) ? trim( (string) $author['bio'] ) : '';

		$author_id = self::seed_post(
			self::BOOTSTRAP_AUTHOR_KEY,
			array(
				'post_type'   => Content_Types::AUTHOR,
				'post_title'  => $name,
				'post_status' => 'draft',
			),
			array(
				'afiliacion' => $affiliation,
				'orcid'      => '',
				'bio'        => $bio,
			),
			array(),
			$apply,
			$report,
			0,
			self::KIND_BOOTSTRAP
		);

		$issue_id = self::seed_post(
			self::BOOTSTRAP_ISSUE_KEY,
			array(
				'post_type'    => Content_Types::ISSUE,
				'post_title'   => 'Número temporal (estructura editorial)',
				'post_status'  => 'draft',
				'post_content' => 'Registro temporal de estructura editorial. Sustituir o eliminar antes de abrir la indexación. No es un número publicado.',
			),
			array(
				'volume_number'  => 1,
				'issue_number'   => 1,
				'year'           => '',
				'date_published' => '',
				'issn'           => '',
				'doi'            => '',
			),
			array(),
			$apply,
			$report,
			0,
			self::KIND_BOOTSTRAP
		);

		self::seed_post(
			self::BOOTSTRAP_ARTICLE_KEY,
			array(
				'post_type'    => Content_Types::ARTICLE,
				'post_title'   => 'Artículo temporal (estructura editorial)',
				'post_status'  => 'draft',
				'post_content' => 'Registro temporal de estructura editorial. Sustituir o eliminar antes de abrir la indexación. No es contenido académico publicado.',
			),
			array(
				'abstract'         => '',
				'doi'              => '',
				'pages'            => '',
				'language'         => 'es',
				'publication_date' => '',
				'authors'          => $author_id ? array( $author_id ) : array(),
				'issue'            => $issue_id ? $issue_id : 0,
			),
			array(),
			$apply,
			$report,
			0,
			self::KIND_BOOTSTRAP
		);

		return $report;
	}

	/**
	 * Create one fixture post when absent.
	 *
	 * @param string $key        Fixture key.
	 * @param array  $postarr    wp_insert_post args.
	 * @param array  $meta       Meta key => value.
	 * @param array  $taxonomies Taxonomy => term names/slugs.
	 * @param bool   $apply      Write when true.
	 * @param array  $report     Report accumulator (by reference).
	 * @param int    $thumbnail  Attachment ID for the featured image.
	 * @param string $kind       KIND_DEMO or KIND_BOOTSTRAP.
	 * @return int Post ID (0 in dry-run for new objects).
	 */
	private static function seed_post( $key, $postarr, $meta, $taxonomies, $apply, &$report, $thumbnail = 0, $kind = self::KIND_DEMO ) {
		$existing = self::find( $key );

		if ( $existing ) {
			$report[] = "$key: exists (id {$existing->ID})";
			return (int) $existing->ID;
		}

		if ( ! $apply ) {
			$report[] = "$key: would create {$postarr['post_type']}";
			return 0;
		}

		$post_id = wp_insert_post( wp_slash( $postarr ), true );

		if ( is_wp_error( $post_id ) ) {
			$report[] = "$key: ERROR " . $post_id->get_error_message();
			return 0;
		}

		update_post_meta( $post_id, self::MARKER, '1' );
		update_post_meta( $post_id, self::FIXTURE_KEY, $key );
		update_post_meta( $post_id, self::KIND, $kind );

		foreach ( $meta as $meta_key => $value ) {
			update_post_meta( $post_id, $meta_key, $value );
		}

		foreach ( $taxonomies as $taxonomy => $terms ) {
			$term_ids = array();

			foreach ( $terms as $term_name ) {
				$existing_term = term_exists( $term_name, $taxonomy );

				if ( ! $existing_term ) {
					$new_term = wp_insert_term( $term_name, $taxonomy );

					if ( ! is_wp_error( $new_term ) ) {
						// Mark fixture-created terms so teardown can
						// remove them when they end up unused.
						update_term_meta( $new_term['term_id'], self::MARKER, '1' );
						$term_ids[] = (int) $new_term['term_id'];
					}
				} else {
					$term_ids[] = (int) ( is_array( $existing_term ) ? $existing_term['term_id'] : $existing_term );
				}
			}

			wp_set_object_terms( $post_id, $term_ids, $taxonomy );
		}

		if ( $thumbnail ) {
			set_post_thumbnail( $post_id, $thumbnail );
		}

		$report[] = "$key: created (id $post_id)";

		return (int) $post_id;
	}

	/**
	 * Verify fixture state: expected objects exist, all carry fake
	 * identifiers, no canonical object carries the marker, no
	 * duplicates per fixture key.
	 *
	 * @return array{report: string[], failures: int}
	 */
	public static function verify() {
		$report   = array();
		$failures = 0;

		$ids = self::all_fixture_ids();
		$report[] = sprintf( '%d fixture objects present', count( $ids ) );

		// Duplicate fixture keys mean seed idempotency broke.
		$keys = array();
		foreach ( $ids as $id ) {
			$key = (string) get_post_meta( $id, self::FIXTURE_KEY, true );

			if ( isset( $keys[ $key ] ) ) {
				$report[] = "DUPLICATE fixture key: $key (ids {$keys[$key]}, $id)";
				$failures++;
			}

			$keys[ $key ] = $id;
		}

		// Identifiers: demo fixtures must keep detectable fakes;
		// bootstrap fixtures must have empty DOI/ORCID/ISSN (never fakes).
		foreach ( $ids as $id ) {
			$post_type = get_post_type( $id );
			$kind      = (string) get_post_meta( $id, self::KIND, true );
			$issn      = (string) get_post_meta( $id, 'issn', true );
			$doi       = (string) get_post_meta( $id, 'doi', true );
			$orcid     = (string) get_post_meta( $id, 'orcid', true );

			if ( self::KIND_BOOTSTRAP === $kind ) {
				if ( '' !== $issn || '' !== $doi || '' !== $orcid ) {
					$report[] = "$post_type $id: bootstrap fixture must not store DOI/ORCID/ISSN";
					$failures++;
				}
				if ( false !== strpos( $issn . $doi . $orcid, '1234-5678' )
					|| false !== strpos( $doi, self::FAKE_DOI_PREFIX )
					|| false !== strpos( $orcid, '0000-0000-' ) ) {
					$report[] = "$post_type $id: bootstrap fixture carries a fake identifier";
					$failures++;
				}
				continue;
			}

			if ( Content_Types::ISSUE === $post_type && self::FAKE_ISSN !== $issn ) {
				$report[] = "issue $id: ISSN is not the fixture fake value";
				$failures++;
			}

			if ( in_array( $post_type, array( Content_Types::ISSUE, Content_Types::ARTICLE ), true ) ) {
				if ( '' !== $doi && 0 !== strpos( $doi, self::FAKE_DOI_PREFIX ) ) {
					$report[] = "$post_type $id: DOI is not a fixture fake value";
					$failures++;
				}
			}

			if ( Content_Types::AUTHOR === $post_type ) {
				if ( '' !== $orcid && 0 !== strpos( $orcid, '0000-0000-' ) ) {
					$report[] = "author $id: ORCID is not a fixture fake value";
					$failures++;
				}
			}
		}

		// Canonical migrated objects must never carry the marker.
		foreach ( $ids as $id ) {
			if ( '' !== (string) get_post_meta( $id, Content_Migrator::META_SOURCE_KEY, true ) ) {
				$report[] = "object $id carries BOTH fixture marker and migration source key";
				$failures++;
			}
		}

		return array(
			'report'   => $report,
			'failures' => $failures,
		);
	}

	/**
	 * Remove every fixture object: posts, attachments (with files),
	 * meta and term relationships; fixture-created terms are removed
	 * when no non-fixture content uses them. Never touches canonical
	 * or unrelated content. Idempotent: an empty run is a no-op.
	 *
	 * @param bool   $apply Write when true.
	 * @param string $kind  Empty = all fixtures; KIND_DEMO or KIND_BOOTSTRAP to filter.
	 * @return string[] Report lines.
	 */
	public static function teardown( $apply, $kind = '' ) {
		$report = array();
		$ids    = self::all_fixture_ids();

		if ( '' !== $kind ) {
			$ids = array_values(
				array_filter(
					$ids,
					static function ( $id ) use ( $kind ) {
						return $kind === (string) get_post_meta( $id, self::KIND, true );
					}
				)
			);
		}

		if ( ! $ids ) {
			$report[] = 'no fixture objects found; nothing to do';
		}

		foreach ( $ids as $id ) {
			$type = get_post_type( $id );

			if ( ! $apply ) {
				$report[] = "would delete $type $id";
				continue;
			}

			// wp_delete_post also deletes post meta and term
			// relationships; attachments delete their files.
			$deleted = ( 'attachment' === $type )
				? wp_delete_attachment( $id, true )
				: wp_delete_post( $id, true );

			$report[] = $deleted ? "deleted $type $id" : "ERROR deleting $type $id";
		}

		if ( '' !== $kind ) {
			return $report;
		}

		// Fixture-created terms now unused by real content.
		foreach ( array( Taxonomies::SECTION, Taxonomies::ARTICLE_TYPE, Taxonomies::KEYWORD ) as $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'meta_key'   => self::MARKER,
					'meta_value' => '1',
				)
			);

			if ( is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				if ( $term->count > 0 ) {
					$report[] = "term {$term->slug} ({$taxonomy}) still in use; kept";
					continue;
				}

				if ( $apply ) {
					wp_delete_term( $term->term_id, $taxonomy );
					$report[] = "deleted term {$term->slug} ({$taxonomy})";
				} else {
					$report[] = "would delete term {$term->slug} ({$taxonomy})";
				}
			}
		}

		return $report;
	}
}
