<?php
/**
 * Public listings keep one order when several posts share a
 * post_date. Core orders archives by post_date alone, so MySQL may
 * return tied rows in any order and pagination repeats or skips them
 * (Vol. 1 Nº 1 bootstrap articles share one date). The post ID breaks
 * the tie, in the listing's own direction.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Content_Types;
use Revistalogos_Core\Queries;
use Revistalogos_Core\Taxonomies;

/**
 * Protects stable ordering of archives, the blog index and the
 * bounded domain queries.
 */
class ArchiveOrderTest extends WP_UnitTestCase {

	const TIED_DATE = '2026-07-31 23:03:24';

	public function set_up() {
		parent::set_up();
		// Register after the permalink structure: setting it re-inits
		// $wp_rewrite and drops the CPT archive rules added before.
		$this->set_permalink_structure( '/%postname%/' );
		Content_Types::register();
		Taxonomies::register();
		flush_rewrite_rules( false );
		update_option( 'posts_per_page', 2 );
	}

	/**
	 * Dado: cinco artículos con la misma fecha y dos por página.
	 * Entonces: las tres páginas del archivo los listan una sola vez,
	 * del ID mayor al menor.
	 */
	public function test_article_archive_pages_break_date_ties_by_id() {
		$ids = $this->make_tied( Content_Types::ARTICLE, 5 );

		$listed = $this->listed_ids( get_post_type_archive_link( Content_Types::ARTICLE ), 3 );

		rsort( $ids );
		$this->assertSame( $ids, $listed );
		$this->assertMatchesRegularExpression( '/post_date DESC, \S*ID DESC/', $GLOBALS['wp_query']->request );
	}

	/**
	 * Dado: artículos con la misma fecha y la misma palabra clave.
	 * Entonces: el archivo de la palabra clave desempata por ID.
	 */
	public function test_keyword_archive_breaks_date_ties_by_id() {
		$ids  = $this->make_tied( Content_Types::ARTICLE, 3 );
		$term = self::factory()->term->create( array( 'taxonomy' => Taxonomies::KEYWORD, 'name' => 'demostracion' ) );
		foreach ( $ids as $id ) {
			wp_set_object_terms( $id, array( $term ), Taxonomies::KEYWORD );
		}

		$listed = $this->listed_ids( get_term_link( $term ), 2 );

		rsort( $ids );
		$this->assertSame( $ids, $listed );
		$this->assertMatchesRegularExpression( '/post_date DESC, \S*ID DESC/', $GLOBALS['wp_query']->request );
	}

	/**
	 * Dado: noticias con la misma fecha.
	 * Entonces: el índice de noticias desempata por ID.
	 */
	public function test_news_index_breaks_date_ties_by_id() {
		$ids = $this->make_tied( 'post', 3 );

		$listed = $this->listed_ids( home_url( '/' ), 2 );

		rsort( $ids );
		$this->assertSame( $ids, $listed );
		$this->assertMatchesRegularExpression( '/post_date DESC, \S*ID DESC/', $GLOBALS['wp_query']->request );
	}

	/**
	 * Dado: ?order=ASC en el archivo.
	 * Entonces: el desempate sigue la misma dirección.
	 */
	public function test_tie_break_follows_the_requested_direction() {
		$ids = $this->make_tied( Content_Types::ARTICLE, 3 );

		$listed = $this->listed_ids( add_query_arg( 'order', 'ASC', get_post_type_archive_link( Content_Types::ARTICLE ) ), 2 );

		sort( $ids );
		$this->assertSame( $ids, $listed );
	}

	/**
	 * Dado: un orden explícito (?orderby=title).
	 * Entonces: no se toca.
	 */
	public function test_explicit_orderby_is_left_alone() {
		$this->make_tied( Content_Types::ARTICLE, 2 );

		$this->go_to( add_query_arg( 'orderby', 'title', get_post_type_archive_link( Content_Types::ARTICLE ) ) );

		$this->assertSame( 'title', $GLOBALS['wp_query']->get( 'orderby' ) );
		$this->assertDoesNotMatchRegularExpression( '/\bID (ASC|DESC)/', $GLOBALS['wp_query']->request );
	}

	/**
	 * Dado: artículos de un número con el mismo orden y la misma fecha.
	 * Entonces: el número los lista del ID menor al mayor.
	 */
	public function test_issue_articles_break_ties_by_id() {
		$issue_id = self::factory()->post->create( array( 'post_type' => Content_Types::ISSUE ) );
		$ids      = $this->make_tied( Content_Types::ARTICLE, 4 );
		foreach ( $ids as $id ) {
			update_post_meta( $id, 'issue', $issue_id );
		}

		sort( $ids );
		$this->assertSame( $ids, wp_list_pluck( Queries::issue_articles( $issue_id ), 'ID' ) );
		$this->assertSame( array_slice( $ids, 0, 2 ), wp_list_pluck( Queries::issue_articles( $issue_id, 2 ), 'ID' ) );
	}

	/**
	 * Dado: artículos de un autor con la misma fecha.
	 * Entonces: el autor los lista del ID mayor al menor.
	 */
	public function test_author_articles_break_ties_by_id() {
		$author_id = (int) self::factory()->post->create(
			array(
				'post_type'   => Content_Types::AUTHOR,
				'post_status' => 'publish',
			)
		);
		$ids       = $this->make_tied( Content_Types::ARTICLE, 4, 'Empate', $author_id );

		rsort( $ids );
		$this->assertSame( $ids, wp_list_pluck( Queries::author_articles( $author_id ), 'ID' ) );
	}

	/**
	 * Dado: resultados de búsqueda del mismo tipo y la misma fecha.
	 * Entonces: la búsqueda desempata por ID después del tipo y la fecha.
	 */
	public function test_search_breaks_ties_by_id() {
		$ids = $this->make_tied( Content_Types::ARTICLE, 3, 'Hermenéutica' );

		$query = Queries::search_query( 'Hermenéutica', 1, 2 );
		$page2 = Queries::search_query( 'Hermenéutica', 2, 2 );

		rsort( $ids );
		$this->assertSame( $ids, array_merge( wp_list_pluck( $query->posts, 'ID' ), wp_list_pluck( $page2->posts, 'ID' ) ) );
		$this->assertMatchesRegularExpression( '/post_date DESC, \S*ID DESC/', $query->request );
	}

	/**
	 * Published posts of one type sharing TIED_DATE. An article is
	 * created as a draft, credited to a published author, then published,
	 * since the plugin refuses to publish an article without one.
	 *
	 * @param string $post_type Post type.
	 * @param int    $count     How many.
	 * @param string $title     Title prefix.
	 * @param int    $author_id Author credited on articles; 0 creates one.
	 * @return int[] IDs in creation order.
	 */
	private function make_tied( $post_type, $count, $title = 'Empate', $author_id = 0 ) {
		if ( Content_Types::ARTICLE === $post_type && 0 === $author_id ) {
			$author_id = (int) self::factory()->post->create(
				array(
					'post_type'   => Content_Types::AUTHOR,
					'post_status' => 'publish',
				)
			);
		}

		$ids = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$id = (int) self::factory()->post->create(
				array(
					'post_type'   => $post_type,
					'post_status' => 'draft',
					'post_title'  => $title . ' ' . $i,
					'post_date'   => self::TIED_DATE,
				)
			);
			if ( $author_id ) {
				update_post_meta( $id, 'authors', array( $author_id ) );
			}
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => 'publish',
					'post_date'   => self::TIED_DATE,
					'edit_date'   => true,
				)
			);
			$this->assertSame( self::TIED_DATE, get_post( $id )->post_date );
			$ids[] = $id;
		}

		return $ids;
	}

	/**
	 * IDs the main query lists over consecutive pages of a URL.
	 *
	 * @param string $url   Listing URL.
	 * @param int    $pages Number of pages to read.
	 * @return int[]
	 */
	private function listed_ids( $url, $pages ) {
		$ids = array();
		for ( $page = 1; $page <= $pages; $page++ ) {
			$this->go_to( $page > 1 ? add_query_arg( 'paged', $page, $url ) : $url );
			$ids = array_merge( $ids, wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		}

		return $ids;
	}
}
