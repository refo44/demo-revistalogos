<?php
/**
 * Site footer, mirroring the static footer partial. Link lists come
 * from the footer menu locations when assigned and fall back to the
 * frozen static lists rendered with native URLs.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_issue_archive   = get_post_type_archive_link( 'issue' );
$revistalogos_article_archive = get_post_type_archive_link( 'article' );
?>
<footer class="footer" role="contentinfo">
	<div class="container">
		<div class="footer__inner">
			<div class="footer__section">
				<h3>LOGO ET SPES</h3>
				<p><?php esc_html_e( 'Revista de Filosofía adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS.', 'revistalogos' ); ?></p>
				<p><strong>ISSN:</strong> <?php esc_html_e( 'Próximamente', 'revistalogos' ); ?><br>
				<strong><?php esc_html_e( 'Depósito Legal:', 'revistalogos' ); ?></strong> <?php esc_html_e( 'Próximamente', 'revistalogos' ); ?></p>
			</div>

			<div class="footer__section">
				<h3><?php esc_html_e( 'Enlaces Rápidos', 'revistalogos' ); ?></h3>
				<?php if ( has_nav_menu( 'footer-quick' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-quick',
							'container'      => false,
							'menu_class'     => 'footer__list',
							'depth'          => 1,
							'walker'         => new Revistalogos_Footer_Nav_Walker(),
						)
					);
					?>
				<?php else : ?>
					<ul class="footer__list">
						<li><a href="<?php echo esc_url( $revistalogos_issue_archive ? $revistalogos_issue_archive : home_url( '/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Números publicados', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( $revistalogos_article_archive ? $revistalogos_article_archive : home_url( '/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Artículos', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/enviar-colaboracion/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/buscar/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Búsqueda', 'revistalogos' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer__section">
				<h3><?php esc_html_e( 'Normas Editoriales', 'revistalogos' ); ?></h3>
				<?php if ( has_nav_menu( 'footer-norms' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer-norms',
							'container'      => false,
							'menu_class'     => 'footer__list',
							'depth'          => 1,
							'walker'         => new Revistalogos_Footer_Nav_Walker(),
						)
					);
					?>
				<?php else : ?>
					<ul class="footer__list">
						<li><a href="<?php echo esc_url( home_url( '/normas/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Normas de Publicación', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/etica/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Ética Editorial', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/politicas/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Políticas', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/comite-editorial/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Comité Editorial', 'revistalogos' ); ?></a></li>
						<li><a href="<?php echo esc_url( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/privacidad/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Privacidad', 'revistalogos' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer__section">
				<h3><?php esc_html_e( 'Contacto', 'revistalogos' ); ?></h3>
				<p><strong>CENFISS</strong><br>
				<?php esc_html_e( 'Centro de Filosofía para la Investigación <Stanislao Strba> - CENFISS', 'revistalogos' ); ?></p>
				<p><strong><?php esc_html_e( 'Ubicación:', 'revistalogos' ); ?></strong> <?php esc_html_e( 'Estado de Carabobo, Valencia, Venezuela', 'revistalogos' ); ?></p>
				<p><?php esc_html_e( 'Email:', 'revistalogos' ); ?> <a href="mailto:revista.cenfiss@gmail.com" class="footer__link">revista.cenfiss@gmail.com</a><br>
				Web: <a href="https://cenfiss.net" class="footer__link" target="_blank" rel="noopener noreferrer">cenfiss.net<span class="visually-hidden"> (se abre en nueva pestaña)</span></a></p>
			</div>
		</div>

		<div class="footer__bottom">
			<p>&copy; 2025 CENFISS.</p>
			<p><?php esc_html_e( 'Contenido del sitio bajo', 'revistalogos' ); ?> <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener noreferrer">Creative Commons Atribución 4.0 Internacional<span class="visually-hidden"> (se abre en nueva pestaña)</span></a>. <?php esc_html_e( 'Código del sitio bajo', 'revistalogos' ); ?> <a href="https://github.com/refo44/demo-revistalogos/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">MIT<span class="visually-hidden"> (se abre en nueva pestaña)</span></a>.</p>
		</div>

		<div class="footer-credits">
			<p><?php esc_html_e( 'Sitio creado por:', 'revistalogos' ); ?> <a href="https://www.linkedin.com/in/rafaelfigueredo/" target="_blank" rel="noopener noreferrer" class="footer-credits__link">Rafael Figueredo Oropeza<span class="visually-hidden"> (abre en nueva pestaña)</span></a> &middot; <a href="mailto:refo44@gmail.com" class="footer-credits__link">refo44@gmail.com</a> &middot; <a href="https://www.instagram.com/ref8chan/" target="_blank" rel="noopener noreferrer" class="footer-credits__link">@ref8chan<span class="visually-hidden"> (abre en nueva pestaña)</span></a></p>
		</div>
	</div>
</footer>
