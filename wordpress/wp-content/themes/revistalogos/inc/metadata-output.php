<?php
/**
 * Fase 3 discoverability metadata (prompt §14 Phase 12): Highwire
 * citation_* tags, Schema.org JSON-LD and minimal Open Graph output.
 * Values are only emitted when they exist — nothing is invented.
 * ORCID sameAs and DOI routes are Fase 4 (ADR 0013) and absent.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta description + Open Graph + canonical (where core does not emit
 * one). Runs once in wp_head.
 */
function revistalogos_head_metadata() {
	$description = '';
	$og_type     = 'website';
	$og_url      = '';

	if ( is_front_page() ) {
		$description = get_bloginfo( 'description' );
		$og_url      = home_url( '/' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();

		if ( $post instanceof WP_Post ) {
			if ( has_excerpt( $post ) ) {
				$description = wp_strip_all_tags( get_the_excerpt( $post ) );
			} elseif ( 'article' === $post->post_type ) {
				$description = wp_trim_words( (string) get_post_meta( $post->ID, 'abstract', true ), 40 );
			} else {
				$description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 );
			}

			$og_type = in_array( $post->post_type, array( 'article', 'post' ), true ) ? 'article' : 'website';
			$og_url  = get_permalink( $post );
		}
	} elseif ( is_post_type_archive() || is_tax() || is_home() ) {
		$og_url = get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ) );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_get_document_title() ) );
	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( $og_type ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:locale" content="es_ES">' . "\n" );

	if ( $og_url ) {
		printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $og_url ) );
	}

	if ( is_singular() && has_post_thumbnail() ) {
		$image = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' );
		if ( $image ) {
			printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		}
	}

	// Core emits rel=canonical for singular views only; cover the front
	// page and archive views without duplicating it.
	if ( ! is_singular() && $og_url ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $og_url ) );
	}
}
add_action( 'wp_head', 'revistalogos_head_metadata', 5 );

/**
 * Highwire citation_* tags for Google Scholar on single articles
 * (docs/03 §4). Only fields with real values are output; citation_doi
 * is not in the approved Fase 3 list and is not emitted.
 */
function revistalogos_highwire_metadata() {
	if ( ! is_singular( 'article' ) ) {
		return;
	}

	$article_id = get_queried_object_id();

	$tags = array(
		'citation_title'         => get_the_title( $article_id ),
		'citation_journal_title' => 'Revista de Filosofía LOGO ET SPES',
	);

	$authors = revistalogos_article_authors( $article_id );

	$pub_date = get_post_meta( $article_id, 'publication_date', true );
	if ( $pub_date ) {
		$tags['citation_publication_date'] = str_replace( '-', '/', $pub_date );
	}

	$issue = revistalogos_article_issue( $article_id );
	if ( $issue ) {
		$volume = absint( get_post_meta( $issue->ID, 'volume_number', true ) );
		$number = absint( get_post_meta( $issue->ID, 'issue_number', true ) );

		if ( $volume ) {
			$tags['citation_volume'] = (string) $volume;
		}
		if ( $number ) {
			$tags['citation_issue'] = (string) $number;
		}
	}

	$pages = (string) get_post_meta( $article_id, 'pages', true );
	if ( $pages && false !== strpos( $pages, '-' ) ) {
		list( $first, $last )       = array_map( 'trim', explode( '-', $pages, 2 ) );
		$tags['citation_firstpage'] = $first;
		$tags['citation_lastpage']  = $last;
	}

	$pdf_url = revistalogos_meta_attachment_url( $article_id );
	if ( $pdf_url ) {
		$tags['citation_pdf_url'] = $pdf_url;
	}

	$language = get_post_meta( $article_id, 'language', true );
	if ( $language ) {
		$tags['citation_language'] = $language;
	}

	printf( '<meta name="citation_title" content="%s">' . "\n", esc_attr( $tags['citation_title'] ) );

	foreach ( $authors as $author ) {
		printf( '<meta name="citation_author" content="%s">' . "\n", esc_attr( get_the_title( $author ) ) );
	}

	unset( $tags['citation_title'] );

	foreach ( $tags as $name => $value ) {
		if ( '' !== $value ) {
			printf( '<meta name="%s" content="%s">' . "\n", esc_attr( $name ), esc_attr( $value ) );
		}
	}
}
add_action( 'wp_head', 'revistalogos_highwire_metadata', 6 );

/**
 * Schema.org JSON-LD: Periodical + Organization on the front page,
 * PublicationIssue on issues, ScholarlyArticle on articles. Unknown
 * properties are omitted; identifiers never invented (ADR 0004).
 */
function revistalogos_schema_metadata() {
	$schema = null;

	if ( is_front_page() ) {
		$schema = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type' => 'Periodical',
					'name'  => 'Revista de Filosofía LOGO ET SPES',
					'url'   => home_url( '/' ),
					'inLanguage' => 'es',
					'publisher'  => array(
						'@type' => 'Organization',
						'name'  => 'Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS',
						'url'   => 'https://cenfiss.net',
					),
				),
			),
		);
	} elseif ( is_singular( 'issue' ) ) {
		$issue_id = get_queried_object_id();

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'PublicationIssue',
			'name'       => get_the_title( $issue_id ),
			'url'        => get_permalink( $issue_id ),
			'inLanguage' => 'es',
			'isPartOf'   => array(
				'@type' => 'Periodical',
				'name'  => 'Revista de Filosofía LOGO ET SPES',
				'url'   => home_url( '/' ),
			),
		);

		$number = absint( get_post_meta( $issue_id, 'issue_number', true ) );
		if ( $number ) {
			$schema['issueNumber'] = (string) $number;
		}

		$date = get_post_meta( $issue_id, 'date_published', true );
		if ( $date ) {
			$schema['datePublished'] = $date;
		}
	} elseif ( is_singular( 'article' ) ) {
		$article_id = get_queried_object_id();

		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'ScholarlyArticle',
			'headline'   => get_the_title( $article_id ),
			'url'        => get_permalink( $article_id ),
			'inLanguage' => get_post_meta( $article_id, 'language', true ) ? get_post_meta( $article_id, 'language', true ) : 'es',
		);

		$authors_schema = array();
		foreach ( revistalogos_article_authors( $article_id ) as $author ) {
			// No ORCID sameAs in Fase 3 (ADR 0013).
			$authors_schema[] = array(
				'@type' => 'Person',
				'name'  => get_the_title( $author ),
			);
		}
		if ( $authors_schema ) {
			$schema['author'] = $authors_schema;
		}

		$abstract = get_post_meta( $article_id, 'abstract', true );
		if ( $abstract ) {
			$schema['abstract'] = $abstract;
		}

		$pub_date = get_post_meta( $article_id, 'publication_date', true );
		if ( $pub_date ) {
			$schema['datePublished'] = $pub_date;
		}

		$pages = (string) get_post_meta( $article_id, 'pages', true );
		if ( $pages ) {
			$schema['pagination'] = $pages;
		}

		$issue = revistalogos_article_issue( $article_id );
		if ( $issue ) {
			$schema['isPartOf'] = array(
				'@type' => 'PublicationIssue',
				'name'  => get_the_title( $issue ),
				'url'   => get_permalink( $issue ),
			);
		}
	}

	if ( null !== $schema ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}
add_action( 'wp_head', 'revistalogos_schema_metadata', 7 );
