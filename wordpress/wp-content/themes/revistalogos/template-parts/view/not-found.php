<?php
/**
 * 404 body. Shared by 404.php and templates/404.html
 * (revistalogos/view).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="error-page">
	<p class="error-page__code">404</p>
	<h1 class="error-page__title"><?php esc_html_e( 'La página que busca no existe.', 'revistalogos' ); ?></h1>
	<p class="error-page__description"><?php esc_html_e( 'Puede volver al inicio o consultar el número actual.', 'revistalogos' ); ?></p>
	<div class="error-page__actions">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Volver al inicio', 'revistalogos' ); ?></a>
		<a href="<?php echo esc_url( revistalogos_current_issue_url() ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ver número actual', 'revistalogos' ); ?></a>
	</div>
</section>
