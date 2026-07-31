<?php
/**
 * Presentation helpers. Every domain lookup goes through the
 * revistalogos-core plugin when active and degrades to a safe empty
 * result when it is not — the theme never duplicates plugin behavior.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current issue (derived by the plugin; null without it).
 *
 * @return WP_Post|null
 */
function revistalogos_current_issue() {
	if ( ! revistalogos_core_active() ) {
		return null;
	}

	return Revistalogos_Core\Queries::current_issue();
}

/**
 * Published articles of an issue.
 *
 * @param int $issue_id Issue ID.
 * @param int $limit    Max results (-1 all).
 * @return WP_Post[]
 */
function revistalogos_issue_articles( $issue_id, $limit = -1 ) {
	if ( ! revistalogos_core_active() ) {
		return array();
	}

	return Revistalogos_Core\Queries::issue_articles( $issue_id, $limit );
}

/**
 * Author profiles credited on an article.
 *
 * @param int $article_id Article ID.
 * @return WP_Post[]
 */
function revistalogos_article_authors( $article_id ) {
	if ( ! revistalogos_core_active() ) {
		return array();
	}

	return Revistalogos_Core\Queries::article_authors( $article_id );
}

/**
 * Issue an article belongs to.
 *
 * @param int $article_id Article ID.
 * @return WP_Post|null
 */
function revistalogos_article_issue( $article_id ) {
	if ( ! revistalogos_core_active() ) {
		return null;
	}

	return Revistalogos_Core\Queries::article_issue( $article_id );
}

/**
 * Published articles credited to an author profile.
 *
 * @param int $author_id Author profile ID.
 * @param int $limit     Max results (-1 all).
 * @return WP_Post[]
 */
function revistalogos_author_articles( $author_id, $limit = -1 ) {
	if ( ! revistalogos_core_active() ) {
		return array();
	}

	return Revistalogos_Core\Queries::author_articles( $author_id, $limit );
}

/**
 * Comma-separated author names for card/citation contexts.
 *
 * @param int $article_id Article ID.
 * @return string Plain text (escape at output).
 */
function revistalogos_article_author_names( $article_id ) {
	$names = array();

	foreach ( revistalogos_article_authors( $article_id ) as $author ) {
		$names[] = get_the_title( $author );
	}

	return implode( ', ', $names );
}

/**
 * Issue label like "Vol. 12 Nº 2 (2025)" from issue meta; falls back to
 * the issue title when numbers are missing.
 *
 * @param int $issue_id Issue ID.
 * @return string Plain text (escape at output).
 */
function revistalogos_issue_label( $issue_id ) {
	$volume = absint( get_post_meta( $issue_id, 'volume_number', true ) );
	$number = absint( get_post_meta( $issue_id, 'issue_number', true ) );
	$year   = absint( get_post_meta( $issue_id, 'year', true ) );

	if ( $volume && $number ) {
		$label = sprintf( 'Vol. %d Nº %d', $volume, $number );

		if ( $year ) {
			$label .= sprintf( ' (%d)', $year );
		}

		return $label;
	}

	return get_the_title( $issue_id );
}

/**
 * Suggested citation (docs/03: computed from authors, title, issue,
 * year, pages and DOI — never stored).
 *
 * @param int $article_id Article ID.
 * @return string Plain text (escape at output).
 */
function revistalogos_article_citation( $article_id ) {
	$authors = revistalogos_article_author_names( $article_id );
	$title   = get_the_title( $article_id );
	$issue   = revistalogos_article_issue( $article_id );
	$pages   = get_post_meta( $article_id, 'pages', true );
	$doi     = get_post_meta( $article_id, 'doi', true );

	$parts = array();

	if ( $authors ) {
		$parts[] = $authors . '.';
	}

	$parts[] = '«' . $title . '».';
	$parts[] = 'Revista de Filosofía LOGO ET SPES';

	if ( $issue ) {
		$parts[] = revistalogos_issue_label( $issue->ID ) . '.';
	}

	if ( $pages ) {
		$parts[] = 'pp. ' . $pages . '.';
	}

	if ( $doi ) {
		$parts[] = 'DOI: ' . $doi . '.';
	}

	return implode( ' ', $parts );
}

/**
 * Attachment URL for a meta field holding an attachment ID.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key (pdf_file).
 * @return string Empty when unset or attachment missing.
 */
function revistalogos_meta_attachment_url( $post_id, $key = 'pdf_file' ) {
	$attachment_id = absint( get_post_meta( $post_id, $key, true ) );

	if ( 0 === $attachment_id ) {
		return '';
	}

	$url = wp_get_attachment_url( $attachment_id );

	return $url ? $url : '';
}

/**
 * Breadcrumbs matching the static markup. Last item is the current one.
 *
 * @param array<int, array{label: string, url?: string}> $items Trail after "Inicio".
 */
function revistalogos_breadcrumbs( $items ) {
	?>
	<nav class="breadcrumbs" aria-label="Migas de pan">
		<ol class="breadcrumbs__list">
			<li class="breadcrumbs__item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="breadcrumbs__link"><?php esc_html_e( 'Inicio', 'revistalogos' ); ?></a></li>
			<?php foreach ( $items as $item ) : ?>
				<?php if ( ! empty( $item['url'] ) ) : ?>
					<li class="breadcrumbs__item"><a href="<?php echo esc_url( $item['url'] ); ?>" class="breadcrumbs__link"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php else : ?>
					<li class="breadcrumbs__item"><span class="breadcrumbs__current"><?php echo esc_html( $item['label'] ); ?></span></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

/**
 * Accessible Spanish note for links opening in a new tab (approved
 * static pattern; avoids duplicate assistive announcements because the
 * visible ↗ arrow stays aria-hidden where used).
 */
function revistalogos_external_link_note() {
	echo '<span class="visually-hidden"> (se abre en nueva pestaña)</span>';
}

/**
 * Static-parity pagination for archive queries.
 *
 * @param WP_Query|null $query Query to paginate; defaults to the main query.
 */
function revistalogos_pagination( $query = null ) {
	get_template_part( 'template-parts/pagination', null, array( 'query' => $query ) );
}

/**
 * URL of the current issue single, or the issues archive while no issue
 * is published.
 *
 * @return string
 */
function revistalogos_current_issue_url() {
	$issue = revistalogos_current_issue();

	if ( $issue ) {
		return get_permalink( $issue );
	}

	$archive = get_post_type_archive_link( 'issue' );

	return $archive ? $archive : home_url( '/' );
}

/**
 * Resolve the "#les-current-issue" placeholder used by migrated menu
 * items to the live current-issue URL at render time.
 *
 * @param array $items Menu items.
 * @return array
 */
function revistalogos_resolve_menu_placeholders( $items ) {
	foreach ( $items as $item ) {
		if ( '#les-current-issue' === $item->url ) {
			$item->url = revistalogos_current_issue_url();
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'revistalogos_resolve_menu_placeholders' );

/**
 * Fallback primary navigation used until a menu is assigned to the
 * "primary" location. Reproduces the frozen static navigation structure
 * with native URLs; safe when revistalogos-core is inactive (the CPT
 * archive links fall back to home).
 */
function revistalogos_fallback_primary_nav() {
	$issue_archive   = get_post_type_archive_link( 'issue' );
	$article_archive = get_post_type_archive_link( 'article' );
	$author_archive  = get_post_type_archive_link( 'author' );

	$items = array(
		array( 'label' => __( 'Inicio', 'revistalogos' ), 'url' => home_url( '/' ) ),
		array(
			'label'    => __( 'Revista', 'revistalogos' ),
			'url'      => $issue_archive ? $issue_archive : home_url( '/' ),
			'children' => array(
				array( 'label' => __( 'Número actual', 'revistalogos' ), 'url' => revistalogos_current_issue_url() ),
				array( 'label' => __( 'Números publicados', 'revistalogos' ), 'url' => $issue_archive ? $issue_archive : home_url( '/' ) ),
				array( 'label' => __( 'Artículos', 'revistalogos' ), 'url' => $article_archive ? $article_archive : home_url( '/' ) ),
				array( 'label' => __( 'Autores', 'revistalogos' ), 'url' => $author_archive ? $author_archive : home_url( '/' ) ),
			),
		),
		array( 'label' => __( 'Normas', 'revistalogos' ), 'url' => home_url( '/normas/' ) ),
		array( 'label' => __( 'Enviar colaboración', 'revistalogos' ), 'url' => home_url( '/enviar-colaboracion/' ) ),
		array( 'label' => __( 'Noticias', 'revistalogos' ), 'url' => home_url( '/noticias/' ) ),
		array( 'label' => __( 'Acerca', 'revistalogos' ), 'url' => home_url( '/acerca/' ) ),
		array( 'label' => __( 'Contacto', 'revistalogos' ), 'url' => home_url( '/contacto/' ) ),
	);

	echo '<ul class="nav__list nav__list--main" id="main-nav">';

	foreach ( $items as $item ) {
		$has_children = ! empty( $item['children'] );

		printf(
			'<li class="nav__item%s"><a href="%s" class="nav__link"%s>%s</a>',
			$has_children ? ' nav__item--has-submenu' : '',
			esc_url( $item['url'] ),
			$has_children ? ' aria-haspopup="true" aria-expanded="false"' : '',
			esc_html( $item['label'] )
		);

		if ( $has_children ) {
			echo '<ul class="nav__submenu">';

			foreach ( $item['children'] as $child ) {
				printf(
					'<li><a href="%s">%s</a></li>',
					esc_url( $child['url'] ),
					esc_html( $child['label'] )
				);
			}

			echo '</ul>';
		}

		echo '</li>';
	}

	// External institutional link, mirrored verbatim from the static header.
	echo '<li class="nav__item"><a href="https://cenfiss.net" class="nav__link" target="_blank" rel="noopener noreferrer">CENFISS ↗<span class="visually-hidden"> (se abre en nueva pestaña)</span></a></li>';

	echo '</ul>';
}
