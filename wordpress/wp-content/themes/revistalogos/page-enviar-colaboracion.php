<?php
/**
 * Enviar colaboración wrapper (page-enviar-colaboracion.html). The
 * public process stays email-based (no portal); documents resolve to
 * Media Library attachments inside the content.
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
			'description' => __( 'Instrucciones para enviar artículos, ensayos y reseñas a LOGO ET SPES.', 'revistalogos' ),
			'aside'       => static function () {
				?>
				<div class="card">
					<h2 class="text-2xl"><?php esc_html_e( 'Enlaces Útiles', 'revistalogos' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/normas/' ) ); ?>"><?php esc_html_e( 'Normas de publicación', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/politicas/' ) ); ?>"><?php esc_html_e( 'Políticas', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/comite-editorial/' ) ); ?>"><?php esc_html_e( 'Comité editorial', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>"><?php esc_html_e( 'Contacto', 'revistalogos' ); ?></a></li>
					</ul>
				</div>
				<?php
			},
		)
	);
endwhile;

get_footer();
