<?php
/**
 * Ética wrapper (page-etica.html). The body is the literal canonical
 * "Normas de Ética" text imported from content-source (docs/03 §2: the
 * static demo summary must not be used). Sidebar cards stay here.
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
			'description' => __( 'Principios éticos y responsabilidades que rigen la labor editorial, la autoría y la evaluación por pares en LOGO ET SPES.', 'revistalogos' ),
			'aside'       => static function () {
				?>
				<div class="card">
					<h2><?php esc_html_e( 'Documentos relacionados', 'revistalogos' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/normas/' ) ); ?>"><?php esc_html_e( 'Normas de Publicación', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/politicas/' ) ); ?>"><?php esc_html_e( 'Políticas', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/enviar-colaboracion/' ) ); ?>"><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></a></li>
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
