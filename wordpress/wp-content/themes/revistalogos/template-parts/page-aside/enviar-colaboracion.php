<?php
/**
 * Enviar colaboración sidebar: useful links.
 * The aside.sidebar wrapper comes from the caller: the institutional
 * page renderer or the revistalogos/page-aside block.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
