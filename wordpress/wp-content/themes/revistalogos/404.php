<?php
/**
 * 404 template, static-parity with 404.html.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<section class="error-page">
			<p class="error-page__code">404</p>
			<h1 class="error-page__title"><?php esc_html_e( 'La página que busca no existe.', 'revistalogos' ); ?></h1>
			<p class="error-page__description"><?php esc_html_e( 'Puede volver al inicio o consultar el número actual.', 'revistalogos' ); ?></p>
			<div class="error-page__actions">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary"><?php esc_html_e( 'Volver al inicio', 'revistalogos' ); ?></a>
				<a href="<?php echo esc_url( revistalogos_current_issue_url() ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ver número actual', 'revistalogos' ); ?></a>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();
