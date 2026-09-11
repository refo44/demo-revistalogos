<?php
/**
 * Derived issue statistics. The Secciones card is omitted when the
 * issue has no assigned section terms.
 *
 * Args:
 * - article_count (int)
 * - section_count (int)
 * - author_count (int)
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_article_count = isset( $args['article_count'] ) ? absint( $args['article_count'] ) : 0;
$revistalogos_section_count = isset( $args['section_count'] ) ? absint( $args['section_count'] ) : 0;
$revistalogos_author_count  = isset( $args['author_count'] ) ? absint( $args['author_count'] ) : 0;

if ( $revistalogos_article_count < 1 ) {
	return;
}
?>
<section class="single-issue__stats">
	<h2><?php esc_html_e( 'Estadísticas del Número', 'revistalogos' ); ?></h2>
	<div class="grid grid-cols-4 gap-4">
		<div class="card text-center">
			<h3><?php echo esc_html( (string) $revistalogos_article_count ); ?></h3>
			<p><?php esc_html_e( 'Artículos', 'revistalogos' ); ?></p>
		</div>
		<?php if ( $revistalogos_section_count > 0 ) : ?>
			<div class="card text-center">
				<h3><?php echo esc_html( (string) $revistalogos_section_count ); ?></h3>
				<p><?php esc_html_e( 'Secciones', 'revistalogos' ); ?></p>
			</div>
		<?php endif; ?>
		<div class="card text-center">
			<h3><?php echo esc_html( (string) $revistalogos_author_count ); ?></h3>
			<p><?php esc_html_e( 'Autores', 'revistalogos' ); ?></p>
		</div>
	</div>
</section>
