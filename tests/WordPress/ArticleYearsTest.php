<?php
/**
 * Publication years for the article archive's year filter, derived from
 * the oldest and newest published article. The theme reads them through
 * a helper and does not query articles itself (ADR 0015, contrato §4).
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Queries;

/**
 * Protects Queries::article_years().
 */
class ArticleYearsTest extends WP_UnitTestCase {

	/**
	 * Dado: ningún artículo publicado.
	 * Entonces: no hay años.
	 */
	public function test_no_published_articles_means_no_years() {
		self::factory()->post->create(
			array(
				'post_type'   => Content_Types::ARTICLE,
				'post_status' => 'draft',
			)
		);

		$this->assertSame( array(), Queries::article_years() );
	}

	/**
	 * Dado: artículos publicados en 2023 y 2026.
	 * Entonces: cada año del intervalo, del más reciente al más antiguo,
	 * aunque un año intermedio no tenga artículos.
	 */
	public function test_years_span_the_oldest_to_the_newest_article_newest_first() {
		$this->publish_article( '2026-02-01 09:00:00' );
		$this->publish_article( '2023-11-30 09:00:00' );

		$this->assertSame( array( 2026, 2025, 2024, 2023 ), Queries::article_years() );
	}

	/**
	 * Dado: un solo año con artículos.
	 * Entonces: ese año.
	 */
	public function test_one_year() {
		$this->publish_article( '2026-07-31 23:03:24' );
		$this->publish_article( '2026-07-31 23:03:24' );

		$this->assertSame( array( 2026 ), Queries::article_years() );
	}

	/**
	 * An article is published only once a published author is credited.
	 *
	 * @param string $date post_date.
	 */
	private function publish_article( $date ) {
		$author_id  = (int) self::factory()->post->create(
			array(
				'post_type'   => Content_Types::AUTHOR,
				'post_status' => 'publish',
			)
		);
		$article_id = (int) self::factory()->post->create(
			array(
				'post_type'   => Content_Types::ARTICLE,
				'post_status' => 'draft',
			)
		);
		update_post_meta( $article_id, 'authors', array( $author_id ) );
		wp_update_post(
			array(
				'ID'          => $article_id,
				'post_status' => 'publish',
				'post_date'   => $date,
				'edit_date'   => true,
			)
		);
	}
}
