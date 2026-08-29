<?php
/**
 * Editorial offprint template for the generated article PDF
 * (issue #10, BACKLOG item 3; ADR 0017 §5).
 *
 * Pure presentation: plain field values in, self-contained HTML out.
 * No WordPress calls, so the visual contract is unit-testable without
 * booting WordPress (docs/24 §6).
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Approved mockup: opción 1 «Clásico filológico» — centered offprint,
 * DejaVu Serif, grayscale only, empty fields omitted.
 *
 * DOI/ORCID/ISSN are rendered exactly as stored, as inert text
 * (ADR 0013): no derived URLs, no validation.
 */
class Article_Pdf_Editorial_Template {

	const SPANISH_MONTHS = array(
		1  => 'enero',
		2  => 'febrero',
		3  => 'marzo',
		4  => 'abril',
		5  => 'mayo',
		6  => 'junio',
		7  => 'julio',
		8  => 'agosto',
		9  => 'septiembre',
		10 => 'octubre',
		11 => 'noviembre',
		12 => 'diciembre',
	);

	/**
	 * @param array $fields Editorial field values. body_html is already
	 *                      sanitized upstream (wp_kses_post) and is the
	 *                      only value emitted unescaped.
	 * @return string Self-contained HTML document.
	 */
	public function render( $fields ) {
		$fields = is_array( $fields ) ? $fields : array();

		$title     = $this->text( $fields, 'title' );
		$body_html = isset( $fields['body_html'] ) && is_string( $fields['body_html'] ) ? $fields['body_html'] : '';

		return '<!DOCTYPE html>' . "\n"
			. '<html lang="es">' . "\n"
			. '<head>' . "\n"
			. '<meta charset="UTF-8">' . "\n"
			. '<title>' . $this->esc( $title ) . '</title>' . "\n"
			. '<style>' . $this->document_css() . '</style>' . "\n"
			. '</head>' . "\n"
			. '<body>' . "\n"
			. '<div id="page-footer">&#8212;&nbsp;<span class="pagenum"></span>&nbsp;&#8212;</div>' . "\n"
			. '<article>' . "\n"
			. '<header>' . "\n"
			. $this->masthead( $fields )
			. $this->title_block( $fields, $title )
			. $this->abstracts( $fields )
			. '</header>' . "\n"
			. '<main>' . "\n"
			. $body_html . "\n"
			. '</main>' . "\n"
			. '</article>' . "\n"
			. '</body>' . "\n"
			. '</html>';
	}

	/**
	 * Centered bibliographic masthead: journal name plus the citation
	 * context line (volume/number/year, pages, ISSN, section). The line
	 * and each segment are omitted when their data is absent.
	 *
	 * @param array $fields Editorial field values.
	 * @return string
	 */
	private function masthead( $fields ) {
		$journal_name = $this->text( $fields, 'journal_name' );
		$biblio_line  = $this->biblio_line( $fields );

		if ( '' === $journal_name && '' === $biblio_line ) {
			return '';
		}

		$html = '<div class="masthead">' . "\n";
		if ( '' !== $journal_name ) {
			$html .= '<p class="journal">' . $this->esc( $journal_name ) . '</p>' . "\n";
		}
		if ( '' !== $biblio_line ) {
			$html .= '<p class="biblio">' . $biblio_line . '</p>' . "\n";
		}
		$html .= '<hr class="rule">' . "\n" . '</div>' . "\n";

		return $html;
	}

	/**
	 * «Vol. 1, N.º 2 (2026), pp. 45-68 · ISSN … · Sección: …» with every
	 * absent segment dropped. Already-escaped output.
	 *
	 * @param array $fields Editorial field values.
	 * @return string
	 */
	private function biblio_line( $fields ) {
		$volume  = $this->positive_int( $fields, 'volume' );
		$number  = $this->positive_int( $fields, 'number' );
		$year    = $this->positive_int( $fields, 'year' );
		$pages   = $this->text( $fields, 'pages' );
		$issn    = $this->text( $fields, 'issn' );
		$section = $this->text( $fields, 'section' );

		$volume_number_parts = array();
		if ( $volume > 0 ) {
			$volume_number_parts[] = 'Vol. ' . $volume;
		}
		if ( $number > 0 ) {
			$volume_number_parts[] = 'N.º ' . $number;
		}
		$citation = implode( ', ', $volume_number_parts );
		if ( $year > 0 ) {
			$citation = ( '' !== $citation ) ? $citation . ' (' . $year . ')' : (string) $year;
		}
		if ( '' !== $pages ) {
			$pages_segment = 'pp. ' . $this->esc( $pages );
			$citation      = ( '' !== $citation ) ? $citation . ', ' . $pages_segment : $pages_segment;
		}

		$segments = array();
		if ( '' !== $citation ) {
			$segments[] = $citation;
		}
		if ( '' !== $issn ) {
			$segments[] = 'ISSN ' . $this->esc( $issn );
		}
		if ( '' !== $section ) {
			$segments[] = 'Sección: ' . $this->esc( $section );
		}

		return implode( ' · ', $segments );
	}

	/**
	 * Centered title block: title, English title, authors with their
	 * affiliation/ORCID line, inert DOI, and Spanish editorial dates.
	 *
	 * @param array  $fields Editorial field values.
	 * @param string $title  Article title.
	 * @return string
	 */
	private function title_block( $fields, $title ) {
		$html = '<div class="titleblock">' . "\n"
			. '<h1>' . $this->esc( $title ) . '</h1>' . "\n";

		$title_en = $this->text( $fields, 'title_en' );
		if ( '' !== $title_en ) {
			$html .= '<p class="title-en">' . $this->esc( $title_en ) . '</p>' . "\n";
		}

		$authors = isset( $fields['authors'] ) && is_array( $fields['authors'] ) ? $fields['authors'] : array();
		foreach ( $authors as $author ) {
			if ( ! is_array( $author ) ) {
				continue;
			}
			$name = $this->text( $author, 'name' );
			if ( '' === $name ) {
				continue;
			}
			$html .= '<p class="author">' . $this->esc( $name ) . '</p>' . "\n";

			$affiliation_parts = array();
			$affiliation       = $this->text( $author, 'affiliation' );
			$orcid             = $this->text( $author, 'orcid' );
			if ( '' !== $affiliation ) {
				$affiliation_parts[] = $this->esc( $affiliation );
			}
			if ( '' !== $orcid ) {
				$affiliation_parts[] = 'ORCID ' . $this->esc( $orcid );
			}
			if ( $affiliation_parts ) {
				$html .= '<p class="affil">' . implode( ' · ', $affiliation_parts ) . '</p>' . "\n";
			}
		}

		$doi = $this->text( $fields, 'doi' );
		if ( '' !== $doi ) {
			$html .= '<p class="ids">DOI ' . $this->esc( $doi ) . '</p>' . "\n";
		}

		$dates_line = $this->dates_line( $fields );
		if ( '' !== $dates_line ) {
			$html .= '<p class="dates">' . $dates_line . '</p>' . "\n";
		}

		return $html . '</div>' . "\n";
	}

	/**
	 * «Recibido: … · Aceptado: … · Publicado: …» in Spanish long form.
	 * Invalid or absent dates are omitted. Already-escaped output.
	 *
	 * @param array $fields Editorial field values.
	 * @return string
	 */
	private function dates_line( $fields ) {
		$labels = array(
			'received_date'    => 'Recibido',
			'accepted_date'    => 'Aceptado',
			'publication_date' => 'Publicado',
		);

		$segments = array();
		foreach ( $labels as $key => $label ) {
			$spanish_date = $this->spanish_date( $this->text( $fields, $key ) );
			if ( '' !== $spanish_date ) {
				$segments[] = $label . ': ' . $spanish_date;
			}
		}

		return implode( ' · ', $segments );
	}

	/**
	 * Y-m-d → «12 de marzo de 2026». Deterministic, locale-independent
	 * (ADR 0017 §8: output stable enough for tests).
	 *
	 * @param string $date Y-m-d candidate.
	 * @return string Spanish long date, or '' when not a valid Y-m-d.
	 */
	private function spanish_date( $date ) {
		if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches ) ) {
			return '';
		}

		$year  = (int) $matches[1];
		$month = (int) $matches[2];
		$day   = (int) $matches[3];
		if ( ! checkdate( $month, $day, $year ) ) {
			return '';
		}

		return $day . ' de ' . self::SPANISH_MONTHS[ $month ] . ' de ' . $year;
	}

	/**
	 * Resumen / Abstract / Palabras clave blocks, each omitted when empty.
	 *
	 * @param array $fields Editorial field values.
	 * @return string
	 */
	private function abstracts( $fields ) {
		$abstract    = $this->text( $fields, 'abstract' );
		$abstract_en = $this->text( $fields, 'abstract_en' );
		$keywords    = $this->keyword_list( $fields );

		if ( '' === $abstract && '' === $abstract_en && '' === $keywords ) {
			return '';
		}

		$html = '';
		if ( '' !== $abstract ) {
			$html .= '<div class="abstract"><span class="label">Resumen:</span> ' . $this->esc( $abstract ) . '</div>' . "\n";
		}
		if ( '' !== $abstract_en ) {
			$html .= '<div class="abstract"><span class="label">Abstract:</span> ' . $this->esc( $abstract_en ) . '</div>' . "\n";
		}
		if ( '' !== $keywords ) {
			$html .= '<p class="keywords"><span class="label">Palabras clave:</span> ' . $this->esc( $keywords ) . '</p>' . "\n";
		}

		return $html . '<hr class="abstract-rule">' . "\n";
	}

	/**
	 * @param array $fields Editorial field values.
	 * @return string «uno; dos; tres.» or ''.
	 */
	private function keyword_list( $fields ) {
		if ( ! isset( $fields['keywords'] ) || ! is_array( $fields['keywords'] ) ) {
			return '';
		}

		$keywords = array();
		foreach ( $fields['keywords'] as $keyword ) {
			if ( is_string( $keyword ) && '' !== trim( $keyword ) ) {
				$keywords[] = trim( $keyword );
			}
		}

		return $keywords ? implode( '; ', $keywords ) . '.' : '';
	}

	/**
	 * Approved opción 1 print CSS: A4 frame, DejaVu Serif, grayscale
	 * only, page counter footer, philological body paragraphs.
	 *
	 * The page counter and fixed footer are Dompdf-safe: no floats
	 * inside fixed elements (they leak into the flow and can loop
	 * pagination — verified during the Phase 1 mockups).
	 *
	 * @return string
	 */
	private function document_css() {
		return '@page { margin: 2.8cm 3cm 3cm 3cm; }'
			. 'body{font-family:"DejaVu Serif",Georgia,serif;font-size:11pt;line-height:1.55;color:#1a1a1a;margin:0;}'
			. '#page-footer{position:fixed;bottom:-2.1cm;left:0;right:0;text-align:center;font-size:9.5pt;color:#444444;}'
			. '#page-footer .pagenum:before{content:counter(page);}'
			. '.masthead{text-align:center;margin:0 0 2.2em;}'
			. '.masthead .journal{font-size:10.5pt;letter-spacing:0.35em;text-transform:uppercase;margin:0 0 0.35em;}'
			. '.masthead .biblio{font-size:9.5pt;color:#333333;margin:0 0 0.9em;}'
			. '.masthead .rule{border:0;border-top:1px solid #000000;width:34%;margin:0 auto;}'
			. '.titleblock{text-align:center;margin:2.4em 0 2em;}'
			. '.titleblock h1{font-size:17pt;font-weight:bold;line-height:1.3;margin:0 0 0.5em;}'
			. '.titleblock .title-en{font-size:12pt;font-style:italic;font-weight:normal;color:#333333;margin:0 0 1.6em;}'
			. '.titleblock .author{font-size:12pt;margin:0 0 0.15em;}'
			. '.titleblock .affil{font-size:9.5pt;color:#333333;margin:0 0 0.9em;}'
			. '.titleblock .ids{font-size:9pt;color:#444444;margin:0.8em 0 0;}'
			. '.titleblock .dates{font-size:9pt;color:#444444;margin:0.2em 0 0;}'
			. '.abstract{margin:0 2.2em 1.1em;font-size:10pt;line-height:1.5;text-align:justify;}'
			. '.abstract .label{font-weight:bold;}'
			. '.keywords{margin:0 2.2em 2.4em;font-size:10pt;}'
			. '.keywords .label{font-weight:bold;}'
			. '.abstract-rule{border:0;border-top:1px solid #999999;width:18%;margin:1.6em auto;}'
			. 'main{text-align:justify;}'
			. 'main p{margin:0;text-indent:1.5em;}'
			. 'main h2+p,main h3+p{text-indent:0;}'
			. 'main h2{font-size:12pt;text-align:center;font-weight:bold;margin:1.8em 0 0.9em;}'
			. 'main h3{font-size:11pt;font-style:italic;font-weight:normal;text-align:center;margin:1.5em 0 0.8em;}'
			. 'main ul,main ol{margin:0 0 0.8em 1.4em;}'
			. 'blockquote{margin:1em 2.2em;font-size:10pt;line-height:1.5;page-break-inside:avoid;}'
			. 'blockquote p{text-indent:0;}'
			. 'sup{font-size:7.5pt;}'
			. 'table{border-collapse:collapse;}'
			. 'table td,table th{border:1px solid #999999;padding:4px 6px;font-size:10pt;}'
			. 'img{max-width:100%;height:auto;}';
	}

	/**
	 * @param array  $values Source array.
	 * @param string $key    Key to read.
	 * @return string Trimmed string value or ''.
	 */
	private function text( $values, $key ) {
		if ( ! isset( $values[ $key ] ) || ! is_string( $values[ $key ] ) ) {
			return '';
		}

		return trim( $values[ $key ] );
	}

	/**
	 * @param array  $values Source array.
	 * @param string $key    Key to read.
	 * @return int Positive integer value or 0.
	 */
	private function positive_int( $values, $key ) {
		if ( ! isset( $values[ $key ] ) || ! is_numeric( $values[ $key ] ) ) {
			return 0;
		}

		return max( 0, (int) $values[ $key ] );
	}

	/**
	 * HTML-escape a plain value. Pure equivalent of esc_html for this
	 * WordPress-free template.
	 *
	 * @param string $value Plain text.
	 * @return string
	 */
	private function esc( $value ) {
		return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}
}
