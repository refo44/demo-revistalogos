<?php
/**
 * Custom post types: issue, article, author (published-content model only;
 * the submission subsystem is deferred by ADR 0005 §4 and must not exist).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the three public CPTs with their rewrite slugs (docs/11 §1,
 * ADR 0008) and per-CPT capabilities consumed by the Managing Editor role.
 */
class Content_Types {

	const ISSUE   = 'issue';
	const ARTICLE = 'article';
	const AUTHOR  = 'author';

	/**
	 * Public query var for the author CPT. Must not be `author`: that is
	 * WordPress's native user-archive query var, so singles rewrite to
	 * `index.php?author={slug}` and 404. Archives (`post_type=author`)
	 * and REST are unaffected.
	 */
	const AUTHOR_QUERY_VAR = 'journal_author';

	/**
	 * Register the CPTs. Hooked on init; also called on activation.
	 */
	public static function register() {
		register_post_type(
			self::ISSUE,
			array(
				'labels'       => array(
					'name'               => __( 'Números', 'revistalogos-core' ),
					'singular_name'      => __( 'Número', 'revistalogos-core' ),
					'add_new'            => __( 'Añadir número', 'revistalogos-core' ),
					'add_new_item'       => __( 'Añadir nuevo número', 'revistalogos-core' ),
					'edit_item'          => __( 'Editar número', 'revistalogos-core' ),
					'new_item'           => __( 'Nuevo número', 'revistalogos-core' ),
					'view_item'          => __( 'Ver número', 'revistalogos-core' ),
					'search_items'       => __( 'Buscar números', 'revistalogos-core' ),
					'not_found'          => __( 'No se encontraron números.', 'revistalogos-core' ),
					'not_found_in_trash' => __( 'No hay números en la papelera.', 'revistalogos-core' ),
					'all_items'          => __( 'Todos los números', 'revistalogos-core' ),
					'archives'           => __( 'Archivo de números', 'revistalogos-core' ),
				),
				'description'  => __( 'Volúmenes publicados de la revista.', 'revistalogos-core' ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-book',
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'has_archive'  => true,
				'rewrite'      => array(
					'slug'       => 'revista/numeros',
					'with_front' => false,
				),
				'capability_type' => array( 'issue', 'issues' ),
				'map_meta_cap' => true,
			)
		);

		register_post_type(
			self::ARTICLE,
			array(
				'labels'       => array(
					'name'               => __( 'Artículos', 'revistalogos-core' ),
					'singular_name'      => __( 'Artículo', 'revistalogos-core' ),
					'add_new'            => __( 'Añadir artículo', 'revistalogos-core' ),
					'add_new_item'       => __( 'Añadir nuevo artículo', 'revistalogos-core' ),
					'edit_item'          => __( 'Editar artículo', 'revistalogos-core' ),
					'new_item'           => __( 'Nuevo artículo', 'revistalogos-core' ),
					'view_item'          => __( 'Ver artículo', 'revistalogos-core' ),
					'search_items'       => __( 'Buscar artículos', 'revistalogos-core' ),
					'not_found'          => __( 'No se encontraron artículos.', 'revistalogos-core' ),
					'not_found_in_trash' => __( 'No hay artículos en la papelera.', 'revistalogos-core' ),
					'all_items'          => __( 'Todos los artículos', 'revistalogos-core' ),
					'archives'           => __( 'Archivo de artículos', 'revistalogos-core' ),
				),
				'description'  => __( 'Contenido académico publicado: artículos, ensayos, reseñas y editoriales.', 'revistalogos-core' ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-media-document',
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
				'has_archive'  => true,
				'rewrite'      => array(
					'slug'       => 'revista/articulos',
					'with_front' => false,
				),
				'capability_type' => array( 'journal_article', 'journal_articles' ),
				'map_meta_cap' => true,
			)
		);

		register_post_type(
			self::AUTHOR,
			array(
				'labels'       => array(
					'name'               => __( 'Autores', 'revistalogos-core' ),
					'singular_name'      => __( 'Autor', 'revistalogos-core' ),
					'add_new'            => __( 'Añadir autor', 'revistalogos-core' ),
					'add_new_item'       => __( 'Añadir nuevo autor', 'revistalogos-core' ),
					'edit_item'          => __( 'Editar autor', 'revistalogos-core' ),
					'new_item'           => __( 'Nuevo autor', 'revistalogos-core' ),
					'view_item'          => __( 'Ver autor', 'revistalogos-core' ),
					'search_items'       => __( 'Buscar autores', 'revistalogos-core' ),
					'not_found'          => __( 'No se encontraron autores.', 'revistalogos-core' ),
					'not_found_in_trash' => __( 'No hay autores en la papelera.', 'revistalogos-core' ),
					'all_items'          => __( 'Todos los autores', 'revistalogos-core' ),
					'archives'           => __( 'Archivo de autores', 'revistalogos-core' ),
				),
				'description'  => __( 'Perfiles públicos de autor, reutilizables entre artículos. No son cuentas de usuario (ADR 0013 §7).', 'revistalogos-core' ),
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-groups',
				'supports'     => array( 'title', 'thumbnail' ),
				'has_archive'  => true,
				'query_var'    => self::AUTHOR_QUERY_VAR,
				'rewrite'      => array(
					'slug'       => 'revista/autores',
					'with_front' => false,
				),
				'capability_type' => array( 'journal_author', 'journal_authors' ),
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Articles use the classic editor so author checkboxes, the PDF
	 * picker and the publish-author rule run on the same post.php save.
	 * Issues keep the block editor; their PDF picker still works as a
	 * classic meta box.
	 *
	 * @param bool   $use       Current value.
	 * @param string $post_type Post type.
	 * @return bool
	 */
	public static function use_block_editor( $use, $post_type ) {
		if ( self::ARTICLE === $post_type ) {
			return false;
		}

		return $use;
	}
}
