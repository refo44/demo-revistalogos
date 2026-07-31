<?php
/**
 * Citation format builders for single-article (static parity: APA,
 * BibTeX, Vancouver, Chicago, MLA, Harvard + RIS export).
 *
 * Name handling uses a bounded heuristic (last token = surname, rest =
 * given names); editors can adjust the author post title order if a
 * compound surname renders wrong.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split a full name into given/surname parts.
 *
 * @param string $full_name Author display name.
 * @return array{given: string, surname: string, initials: string}
 */
function revistalogos_split_name( $full_name ) {
	$parts   = preg_split( '/\s+/', trim( $full_name ) );
	$surname = array_pop( $parts );
	$given   = implode( ' ', $parts );

	$initials = '';
	foreach ( $parts as $part ) {
		if ( '' !== $part ) {
			$initials .= mb_substr( $part, 0, 1 ) . '.';
		}
	}

	return array(
		'given'    => $given,
		'surname'  => (string) $surname,
		'initials' => $initials,
	);
}

/**
 * Gather the data every citation format needs.
 *
 * @param int $article_id Article ID.
 * @return array<string, mixed>
 */
function revistalogos_citation_data( $article_id ) {
	$issue = revistalogos_article_issue( $article_id );

	$authors = array();
	foreach ( revistalogos_article_authors( $article_id ) as $author ) {
		$authors[] = revistalogos_split_name( get_the_title( $author ) );
	}

	$pub_date = get_post_meta( $article_id, 'publication_date', true );
	$year     = $pub_date ? substr( $pub_date, 0, 4 ) : get_the_date( 'Y', $article_id );

	$pages = get_post_meta( $article_id, 'pages', true );
	$doi   = get_post_meta( $article_id, 'doi', true );

	return array(
		'title'   => get_the_title( $article_id ),
		'authors' => $authors,
		'year'    => $year,
		'volume'  => $issue ? absint( get_post_meta( $issue->ID, 'volume_number', true ) ) : 0,
		'number'  => $issue ? absint( get_post_meta( $issue->ID, 'issue_number', true ) ) : 0,
		'pages'   => $pages,
		'doi'     => $doi ? $doi : __( 'Próximamente', 'revistalogos' ),
		'url'     => get_permalink( $article_id ),
	);
}

/**
 * Build the six visible citation formats.
 *
 * @param int $article_id Article ID.
 * @return array<string, string> Format label => plain-text citation.
 */
function revistalogos_citation_formats( $article_id ) {
	$d = revistalogos_citation_data( $article_id );

	$apa_authors = array();
	$full_names  = array();
	$mla_names   = array();

	foreach ( $d['authors'] as $i => $a ) {
		$apa_authors[] = $a['surname'] . ', ' . $a['initials'];
		$full_names[]  = ( 0 === $i ) ? $a['surname'] . ', ' . $a['given'] : $a['given'] . ' ' . $a['surname'];
		$mla_names[]   = $a['given'] . ' ' . $a['surname'];
	}

	$vol_no = $d['volume'] ? sprintf( '%d(%d)', $d['volume'], $d['number'] ) : '';

	$formats = array();

	$formats['APA'] = sprintf(
		'%s (%s). %s. LOGO ET SPES, %s%s. DOI: %s',
		implode( ', & ', $apa_authors ),
		$d['year'],
		$d['title'],
		$vol_no,
		$d['pages'] ? ', ' . $d['pages'] : '',
		$d['doi']
	);

	$bibtex_key = '';
	if ( $d['authors'] ) {
		$bibtex_key = strtolower( remove_accents( $d['authors'][0]['surname'] ) ) . $d['year'];
	}

	$formats['BibTeX'] = "@article{{$bibtex_key},\n"
		. '  author  = {' . implode(
			' and ',
			array_map(
				static function ( $a ) {
					return $a['surname'] . ', ' . $a['given'];
				},
				$d['authors']
			)
		) . "},\n"
		. '  title   = {' . $d['title'] . "},\n"
		. "  journal = {LOGO ET SPES},\n"
		. '  year    = {' . $d['year'] . "},\n"
		. '  volume  = {' . $d['volume'] . "},\n"
		. '  number  = {' . $d['number'] . "},\n"
		. '  pages   = {' . $d['pages'] . "},\n"
		. '  doi     = {' . $d['doi'] . "}\n"
		. '}';

	$formats['Vancouver'] = sprintf(
		'%s. %s. LOGO ET SPES. %s;%s:%s. DOI: %s',
		implode(
			', ',
			array_map(
				static function ( $a ) {
					return $a['surname'] . ' ' . str_replace( '.', '', $a['initials'] );
				},
				$d['authors']
			)
		),
		$d['title'],
		$d['year'],
		$d['volume'] ? sprintf( '%d(%d)', $d['volume'], $d['number'] ) : '',
		$d['pages'],
		$d['doi']
	);

	$formats['Chicago'] = sprintf(
		'%s. "%s." LOGO ET SPES %s, no. %s (%s)%s. DOI: %s',
		implode( ', and ', $full_names ),
		$d['title'],
		$d['volume'],
		$d['number'],
		$d['year'],
		$d['pages'] ? ': ' . $d['pages'] : '',
		$d['doi']
	);

	$formats['MLA'] = sprintf(
		'%s. "%s." LOGO ET SPES, vol. %s, no. %s, %s%s. DOI: %s',
		implode( ', and ', $mla_names ),
		$d['title'],
		$d['volume'],
		$d['number'],
		$d['year'],
		$d['pages'] ? ', pp. ' . $d['pages'] : '',
		$d['doi']
	);

	$formats['Harvard'] = sprintf(
		"%s %s, '%s', LOGO ET SPES, vol. %s, no. %s%s. DOI: %s",
		implode(
			' & ',
			array_map(
				static function ( $a ) {
					return $a['surname'] . ', ' . $a['initials'];
				},
				$d['authors']
			)
		),
		$d['year'],
		$d['title'],
		$d['volume'],
		$d['number'],
		$d['pages'] ? ', pp. ' . $d['pages'] : '',
		$d['doi']
	);

	return $formats;
}

/**
 * RIS export payload for the download button.
 *
 * @param int $article_id Article ID.
 * @return string
 */
function revistalogos_citation_ris( $article_id ) {
	$d = revistalogos_citation_data( $article_id );

	$pages = array_pad( explode( '-', (string) $d['pages'], 2 ), 2, '' );

	$lines = array( 'TY  - JOUR', 'TI  - ' . $d['title'] );

	foreach ( $d['authors'] as $a ) {
		$lines[] = 'AU  - ' . $a['surname'] . ', ' . $a['given'];
	}

	$lines[] = 'T2  - LOGO ET SPES';
	$lines[] = 'PY  - ' . $d['year'];
	$lines[] = 'VL  - ' . $d['volume'];
	$lines[] = 'IS  - ' . $d['number'];

	if ( '' !== trim( $pages[0] ) ) {
		$lines[] = 'SP  - ' . trim( $pages[0] );
	}
	if ( '' !== trim( $pages[1] ) ) {
		$lines[] = 'EP  - ' . trim( $pages[1] );
	}

	$lines[] = 'DO  - ' . $d['doi'];
	$lines[] = 'UR  - ' . $d['url'];
	$lines[] = 'ER  - ';

	return implode( "\n", $lines );
}
