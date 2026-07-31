<?php
/**
 * Acerca wrapper (page-acerca.html): shared institutional renderer plus
 * the page-specific sidebar (journal info + CENFISS cards).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part(
		'template-parts/content/content-institutional-page',
		null,
		array(
			'description' => __( 'Conoce el enfoque, alcance y objetivos de LOGO ET SPES, revista de filosofía multidisciplinar del Centro de Filosofía para la Investigación Stanislao Strba (CENFISS).', 'revistalogos' ),
			'aside'       => static function () {
				?>
				<div class="card">
					<h3><?php esc_html_e( 'Información de la Revista', 'revistalogos' ); ?></h3>
					<dl>
						<dt>ISSN:</dt>
						<dd><?php esc_html_e( 'Próximamente', 'revistalogos' ); ?></dd>

						<dt>DOI:</dt>
						<dd><?php esc_html_e( 'Próximamente', 'revistalogos' ); ?></dd>

						<dt><?php esc_html_e( 'Frecuencia:', 'revistalogos' ); ?></dt>
						<dd><?php esc_html_e( 'Anual', 'revistalogos' ); ?></dd>

						<dt><?php esc_html_e( 'Idioma:', 'revistalogos' ); ?></dt>
						<dd><?php esc_html_e( 'Español', 'revistalogos' ); ?></dd>

						<dt><?php esc_html_e( 'Acceso:', 'revistalogos' ); ?></dt>
						<dd><?php esc_html_e( 'Abierto', 'revistalogos' ); ?></dd>

						<dt><?php esc_html_e( 'Licencia:', 'revistalogos' ); ?></dt>
						<dd>CC BY 4.0</dd>
					</dl>
				</div>

				<div class="card">
					<h3>CENFISS</h3>
					<p><?php esc_html_e( 'El Centro de Filosofía para la Investigación Stanislao Strba (CENFISS) es una institución dedicada a la investigación y difusión del conocimiento filosófico y teológico.', 'revistalogos' ); ?></p>
					<p><strong><?php esc_html_e( 'Ubicación:', 'revistalogos' ); ?></strong> <?php esc_html_e( 'Estado de Carabobo, Valencia, Venezuela', 'revistalogos' ); ?></p>
					<p><a href="https://cenfiss.net" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visitar sitio web', 'revistalogos' ); ?><span class="visually-hidden"> (se abre en nueva pestaña)</span></a></p>
				</div>
				<?php
			},
		)
	);
endwhile;

get_footer();
