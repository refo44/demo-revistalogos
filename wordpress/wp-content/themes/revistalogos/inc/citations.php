<?php
/**
 * Citation format builders for single-article (static parity: APA,
 * BibTeX, Vancouver, Chicago, MLA, Harvard + RIS export).
 *
 * Empty bibliographic surname uses last token = family name. A filled
 * `citation_surname` wins; otherwise `family_names` (ADR 0022). Optional
 * article `citation_override_*` replaces that box.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split a full name into given/surname parts.
 *
 * @param string $full_name         Author display name (post title).
 * @param string $citation_surname Optional bibliographic surname. Empty
 *                                  falls back to family_names, then last-token.
 * @param string $given_names      Optional stored given names.
 * @param string $family_names     Optional stored family names.
 * @return array{given: string, surname: string, initials: string}
 */
function revistalogos_split_name( $full_name, $citation_surname = '', $given_names = '', $family_names = '' ) {
	$full_name        = trim( (string) $full_name );
	$citation_surname = trim( (string) $citation_surname );
	$given_names      = trim( (string) $given_names );
	$family_names     = trim( (string) $family_names );
	$surname          = '' !== $citation_surname ? $citation_surname : $family_names;

	if ( '' !== $surname ) {
		if ( '' !== $given_names ) {
			$given = $given_names;
		} elseif ( $full_name === $surname ) {
			$given = '';
		} elseif ( preg_match( '/^(.*)\s+' . preg_quote( $surname, '/' ) . '(?:\s+.*)?$/u', $full_name, $matches ) ) {
			$given = trim( $matches[1] );
		} else {
			$given = $full_name;
		}

		return array(
			'given'    => $given,
			'surname'  => $surname,
			'initials' => revistalogos_citation_initials( $given ),
		);
	}

	$parts   = preg_split( '/\s+/', $full_name );
	$surname = array_pop( $parts );
	$given   = implode( ' ', $parts );

	return array(
		'given'    => $given,
		'surname'  => (string) $surname,
		'initials' => revistalogos_citation_initials( $given ),
	);
}

/**
 * Initials of given names (no spaces), matching the historical builder.
 *
 * @param string $given Given names.
 * @return string
 */
function revistalogos_citation_initials( $given ) {
	$initials = '';
	foreach ( preg_split( '/\s+/', (string) $given ) as $part ) {
		if ( '' !== $part ) {
			$initials .= mb_substr( $part, 0, 1 ) . '.';
		}
	}

	return $initials;
}

/**
 * Replace generated citation boxes with non-empty overrides.
 *
 * @param array<string, string> $formats   Label => generated text.
 * @param array<string, string> $overrides Label => optional override.
 * @return array<string, string>
 */
function revistalogos_merge_citation_overrides( $formats, $overrides ) {
	if ( ! is_array( $formats ) ) {
		return array();
	}

	if ( ! is_array( $overrides ) ) {
		return $formats;
	}

	foreach ( array_keys( $formats ) as $label ) {
		if ( ! isset( $overrides[ $label ] ) || ! is_string( $overrides[ $label ] ) ) {
			continue;
		}

		if ( '' === trim( $overrides[ $label ] ) ) {
			continue;
		}

		$formats[ $label ] = $overrides[ $label ];
	}

	return $formats;
}

/**
 * Format label => article meta key (ADR 0021). Plugin map wins when loaded.
 *
 * @return array<string, string>
 */
function revistalogos_citation_override_map() {
	if ( class_exists( '\Revistalogos_Core\Metadata' ) && is_callable( array( '\Revistalogos_Core\Metadata', 'citation_override_keys' ) ) ) {
		return \Revistalogos_Core\Metadata::citation_override_keys();
	}

	return array(
		'APA'       => 'citation_override_apa',
		'BibTeX'    => 'citation_override_bibtex',
		'Vancouver' => 'citation_override_vancouver',
		'Chicago'   => 'citation_override_chicago',
		'MLA'       => 'citation_override_mla',
		'Harvard'   => 'citation_override_harvard',
		'RIS'       => 'citation_override_ris',
	);
}

/**
 * Stored Cómo Citar overrides for an article (empty string = none).
 *
 * @param int $article_id Article ID.
 * @return array<string, string>
 */
function revistalogos_citation_overrides( $article_id ) {
	$overrides = array();

	foreach ( revistalogos_citation_override_map() as $label => $key ) {
		$overrides[ $label ] = (string) get_post_meta( $article_id, $key, true );
	}

	return $overrides;
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
		$authors[] = revistalogos_split_name(
			get_the_title( $author ),
			(string) get_post_meta( $author->ID, 'citation_surname', true ),
			(string) get_post_meta( $author->ID, 'given_names', true ),
			(string) get_post_meta( $author->ID, 'family_names', true )
		);
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

	return revistalogos_merge_citation_overrides( $formats, revistalogos_citation_overrides( $article_id ) );
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

	$generated = implode( "\n", $lines );
	$merged    = revistalogos_merge_citation_overrides(
		array( 'RIS' => $generated ),
		revistalogos_citation_overrides( $article_id )
	);

	return $merged['RIS'];
}
