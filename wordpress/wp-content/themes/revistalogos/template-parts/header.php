<?php
/**
 * Site header: logo + primary navigation. Markup mirrors the static
 * header partial; links use native URL APIs (ADR 0008 §2).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="header" role="banner">
	<div class="container">
		<div class="header__inner">
			<div class="header__top">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="header__logo">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo-revista.png' ); ?>" alt="" class="header__logo-img" width="1024" height="1024">
					<span class="header__logo-text">
						<span class="header__logo-title">LOGO ET SPES</span>
						<span class="header__logo-subtitle">Revista de Filosofía</span>
					</span>
				</a>
			</div>

			<nav class="nav" aria-label="Navegación principal">
				<button class="nav__toggle" aria-expanded="false" aria-controls="main-nav">
					<span class="nav__toggle-icon nav__toggle-icon--open" aria-hidden="true">☰</span>
					<span class="nav__toggle-icon nav__toggle-icon--close" aria-hidden="true">✕</span>
					<span class="visually-hidden"><?php esc_html_e( 'Menú', 'revistalogos' ); ?></span>
				</button>

				<div class="nav__container">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'container'      => false,
								'menu_class'     => 'nav__list nav__list--main',
								'menu_id'        => 'main-nav',
								'walker'         => new Revistalogos_Nav_Walker(),
								'depth'          => 2,
							)
						);
					} else {
						revistalogos_fallback_primary_nav();
					}
					?>
				</div>
			</nav>
		</div>
	</div>
</header>
