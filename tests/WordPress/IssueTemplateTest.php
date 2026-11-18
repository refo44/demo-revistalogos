<?php
/**
 * FSE single issue (ADR 0015 §7, Session 7): templates/single-issue.html
 * composes the revistalogos/view block with the issue-single view, which
 * renders the same template-parts/view/issue-single part that
 * single-issue.php prints inside its loop.
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
 * Locks the single issue template contract.
 */
class IssueTemplateTest extends WP_UnitTestCase {

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
	 * Dado: la plantilla single-issue.
	 * Entonces: cabecera, main#main-content, container, un solo
	 * revistalogos/view con la vista issue-single, y pie, en ese orden.
	 */
	public function test_template_composes_the_issue_view_inside_main() {
		$blocks = $this->template_blocks( 'single-issue' );

		$this->assertSame(
			array( 'core/template-part', 'core/group', 'core/group', 'revistalogos/view', 'core/template-part' ),
			wp_list_pluck( $blocks, 'blockName' )
		);
		$this->assertSame( 'main', $blocks[1]['attrs']['tagName'] );
		$this->assertSame( 'main-content', $blocks[1]['attrs']['anchor'] );
		$this->assertSame( 'container', $blocks[2]['attrs']['className'] );
		$this->assertSame( 'issue-single', $blocks[3]['attrs']['view'] );
	}

	/**
	 * Dado: la ficha de un número y su vista PHP single-issue.php.
	 * Entonces: el resolvedor de plantillas elige la HTML, y la PHP se
	 * conserva hasta el cambio (contrato de conversión).
	 */
	public function test_block_template_wins_over_the_php_view() {
		global $_wp_current_template_id;

		// Core adds this for block themes on after_setup_theme, which switch_theme() does not fire.
		add_theme_support( 'block-templates' );

		$theme_dir = get_template_directory();
		$this->assertFileExists( $theme_dir . '/single-issue.php' );

		locate_block_template( $theme_dir . '/single-issue.php', 'single', array( 'single-issue.php', 'single.php' ) );

		$this->assertSame( self::THEME . '//single-issue', $_wp_current_template_id );
	}

	/**
	 * Dado: un número publicado con un editorial y un artículo.
	 * Entonces: migas, cabecera con etiqueta y metadatos, tabla de
	 * contenidos, editorial en línea y estadísticas, igual que la parte
	 * que imprime single-issue.php.
	 */
	public function test_issue_view_renders_header_toc_editorial_and_stats() {
		$issue_id = $this->make_issue();
		$editorial = $this->make_article( $issue_id, 'Presentación del número' );
		wp_update_post(
			array(
				'ID'           => $editorial,
				'post_content' => '<!-- wp:paragraph --><p>Texto del editorial.</p><!-- /wp:paragraph -->',
			)
		);
		wp_set_object_terms( $editorial, array( 'editorial' ), 'article_type' );
		$this->make_article( $issue_id, 'Sobre la esperanza' );
		$this->go_to( get_permalink( $issue_id ) );

		$html = $this->render_view( 'issue-single' );

		$this->assertStringContainsString( '<nav class="breadcrumbs" aria-label="Migas de pan">', $html );
		$this->assertStringContainsString( '<header class="single-issue__header">', $html );
		$this->assertStringContainsString( '<h1 class="single-issue__title">Vol. 1 Nº 1 (2026)</h1>', $html );
		$this->assertStringContainsString( '<div class="single-issue__meta">', $html );
		$this->assertStringContainsString( 'Próximamente', $html );
		$this->assertStringContainsString( 'Descripción del número.', $html );
		$this->assertStringContainsString( '<section class="toc" aria-labelledby="toc-title">', $html );
		$this->assertStringContainsString( 'Sobre la esperanza', $html );
		$this->assertStringContainsString( '<section class="single-issue__editorial">', $html );
		$this->assertStringContainsString( 'Texto del editorial.', $html );
		$this->assertStringContainsString( '<section class="single-issue__stats">', $html );
		$this->assertStringNotContainsString( 'Secciones', $html );
		$this->assertStringNotContainsString( 'single-issue__actions', $html );

		rewind_posts();
		$this->assertSame( $this->part( 'template-parts/view/issue-single' ), $html );
	}

	/**
	 * Dado: un número con PDF en la Media Library.
	 * Entonces: aparecen los dos botones del PDF.
	 */
	public function test_issue_view_shows_pdf_actions_only_with_a_pdf() {
		$issue_id = $this->make_issue();
		$upload   = wp_upload_bits( 'numero-qa.pdf', null, '%PDF-1.4 qa' );
		$this->assertEmpty( $upload['error'] ?? '', wp_json_encode( $upload ) );
		$pdf_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/pdf',
				'post_title'     => 'numero-qa',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$issue_id
		);
		update_post_meta( $issue_id, 'pdf_file', $pdf_id );
		$this->go_to( get_permalink( $issue_id ) );

		$html = $this->render_view( 'issue-single' );

		$this->assertStringContainsString( '<div class="single-issue__actions">', $html );
		$this->assertStringContainsString( 'Descargar PDF del número completo', $html );
		$this->assertStringContainsString( 'Ver PDF', $html );
	}

	/**
	 * Dado: un número sin artículos publicados.
	 * Entonces: no hay tabla de contenidos, editorial ni estadísticas.
	 */
	public function test_issue_view_without_articles_omits_toc_editorial_and_stats() {
		$this->go_to( get_permalink( $this->make_issue() ) );

		$html = $this->render_view( 'issue-single' );

		$this->assertStringContainsString( '<header class="single-issue__header">', $html );
		$this->assertStringNotContainsString( 'class="toc"', $html );
		$this->assertStringNotContainsString( 'single-issue__editorial', $html );
		$this->assertStringNotContainsString( 'single-issue__stats', $html );
	}

	/**
	 * Dado: la vista del número fuera de la ficha de un número (una
	 * noticia, o una URL sin entrada).
	 * Entonces: el bloque no emite nada.
	 */
	public function test_issue_view_is_empty_outside_an_issue() {
		$post_id = (int) self::factory()->post->create( array( 'post_status' => 'publish' ) );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertSame( '', $this->render_view( 'issue-single' ) );

		$this->go_to( home_url( '/?p=987654321' ) );
		$this->assertSame( '', $this->render_view( 'issue-single' ) );
	}

	/**
	 * Dado: la plantilla HTML completa, que core pasa entera por
	 * wptexturize(), y un artículo cuyas páginas son «15--32». (Títulos y
	 * autores pasan por the_title, que texturiza también en la vista
	 * clásica.)
	 * Entonces: un solo main, y la tabla de contenidos conserva los
	 * metadatos de la vista clásica, sin raya tipográfica.
	 */
	public function test_single_issue_template_keeps_text_after_texturize() {
		$issue_id = $this->make_issue();
		$article_id = $this->make_article( $issue_id, 'Sobre la esperanza' );
		update_post_meta( $article_id, 'pages', '15--32' );
		$this->go_to( get_permalink( $issue_id ) );

		$html = $this->render_template( 'single-issue' );
		$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' );

		$this->assertSame( 1, substr_count( $html, '<main' ) );
		$this->assertStringContainsString( '<main id="main-content" class="wp-block-group main-content', $html );
		$this->assertStringContainsString( 'pp. 15--32', $text );
		$this->assertStringNotContainsString( "pp. 15\u{2013}32", $text );
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
