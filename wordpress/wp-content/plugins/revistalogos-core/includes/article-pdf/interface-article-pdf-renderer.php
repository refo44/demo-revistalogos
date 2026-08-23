<?php
/**
 * Replaceable article PDF renderer seam (ADR 0017 work unit 3).
 *
 * WordPress-independent. A real library is not selected here.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * article/source input → PDF rendering attempt → bytes or empty failure.
 */
interface Article_Pdf_Renderer {

	/**
	 * Render an article source to PDF bytes.
	 *
	 * Success is a non-empty string. Empty string is failure.
	 * This slice does not persist or write files.
	 *
	 * @param mixed $source Opaque article/source payload.
	 * @return string
	 */
	public function render( $source );
}
