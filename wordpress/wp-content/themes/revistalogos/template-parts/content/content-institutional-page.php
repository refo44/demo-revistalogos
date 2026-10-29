<?php
/**
 * Shared renderer for institutional pages: breadcrumbs + archive-header
 * + content-layout shell. The body always comes from WordPress via
 * the_content() (canonical wording is never hardcoded here — §7.4).
 *
 * Args:
 * - aside: callable rendering the page-specific sidebar, or null.
 * - before_content / after_content: callables for page-specific regions
 *   (e.g. the contact form) kept outside the canonical prose.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_aside  = isset( $args['aside'] ) && is_callable( $args['aside'] ) ? $args['aside'] : null;
$revistalogos_before = isset( $args['before_content'] ) && is_callable( $args['before_content'] ) ? $args['before_content'] : null;
$revistalogos_after  = isset( $args['after_content'] ) && is_callable( $args['after_content'] ) ? $args['after_content'] : null;
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php get_template_part( 'template-parts/page-header', null, array( 'post' => get_post() ) ); ?>

		<div class="content-layout">
			<div class="content-main">
				<?php
				if ( $revistalogos_before ) {
					call_user_func( $revistalogos_before );
				}

				the_content();

				if ( $revistalogos_after ) {
					call_user_func( $revistalogos_after );
				}
				?>
			</div>

			<?php if ( $revistalogos_aside ) : ?>
				<aside class="sidebar">
					<?php call_user_func( $revistalogos_aside ); ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</main>
