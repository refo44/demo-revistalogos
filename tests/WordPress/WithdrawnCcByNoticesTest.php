<?php
/**
 * Upgrade rewrites published Contacto and Políticas bodies.
 *
 * @package Revistalogos
 */

use Revistalogos_Core\Withdrawn_Cc_By_Notices;

/**
 * Production pages keep their post_content; the upgrade must strip the
 * withdrawn CC BY grant without touching other pages.
 */
class WithdrawnCcByNoticesTest extends WP_UnitTestCase {

	/**
	 * Dado: Contacto y Políticas publicadas con el deed CC BY.
	 * Cuando: corre el rewrite de upgrade.
	 * Entonces: esas páginas reservan el contenido; Privacidad no cambia.
	 */
	public function test_apply_to_pages_rewrites_contacto_and_politicas_only() {
		$contacto_id = $this->factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => 'contacto',
				'post_status'  => 'publish',
				'post_title'   => 'Contacto',
				'post_content' => 'El contenido editorial publicado en este sitio se distribuye bajo <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribución 4.0 Internacional</a>.',
			)
		);
		$politicas_id = $this->factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => 'politicas',
				'post_status'  => 'publish',
				'post_title'   => 'Políticas',
				'post_content' => 'El usuario consultante puede hacer uso del contenido, siempre y cuando cite la autoría y la fuente conforme a la licencia <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribución 4.0 Internacional (CC BY 4.0)</a>, ya que nuestros autores conservan todos los derechos.',
			)
		);
		$privacidad = 'El sitio sí contiene enlaces a páginas externas (CENFISS, Creative Commons, repositorios académicos, entre otros).';
		$privacy_id = $this->factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => 'privacidad',
				'post_status'  => 'publish',
				'post_title'   => 'Privacidad',
				'post_content' => $privacidad,
			)
		);

		$updated = Withdrawn_Cc_By_Notices::apply_to_pages();

		$this->assertSame( 2, $updated );
		$this->assertStringContainsString(
			'todos los derechos reservados',
			get_post( $contacto_id )->post_content
		);
		$this->assertStringNotContainsString(
			'creativecommons.org/licenses/by',
			get_post( $contacto_id )->post_content
		);
		$this->assertStringContainsString(
			'está reservado',
			get_post( $politicas_id )->post_content
		);
		$this->assertStringNotContainsString(
			'CC BY',
			get_post( $politicas_id )->post_content
		);
		$this->assertSame( $privacidad, get_post( $privacy_id )->post_content );
		$this->assertSame( 0, Withdrawn_Cc_By_Notices::apply_to_pages() );
	}
}
