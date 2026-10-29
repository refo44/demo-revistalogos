<?php
/**
 * Domain blocks for the block templates (ADR 0015 §7, issue #78). The
 * plugin registers the blocks and resolves their data; the markup comes
 * from the active theme's template-parts, so the classic PHP views and
 * the block templates share one source (ADR 0005). Empty data, or a
 * theme without the part, renders an empty string.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block families and their editor-only preview script.
 */
class Domain_Blocks {

	const EDITOR_SCRIPT = 'revistalogos-domain-blocks-editor';

	/**
	 * Hook registration on init.
	 */
	public static function register_hooks() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers the editor script, then each block family. No front-end
	 * asset: the blocks reuse main.css from the theme.
	 */
	public static function register() {
		wp_register_script(
			self::EDITOR_SCRIPT,
			REVISTALOGOS_CORE_URL . 'assets/js/domain-blocks-editor.js',
			array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-server-side-render' ),
			REVISTALOGOS_CORE_VERSION,
			true
		);

		Issue_Blocks::register();
		Article_Blocks::register();
		Author_Blocks::register();
	}

	/**
	 * Registers one block from plugin blocks/<name>/block.json.
	 *
	 * @param string   $name            Block directory under blocks/.
	 * @param callable $render_callback Server render.
	 */
	public static function register_block( $name, $render_callback ) {
		register_block_type(
			REVISTALOGOS_CORE_DIR . 'blocks/' . $name,
			array( 'render_callback' => $render_callback )
		);
	}

	/**
	 * Buffers a theme template part. A missing part, or one that prints
	 * only whitespace, yields ''.
	 *
	 * @param string $slug Template part slug.
	 * @param array  $args Part args.
	 * @return string
	 */
	public static function theme_part( $slug, $args ) {
		ob_start();
		$found = get_template_part( $slug, null, $args );
		$html  = (string) ob_get_clean();

		if ( false === $found || '' === trim( $html ) ) {
			return '';
		}

		return $html;
	}

	/**
	 * The published-or-previewed post the block context points at, when
	 * it has the expected type.
	 *
	 * @param \WP_Block|null $block     Block instance.
	 * @param string         $post_type Expected post type.
	 * @return \WP_Post|null
	 */
	public static function context_post( $block, $post_type ) {
		if ( ! $block instanceof \WP_Block || empty( $block->context['postId'] ) ) {
			return null;
		}

		$post = get_post( (int) $block->context['postId'] );

		return $post && $post_type === $post->post_type ? $post : null;
	}

	/**
	 * Heading attribute limited to the tags the cards accept.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function heading( $attributes ) {
		return isset( $attributes['heading'] ) && 'h3' === $attributes['heading'] ? 'h3' : 'h2';
	}
}
