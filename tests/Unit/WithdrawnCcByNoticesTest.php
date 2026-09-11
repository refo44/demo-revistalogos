<?php
/**
 * Withdrawal of the public CC BY grant from institutional copy.
 *
 * @package Revistalogos
 */

use PHPUnit\Framework\TestCase;
use Revistalogos_Core\Withdrawn_Cc_By_Notices;

/**
 * Contacto and Políticas stop offering CC BY; other HTML is untouched.
 */
class WithdrawnCcByNoticesTest extends TestCase {

	/**
	 * Dado: la frase de Contacto que otorgaba CC BY.
	 * Entonces: queda reservada y sin el deed.
	 */
	public function test_rewrites_contacto_cc_by_sentence() {
		$html = 'El contenido editorial publicado en este sitio se distribuye bajo <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribución 4.0 Internacional</a>. MIT sigue.';

		$rewritten = Withdrawn_Cc_By_Notices::rewrite_html( $html );

		$this->assertStringContainsString(
			'El contenido editorial publicado en este sitio: todos los derechos reservados.',
			$rewritten
		);
		$this->assertStringContainsString( 'MIT sigue.', $rewritten );
		$this->assertStringNotContainsString( 'creativecommons.org/licenses/by', $rewritten );
	}

	/**
	 * Dado: la frase de Políticas que otorgaba CC BY.
	 * Entonces: reserva el uso y conserva la cita a la ley venezolana.
	 */
	public function test_rewrites_politicas_cc_by_sentence() {
		$html = 'El usuario consultante puede hacer uso del contenido, siempre y cuando cite la autoría y la fuente conforme a la licencia <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribución 4.0 Internacional (CC BY 4.0)</a>, ya que nuestros autores conservan todos los derechos.';

		$rewritten = Withdrawn_Cc_By_Notices::rewrite_html( $html );

		$this->assertStringContainsString(
			'El contenido editorial está reservado. El usuario consultante no puede reproducirlo ni reutilizarlo sin autorización previa de CENFISS o del titular,',
			$rewritten
		);
		$this->assertStringContainsString( 'ya que nuestros autores conservan todos los derechos.', $rewritten );
		$this->assertStringNotContainsString( 'CC BY', $rewritten );
	}

	/**
	 * Dado: un aviso de Privacidad que solo nombra Creative Commons como enlace externo.
	 * Entonces: no se reescribe.
	 */
	public function test_leaves_unrelated_creative_commons_mention() {
		$html = 'El sitio sí contiene enlaces a páginas externas (CENFISS, Creative Commons, repositorios académicos, entre otros).';

		$this->assertSame( $html, Withdrawn_Cc_By_Notices::rewrite_html( $html ) );
	}
}
