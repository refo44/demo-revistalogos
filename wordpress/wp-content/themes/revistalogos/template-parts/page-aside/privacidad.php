<?php
/**
 * Visitor privacy sidebar (ADR 0011 §4): summary, related documents and site licences.
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
	<h2><?php esc_html_e( 'En resumen', 'revistalogos' ); ?></h2>
	<ul>
		<li><?php esc_html_e( 'Sin cookies.', 'revistalogos' ); ?></li>
		<li><?php esc_html_e( 'Estadísticas de uso propias, en nuestro servidor, sin identificarte.', 'revistalogos' ); ?></li>
		<li><?php esc_html_e( 'Sin rastreo entre sitios y sin recursos de terceros.', 'revistalogos' ); ?></li>
		<li><?php esc_html_e( 'Solo tratamos lo que nos escribes y los registros técnicos del servidor.', 'revistalogos' ); ?></li>
	</ul>
</div>

<div class="card">
	<h2><?php esc_html_e( 'Documentos relacionados', 'revistalogos' ); ?></h2>
	<ul>
		<li><a href="<?php echo esc_url( home_url( '/politicas/#politica-privacidad' ) ); ?>"><?php esc_html_e( 'Privacidad y confidencialidad editorial', 'revistalogos' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/politicas/' ) ); ?>"><?php esc_html_e( 'Políticas', 'revistalogos' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/etica/' ) ); ?>"><?php esc_html_e( 'Ética Editorial', 'revistalogos' ); ?></a></li>
		<li><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>"><?php esc_html_e( 'Contacto', 'revistalogos' ); ?></a></li>
	</ul>
</div>

<div class="card">
	<h2><?php esc_html_e( 'Licencias del sitio', 'revistalogos' ); ?></h2>
	<p><?php esc_html_e( 'El contenido editorial del sitio: todos los derechos reservados.', 'revistalogos' ); ?></p>
	<p><?php esc_html_e( 'El código fuente del sitio se distribuye bajo licencia', 'revistalogos' ); ?> <a href="https://github.com/refo44/demo-revistalogos/blob/main/LICENSE" target="_blank" rel="noopener noreferrer">MIT<span class="visually-hidden"> (se abre en nueva pestaña)</span></a>.</p>
</div>
