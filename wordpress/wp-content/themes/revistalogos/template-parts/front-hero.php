<?php
/**
 * Front-page hero, static-parity with index.html. Hero copy is permanent
 * UI copy; the call to action points at the current issue. Shared by
 * front-page.php and the revistalogos/front-hero block.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="front-page__hero">
	<div class="hero">
		<div class="hero__content">
			<h1 class="hero__title">LOGO ET SPES</h1>
			<p class="hero__subtitle"><?php esc_html_e( 'Revista de Filosofía', 'revistalogos' ); ?></p>
			<p class="hero__description">
				<?php esc_html_e( 'La Revista de Filosofía adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS, es una publicación digital venezolana enfocada en el pensamiento filosófico multidisciplinar. Es de acceso abierto; arbitrada bajo la modalidad <doble anónimo o doble ciego>; con periodicidad anual. Sus páginas están disponibles para difundir investigaciones originales -de autores nacionales e internacionales- que coadyuven a promover el desarrollo de todas las áreas de la Filosofía.', 'revistalogos' ); ?>
			</p>
			<div class="hero__actions">
				<a href="<?php echo esc_url( revistalogos_current_issue_url() ); ?>" class="btn btn--primary btn--large"><?php esc_html_e( 'Ver número actual', 'revistalogos' ); ?></a>
			</div>
		</div>
	</div>
</section>
