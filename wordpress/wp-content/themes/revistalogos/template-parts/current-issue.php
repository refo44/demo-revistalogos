<?php
/**
 * Front-page "Número actual" section with the featured issue card.
 * Shared by front-page.php and the revistalogos/current-issue block.
 *
 * Args:
 * - post: WP_Post current issue (required; nothing renders without it).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_current = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $revistalogos_current ) {
	return;
}
?>
<section class="front-page__current-issue">
	<h2 class="front-page__section-title"><?php esc_html_e( 'Número actual', 'revistalogos' ); ?></h2>
	<?php
	get_template_part(
		'template-parts/issue-card',
		null,
		array(
			'post'     => $revistalogos_current,
			'heading'  => 'h3',
			'featured' => true,
		)
	);
	?>
</section>
