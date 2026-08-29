<?php
/**
 * Throwaway visual sample (Phase 2 verification, issue #10).
 * Read-only: builds the offprint source for an existing published
 * article and renders it with the production renderer. Writes only
 * to /mockups (host tmp dir). Delete after review.
 */
$ids = array_map( 'intval', explode( ',', getenv( 'SAMPLE_IDS' ) ?: '' ) );
$builder  = new Revistalogos_Core\Article_Pdf_WordPress_Source_Builder();
$renderer = new Revistalogos_Core\Dompdf_Article_Pdf_Renderer();
foreach ( $ids as $id ) {
	if ( $id <= 0 ) { continue; }
	$html = $builder->build( $id );
	if ( is_wp_error( $html ) ) { echo "ERR $id: " . $html->get_error_code() . "\n"; continue; }
	file_put_contents( "/mockups/muestra-articulo-$id.html", $html );
	$pdf = $renderer->render( $html );
	if ( '' === $pdf ) { echo "ERR $id: render vacío\n"; continue; }
	file_put_contents( "/mockups/muestra-articulo-$id.pdf", $pdf );
	echo "OK $id: " . strlen( $pdf ) . " bytes, título: " . get_the_title( $id ) . "\n";
}
