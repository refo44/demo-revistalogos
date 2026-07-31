<?php
/**
 * Accessible empty state for archives and search, static-parity with
 * the search-empty block of search.html.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_article_archive = get_post_type_archive_link( 'article' );
$revistalogos_issue_archive   = get_post_type_archive_link( 'issue' );

$revistalogos_title = isset( $args['title'] ) ? $args['title'] : __( 'No se encontraron resultados para su búsqueda.', 'revistalogos' );
$revistalogos_text  = isset( $args['text'] ) ? $args['text'] : __( 'Puede intentar con otros términos o consultar el archivo de artículos.', 'revistalogos' );
?>
<section class="search-empty" aria-labelledby="search-empty-title">
	<h2 id="search-empty-title"><?php echo esc_html( $revistalogos_title ); ?></h2>
	<p><?php echo esc_html( $revistalogos_text ); ?></p>
	<div class="error-page__actions">
		<?php if ( $revistalogos_article_archive ) : ?>
			<a href="<?php echo esc_url( $revistalogos_article_archive ); ?>" class="btn btn--primary"><?php esc_html_e( 'Ver todos los artículos', 'revistalogos' ); ?></a>
		<?php endif; ?>
		<?php if ( $revistalogos_issue_archive ) : ?>
			<a href="<?php echo esc_url( $revistalogos_issue_archive ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ver todos los números', 'revistalogos' ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ir a Inicio', 'revistalogos' ); ?></a>
	</div>
</section>
