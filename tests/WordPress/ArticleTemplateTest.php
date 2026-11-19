<?php
/**
 * FSE single article (ADR 0015 §7, Session 8): templates/single-article.html
 * composes the revistalogos/view block with the article-single view, which
 * renders the same template-parts/view/article-single part that
 * single-article.php prints inside its loop.
 *
 * @package Revistalogos
 */

// functions.php is not loaded here; site-blocks.php versions its editor script.
defined( 'REVISTALOGOS_THEME_VERSION' ) || define( 'REVISTALOGOS_THEME_VERSION', 'test' );

if ( ! function_exists( 'revistalogos_core_active' ) ) {
	function revistalogos_core_active() {
		return class_exists( 'Revistalogos_Core\Plugin' );
	}
}

$revistalogos_theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
require_once $revistalogos_theme_inc . '/template-tags.php';
require_once $revistalogos_theme_inc . '/class-nav-walker.php';
require_once $revistalogos_theme_inc . '/citations.php';
require_once $revistalogos_theme_inc . '/site-blocks.php';

/**
 * Locks the single article template contract.
 */
class ArticleTemplateTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

	/**
	 * @var string
	 */
	private $previous_stylesheet;

	public function set_up() {
		parent::set_up();
		$this->previous_stylesheet = get_stylesheet();
		switch_theme( self::THEME );
		wp_clean_theme_json_cache();

		register_nav_menus(
			array(
				'primary'      => 'Navegación principal',
				'footer-quick' => 'Footer: Enlaces Rápidos',
				'footer-norms' => 'Footer: Normas Editoriales',
			)
		);

		// Every theme block, so re-registering does not trip "already registered".
		foreach ( glob( get_template_directory() . '/blocks/*', GLOB_ONLYDIR ) as $dir ) {
			$name = 'revistalogos/' . basename( $dir );
			if ( WP_Block_Type_Registry::get_instance()->is_registered( $name ) ) {
				unregister_block_type( $name );
			}
		}
		revistalogos_register_site_blocks();
	}

	public function tear_down() {
		switch_theme( $this->previous_stylesheet );
		wp_clean_theme_json_cache();
		parent::tear_down();
	}

	/**
	 * Dado: la plantilla single-article.
	 * Entonces: cabecera, main#main-content, container, un solo
	 * revistalogos/view con la vista article-single, y pie, en ese orden.
	 */
	public function test_template_composes_the_article_view_inside_main() {
		$blocks = $this->template_blocks( 'single-article' );

		$this->assertSame(
			array( 'core/template-part', 'core/group', 'core/group', 'revistalogos/view', 'core/template-part' ),
			wp_list_pluck( $blocks, 'blockName' )
		);
		$this->assertSame( 'main', $blocks[1]['attrs']['tagName'] );
		$this->assertSame( 'main-content', $blocks[1]['attrs']['anchor'] );
		$this->assertSame( 'container', $blocks[2]['attrs']['className'] );
		$this->assertSame( 'article-single', $blocks[3]['attrs']['view'] );
	}

	/**
	 * Dado: la ficha de un artículo y su vista PHP single-article.php.
	 * Entonces: el resolvedor de plantillas elige la HTML, y la PHP se
	 * conserva hasta el cambio (contrato de conversión).
	 */
	public function test_block_template_wins_over_the_php_view() {
		global $_wp_current_template_id;

		// Core adds this for block themes on after_setup_theme, which switch_theme() does not fire.
		add_theme_support( 'block-templates' );

		$theme_dir = get_template_directory();
		$this->assertFileExists( $theme_dir . '/single-article.php' );

		locate_block_template( $theme_dir . '/single-article.php', 'single', array( 'single-article.php', 'single.php' ) );

		$this->assertSame( self::THEME . '//single-article', $_wp_current_template_id );
	}

	/**
	 * Dado: un artículo publicado con número, autor, título en inglés,
	 * resúmenes, palabras clave, páginas, fechas y cuerpo.
	 * Entonces: migas, cabecera, ficha de metadatos, resúmenes, palabras
	 * clave, cuerpo, Cómo Citar y «Ver número completo», igual que la
	 * parte que imprime single-article.php.
	 */
	public function test_article_view_renders_header_metadata_body_and_citation() {
		$issue_id   = $this->make_issue();
		$article_id = $this->make_article( $issue_id, 'Sobre la esperanza' );
		update_post_meta( $article_id, 'title_en', 'On hope' );
		update_post_meta( $article_id, 'abstract', 'Resumen del artículo.' );
		update_post_meta( $article_id, 'abstract_en', 'Article abstract.' );
		update_post_meta( $article_id, 'pages', '15-32' );
		update_post_meta( $article_id, 'received_date', '2026-01-10' );
		update_post_meta( $article_id, 'accepted_date', '2026-02-20' );
		update_post_meta( $article_id, 'publication_date', '2026-03-30' );
		wp_set_object_terms( $article_id, array( 'esperanza', 'metafísica' ), 'keyword' );
		wp_update_post(
			array(
				'ID'           => $article_id,
				'post_content' => '<!-- wp:paragraph --><p>Cuerpo del artículo.</p><!-- /wp:paragraph -->',
			)
		);
		$this->go_to( get_permalink( $article_id ) );

		$html = $this->render_view( 'article-single' );
		$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );

		$this->assertStringContainsString( '<nav class="breadcrumbs" aria-label="Migas de pan">', $html );
		$this->assertStringContainsString( '<header class="single-article__header">', $html );
		$this->assertStringContainsString( '<h1 class="single-article__title">Sobre la esperanza</h1>', $html );
		$this->assertStringContainsString( '<p class="single-article__subtitle">On hope</p>', $html );
		$this->assertStringContainsString( '<div class="single-article__authors">', $html );
		$this->assertStringContainsString( '<strong>Autor de Sobre la esperanza</strong>', $html );
		$this->assertStringContainsString( '<strong>Vol. 1 Nº 1 (2026)</strong>', $html );
		$this->assertStringContainsString( 'pp. 15-32', $text );
		$this->assertStringContainsString( 'Próximamente', $html );
		$this->assertStringContainsString( 'Recibido:', $html );
		$this->assertStringContainsString( 'Aceptado:', $html );
		$this->assertStringContainsString( 'Publicado:', $html );
		$this->assertStringContainsString( '<section class="metadata-box" aria-labelledby="metadata-title">', $html );
		$this->assertStringContainsString( '<article class="single-article__content">', $html );
		$this->assertStringContainsString( '<h2 id="abs-es">Resumen</h2>', $html );
		$this->assertStringContainsString( 'Resumen del artículo.', $html );
		$this->assertStringContainsString( '<div class="single-article__abstract" lang="en">', $html );
		$this->assertStringContainsString( 'Article abstract.', $html );
		$this->assertStringContainsString( 'Palabras clave', $html );
		$this->assertStringContainsString( 'esperanza', $html );
		$this->assertStringContainsString( 'Cuerpo del artículo.</p>', $html );
		$this->assertStringContainsString( '<section class="single-article__section citation-section">', $html );
		$this->assertStringContainsString( '<section class="single-article__actions">', $html );
		$this->assertStringContainsString( 'Ver número completo', $html );
		$this->assertStringNotContainsString( 'btn--pdf', $html );

		rewind_posts();
		$this->assertSame( $this->part( 'template-parts/view/article-single' ), $html );
	}

	/**
	 * Dado: un artículo con PDF en la Media Library.
	 * Entonces: aparecen los dos botones del PDF del artículo.
	 */
	public function test_article_view_shows_pdf_actions_only_with_a_pdf() {
		$article_id = $this->make_article( $this->make_issue(), 'Sobre la esperanza' );
		$upload     = wp_upload_bits( 'articulo-qa.pdf', null, '%PDF-1.4 qa' );
		$this->assertEmpty( $upload['error'] ?? '', wp_json_encode( $upload ) );
		$pdf_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/pdf',
				'post_title'     => 'articulo-qa',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$article_id
		);
		update_post_meta( $article_id, 'pdf_file', $pdf_id );
		$this->go_to( get_permalink( $article_id ) );

		$html = $this->render_view( 'article-single' );

		$this->assertStringContainsString( 'class="btn btn--pdf btn--large"', $html );
		$this->assertStringContainsString( 'Descargar PDF del artículo', $html );
		$this->assertStringContainsString( 'Ver PDF', $html );
	}

	/**
	 * Dado: un artículo sin título en inglés, resúmenes, palabras clave,
	 * fechas ni cuerpo.
	 * Entonces: esas partes no aparecen; la cabecera, la ficha de
	 * metadatos y Cómo Citar siguen.
	 */
	public function test_article_view_omits_empty_sections() {
		$article_id = $this->make_article( $this->make_issue(), 'Sobre la esperanza' );
		$this->go_to( get_permalink( $article_id ) );

		$html = $this->render_view( 'article-single' );

		$this->assertStringContainsString( '<header class="single-article__header">', $html );
		$this->assertStringContainsString( 'class="metadata-box"', $html );
		$this->assertStringContainsString( 'citation-section', $html );
		$this->assertStringNotContainsString( 'single-article__subtitle', $html );
		$this->assertStringNotContainsString( 'id="abs-es"', $html );
		$this->assertStringNotContainsString( 'id="abs-en"', $html );
		$this->assertStringNotContainsString( 'Palabras clave', $html );
		$this->assertStringNotContainsString( 'Recibido:', $html );
	}

	/**
	 * Dado: la vista del artículo fuera de la ficha de un artículo (una
	 * noticia, un número, o una URL sin entrada).
	 * Entonces: el bloque no emite nada.
	 */
	public function test_article_view_is_empty_outside_an_article() {
		$post_id = (int) self::factory()->post->create( array( 'post_status' => 'publish' ) );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertSame( '', $this->render_view( 'article-single' ) );

		$this->go_to( get_permalink( $this->make_issue() ) );
		$this->assertSame( '', $this->render_view( 'article-single' ) );

		$this->go_to( home_url( '/?p=987654321' ) );
		$this->assertSame( '', $this->render_view( 'article-single' ) );
	}

	/**
	 * Dado: la plantilla HTML completa, que core pasa entera por
	 * wptexturize(), y un artículo cuyo resumen y páginas llevan «--»,
	 * «...» y comillas rectas, que la vista clásica imprime sin texturizar.
	 * Entonces: un solo main, y el texto queda como en la vista clásica.
	 */
	public function test_single_article_template_keeps_text_after_texturize() {
		$article_id = $this->make_article( $this->make_issue(), 'Sobre la esperanza' );
		update_post_meta( $article_id, 'pages', '15--32' );
		update_post_meta( $article_id, 'abstract', 'El "ser" -- y la nada...' );
		$this->go_to( get_permalink( $article_id ) );

		$html = $this->render_template( 'single-article' );
		$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );

		$this->assertSame( 1, substr_count( $html, '<main' ) );
		$this->assertStringContainsString( '<main id="main-content" class="wp-block-group main-content', $html );
		$this->assertStringContainsString( 'pp. 15--32', $text );
		$this->assertStringContainsString( 'El "ser" -- y la nada...', $text );
		$this->assertStringNotContainsString( "pp. 15\u{2013}32", $text );
		$this->assertStringNotContainsString( "\u{2026}", $text );
	}

	/**
	 * @param string $slug Template slug.
	 * @return string Rendered template, loop started like core does.
	 */
	private function render_template( $slug ) {
		global $_wp_current_template_content, $_wp_current_template_id;

		$template                     = get_block_template( self::THEME . '//' . $slug );
		$_wp_current_template_id      = $template->id;
		$_wp_current_template_content = $template->content;

		return get_the_block_template_html();
	}

	/**
	 * @param string $slug Template slug.
	 * @return array[] Every parsed block in document order, whitespace dropped.
	 */
	private function template_blocks( $slug ) {
		$template = get_block_template( self::THEME . '//' . $slug );
		$this->assertNotNull( $template, $slug );

		$flat  = array();
		$queue = parse_blocks( $template->content );
		while ( $queue ) {
			$block = array_shift( $queue );
			if ( ! $block['blockName'] ) {
				continue;
			}
			$flat[] = $block;
			$queue  = array_merge( $block['innerBlocks'], $queue );
		}

		return $flat;
	}

	/**
	 * @param string $view View attribute.
	 * @return string
	 */
	private function render_view( $view ) {
		return do_blocks( '<!-- wp:revistalogos/view ' . wp_json_encode( array( 'view' => $view ) ) . ' /-->' );
	}

	/**
	 * @param string $slug Theme template part.
	 * @return string
	 */
	private function part( $slug ) {
		ob_start();
		get_template_part( $slug );

		// Blocks print the part shielded from template-level wptexturize().
		return revistalogos_shield_from_texturize( (string) ob_get_clean() );
	}

	/**
	 * @return int Published issue ID (Vol. 1 Nº 1, 2026).
	 */
	private function make_issue() {
		$issue_id = (int) self::factory()->post->create(
			array(
				'post_type'    => 'issue',
				'post_title'   => 'Volumen 1, Número 1',
				'post_content' => '<!-- wp:paragraph --><p>Descripción del número.</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $issue_id, 'volume_number', 1 );
		update_post_meta( $issue_id, 'issue_number', 1 );
		update_post_meta( $issue_id, 'year', 2026 );

		return $issue_id;
	}

	/**
	 * Publish needs a published Author already assigned (plugin 0.2.5).
	 *
	 * @param int    $issue_id Issue ID.
	 * @param string $title    Article title.
	 * @return int Published article ID.
	 */
	private function make_article( $issue_id, $title ) {
		$author_id  = (int) self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => 'Autor de ' . $title,
				'post_status' => 'publish',
			)
		);
		$article_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'article',
				'post_title'  => $title,
				'post_status' => 'draft',
			)
		);
		update_post_meta( $article_id, 'issue', $issue_id );
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		wp_update_post(
			array(
				'ID'          => $article_id,
				'post_status' => 'publish',
			)
		);

		return $article_id;
	}
}
