<?php
/**
 * Throwaway Phase-1 script (issue #10): render the three mockup HTML files
 * to PDF with the plugin's EXISTING Dompdf vendor, using the exact renderer
 * options (remote OFF, PHP OFF, DejaVu, A4 portrait). Reads plugin vendor/
 * only; changes no plugin code. Not plugin runtime. Delete after Phase 1.
 */

require '/var/www/html/wp-content/plugins/revistalogos-core/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$files = array(
	'opcion-1-clasico-filologico',
	'opcion-2-institucional-cenfiss',
	'opcion-3-contemporaneo-sobrio',
);

foreach ( $files as $name ) {
	$html = file_get_contents( '/mockups/' . $name . '.html' );
	if ( false === $html || '' === $html ) {
		fwrite( STDERR, "SKIP $name: unreadable\n" );
		continue;
	}

	$options = new Options();
	$options->setIsRemoteEnabled( false );
	$options->setIsPhpEnabled( false );
	$options->setDefaultFont( 'DejaVu Sans' );

	$dompdf = new Dompdf( $options );
	$dompdf->loadHtml( $html, 'UTF-8' );
	$dompdf->setPaper( 'A4', 'portrait' );
	$dompdf->render();
	$pdf = $dompdf->output();

	if ( ! is_string( $pdf ) || '' === $pdf || 0 !== strpos( $pdf, '%PDF-' ) ) {
		fwrite( STDERR, "FAIL $name: no PDF bytes\n" );
		continue;
	}

	file_put_contents( '/mockups/' . $name . '.pdf', $pdf );
	echo "OK $name: " . strlen( $pdf ) . " bytes, pages: " . $dompdf->getCanvas()->get_page_count() . "\n";
}
