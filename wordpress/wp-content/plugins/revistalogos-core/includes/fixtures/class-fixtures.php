<?php
/**
 * Fixture system (prompt §14 Phase 7, ADR 0004) plus Volume 1 editorial
 * bootstrap. Entirely separate from the institutional migration.
 *
 * Test fixtures (disposable demo): _les_fixture = 1, kind demo, fake
 * identifiers 1234-5678 / 10.1234/les.* / 0000-0000-*. Teardown deletes
 * them. Never run the demo seed on production.
 *
 * Editorial bootstrap (Volume 1): _les_bootstrap = 1 with a stable key
 * and source hash. Objects are normal editable posts expected to become
 * the real first issue through wp-admin. Teardown never deletes adopted
 * or manual objects, never deletes the canonical author, and never
 * touches institutional Pages. No fake DOI/ORCID/ISSN.
 *
 * Canonical migrated content never carries either marker.
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

	const BOOTSTRAP_MARKER      = '_les_bootstrap';
	const BOOTSTRAP_KEY         = '_les_bootstrap_key';
	const BOOTSTRAP_KIND        = '_les_bootstrap_kind';
	const BOOTSTRAP_VERSION     = '_les_bootstrap_version';
	const BOOTSTRAP_SOURCE_HASH = '_les_bootstrap_source_hash';
	const BOOTSTRAP_ADOPTED     = '_les_bootstrap_adopted';

	const BOOTSTRAP_KIND_VOLUME_1 = 'volume-1';
	const BOOTSTRAP_VERSION_VALUE = '1';

	const CANONICAL_AUTHOR_SLUG = 'rafael-eduardo-figueredo-oropeza';

	const VOLUME1_ISSUE_KEY     = 'volume-1-issue-1';
	const VOLUME1_EDITORIAL_KEY = 'volume-1-editorial';
	const VOLUME1_COVER_KEY     = 'volume-1-media-cover';
	const VOLUME1_ISSUE_PDF_KEY = 'volume-1-media-issue-pdf';
	const VOLUME1_ARTICLE_PDF_KEY = 'volume-1-media-article-pdf';

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
	 * Volume 1 editorial bootstrap. Creates one published issue and the
	 * sample-structure articles as normal editable objects. Reuses the
	 * canonical author by slug; never creates, marks or deletes that
	 * author. Idempotent by bootstrap key. Never overwrites adopted or
	 * existing objects. No fake DOI/ORCID/ISSN.
	 *
	 * @param bool   $apply       Write when true.
	 * @param string $author_slug Canonical author post_name.
	 * @return string[]|\WP_Error Report lines, or error on fail-safe.
	 */
	public static function bootstrap( $apply, $author_slug = self::CANONICAL_AUTHOR_SLUG ) {
		$author = self::resolve_canonical_author( $author_slug );

		if ( is_wp_error( $author ) ) {
			return $author;
		}

		$dataset = self::volume1_dataset();
		$preflight = self::bootstrap_preflight( $dataset, $author );

		if ( is_wp_error( $preflight ) ) {
			return $preflight;
		}

		Relationships::$skip_article_publish_guard = true;

		try {
			return self::bootstrap_write( $apply, $author, $dataset );
		} finally {
			Relationships::$skip_article_publish_guard = false;
		}
	}

	/**
	 * Write path for Volume 1 bootstrap. Caller owns the publish-guard skip flag.
	 *
	 * @param bool     $apply   Write when true.
	 * @param \WP_Post $author  Canonical author.
	 * @param array    $dataset Volume 1 dataset.
	 * @return string[]|\WP_Error
	 */
	private static function bootstrap_write( $apply, $author, $dataset ) {
		$report   = array();
		$report[] = sprintf(
			'author: reuse id %d slug %s (manual; not bootstrap-owned)',
			$author->ID,
			$author->post_name
		);

		$ids = array();

		foreach ( self::volume1_media_dataset() as $key => $cfg ) {
			$media_id = self::seed_bootstrap_media( $key, $cfg[0], $cfg[1], $cfg[2], $apply, $report );

			if ( is_wp_error( $media_id ) ) {
				return $media_id;
			}

			$ids[ $key ] = $media_id;
		}

		$issue_meta = array(
			'volume_number'  => 1,
			'issue_number'   => 1,
			'year'           => '',
			'date_published' => '',
			'issn'           => '',
			'doi'            => '',
		);

		if ( ! empty( $ids[ self::VOLUME1_ISSUE_PDF_KEY ] ) ) {
			$issue_meta['pdf_file'] = $ids[ self::VOLUME1_ISSUE_PDF_KEY ];
		}

		$issue_id = self::seed_bootstrap_post(
			self::VOLUME1_ISSUE_KEY,
			array(
				'post_type'    => Content_Types::ISSUE,
				'post_title'   => $dataset['issue']['title'],
				'post_name'    => $dataset['issue']['slug'],
				'post_status'  => 'publish',
				'post_content' => $dataset['issue']['content'],
			),
			$issue_meta,
			array(),
			$apply,
			$report,
			isset( $ids[ self::VOLUME1_COVER_KEY ] ) ? $ids[ self::VOLUME1_COVER_KEY ] : 0
		);

		if ( is_wp_error( $issue_id ) ) {
			return $issue_id;
		}

		foreach ( $dataset['articles'] as $article ) {
			$author_ids = ! empty( $article['link_rafael'] ) ? array( (int) $author->ID ) : array();
			$meta       = array(
				'title_en'         => $article['title_en'],
				'abstract'         => $article['abstract'],
				'abstract_en'      => '',
				'doi'              => '',
				'pages'            => $article['pages'],
				'language'         => 'es',
				'publication_date' => '',
				'received_date'    => '',
				'accepted_date'    => '',
				'authors'          => $author_ids,
				'issue'            => $issue_id ? $issue_id : 0,
			);

			if ( ! empty( $article['pdf'] ) && ! empty( $ids[ self::VOLUME1_ARTICLE_PDF_KEY ] ) ) {
				$meta['pdf_file'] = $ids[ self::VOLUME1_ARTICLE_PDF_KEY ];
			}

			$taxonomies = array(
				Taxonomies::ARTICLE_TYPE => array( $article['type'] ),
			);

			if ( $article['section'] ) {
				$taxonomies[ Taxonomies::SECTION ] = array( $article['section'] );
			}

			if ( ! empty( $article['keywords'] ) ) {
				$taxonomies[ Taxonomies::KEYWORD ] = $article['keywords'];
			}

			$created = self::seed_bootstrap_post(
				$article['key'],
				array(
					'post_type'    => Content_Types::ARTICLE,
					'post_title'   => $article['title'],
					'post_name'    => $article['slug'],
					'post_status'  => 'publish',
					'post_content' => $article['content'],
					'menu_order'   => $article['menu_order'],
				),
				$meta,
				$taxonomies,
				$apply,
				$report
			);

			if ( is_wp_error( $created ) ) {
				return $created;
			}
		}

		return $report;
	}

	/**
	 * Read-only Volume 1 plan (same as bootstrap dry-run).
	 *
	 * @param string $author_slug Canonical author post_name.
	 * @return string[]|\WP_Error
	 */
	public static function plan( $author_slug = self::CANONICAL_AUTHOR_SLUG ) {
		return self::bootstrap( false, $author_slug );
	}

	/**
	 * Static-maquette Volume 1 dataset (owner Option 2, 2026-08-19).
	 * Titles/abstracts/sections/order come from static/ Vol. 12 Nº 2 as
	 * bootstrap-owned placeholders, retargeted to Vol. 1 Nº 1. Dummy
	 * author identities, fake DOI/ORCID/ISSN and bibliographic page
	 * ranges are not used.
	 *
	 * @return array<string, mixed>
	 */
	private static function volume1_dataset() {
		$placeholder_body = 'Contenido placeholder del Volume 1. Sustituir en wp-admin por el texto editorial real.';

		return array(
			'issue'    => array(
				'title'   => 'Filosofía Contemporánea: Nuevas Perspectivas',
				'slug'    => 'vol-1-n-1',
				'content' => 'Este número presenta una selección de artículos que abordan temas fundamentales de la filosofía contemporánea, desde la metafísica hasta la ética aplicada. Los trabajos incluidos reflejan la diversidad y riqueza del pensamiento filosófico actual, ofreciendo nuevas perspectivas sobre problemas clásicos y emergentes en el campo de la filosofía.',
			),
			'articles' => array(
				array(
					'key'         => self::VOLUME1_EDITORIAL_KEY,
					'title'       => 'Editorial',
					'slug'        => 'editorial-vol-1-n-1',
					'type'        => 'editorial',
					'section'     => null,
					'title_en'    => '',
					'abstract'    => '',
					'pages'       => '',
					'keywords'    => array(),
					'menu_order'  => 0,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => "Es un honor presentar este nuevo número de Logos et Spes, que continúa con nuestra tradición de publicar investigaciones rigurosas y originales en el campo de la filosofía. Los seis artículos que componen este volumen abordan cuestiones fundamentales que han ocupado a los filósofos a lo largo de la historia, pero desde perspectivas contemporáneas que reflejan los desafíos y oportunidades de nuestro tiempo.\n\nLa diversidad temática de este número es particularmente notable. Desde las reflexiones metafísicas sobre la naturaleza del ser hasta los análisis éticos sobre la responsabilidad social, pasando por las consideraciones epistemológicas sobre el conocimiento en la era digital y las exploraciones en filosofía de la religión, cada contribución ofrece una perspectiva única y valiosa.",
				),
				array(
					'key'         => 'volume-1-article-1',
					'title'       => 'La naturaleza del ser en la filosofía contemporánea',
					'slug'        => 'la-naturaleza-del-ser-en-la-filosofia-contemporanea',
					'type'        => 'article',
					'section'     => 'Metafísica',
					'title_en'    => 'The Nature of Being in Contemporary Philosophy',
					'abstract'    => 'Este artículo examina las principales corrientes del pensamiento ontológico contemporáneo, analizando las contribuciones de Heidegger, Sartre y otros filósofos modernos al problema del ser. Se propone una síntesis crítica que permita comprender la evolución del concepto de ser en la filosofía del siglo XX.',
					'pages'       => '',
					'keywords'    => array( 'ontología', 'ser', 'Heidegger', 'Sartre', 'filosofía contemporánea' ),
					'menu_order'  => 1,
					'link_rafael' => true,
					'pdf'         => true,
					'content'     => $placeholder_body,
				),
				array(
					'key'         => 'volume-1-article-2',
					'title'       => 'Fundamentos de la ética aplicada en el siglo XXI',
					'slug'        => 'fundamentos-de-la-etica-aplicada-en-el-siglo-xxi',
					'type'        => 'article',
					'section'     => 'Ética',
					'title_en'    => 'Foundations of Applied Ethics in the 21st Century',
					'abstract'    => 'Este trabajo analiza los fundamentos teóricos de la ética aplicada en el contexto del siglo XXI, considerando los nuevos desafíos morales planteados por la tecnología, la globalización y los cambios sociales. Se examinan las principales corrientes éticas contemporáneas y su aplicación práctica.',
					'pages'       => '',
					'keywords'    => array( 'ética aplicada', 'moral', 'tecnología', 'globalización', 'responsabilidad' ),
					'menu_order'  => 2,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => $placeholder_body,
				),
				array(
					'key'         => 'volume-1-article-3',
					'title'       => 'Justicia distributiva y responsabilidad social',
					'slug'        => 'justicia-distributiva-y-responsabilidad-social',
					'type'        => 'article',
					'section'     => 'Ética',
					'title_en'    => 'Distributive Justice and Social Responsibility',
					'abstract'    => 'Este artículo explora la relación entre justicia distributiva y responsabilidad social en el marco de las sociedades contemporáneas. Se analizan las teorías de Rawls, Sen y otros pensadores para comprender cómo se puede lograr una distribución justa de recursos y oportunidades.',
					'pages'       => '',
					'keywords'    => array( 'justicia distributiva', 'responsabilidad social', 'Rawls', 'Sen', 'equidad' ),
					'menu_order'  => 3,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => $placeholder_body,
				),
				array(
					'key'         => 'volume-1-article-4',
					'title'       => 'El problema del conocimiento en la era digital',
					'slug'        => 'el-problema-del-conocimiento-en-la-era-digital',
					'type'        => 'article',
					'section'     => 'Epistemología',
					'title_en'    => 'The Problem of Knowledge in the Digital Age',
					'abstract'    => 'Este trabajo examina cómo la revolución digital ha transformado nuestra comprensión del conocimiento y la verdad. Se analizan los desafíos epistemológicos planteados por la información digital, las redes sociales y la inteligencia artificial.',
					'pages'       => '',
					'keywords'    => array( 'epistemología', 'conocimiento digital', 'verdad', 'información', 'tecnología' ),
					'menu_order'  => 4,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => $placeholder_body,
				),
				array(
					'key'         => 'volume-1-article-5',
					'title'       => 'Secularización y experiencia religiosa en la modernidad',
					'slug'        => 'secularizacion-y-experiencia-religiosa-en-la-modernidad',
					'type'        => 'article',
					'section'     => 'Filosofía de la Religión',
					'title_en'    => 'Secularization and Religious Experience in Modernity',
					'abstract'    => 'Este artículo analiza el proceso de secularización en las sociedades modernas y su impacto en la experiencia religiosa. Se examinan las teorías de Weber, Berger y otros sociólogos de la religión para comprender la persistencia de lo religioso en contextos secularizados.',
					'pages'       => '',
					'keywords'    => array( 'secularización', 'experiencia religiosa', 'modernidad', 'Weber', 'Berger' ),
					'menu_order'  => 5,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => $placeholder_body,
				),
				array(
					'key'         => 'volume-1-article-6',
					'title'       => 'Teodicea y el problema del mal en el pensamiento contemporáneo',
					'slug'        => 'teodicea-y-el-problema-del-mal-en-el-pensamiento-contemporaneo',
					'type'        => 'article',
					'section'     => 'Filosofía de la Religión',
					'title_en'    => 'Theodicy and the Problem of Evil in Contemporary Thought',
					'abstract'    => 'Este trabajo examina las respuestas contemporáneas al problema del mal desde la perspectiva de la teodicea. Se analizan las contribuciones de Plantinga, Hick y otros filósofos de la religión para comprender cómo se puede mantener la creencia en un Dios omnibenevolente frente a la existencia del mal.',
					'pages'       => '',
					'keywords'    => array( 'teodicea', 'problema del mal', 'Dios', 'Plantinga', 'Hick', 'omnibenevolencia' ),
					'menu_order'  => 6,
					'link_rafael' => false,
					'pdf'         => false,
					'content'     => $placeholder_body,
				),
			),
		);
	}

	/**
	 * Inspect the canonical Author CPT by slug. Never creates, marks or
	 * deletes that author. Fail-safe on 0 or >1 matches and on fixture
	 * or bootstrap markers.
	 *
	 * @param string $slug Author post_name.
	 * @return array<string, mixed>
	 */
	public static function inspect_canonical_author( $slug = self::CANONICAL_AUTHOR_SLUG ) {
		$slug = sanitize_title( (string) $slug );
		$result = array(
			'slug'    => $slug,
			'count'   => 0,
			'matches' => array(),
			'author'  => null,
			'pass'    => false,
			'code'    => 'missing',
			'errors'  => array(),
		);

		if ( '' === $slug ) {
			$result['code']     = 'empty';
			$result['errors'][] = 'Author slug is empty.';
			return $result;
		}

		$posts = get_posts(
			array(
				'post_type'      => Content_Types::AUTHOR,
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'numberposts'    => -1,
				'no_found_rows'  => true,
			)
		);

		$result['count']   = count( $posts );
		$result['matches'] = $posts;

		if ( count( $posts ) > 1 ) {
			$ids            = implode( ', ', wp_list_pluck( $posts, 'ID' ) );
			$result['code'] = 'ambiguous';
			$result['errors'][] = sprintf(
				'Ambiguous author slug %s (ids %s). Refusing to choose. Do not create another duplicate.',
				$slug,
				$ids
			);
			return $result;
		}

		if ( ! $posts ) {
			$result['code']     = 'missing';
			$result['errors'][] = sprintf(
				'No Author CPT with slug %s. Create that author manually (not as a bootstrap object), then re-run. Bootstrap never creates this author.',
				$slug
			);
			return $result;
		}

		$author = $posts[0];
		$result['author'] = $author;

		if ( Content_Types::AUTHOR !== $author->post_type ) {
			$result['code']     = 'wrong_type';
			$result['errors'][] = sprintf( 'Canonical author id %d is not post_type=author.', $author->ID );
			return $result;
		}

		if ( '1' === (string) get_post_meta( $author->ID, self::BOOTSTRAP_MARKER, true )
			|| '1' === (string) get_post_meta( $author->ID, self::MARKER, true ) ) {
			$result['code']     = 'marked';
			$result['errors'][] = sprintf(
				'Canonical author id %d slug %s carries a fixture or bootstrap marker. Refusing to proceed; resolve manually. Do not create a duplicate.',
				$author->ID,
				$author->post_name
			);
			return $result;
		}

		$result['pass'] = true;
		$result['code'] = 'ok';
		return $result;
	}

	/**
	 * Find the canonical Author CPT by slug. Fail-safe on 0 or >1 matches.
	 *
	 * @param string $slug Author post_name.
	 * @return \WP_Post|\WP_Error
	 */
	public static function resolve_canonical_author( $slug ) {
		$inspection = self::inspect_canonical_author( $slug );

		if ( $inspection['pass'] ) {
			return $inspection['author'];
		}

		return new \WP_Error( 'author_' . $inspection['code'], implode( ' ', $inspection['errors'] ) );
	}

	/**
	 * Read-only structured Volume 1 plan for the temporary admin screen.
	 * Writes nothing. Uses the same author, collision and dataset rules
	 * as Fixtures::plan() / bootstrap().
	 *
	 * @param string $author_slug Canonical author post_name.
	 * @return array<string, mixed>
	 */
	public static function bootstrap_plan_state( $author_slug = self::CANONICAL_AUTHOR_SLUG ) {
		$author_slug = sanitize_title( (string) $author_slug );
		$author_gate = self::inspect_canonical_author( $author_slug );
		$dataset     = self::volume1_dataset();
		$objects     = self::bootstrap_object_rows( $dataset, $author_gate );
		$collisions  = array();
		$lines       = array();
		$can_apply   = false;

		if ( $author_gate['pass'] ) {
			$collisions = self::bootstrap_preflight_errors( $dataset, $author_gate['author'] );

			if ( ! $collisions ) {
				$planned = self::bootstrap( false, $author_slug );

				if ( is_wp_error( $planned ) ) {
					$collisions[] = $planned->get_error_message();
				} else {
					$lines     = $planned;
					$can_apply = true;
				}
			}
		} else {
			$collisions = $author_gate['errors'];
		}

		$gate = ( $author_gate['pass'] && ! $collisions && $can_apply ) ? 'PASS' : 'BLOCKED';

		return array(
			'author'     => $author_gate,
			'objects'    => $objects,
			'collisions' => $collisions,
			'lines'      => $lines,
			'can_apply'  => $can_apply,
			'gate'       => $gate,
			'source'     => 'static Vol. 12 Nº 2 maquette (Option 2), retargeted to Vol. 1 Nº 1',
		);
	}

	/**
	 * Structured verify for the temporary admin screen. Reuses
	 * Fixtures::verify() and adds public URLs and relationship rows.
	 *
	 * @param string $author_slug Canonical author post_name.
	 * @return array<string, mixed>
	 */
	public static function bootstrap_verify_state( $author_slug = self::CANONICAL_AUTHOR_SLUG ) {
		$verify      = self::verify();
		$author_gate = self::inspect_canonical_author( $author_slug );
		$dataset     = self::volume1_dataset();
		$issue       = self::find_bootstrap( self::VOLUME1_ISSUE_KEY );
		$articles    = array();

		foreach ( $dataset['articles'] as $article ) {
			$post     = self::find_bootstrap( $article['key'] );
			$issue_id = $post ? absint( get_post_meta( $post->ID, 'issue', true ) ) : 0;
			$authors  = $post ? get_post_meta( $post->ID, 'authors', true ) : array();
			$pages    = $post ? (string) get_post_meta( $post->ID, 'pages', true ) : '';

			$articles[] = array(
				'key'        => $article['key'],
				'slug'       => $article['slug'],
				'found'      => (bool) $post,
				'post_id'    => $post ? (int) $post->ID : 0,
				'url'        => $post ? get_permalink( $post ) : home_url( user_trailingslashit( 'revista/articulos/' . $article['slug'] ) ),
				'issue_id'   => $issue_id,
				'authors'    => is_array( $authors ) ? array_map( 'absint', $authors ) : array(),
				'link_rafael'=> ! empty( $article['link_rafael'] ),
				'pages'      => $pages,
				'adopted'    => $post ? self::is_adopted( $post->ID ) : false,
			);
		}

		$urls = array(
			home_url( user_trailingslashit( 'revista/numeros' ) ),
			home_url( user_trailingslashit( 'revista/articulos' ) ),
			home_url( user_trailingslashit( 'revista/autores' ) ),
		);

		if ( $issue ) {
			$urls[] = get_permalink( $issue );
		}

		foreach ( $articles as $row ) {
			$urls[] = $row['url'];
		}

		if ( $author_gate['author'] ) {
			$urls[] = get_permalink( $author_gate['author'] );
		}

		return array(
			'verify'     => $verify,
			'author'     => $author_gate,
			'issue'      => $issue,
			'articles'   => $articles,
			'urls'       => array_values( array_unique( array_filter( $urls ) ) ),
			'pass'       => 0 === (int) $verify['failures'],
		);
	}

	/**
	 * Placeholder media sideloaded for Volume 1 (source file, dest name, title).
	 *
	 * @return array<string, array{0:string,1:string,2:string}>
	 */
	private static function volume1_media_dataset() {
		return array(
			self::VOLUME1_COVER_KEY       => array( 'portada-ejemplo.jpg', 'vol-1-n-1-portada.jpg', 'Portada placeholder Vol. 1 Nº 1' ),
			self::VOLUME1_ISSUE_PDF_KEY   => array( 'numero-v12n2-2025.pdf', 'vol-1-n-1.pdf', 'PDF placeholder del número Vol. 1 Nº 1' ),
			self::VOLUME1_ARTICLE_PDF_KEY => array( 'articulo-01.pdf', 'vol-1-articulo-01.pdf', 'PDF placeholder del artículo 1' ),
		);
	}

	/**
	 * Classify each Volume 1 target for the admin plan table.
	 *
	 * @param array $dataset     Volume 1 dataset.
	 * @param array $author_gate inspect_canonical_author() result.
	 * @return array<int, array<string, mixed>>
	 */
	private static function bootstrap_object_rows( $dataset, $author_gate ) {
		$rows   = array();
		$author = $author_gate['author'];

		$author_status = 'BLOCKED';
		$author_detail = implode( ' ', $author_gate['errors'] );

		if ( $author_gate['pass'] ) {
			$author_status = 'REUSE';
			$author_detail = sprintf(
				'manual Author CPT id %d; never created, marked or overwritten',
				$author->ID
			);
		} elseif ( 'ambiguous' === $author_gate['code'] ) {
			$author_status = 'CONFLICT';
		}

		$rows[] = array(
			'kind'      => 'author',
			'key'       => 'canonical-author',
			'title'     => 'Rafael Eduardo Figueredo Oropeza',
			'slug'      => $author_gate['slug'],
			'post_type' => Content_Types::AUTHOR,
			'path'      => user_trailingslashit( 'revista/autores/' . $author_gate['slug'] ),
			'status'    => $author_status,
			'post_id'   => $author ? (int) $author->ID : 0,
			'detail'    => $author_detail,
			'source'    => 'manual Author CPT (never bootstrap-owned)',
		);

		foreach ( self::volume1_media_dataset() as $key => $cfg ) {
			$rows[] = self::classify_bootstrap_target(
				'attachment',
				sanitize_title( pathinfo( $cfg[1], PATHINFO_FILENAME ) ),
				$key,
				$cfg[2],
				'media',
				'resources/fixtures/' . $cfg[0]
			);
		}

		$rows[] = self::classify_bootstrap_target(
			Content_Types::ISSUE,
			$dataset['issue']['slug'],
			self::VOLUME1_ISSUE_KEY,
			$dataset['issue']['title'],
			'issue',
			'static Vol. 12 Nº 2 maquette (Option 2)'
		);

		foreach ( $dataset['articles'] as $article ) {
			$rows[] = self::classify_bootstrap_target(
				Content_Types::ARTICLE,
				$article['slug'],
				$article['key'],
				$article['title'],
				'article',
				'static Vol. 12 Nº 2 maquette (Option 2)'
			);
		}

		return $rows;
	}

	/**
	 * Classify one bootstrap target as CREATE, REUSE, ADOPTED or CONFLICT.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Planned public slug.
	 * @param string $key       Bootstrap key.
	 * @param string $title     Planned title.
	 * @param string $kind      issue, article or media.
	 * @param string $source    Human-readable source.
	 * @return array<string, mixed>
	 */
	private static function classify_bootstrap_target( $post_type, $slug, $key, $title, $kind, $source ) {
		$owned    = self::find_bootstrap( $key );
		$path     = ( 'attachment' === $post_type )
			? $slug
			: user_trailingslashit(
				( Content_Types::ISSUE === $post_type ? 'revista/numeros/' : 'revista/articulos/' ) . $slug
			);
		$row      = array(
			'kind'      => $kind,
			'key'       => $key,
			'title'     => $title,
			'slug'      => $owned ? $owned->post_name : $slug,
			'post_type' => $post_type,
			'path'      => $path,
			'status'    => 'CREATE',
			'post_id'   => 0,
			'detail'    => 'would create',
			'source'    => $source,
		);

		if ( $owned ) {
			$row['post_id'] = (int) $owned->ID;
			$row['slug']    = $owned->post_name;
			$owned_kind     = (string) get_post_meta( $owned->ID, self::BOOTSTRAP_KIND, true );

			if ( '' !== $owned_kind && self::BOOTSTRAP_KIND_VOLUME_1 !== $owned_kind ) {
				$row['status'] = 'CONFLICT';
				$row['detail'] = sprintf( 'bootstrap key %s has unexpected kind %s', $key, $owned_kind );
				return $row;
			}

			if ( self::is_adopted( $owned->ID ) ) {
				$row['status'] = 'ADOPTED';
				$row['detail'] = sprintf( 'id %d adopted; left untouched', $owned->ID );
				return $row;
			}

			$row['status'] = 'REUSE';
			$row['detail'] = sprintf( 'id %d exists; skip overwrite', $owned->ID );
			return $row;
		}

		$existing = get_posts(
			array(
				'post_type'      => $post_type,
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'numberposts'    => -1,
				'no_found_rows'  => true,
			)
		);

		foreach ( $existing as $post ) {
			$post_key = (string) get_post_meta( $post->ID, self::BOOTSTRAP_KEY, true );

			if ( $key === $post_key ) {
				continue;
			}

			$row['status']  = 'CONFLICT';
			$row['post_id'] = (int) $post->ID;
			$row['detail']  = sprintf(
				'slug %s already exists on %s id %d and is not bootstrap key %s',
				$slug,
				$post_type,
				$post->ID,
				$key
			);
			return $row;
		}

		return $row;
	}

	/**
	 * Collision messages for leftover disposable bootstrap, a manual
	 * Vol. 1 issue, or a slug owned by a non-bootstrap object.
	 *
	 * @param array    $dataset Volume 1 dataset.
	 * @param \WP_Post $author  Canonical author.
	 * @return string[]
	 */
	private static function bootstrap_preflight_errors( $dataset, $author ) {
		$errors = array();

		$issues = get_posts(
			array(
				'post_type'      => Content_Types::ISSUE,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'volume_number',
						'value' => '1',
					),
					array(
						'key'   => 'issue_number',
						'value' => '1',
					),
				),
				'no_found_rows'  => true,
			)
		);

		foreach ( $issues as $issue ) {
			$bootstrap_key = (string) get_post_meta( $issue->ID, self::BOOTSTRAP_KEY, true );
			$fixture_key   = (string) get_post_meta( $issue->ID, self::FIXTURE_KEY, true );

			if ( self::VOLUME1_ISSUE_KEY === $bootstrap_key ) {
				continue;
			}

			if ( self::BOOTSTRAP_ISSUE_KEY === $fixture_key ) {
				$errors[] = sprintf(
					'Existing issue id %d is leftover disposable bootstrap (fixture key %s). Run `wp revistalogos fixtures teardown --kind=bootstrap` before Volume 1 bootstrap.',
					$issue->ID,
					$fixture_key
				);
				continue;
			}

			$errors[] = sprintf(
				'Existing issue id %d slug %s is already Vol. 1 Nº 1 and is not Volume 1 bootstrap-owned. Refusing to overwrite or adopt it automatically.',
				$issue->ID,
				$issue->post_name
			);
		}

		$candidates = array(
			array( Content_Types::ISSUE, $dataset['issue']['slug'], self::VOLUME1_ISSUE_KEY ),
		);

		foreach ( $dataset['articles'] as $article ) {
			$candidates[] = array( Content_Types::ARTICLE, $article['slug'], $article['key'] );
		}

		foreach ( $candidates as $candidate ) {
			list( $post_type, $slug, $key ) = $candidate;
			$owned = self::find_bootstrap( $key );

			$existing = get_posts(
				array(
					'post_type'      => $post_type,
					'name'           => $slug,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'no_found_rows'  => true,
				)
			);

			foreach ( $existing as $post ) {
				$post_key = (string) get_post_meta( $post->ID, self::BOOTSTRAP_KEY, true );

				if ( $key === $post_key ) {
					continue;
				}

				$errors[] = sprintf(
					'Slug %s already exists on %s id %d and is not bootstrap key %s. Refusing to create a duplicate.',
					$slug,
					$post_type,
					$post->ID,
					$key
				);
			}

			unset( $owned );
		}

		if ( Content_Types::AUTHOR !== $author->post_type ) {
			$errors[] = sprintf( 'Canonical author id %d is not post_type=author.', $author->ID );
		}

		return $errors;
	}

	/**
	 * Wrap preflight errors for CLI/bootstrap apply.
	 *
	 * @param array    $dataset Volume 1 dataset.
	 * @param \WP_Post $author  Canonical author.
	 * @return true|\WP_Error
	 */
	private static function bootstrap_preflight( $dataset, $author ) {
		$errors = self::bootstrap_preflight_errors( $dataset, $author );

		if ( $errors ) {
			return new \WP_Error( 'bootstrap_collision', implode( ' ', $errors ) );
		}

		return true;
	}

	/**
	 * Find a Volume 1 bootstrap object by stable key.
	 *
	 * @param string $key Bootstrap key.
	 * @return \WP_Post|null
	 */
	private static function find_bootstrap( $key ) {
		$args = array(
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => self::BOOTSTRAP_KEY,
			'meta_value'     => $key,
			'no_found_rows'  => true,
		);

		$posts = get_posts( array_merge( $args, array( 'post_type' => 'any' ) ) );

		if ( $posts ) {
			return $posts[0];
		}

		$attachments = get_posts( array_merge( $args, array( 'post_type' => 'attachment' ) ) );

		return $attachments ? $attachments[0] : null;
	}

	/**
	 * Every Volume 1 bootstrap post/attachment ID.
	 *
	 * @return int[]
	 */
	public static function all_bootstrap_ids() {
		$args = array(
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => self::BOOTSTRAP_MARKER,
			'meta_value'     => '1',
			'no_found_rows'  => true,
		);

		$posts       = get_posts( array_merge( $args, array( 'post_type' => 'any' ) ) );
		$attachments = get_posts( array_merge( $args, array( 'post_type' => 'attachment' ) ) );

		return array_values( array_unique( array_map( 'absint', array_merge( $posts, $attachments ) ) ) );
	}

	/**
	 * Sideload one bootstrap-owned attachment. Idempotent by key.
	 *
	 * @param string $key         Bootstrap key.
	 * @param string $source_name File under resources/fixtures/.
	 * @param string $dest_name   Uploaded filename.
	 * @param string $title       Attachment title.
	 * @param bool   $apply       Write when true.
	 * @param array  $report      Report accumulator.
	 * @return int|\WP_Error Attachment ID (0 in dry-run for new objects).
	 */
	private static function seed_bootstrap_media( $key, $source_name, $dest_name, $title, $apply, &$report ) {
		$existing = self::find_bootstrap( $key );

		if ( $existing ) {
			self::refresh_adoption( $existing->ID, false );
			$state    = self::is_adopted( $existing->ID ) ? 'adopted' : 'exists';
			$report[] = "media $key: $state (id {$existing->ID})";
			return (int) $existing->ID;
		}

		if ( ! $apply ) {
			$report[] = "media $key: would import $dest_name";
			return 0;
		}

		$source = REVISTALOGOS_CORE_DIR . 'resources/fixtures/' . $source_name;

		if ( ! file_exists( $source ) ) {
			return new \WP_Error( 'bootstrap_media_missing', "media $key: seed file missing ($source_name)" );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = wp_tempnam( $dest_name );
		copy( $source, $tmp );

		$attachment_id = media_handle_sideload(
			array(
				'name'     => $dest_name,
				'tmp_name' => $tmp,
			),
			0,
			$title
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		self::mark_bootstrap( $attachment_id, $key );
		update_post_meta( $attachment_id, self::BOOTSTRAP_SOURCE_HASH, self::snapshot_hash( $attachment_id ) );
		$report[] = "media $key: imported (id $attachment_id)";

		return (int) $attachment_id;
	}

	/**
	 * Create one Volume 1 bootstrap post when absent. Never overwrites.
	 *
	 * @param string $key        Bootstrap key.
	 * @param array  $postarr    wp_insert_post args.
	 * @param array  $meta       Meta key => value.
	 * @param array  $taxonomies Taxonomy => term names.
	 * @param bool   $apply      Write when true.
	 * @param array  $report     Report accumulator.
	 * @param int    $thumbnail  Featured image ID.
	 * @return int|\WP_Error Post ID (0 in dry-run for new objects).
	 */
	private static function seed_bootstrap_post( $key, $postarr, $meta, $taxonomies, $apply, &$report, $thumbnail = 0 ) {
		$existing = self::find_bootstrap( $key );

		if ( $existing ) {
			self::refresh_adoption( $existing->ID, $apply );

			if ( self::is_adopted( $existing->ID ) ) {
				$report[] = "$key: adopted (id {$existing->ID}); left untouched";
			} else {
				$report[] = "$key: exists (id {$existing->ID})";
			}

			return (int) $existing->ID;
		}

		if ( ! $apply ) {
			$report[] = "$key: would create {$postarr['post_type']} {$postarr['post_name']}";
			return 0;
		}

		$post_id = wp_insert_post( wp_slash( $postarr ), true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		self::mark_bootstrap( $post_id, $key );

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

		update_post_meta( $post_id, self::BOOTSTRAP_SOURCE_HASH, self::snapshot_hash( $post_id ) );
		$report[] = "$key: created (id $post_id)";

		return (int) $post_id;
	}

	/**
	 * Mark an object as Volume 1 bootstrap-owned. Never used on the
	 * canonical author.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Bootstrap key.
	 */
	private static function mark_bootstrap( $post_id, $key ) {
		update_post_meta( $post_id, self::BOOTSTRAP_MARKER, '1' );
		update_post_meta( $post_id, self::BOOTSTRAP_KEY, $key );
		update_post_meta( $post_id, self::BOOTSTRAP_KIND, self::BOOTSTRAP_KIND_VOLUME_1 );
		update_post_meta( $post_id, self::BOOTSTRAP_VERSION, self::BOOTSTRAP_VERSION_VALUE );
	}

	/**
	 * Hash of the editable editorial fields used to detect adoption.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function snapshot_hash( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return '';
		}

		$payload = array(
			$post->post_title,
			$post->post_content,
			$post->post_name,
			$post->post_status,
			(string) $post->menu_order,
			(string) get_post_thumbnail_id( $post_id ),
		);

		foreach ( array(
			'abstract',
			'abstract_en',
			'title_en',
			'pages',
			'language',
			'doi',
			'issn',
			'volume_number',
			'issue_number',
			'year',
			'date_published',
			'publication_date',
			'pdf_file',
			'issue',
		) as $meta_key ) {
			$value     = get_post_meta( $post_id, $meta_key, true );
			$payload[] = is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
		}

		$authors   = get_post_meta( $post_id, 'authors', true );
		$payload[] = is_array( $authors ) ? implode( ',', array_map( 'absint', $authors ) ) : '';

		foreach ( array( Taxonomies::SECTION, Taxonomies::ARTICLE_TYPE, Taxonomies::KEYWORD ) as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$payload[] = '';
				continue;
			}

			$terms     = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
			$payload[] = is_wp_error( $terms ) ? '' : implode( ',', $terms );
		}

		return hash( 'sha256', implode( '|', $payload ) );
	}

	/**
	 * Whether a bootstrap object has been editorially adopted.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_adopted( $post_id ) {
		if ( '1' === (string) get_post_meta( $post_id, self::BOOTSTRAP_ADOPTED, true ) ) {
			return true;
		}

		if ( '1' !== (string) get_post_meta( $post_id, self::BOOTSTRAP_MARKER, true ) ) {
			return false;
		}

		$stored = (string) get_post_meta( $post_id, self::BOOTSTRAP_SOURCE_HASH, true );

		if ( '' === $stored ) {
			return false;
		}

		return self::snapshot_hash( $post_id ) !== $stored;
	}

	/**
	 * Persist the adopted flag when content has drifted. Sticky: never
	 * cleared automatically.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $persist Write the flag when true.
	 */
	private static function refresh_adoption( $post_id, $persist ) {
		if ( ! $persist || self::is_adopted( $post_id ) === false ) {
			return;
		}

		if ( '1' !== (string) get_post_meta( $post_id, self::BOOTSTRAP_ADOPTED, true ) ) {
			update_post_meta( $post_id, self::BOOTSTRAP_ADOPTED, '1' );
		}
	}

	/**
	 * Canonical author must never be deleted by fixture/bootstrap cleanup.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private static function is_protected_author( $post_id ) {
		$post = get_post( $post_id );

		return $post
			&& Content_Types::AUTHOR === $post->post_type
			&& self::CANONICAL_AUTHOR_SLUG === $post->post_name;
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
	 * Verify fixture and Volume 1 bootstrap state: keys unique, demo
	 * identifiers fake, bootstrap identifiers empty, canonical author
	 * unmarked, no migration-source contamination.
	 *
	 * @return array{report: string[], failures: int}
	 */
	public static function verify() {
		$report   = array();
		$failures = 0;

		$fixture_ids   = self::all_fixture_ids();
		$bootstrap_ids = self::all_bootstrap_ids();
		$report[]      = sprintf( '%d demo/legacy fixture objects present', count( $fixture_ids ) );
		$report[]      = sprintf( '%d Volume 1 bootstrap objects present', count( $bootstrap_ids ) );

		$keys = array();
		foreach ( $fixture_ids as $id ) {
			$key = (string) get_post_meta( $id, self::FIXTURE_KEY, true );

			if ( isset( $keys[ $key ] ) ) {
				$report[] = "DUPLICATE fixture key: $key (ids {$keys[$key]}, $id)";
				$failures++;
			}

			$keys[ $key ] = $id;
		}

		$bootstrap_keys = array();
		foreach ( $bootstrap_ids as $id ) {
			$key = (string) get_post_meta( $id, self::BOOTSTRAP_KEY, true );

			if ( isset( $bootstrap_keys[ $key ] ) ) {
				$report[] = "DUPLICATE bootstrap key: $key (ids {$bootstrap_keys[$key]}, $id)";
				$failures++;
			}

			$bootstrap_keys[ $key ] = $id;

			if ( self::is_protected_author( $id ) ) {
				$report[] = "canonical author $id carries _les_bootstrap; must never be bootstrap-owned";
				$failures++;
			}

			if ( self::is_adopted( $id ) ) {
				$report[] = sprintf(
					'%s %d (%s): adopted; left as editorial content',
					get_post_type( $id ),
					$id,
					$key
				);
			}

			$issn  = (string) get_post_meta( $id, 'issn', true );
			$doi   = (string) get_post_meta( $id, 'doi', true );
			$orcid = (string) get_post_meta( $id, 'orcid', true );

			if ( '' !== $issn || '' !== $doi || '' !== $orcid ) {
				$report[] = get_post_type( $id ) . " $id: Volume 1 bootstrap must not store DOI/ORCID/ISSN";
				$failures++;
			}

			if ( false !== strpos( $issn . $doi . $orcid, '1234-5678' )
				|| false !== strpos( $doi, self::FAKE_DOI_PREFIX )
				|| false !== strpos( $orcid, '0000-0000-' ) ) {
				$report[] = get_post_type( $id ) . " $id: Volume 1 bootstrap carries a fake identifier";
				$failures++;
			}

			if ( '' !== (string) get_post_meta( $id, Content_Migrator::META_SOURCE_KEY, true ) ) {
				$report[] = "object $id carries BOTH bootstrap marker and migration source key";
				$failures++;
			}

			if ( '1' === (string) get_post_meta( $id, self::MARKER, true ) ) {
				$report[] = "object $id carries BOTH _les_bootstrap and _les_fixture";
				$failures++;
			}

			$pages = (string) get_post_meta( $id, 'pages', true );

			if ( preg_match( '/\d+\s*[–-]\s*\d/', $pages ) ) {
				$report[] = get_post_type( $id ) . " $id: Volume 1 bootstrap must not store dummy bibliographic page ranges";
				$failures++;
			}
		}

		foreach ( $fixture_ids as $id ) {
			$post_type = get_post_type( $id );
			$kind      = (string) get_post_meta( $id, self::KIND, true );
			$issn      = (string) get_post_meta( $id, 'issn', true );
			$doi       = (string) get_post_meta( $id, 'doi', true );
			$orcid     = (string) get_post_meta( $id, 'orcid', true );

			if ( self::KIND_BOOTSTRAP === $kind ) {
				if ( '' !== $issn || '' !== $doi || '' !== $orcid ) {
					$report[] = "$post_type $id: leftover bootstrap fixture must not store DOI/ORCID/ISSN";
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

			if ( '' !== (string) get_post_meta( $id, Content_Migrator::META_SOURCE_KEY, true ) ) {
				$report[] = "object $id carries BOTH fixture marker and migration source key";
				$failures++;
			}
		}

		$canonical = get_posts(
			array(
				'post_type'      => Content_Types::AUTHOR,
				'name'           => self::CANONICAL_AUTHOR_SLUG,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
			)
		);

		if ( count( $canonical ) > 1 ) {
			$report[] = 'Ambiguous canonical author slug ' . self::CANONICAL_AUTHOR_SLUG;
			$failures++;
		} elseif ( 1 === count( $canonical ) ) {
			$author_id = $canonical[0]->ID;
			$report[]  = sprintf( 'canonical author id %d slug %s present', $author_id, self::CANONICAL_AUTHOR_SLUG );

			if ( '1' === (string) get_post_meta( $author_id, self::BOOTSTRAP_MARKER, true )
				|| '1' === (string) get_post_meta( $author_id, self::MARKER, true ) ) {
				$report[] = "canonical author $author_id must not carry fixture or bootstrap markers";
				$failures++;
			}
		}

		return array(
			'report'   => $report,
			'failures' => $failures,
		);
	}

	/**
	 * Remove disposable fixtures and unadopted Volume 1 bootstrap
	 * objects. Never deletes adopted bootstrap content, the canonical
	 * author, institutional migration objects, or unmarked posts.
	 * Idempotent: an empty run is a no-op.
	 *
	 * @param bool   $apply Write when true.
	 * @param string $kind  Empty = demo+legacy fixtures only (not Volume 1);
	 *                      KIND_DEMO; KIND_BOOTSTRAP = leftover fixture
	 *                      bootstrap + unadopted Volume 1 objects.
	 * @return string[] Report lines.
	 */
	public static function teardown( $apply, $kind = '' ) {
		$report = array();
		$ids    = array();

		if ( self::KIND_BOOTSTRAP === $kind ) {
			$legacy = array_values(
				array_filter(
					self::all_fixture_ids(),
					static function ( $id ) {
						return self::KIND_BOOTSTRAP === (string) get_post_meta( $id, self::KIND, true );
					}
				)
			);
			$ids    = array_values( array_unique( array_merge( $legacy, self::all_bootstrap_ids() ) ) );
		} elseif ( self::KIND_DEMO === $kind ) {
			$ids = array_values(
				array_filter(
					self::all_fixture_ids(),
					static function ( $id ) {
						return self::KIND_DEMO === (string) get_post_meta( $id, self::KIND, true )
							|| '' === (string) get_post_meta( $id, self::KIND, true );
					}
				)
			);
		} else {
			$ids = self::all_fixture_ids();
		}

		if ( ! $ids ) {
			$report[] = 'no matching fixture/bootstrap objects found; nothing to do';
		}

		// Classify keep vs delete before any wp_delete_post(). Deleting an
		// unadopted issue fires Relationships::cleanup_references, which
		// strips `issue` meta from remaining articles. That meta is part of
		// snapshot_hash(), so classifying after that mutation would make
		// still-unadopted articles look adopted and survive teardown.
		$delete_ids = array();

		foreach ( $ids as $id ) {
			$type = get_post_type( $id );

			if ( self::is_protected_author( $id ) ) {
				$report[] = "kept author $id (canonical slug; never deleted)";
				continue;
			}

			if ( '' !== (string) get_post_meta( $id, Content_Migrator::META_SOURCE_KEY, true ) ) {
				$report[] = "kept $type $id (institutional migration object)";
				continue;
			}

			if ( '1' === (string) get_post_meta( $id, self::BOOTSTRAP_MARKER, true ) && self::is_adopted( $id ) ) {
				$report[] = "kept $type $id (adopted Volume 1 content; teardown refused)";
				continue;
			}

			if ( ! $apply ) {
				$report[] = "would delete $type $id";
				continue;
			}

			$delete_ids[] = $id;
		}

		foreach ( $delete_ids as $id ) {
			$type    = get_post_type( $id );
			$deleted = ( 'attachment' === $type )
				? wp_delete_attachment( $id, true )
				: wp_delete_post( $id, true );

			$report[] = $deleted ? "deleted $type $id" : "ERROR deleting $type $id";
		}

		if ( '' !== $kind ) {
			return $report;
		}

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
