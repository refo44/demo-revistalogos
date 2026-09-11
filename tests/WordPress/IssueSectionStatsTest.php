<?php
/**
 * Issue statistics count only assigned section terms. When that
 * derived count is 0 (Vol. 1 Nº 1 has no editorial sections), the
 * Secciones card is omitted so the page does not show a zero.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Queries;
use Revistalogos_Core\Taxonomies;

/**
 * Protects the derived section count and its public visibility.
 */
class IssueSectionStatsTest extends WP_UnitTestCase {

	/**
	 * Dado: un número publicado con artículos sin término de sección
	 * (Vol. 1 Nº 1: decisión editorial de no usar secciones).
	 * Entonces: el conteo derivado es 0.
	 */
	public function test_articles_without_assigned_section_count_as_zero() {
		$issue_id = $this->make_published_issue();
		$this->make_published_article( $issue_id, 'Editorial sin sección' );
		$this->make_published_article( $issue_id, 'Artículo sin sección' );

		$this->assertSame( 0, Queries::issue_section_count( $issue_id ) );
	}

	/**
	 * Dado: un número sin secciones asignadas.
	 * Cuando: se muestra la ficha pública del número.
	 * Entonces: no aparece la tarjeta Secciones.
	 */
	public function test_section_stat_is_hidden_when_count_is_zero() {
		$issue_id = $this->make_published_issue();
		$this->make_published_article( $issue_id, 'Artículo sin sección' );

		$html = $this->render_issue_stats(
			Queries::issue_articles( $issue_id ),
			Queries::issue_section_count( $issue_id ),
			1
		);

		$this->assertStringContainsString( 'Estadísticas del Número', $html );
		$this->assertStringContainsString( 'Artículos', $html );
		$this->assertStringNotContainsString( 'Secciones', $html );
	}

	/**
	 * Dado: artículos en Ética y Metafísica.
	 * Entonces: el número declara dos secciones y la tarjeta se muestra.
	 */
	public function test_assigned_sections_are_counted_and_shown() {
		$issue_id = $this->make_published_issue();
		$this->make_published_article( $issue_id, 'Ser y tiempo', 'Metafísica' );
		$this->make_published_article( $issue_id, 'Ética aplicada', 'Ética' );

		$this->assertSame( 2, Queries::issue_section_count( $issue_id ) );

		$html = $this->render_issue_stats(
			Queries::issue_articles( $issue_id ),
			Queries::issue_section_count( $issue_id ),
			2
		);
		$this->assertStringContainsString( 'Secciones', $html );
		$this->assertStringContainsString( '>2</h3>', $html );
	}

	/**
	 * Dado: un artículo en Ética y otro sin sección.
	 * Entonces: solo cuenta la sección asignada.
	 */
	public function test_unassigned_articles_do_not_add_a_section() {
		$issue_id = $this->make_published_issue();
		$this->make_published_article( $issue_id, 'Ética aplicada', 'Ética' );
		$this->make_published_article( $issue_id, 'Sin clasificar' );

		$this->assertSame( 1, Queries::issue_section_count( $issue_id ) );
	}

	/**
	 * Dado: varios artículos en la misma sección.
	 * Entonces: esa sección cuenta una sola vez.
	 */
	public function test_several_articles_in_one_section_count_once() {
		$issue_id = $this->make_published_issue();
		$this->make_published_article( $issue_id, 'Primero', 'Ética' );
		$this->make_published_article( $issue_id, 'Segundo', 'Ética' );

		$this->assertSame( 1, Queries::issue_section_count( $issue_id ) );
	}

	/**
	 * Dado: un número sin artículos publicados.
	 * Entonces: el conteo de secciones es cero.
	 */
	public function test_issue_without_published_articles_has_zero_sections() {
		$issue_id = $this->make_published_issue();

		$this->assertSame( 0, Queries::issue_section_count( $issue_id ) );
	}

	/**
	 * @param \WP_Post[] $articles      Published articles of the issue.
	 * @param int        $section_count Derived assigned-section count.
	 * @param int        $author_count  Distinct credited authors.
	 * @return string Stats block HTML.
	 */
	private function render_issue_stats( $articles, $section_count, $author_count ) {
		$args = array(
			'article_count' => count( $articles ),
			'section_count' => $section_count,
			'author_count'  => $author_count,
		);

		ob_start();
		require dirname( __DIR__, 2 ) . '/wordpress/wp-content/themes/revistalogos/template-parts/issue-stats.php';

		return (string) ob_get_clean();
	}

	/**
	 * @return int Published issue ID.
	 */
	private function make_published_issue() {
		return (int) self::factory()->post->create(
			array(
				'post_type'   => 'issue',
				'post_title'  => 'Vol. 1 Nº 1',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Factory publish of an article is demoted to draft unless a
	 * published Author CPT is already assigned (plugin 0.2.5).
	 *
	 * @param int         $issue_id Issue ID.
	 * @param string      $title    Article title.
	 * @param string|null $section  Optional section term name.
	 * @return int Article ID.
	 */
	private function make_published_article( $issue_id, $title, $section = null ) {
		$author_id = (int) self::factory()->post->create(
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

		if ( null !== $section ) {
			wp_set_object_terms( $article_id, array( $section ), Taxonomies::SECTION );
		}

		return $article_id;
	}
}
