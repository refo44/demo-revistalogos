<?php
/**
 * Contacto "Enviar Mensaje" region: Contact Form 7 when configured
 * (ADR 0010), otherwise an accessible mailto fallback. Kept outside the
 * canonical prose. Shared by page-contacto.php and the
 * revistalogos/contact-form block.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="contact-section">
	<h2><?php esc_html_e( 'Enviar Mensaje', 'revistalogos' ); ?></h2>
	<?php if ( function_exists( 'wpcf7_contact_form' ) && get_option( 'revistalogos_contact_form_id' ) ) : ?>
		<p class="form-required-note"><?php esc_html_e( '* Campos obligatorios.', 'revistalogos' ); ?></p>
		<?php echo do_shortcode( '[contact-form-7 id="' . absint( get_option( 'revistalogos_contact_form_id' ) ) . '" html_class="contact-form"]' ); ?>
		<p><a href="<?php echo esc_url( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacidad/' ) ); ?>"><?php esc_html_e( 'Consulte cómo tratamos sus datos en el Aviso de Privacidad.', 'revistalogos' ); ?></a></p>
	<?php else : ?>
		<div class="contact-info">
			<p><?php esc_html_e( 'Puede escribirnos directamente a', 'revistalogos' ); ?> <a href="mailto:revista.cenfiss@gmail.com">revista.cenfiss@gmail.com</a>.</p>
		</div>
	<?php endif; ?>
</section>
