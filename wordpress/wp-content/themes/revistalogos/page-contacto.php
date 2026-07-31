<?php
/**
 * Contacto wrapper (page-contacto.html). The canonical prose renders
 * via the_content(); the "Enviar Mensaje" region holds Contact Form 7
 * when available (ADR 0010) with an accessible mailto fallback, keeping
 * form configuration outside the canonical prose.
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
			'description'   => __( 'Información de contacto institucional de LOGO ET SPES y del CENFISS.', 'revistalogos' ),
			'after_content' => static function () {
				?>
				<section class="contact-section">
					<h2><?php esc_html_e( 'Enviar Mensaje', 'revistalogos' ); ?></h2>
					<?php if ( function_exists( 'wpcf7_contact_form' ) && get_option( 'revistalogos_contact_form_id' ) ) : ?>
						<p class="form-required-note"><?php esc_html_e( '* Campos obligatorios.', 'revistalogos' ); ?></p>
						<?php echo do_shortcode( '[contact-form-7 id="' . absint( get_option( 'revistalogos_contact_form_id' ) ) . '"]' ); ?>
						<p><a href="<?php echo esc_url( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacidad/' ) ); ?>"><?php esc_html_e( 'Consulte cómo tratamos sus datos en el Aviso de Privacidad.', 'revistalogos' ); ?></a></p>
					<?php else : ?>
						<div class="contact-info">
							<p><?php esc_html_e( 'Puede escribirnos directamente a', 'revistalogos' ); ?> <a href="mailto:revista.cenfiss@gmail.com">revista.cenfiss@gmail.com</a>.</p>
						</div>
					<?php endif; ?>
				</section>
				<?php
			},
		)
	);
endwhile;

get_footer();
