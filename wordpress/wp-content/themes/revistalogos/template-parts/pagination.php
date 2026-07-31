<?php
/**
 * Accessible pagination, static-parity markup (partials/pagination.html).
 * Stable page/N/ URLs from WordPress pagination.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : $GLOBALS['wp_query'];
$revistalogos_total = (int) $revistalogos_query->max_num_pages;

if ( $revistalogos_total <= 1 ) {
	return;
}

$revistalogos_current = max( 1, (int) get_query_var( 'paged' ) );

$revistalogos_pages = paginate_links(
	array(
		'total'     => $revistalogos_total,
		'current'   => $revistalogos_current,
		'type'      => 'array',
		'prev_next' => false,
		'mid_size'  => 1,
		'end_size'  => 1,
	)
);
?>
<nav class="pagination" aria-label="<?php esc_attr_e( 'Navegación de páginas', 'revistalogos' ); ?>">
	<div class="pagination__info">
		<?php
		/* translators: 1: current page, 2: total pages. */
		printf( esc_html__( 'Página %1$d de %2$d', 'revistalogos' ), (int) $revistalogos_current, (int) $revistalogos_total );
		?>
	</div>

	<ul class="pagination__list">
		<li class="pagination__item">
			<?php if ( $revistalogos_current > 1 ) : ?>
				<a href="<?php echo esc_url( get_pagenum_link( $revistalogos_current - 1 ) ); ?>" class="pagination__link" aria-label="<?php esc_attr_e( 'Página anterior', 'revistalogos' ); ?>">
					&lsaquo; <?php esc_html_e( 'Anterior', 'revistalogos' ); ?>
				</a>
			<?php else : ?>
				<a href="#" class="pagination__link pagination__link--disabled" aria-label="<?php esc_attr_e( 'Página anterior', 'revistalogos' ); ?>" aria-disabled="true">
					&lsaquo; <?php esc_html_e( 'Anterior', 'revistalogos' ); ?>
				</a>
			<?php endif; ?>
		</li>

		<?php if ( is_array( $revistalogos_pages ) ) : ?>
			<?php
			$revistalogos_page_number = 0;
			foreach ( $revistalogos_pages as $revistalogos_page_html ) :
				// paginate_links emits anchors/spans; rebuild them with the
				// static class contract while keeping URLs and order.
				if ( false !== strpos( $revistalogos_page_html, 'dots' ) ) :
					?>
					<li class="pagination__item">
						<span class="pagination__link pagination__link--disabled" aria-hidden="true">&hellip;</span>
					</li>
					<?php
					continue;
				endif;

				$revistalogos_label = trim( wp_strip_all_tags( $revistalogos_page_html ) );
				$revistalogos_page_number = (int) str_replace( array( '.', ',' ), '', $revistalogos_label );
				$revistalogos_is_current  = ( $revistalogos_page_number === $revistalogos_current );

				preg_match( '/href="([^"]+)"/', $revistalogos_page_html, $revistalogos_href );
				$revistalogos_url = $revistalogos_is_current || empty( $revistalogos_href[1] )
					? get_pagenum_link( $revistalogos_page_number )
					: html_entity_decode( $revistalogos_href[1] );
				?>
				<li class="pagination__item">
					<?php if ( $revistalogos_is_current ) : ?>
						<a href="<?php echo esc_url( $revistalogos_url ); ?>" class="pagination__link pagination__link--current" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: page number. */ __( 'Página %d, página actual', 'revistalogos' ), $revistalogos_page_number ) ); ?>" aria-current="page">
							<?php echo esc_html( $revistalogos_label ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $revistalogos_url ); ?>" class="pagination__link" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: page number. */ __( 'Página %d', 'revistalogos' ), $revistalogos_page_number ) ); ?>">
							<?php echo esc_html( $revistalogos_label ); ?>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>

		<li class="pagination__item">
			<?php if ( $revistalogos_current < $revistalogos_total ) : ?>
				<a href="<?php echo esc_url( get_pagenum_link( $revistalogos_current + 1 ) ); ?>" class="pagination__link" aria-label="<?php esc_attr_e( 'Página siguiente', 'revistalogos' ); ?>">
					<?php esc_html_e( 'Siguiente', 'revistalogos' ); ?> &rsaquo;
				</a>
			<?php else : ?>
				<a href="#" class="pagination__link pagination__link--disabled" aria-label="<?php esc_attr_e( 'Página siguiente', 'revistalogos' ); ?>" aria-disabled="true">
					<?php esc_html_e( 'Siguiente', 'revistalogos' ); ?> &rsaquo;
				</a>
			<?php endif; ?>
		</li>
	</ul>
</nav>
