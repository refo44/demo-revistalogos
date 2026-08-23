<?php
/**
 * Dompdf implementation of the article PDF renderer seam
 * (ADR 0017 work unit 4).
 *
 * Self-contained HTML string → in-memory PDF bytes. No WordPress,
 * persistence or remote resource loading.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML → PDF bytes via Dompdf.
 */
class Dompdf_Article_Pdf_Renderer implements Article_Pdf_Renderer {

	/**
	 * @param mixed $source Self-contained HTML string.
	 * @return string Non-empty PDF bytes on success; empty string on failure.
	 */
	public function render( $source ) {
		if ( ! is_string( $source ) || '' === $source ) {
			return '';
		}

		if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
			return '';
		}

		try {
			$options = new Options();
			$options->setIsRemoteEnabled( false );
			$options->setIsPhpEnabled( false );
			$options->setDefaultFont( 'DejaVu Sans' );

			$dompdf = new Dompdf( $options );
			$dompdf->loadHtml( $source, 'UTF-8' );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();
			$output = $dompdf->output();

			if ( ! is_string( $output ) || '' === $output ) {
				return '';
			}

			return $output;
		} catch ( \Throwable $e ) {
			return '';
		}
	}
}
