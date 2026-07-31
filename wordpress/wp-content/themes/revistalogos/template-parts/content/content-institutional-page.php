<?php
/**
 * Shared renderer for institutional pages: breadcrumbs + archive-header
 * + content-layout shell. The body always comes from WordPress via
 * the_content() (canonical wording is never hardcoded here — §7.4).
 *
 * Args:
 * - description: header description shown when the page has no excerpt.
 * - aside: callable rendering the page-specific sidebar, or null.
 * - before_content / after_content: callables for page-specific regions
 *   (e.g. the contact form) kept outside the canonical prose.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_description = isset( $args['description'] ) ? $args['description'] : '';
$revistalogos_aside       = isset( $args['aside'] ) && is_callable( $args['aside'] ) ? $args['aside'] : null;
$revistalogos_before      = isset( $args['before_content'] ) && is_callable( $args['before_content'] ) ? $args['before_content'] : null;
$revistalogos_after       = isset( $args['after_content'] ) && is_callable( $args['after_content'] ) ? $args['after_content'] : null;

if ( has_excerpt() ) {
	$revistalogos_description = get_the_excerpt();
}
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php revistalogos_breadcrumbs( array( array( 'label' => get_the_title() ) ) ); ?>

		<header class="archive-header">
			<h1 class="archive-header__title"><?php the_title(); ?></h1>
			<?php if ( $revistalogos_description ) : ?>
				<p class="archive-header__description"><?php echo esc_html( $revistalogos_description ); ?></p>
			<?php endif; ?>
		</header>

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
