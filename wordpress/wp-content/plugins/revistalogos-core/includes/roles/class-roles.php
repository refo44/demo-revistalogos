<?php
/**
 * Managing Editor role (docs/03 §6, ADR 0005 §4): custom role, distinct
 * from the native Editor, scoped to published journal content. No
 * submission capabilities exist anywhere (deferred subsystem).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installs the Managing Editor role and grants CPT capabilities to
 * administrators. Idempotent for activation and upgrades.
 */
class Roles {

	const MANAGING_EDITOR = 'managing_editor';

	/**
	 * Capabilities for each journal CPT (capability_type from
	 * Content_Types with map_meta_cap).
	 *
	 * @return string[]
	 */
	private static function cpt_capabilities() {
		$caps = array();

		foreach ( array(
			array( 'issue', 'issues' ),
			array( 'journal_article', 'journal_articles' ),
			array( 'journal_author', 'journal_authors' ),
		) as $pair ) {
			list( $singular, $plural ) = $pair;

			$caps[] = "edit_{$plural}";
			$caps[] = "edit_others_{$plural}";
			$caps[] = "edit_published_{$plural}";
			$caps[] = "edit_private_{$plural}";
			$caps[] = "publish_{$plural}";
			$caps[] = "read_private_{$plural}";
			$caps[] = "delete_{$plural}";
			$caps[] = "delete_others_{$plural}";
			$caps[] = "delete_published_{$plural}";
			$caps[] = "delete_private_{$plural}";
		}

		return $caps;
	}

	/**
	 * Least-privilege capability set for the Managing Editor: journal
	 * CPTs, news posts, institutional pages, media and taxonomy terms.
	 * No user, theme, plugin or option management.
	 *
	 * @return array<string, bool>
	 */
	private static function managing_editor_capabilities() {
		$caps = array(
			'read'                   => true,
			'upload_files'           => true,
			// Noticias (native posts).
			'edit_posts'             => true,
			'edit_others_posts'      => true,
			'edit_published_posts'   => true,
			'edit_private_posts'     => true,
			'publish_posts'          => true,
			'read_private_posts'     => true,
			'delete_posts'           => true,
			'delete_others_posts'    => true,
			'delete_published_posts' => true,
			'delete_private_posts'   => true,
			// Institutional pages.
			'edit_pages'             => true,
			'edit_others_pages'      => true,
			'edit_published_pages'   => true,
			'edit_private_pages'     => true,
			'publish_pages'          => true,
			'read_private_pages'     => true,
			'delete_pages'           => true,
			'delete_others_pages'    => true,
			'delete_published_pages' => true,
			'delete_private_pages'   => true,
			// Taxonomy terms (section, article_type, keyword use default caps).
			'manage_categories'      => true,
		);

		foreach ( self::cpt_capabilities() as $cap ) {
			$caps[ $cap ] = true;
		}

		return $caps;
	}

	/**
	 * Install or refresh the role and grant CPT capabilities to the
	 * administrator role. Safe to run repeatedly.
	 */
	public static function install() {
		$capabilities = self::managing_editor_capabilities();

		remove_role( self::MANAGING_EDITOR );
		add_role(
			self::MANAGING_EDITOR,
			__( 'Managing Editor', 'revistalogos-core' ),
			$capabilities
		);

		$admin = get_role( 'administrator' );

		if ( $admin ) {
			foreach ( self::cpt_capabilities() as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}
}
