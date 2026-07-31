<?php
/**
 * Taxonomies: section (hierarchical), article_type, keyword. The optional
 * philosopher taxonomy stays unregistered until a binding decision
 * activates it (docs/03 §5).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers article taxonomies and seeds the approved initial terms.
 */
class Taxonomies {

	const SECTION      = 'section';
	const ARTICLE_TYPE = 'article_type';
	const KEYWORD      = 'keyword';

	/**
	 * Approved initial section terms (docs/03 §5).
	 *
	 * @var string[]
	 */
	const SECTION_TERMS = array(
		'Metafísica',
		'Ética',
		'Epistemología',
		'Filosofía de la Religión',
		'Filosofía Política',
		'Lógica',
		'Historia de la Filosofía',
		'Otros',
	);

	/**
	 * Canonical stored article_type values mapped to approved Spanish
	 * admin labels (docs/03 §5; canonical values stay in English).
	 *
	 * @var array<string, string>
	 */
	const ARTICLE_TYPE_TERMS = array(
		'article'   => 'Artículo',
		'essay'     => 'Ensayo',
		'review'    => 'Reseña',
		'editorial' => 'Editorial',
	);

	/**
	 * Register the taxonomies. Hooked on init after the CPTs.
	 */
	public static function register() {
		register_taxonomy(
			self::SECTION,
			Content_Types::ARTICLE,
			array(
				'labels'       => array(
					'name'          => __( 'Secciones', 'revistalogos-core' ),
					'singular_name' => __( 'Sección', 'revistalogos-core' ),
					'search_items'  => __( 'Buscar secciones', 'revistalogos-core' ),
					'all_items'     => __( 'Todas las secciones', 'revistalogos-core' ),
					'edit_item'     => __( 'Editar sección', 'revistalogos-core' ),
					'add_new_item'  => __( 'Añadir nueva sección', 'revistalogos-core' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'       => 'revista/seccion',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::ARTICLE_TYPE,
			Content_Types::ARTICLE,
			array(
				'labels'       => array(
					'name'          => __( 'Tipos', 'revistalogos-core' ),
					'singular_name' => __( 'Tipo', 'revistalogos-core' ),
					'search_items'  => __( 'Buscar tipos', 'revistalogos-core' ),
					'all_items'     => __( 'Todos los tipos', 'revistalogos-core' ),
					'edit_item'     => __( 'Editar tipo', 'revistalogos-core' ),
					'add_new_item'  => __( 'Añadir nuevo tipo', 'revistalogos-core' ),
				),
				'hierarchical' => false,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'       => 'revista/tipo',
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			self::KEYWORD,
			Content_Types::ARTICLE,
			array(
				'labels'       => array(
					'name'          => __( 'Palabras clave', 'revistalogos-core' ),
					'singular_name' => __( 'Palabra clave', 'revistalogos-core' ),
					'search_items'  => __( 'Buscar palabras clave', 'revistalogos-core' ),
					'all_items'     => __( 'Todas las palabras clave', 'revistalogos-core' ),
					'edit_item'     => __( 'Editar palabra clave', 'revistalogos-core' ),
					'add_new_item'  => __( 'Añadir nueva palabra clave', 'revistalogos-core' ),
				),
				'hierarchical' => false,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => array(
					'slug'       => 'revista/palabra-clave',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Insert the approved initial terms. Idempotent: existing terms are
	 * left untouched. Runs on activation and upgrade only.
	 */
	public static function insert_initial_terms() {
		foreach ( self::SECTION_TERMS as $name ) {
			if ( ! term_exists( $name, self::SECTION ) ) {
				wp_insert_term( $name, self::SECTION );
			}
		}

		foreach ( self::ARTICLE_TYPE_TERMS as $slug => $label ) {
			if ( ! term_exists( $slug, self::ARTICLE_TYPE ) ) {
				wp_insert_term( $label, self::ARTICLE_TYPE, array( 'slug' => $slug ) );
			}
		}
	}
}
