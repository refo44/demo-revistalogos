<?php
/**
 * Issue block family: current issue, issue card and issue TOC.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Issue blocks, rendered through the theme's issue partials.
 */
class Issue_Blocks {

	/**
	 * Registers the issue blocks.
	 */
	public static function register() {
		Domain_Blocks::register_block( 'current-issue', array( __CLASS__, 'render_current_issue' ) );
		Domain_Blocks::register_block( 'issue-card', array( __CLASS__, 'render_issue_card' ) );
		Domain_Blocks::register_block( 'issue-toc', array( __CLASS__, 'render_issue_toc' ) );
	}

	/**
	 * Front-page "Número actual" section; empty without a published issue.
	 *
	 * @return string
	 */
	public static function render_current_issue() {
		$issue = Queries::current_issue();

		if ( ! $issue ) {
			return '';
		}

		return Domain_Blocks::theme_part( 'template-parts/current-issue', array( 'post' => $issue ) );
	}

	/**
	 * Issue card for the context issue (Query Loop or single issue).
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_issue_card( $attributes, $content = '', $block = null ) {
		$issue = Domain_Blocks::context_post( $block, Content_Types::ISSUE );

		if ( ! $issue ) {
			return '';
		}

		return Domain_Blocks::theme_part(
			'template-parts/issue-card',
			array(
				'post'       => $issue->ID,
				'heading'    => Domain_Blocks::heading( $attributes ),
				'featured'   => ! empty( $attributes['featured'] ),
				'show_stats' => ! empty( $attributes['showStats'] ),
			)
		);
	}

	/**
	 * Table of contents of the context issue; empty without articles.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Unused.
	 * @param \WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_issue_toc( $attributes, $content = '', $block = null ) {
		$issue = Domain_Blocks::context_post( $block, Content_Types::ISSUE );

		if ( ! $issue ) {
			return '';
		}

		return Domain_Blocks::theme_part( 'template-parts/toc', array( 'issue_id' => $issue->ID ) );
	}
}
