<?php
/**
 * FSE domain blocks (ADR 0015 §7, Session 4): revistalogos-core registers
 * the blocks and resolves their data; the markup still comes from the
 * theme's template-parts, so a block renders exactly what the classic
 * PHP view renders. No data, or no theme part, returns an empty string.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Domain_Blocks;

// functions.php is not loaded here; the theme helpers check the plugin.
if ( ! function_exists( 'revistalogos_core_active' ) ) {
	function revistalogos_core_active() {
		return class_exists( 'Revistalogos_Core\Plugin' );
	}
}

$revistalogos_theme_inc = dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/inc';
require_once $revistalogos_theme_inc . '/template-tags.php';
require_once $revistalogos_theme_inc . '/citations.php';

/**
 * Locks the domain block contract: names, empty output, theme parity.
 */
class DomainBlocksTest extends WP_UnitTestCase {

	private const THEME = 'revistalogos';

	private const BLOCKS = array(
		'revistalogos/current-issue',
		'revistalogos/issue-card',
		'revistalogos/issue-toc',
		'revistalogos/article-card',
		'revistalogos/article-metadata',
		'revistalogos/article-citation',
		'revistalogos/author-card',
	);

	/**
	 * @var string
	 */
	private $previous_stylesheet;

	public function set_up() {
		parent::set_up();
		$this->previous_stylesheet = get_stylesheet();
		switch_theme( self::THEME );
	}

	public function tear_down() {
		switch_theme( $this->previous_stylesheet );
		parent::tear_down();
	}

	/**
	 * Dado: el plugin activo.
	 * Entonces: registra los siete bloques de dominio, cada uno con su
	 * render_callback y un script solo de editor.
	 */
	public function test_plugin_registers_the_domain_blocks() {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( self::BLOCKS as $name ) {
			$type = $registry->get_registered( $name );
			$this->assertNotNull( $type, $name );
			$this->assertIsCallable( $type->render_callback, $name );
			$this->assertSame( array( 'revistalogos-domain-blocks-editor' ), $type->editor_script_handles, $name );
			$this->assertSame( array(), $type->script_handles, $name );
			$this->assertSame( array(), $type->view_script_handles, $name );
		}
	}

	/**
	 * Dado: la portada clásica.
	 * Entonces: el script del editor no se encola en el front.
	 */
	public function test_editor_script_is_not_enqueued_on_the_front() {
		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_script_is( 'revistalogos-domain-blocks-editor', 'enqueued' ) );
	}

	/**
	 * Dado: ningún número publicado.
	 * Entonces: el bloque de número actual no emite nada (ni el título).
	 */
	public function test_current_issue_is_empty_without_a_published_issue() {
		$this->assertSame( '', $this->render( 'current-issue' ) );
	}

	/**
	 * Dado: un número publicado.
	 * Entonces: el bloque emite la sección de portada con la tarjeta
	 * destacada, igual que front-page.php.
	 */
	public function test_current_issue_renders_the_front_page_section() {
		$issue_id = $this->make_issue();

		$html = $this->render( 'current-issue' );

		$this->assertStringContainsString( '<section class="front-page__current-issue">', $html );
		$this->assertStringContainsString( 'Número actual', $html );
		$this->assertStringContainsString( 'class="card issue-card front-page__featured-issue"', $html );
		$this->assertStringContainsString( '<h3 class="card__title issue-card__title">', $html );
		$this->assertSame(
			$this->part( 'template-parts/current-issue', array( 'post' => get_post( $issue_id ) ) ),
			$html
		);
	}

	/**
	 * Dado: un número en el contexto del bloque (Query Loop o single).
	 * Entonces: la tarjeta es la misma que emite template-parts/issue-card.
	 */
	public function test_issue_card_matches_the_theme_part() {
		$issue_id = $this->make_issue();
		$this->make_article( $issue_id, 'Ser y tiempo' );

		$html = $this->render(
			'issue-card',
			array(
				'heading'   => 'h3',
				'showStats' => true,
			),
			$issue_id
		);

		$this->assertStringContainsString( '<article class="card issue-card">', $html );
		$this->assertStringContainsString( 'class="issue-card__stats"', $html );
		$this->assertSame(
			$this->part(
				'template-parts/issue-card',
				array(
					'post'       => $issue_id,
					'heading'    => 'h3',
					'show_stats' => true,
				)
			),
			$html
		);
	}

	/**
	 * Dado: sin contexto, o con un post de otro tipo.
	 * Entonces: cada bloque de tarjeta devuelve cadena vacía, sin fatal.
	 */
	public function test_context_blocks_are_empty_without_a_matching_post() {
		$author_id = $this->make_author( 'Autora sin artículos' );

		foreach ( array( 'issue-card', 'issue-toc', 'article-card', 'article-metadata', 'article-citation' ) as $block ) {
			$this->assertSame( '', $this->render( $block ), $block . ' without context' );
			$this->assertSame( '', $this->render( $block, array(), $author_id ), $block . ' with an author' );
		}

		$this->assertSame( '', $this->render( 'author-card' ), 'author-card without context' );
	}

	/**
	 * Dado: un número sin artículos publicados.
	 * Entonces: el índice no emite el encabezado vacío.
	 */
	public function test_issue_toc_is_empty_without_articles() {
		$this->assertSame( '', $this->render( 'issue-toc', array(), $this->make_issue() ) );
	}

	/**
	 * Dado: un número con artículos.
	 * Entonces: el índice es el de template-parts/toc.
	 */
	public function test_issue_toc_matches_the_theme_part() {
		$issue_id = $this->make_issue();
		$this->make_article( $issue_id, 'Ética aplicada' );

		$html = $this->render( 'issue-toc', array(), $issue_id );

		$this->assertStringContainsString( '<section class="toc" aria-labelledby="toc-title">', $html );
		$this->assertStringContainsString( 'class="toc__link"', $html );
		$this->assertSame( $this->part( 'template-parts/toc', array( 'issue_id' => $issue_id ) ), $html );
	}

	/**
	 * Dado: un artículo con autor y resumen.
	 * Entonces: la tarjeta es la de template-parts/article-card.
	 */
	public function test_article_card_matches_the_theme_part() {
		$article_id = $this->make_article( $this->make_issue(), 'Ser y tiempo' );
		update_post_meta( $article_id, 'abstract', 'Un resumen.' );

		$html = $this->render( 'article-card', array( 'heading' => 'h3' ), $article_id );

		$this->assertStringContainsString( '<article class="card article-card">', $html );
		$this->assertStringContainsString( 'class="article-card__authors"', $html );
		$this->assertSame(
			$this->part(
				'template-parts/article-card',
				array(
					'post'    => $article_id,
					'heading' => 'h3',
				)
			),
			$html
		);
	}

	/**
	 * Dado: un artículo.
	 * Entonces: la caja de metadatos es la de template-parts/metadata-box.
	 */
	public function test_article_metadata_matches_the_theme_part() {
		$article_id = $this->make_article( $this->make_issue(), 'Ser y tiempo' );

		$html = $this->render( 'article-metadata', array(), $article_id );

		$this->assertStringContainsString( '<section class="metadata-box"', $html );
		$this->assertSame( $this->part( 'template-parts/metadata-box', array( 'article_id' => $article_id ) ), $html );
	}

	/**
	 * Dado: un artículo.
	 * Entonces: Cómo Citar sale de template-parts/citation-section, con los
	 * formatos existentes, el RIS y la URL canónica del artículo.
	 */
	public function test_article_citation_matches_the_theme_part() {
		$article_id = $this->make_article( $this->make_issue(), 'Ser y tiempo' );

		$html = $this->render( 'article-citation', array(), $article_id );

		$this->assertStringContainsString( '<section class="single-article__section citation-section">', $html );
		$this->assertStringContainsString( 'Cómo Citar', $html );
		$this->assertStringContainsString( 'class="citation-format"', $html );
		$this->assertStringContainsString( '<pre id="ris-data" hidden>', $html );
		$this->assertStringContainsString( esc_url( get_permalink( $article_id ) ), $html );
		$this->assertSame( $this->part( 'template-parts/citation-section', array( 'article_id' => $article_id ) ), $html );
	}

	/**
	 * Dado: un perfil de autor.
	 * Entonces: la tarjeta es la de template-parts/author-card.
	 */
	public function test_author_card_matches_the_theme_part() {
		$author_id = $this->make_author( 'Autora con perfil' );
		update_post_meta( $author_id, 'afiliacion', 'Universidad de Carabobo' );

		$html = $this->render( 'author-card', array(), $author_id );

		$this->assertStringContainsString( '<article class="card author-card">', $html );
		$this->assertStringContainsString( 'Universidad de Carabobo', $html );
		$this->assertSame( $this->part( 'template-parts/author-card', array( 'post' => $author_id ) ), $html );
	}

	/**
	 * Dado: un theme sin la parte pedida.
	 * Entonces: el bloque devuelve cadena vacía en lugar de fallar.
	 */
	public function test_missing_theme_part_renders_empty() {
		$this->assertSame( '', Domain_Blocks::theme_part( 'template-parts/does-not-exist', array() ) );
	}

	/**
	 * @param string   $block   Block name without namespace.
	 * @param array    $attrs   Block attributes.
	 * @param int|null $post_id Context post ID.
	 * @return string
	 */
	private function render( $block, $attrs = array(), $post_id = null ) {
		$parsed  = array(
			'blockName'    => 'revistalogos/' . $block,
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$context = null === $post_id ? array() : array(
			'postId'   => $post_id,
			'postType' => get_post_type( $post_id ),
		);

		return ( new WP_Block( $parsed, $context ) )->render();
	}

	/**
	 * @param string $slug Theme template part.
	 * @param array  $args Part args.
	 * @return string
	 */
	private function part( $slug, $args ) {
		ob_start();
		get_template_part( $slug, null, $args );

		return (string) ob_get_clean();
	}

	/**
	 * @return int Published issue ID.
	 */
	private function make_issue() {
		$issue_id = (int) self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => 'Vol. 1 Nº 1',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $issue_id, 'volume_number', 1 );
		update_post_meta( $issue_id, 'issue_number', 1 );
		update_post_meta( $issue_id, 'year', 2026 );
		update_post_meta( $issue_id, 'date_published', '2026-10-01' );

		return $issue_id;
	}

	/**
	 * @param string $name Author name.
	 * @return int Published author ID.
	 */
	private function make_author( $name ) {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'author',
				'post_title'  => $name,
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Publish needs a published Author already assigned (plugin 0.2.5).
	 *
	 * @param int    $issue_id Issue ID.
	 * @param string $title    Article title.
	 * @return int Article ID.
	 */
	private function make_article( $issue_id, $title ) {
		$author_id  = $this->make_author( 'Autor de ' . $title );
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
