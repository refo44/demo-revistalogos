<?php
/**
 * Author card for the authors archive. Composed from existing card
 * components (no new design system): avatar, name, affiliation, link.
 *
 * Args:
 * - post: WP_Post author profile (required).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_author = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $revistalogos_author ) {
	return;
}

$revistalogos_afiliacion = get_post_meta( $revistalogos_author->ID, 'afiliacion', true );
$revistalogos_articles   = revistalogos_author_articles( $revistalogos_author->ID );
$revistalogos_count      = count( $revistalogos_articles );
?>
<article class="card author-card">
	<header class="card__header">
		<h2 class="card__title">
			<a href="<?php echo esc_url( get_permalink( $revistalogos_author ) ); ?>"><?php echo esc_html( get_the_title( $revistalogos_author ) ); ?></a>
		</h2>
	</header>
	<div class="card__content">
		<?php if ( $revistalogos_afiliacion ) : ?>
			<p><?php echo esc_html( $revistalogos_afiliacion ); ?></p>
		<?php endif; ?>
		<?php if ( $revistalogos_count > 0 ) : ?>
			<p><strong>
			<?php
			/* translators: %d: number of articles. */
			echo esc_html( sprintf( _n( '%d artículo publicado', '%d artículos publicados', $revistalogos_count, 'revistalogos' ), $revistalogos_count ) );
			?>
			</strong></p>
		<?php endif; ?>
	</div>
	<footer class="card__footer">
		<a href="<?php echo esc_url( get_permalink( $revistalogos_author ) ); ?>" class="btn btn--secondary btn--small"><?php esc_html_e( 'Ver perfil', 'revistalogos' ); ?></a>
	</footer>
</article>
