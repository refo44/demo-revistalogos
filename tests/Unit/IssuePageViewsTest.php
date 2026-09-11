<?php
/**
 * Public issue page-view count. Reads WP Statistics when present;
 * never sums article views (Estadísticas del Número only).
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Issue_Page_Views;

require_once dirname( __DIR__, 2 ) . '/wordpress/wp-content/plugins/revistalogos-core/includes/queries/class-issue-page-views.php';

/**
 * Protects the issue-only view number shown in issue statistics.
 */
class IssuePageViewsTest extends TestCase {

	/**
	 * Dado: WP Statistics reporta 15 visitas de la ficha del número.
	 * Entonces: el conteo público es 15.
	 */
	public function test_count_uses_hits_of_the_issue_page_only() {
		$hits_reader = static function ( $issue_id ) {
			return 42 === $issue_id ? 15 : 99;
		};

		$this->assertSame( 15, Issue_Page_Views::count( 42, $hits_reader ) );
	}

	/**
	 * Dado: el plugin no está, está apagado o no hay datos.
	 * Entonces: el conteo es 0 (la tarjeta se oculta).
	 */
	public function test_count_is_zero_when_hits_reader_returns_nothing() {
		$hits_reader = static function () {
			return 0;
		};

		$this->assertSame( 0, Issue_Page_Views::count( 42, $hits_reader ) );
	}

	/**
	 * Dado: un ID de número inválido.
	 * Entonces: el conteo es 0 y no se consulta el lector.
	 */
	public function test_invalid_issue_id_is_zero_without_reading_hits() {
		$called      = false;
		$hits_reader = static function () use ( &$called ) {
			$called = true;
			return 15;
		};

		$this->assertSame( 0, Issue_Page_Views::count( 0, $hits_reader ) );
		$this->assertFalse( $called );
	}
}
