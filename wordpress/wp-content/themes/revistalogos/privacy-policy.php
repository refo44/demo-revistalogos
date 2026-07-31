<?php
/**
 * Visitor privacy page (page-privacidad.html), rendered for the page
 * assigned in Settings → Privacy (ADR 0011 §4). Distinct from the
 * editorial confidentiality section of page-politicas.
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
			'description' => __( 'Qué datos se tratan cuando visitas este sitio web o nos escribes, y qué derechos tienes sobre ellos.', 'revistalogos' ),
			'aside'       => static function () {
				?>
				<div class="card">
					<h2><?php esc_html_e( 'En resumen', 'revistalogos' ); ?></h2>
					<ul>
						<li><?php esc_html_e( 'Sin cookies.', 'revistalogos' ); ?></li>
						<li><?php esc_html_e( 'Estadísticas de uso propias, en nuestro servidor, sin identificarte.', 'revistalogos' ); ?></li>
						<li><?php esc_html_e( 'Sin rastreo entre sitios y sin recursos de terceros.', 'revistalogos' ); ?></li>
						<li><?php esc_html_e( 'Solo tratamos lo que nos escribes y los registros técnicos del servidor.', 'revistalogos' ); ?></li>
					</ul>
				</div>

				<div class="card">
					<h2><?php esc_html_e( 'Documentos relacionados', 'revistalogos' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/politicas/#politica-privacidad' ) ); ?>"><?php esc_html_e( 'Privacidad y confidencialidad editorial', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/politicas/' ) ); ?>"><?php esc_html_e( 'Políticas', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/etica/' ) ); ?>"><?php esc_html_e( 'Ética Editorial', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>"><?php esc_html_e( 'Contacto', 'revistalogos' ); ?></a></li>
					</ul>
				</div>

				<div class="card">
					<h2><?php esc_html_e( 'Licencias del sitio', 'revistalogos' ); ?></h2>
					<p><?php esc_html_e( 'El contenido editorial del sitio se publica bajo', 'revistalogos' ); ?> <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">CC BY 4.0<span class="visually-hidden"> (se abre en nueva pestaña)</span></a>.</p>
					<p><?php esc_html_e( 'El código fuente del sitio se distribuye bajo licencia', 'revistalogos' ); ?> <a href="https://github.com/refo44/demo-revistalogos/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">MIT<span class="visually-hidden"> (se abre en nueva pestaña)</span></a>.</p>
				</div>
				<?php
			},
		)
	);
endwhile;

get_footer();
