<?php
/**
 * Institutional page header: breadcrumbs plus archive-header with the
 * page title and its description (excerpt, else the slug default).
 * Shared by content-institutional-page.php and the
 * revistalogos/page-header block.
 *
 * Args:
 * - post: page ID or WP_Post.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_page = get_post( isset( $args['post'] ) ? $args['post'] : null );

if ( ! $revistalogos_page ) {
	return;
}

$revistalogos_description = revistalogos_page_description( $revistalogos_page );

revistalogos_breadcrumbs( array( array( 'label' => get_the_title( $revistalogos_page ) ) ) );
?>

<header class="archive-header">
	<h1 class="archive-header__title"><?php echo wp_kses_post( get_the_title( $revistalogos_page ) ); ?></h1>
	<?php if ( $revistalogos_description ) : ?>
		<p class="archive-header__description"><?php echo esc_html( $revistalogos_description ); ?></p>
	<?php endif; ?>
</header>
