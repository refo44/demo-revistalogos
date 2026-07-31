<?php
/**
 * Articles archive, static-parity with archive-article.html. Also
 * renders the section/article_type/keyword taxonomy archives through
 * thin taxonomy-*.php delegates (docs/12 §4.1, option A): the main
 * query arrives already filtered and only the heading adapts.
 *
 * Filters use native query vars only (s, section, year) — no custom
 * rewrites or query code (ADR 0008 KISS).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_issue_archive   = get_post_type_archive_link( 'issue' );
$revistalogos_article_archive = get_post_type_archive_link( 'article' );

$revistalogos_tax_term = is_tax() ? get_queried_object() : null;

if ( $revistalogos_tax_term instanceof WP_Term ) {
	$revistalogos_page_title = $revistalogos_tax_term->name;
	/* translators: %s: term name. */
	$revistalogos_page_description = sprintf( __( 'Artículos publicados en Logos et Spes clasificados en «%s».', 'revistalogos' ), $revistalogos_tax_term->name );
	$revistalogos_crumb            = $revistalogos_tax_term->name;
} else {
	$revistalogos_page_title       = __( 'Archivo de Artículos', 'revistalogos' );
	$revistalogos_page_description = __( 'Archivo completo de artículos publicados en Logos et Spes. Busca y accede a todos los artículos de filosofía organizados por sección, autor y fecha.', 'revistalogos' );
	$revistalogos_crumb            = __( 'Artículos', 'revistalogos' );
}

$revistalogos_sections = get_terms(
	array(
		'taxonomy'   => 'section',
		'hide_empty' => false,
	)
);

// Distinct publication years from the oldest and newest published article.
$revistalogos_years  = array();
$revistalogos_oldest = get_posts(
	array(
		'post_type'      => 'article',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	)
);
$revistalogos_newest = get_posts(
	array(
		'post_type'      => 'article',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

if ( $revistalogos_oldest && $revistalogos_newest ) {
	$revistalogos_year_from = (int) get_the_date( 'Y', $revistalogos_oldest[0] );
	$revistalogos_year_to   = (int) get_the_date( 'Y', $revistalogos_newest[0] );

	for ( $revistalogos_y = $revistalogos_year_to; $revistalogos_y >= $revistalogos_year_from; $revistalogos_y-- ) {
		$revistalogos_years[] = $revistalogos_y;
	}
}

$revistalogos_selected_section = sanitize_title( get_query_var( 'section' ) );
$revistalogos_selected_year    = absint( get_query_var( 'year' ) );
$revistalogos_search_terms     = get_search_query();
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php
		revistalogos_breadcrumbs(
			array(
				array(
					'label' => __( 'Revista', 'revistalogos' ),
					'url'   => $revistalogos_issue_archive ? $revistalogos_issue_archive : home_url( '/' ),
				),
				array( 'label' => $revistalogos_crumb ),
			)
		);
		?>

		<header class="archive-header">
			<h1 class="archive-header__title"><?php echo esc_html( $revistalogos_page_title ); ?></h1>
			<p class="archive-header__description"><?php echo esc_html( $revistalogos_page_description ); ?></p>
		</header>

		<!-- Filtros de búsqueda -->
		<section class="archive-filters">
			<div class="card">
				<form class="archive-filters__form" method="get" action="<?php echo esc_url( $revistalogos_article_archive ? $revistalogos_article_archive : home_url( '/' ) ); ?>">
					<input type="hidden" name="post_type" value="article">
					<div class="form-group">
						<label for="search-query"><?php esc_html_e( 'Buscar', 'revistalogos' ); ?></label>
						<input type="text" id="search-query" name="s" value="<?php echo esc_attr( $revistalogos_search_terms ); ?>" placeholder="<?php esc_attr_e( 'Título, autor, palabras clave...', 'revistalogos' ); ?>">
					</div>
					<div class="form-group">
						<label for="filter-section"><?php esc_html_e( 'Sección', 'revistalogos' ); ?></label>
						<select id="filter-section" name="section">
							<option value=""><?php esc_html_e( 'Todas las secciones', 'revistalogos' ); ?></option>
							<?php if ( is_array( $revistalogos_sections ) ) : ?>
								<?php foreach ( $revistalogos_sections as $revistalogos_section_term ) : ?>
									<option value="<?php echo esc_attr( $revistalogos_section_term->slug ); ?>" <?php selected( $revistalogos_selected_section, $revistalogos_section_term->slug ); ?>><?php echo esc_html( $revistalogos_section_term->name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="filter-year"><?php esc_html_e( 'Año', 'revistalogos' ); ?></label>
						<select id="filter-year" name="year">
							<option value=""><?php esc_html_e( 'Todos los años', 'revistalogos' ); ?></option>
							<?php foreach ( $revistalogos_years as $revistalogos_year_option ) : ?>
								<option value="<?php echo esc_attr( (string) $revistalogos_year_option ); ?>" <?php selected( $revistalogos_selected_year, $revistalogos_year_option ); ?>><?php echo esc_html( (string) $revistalogos_year_option ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mt-4">
						<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Buscar', 'revistalogos' ); ?></button>
						<button type="reset" class="btn btn--secondary"><?php esc_html_e( 'Limpiar filtros', 'revistalogos' ); ?></button>
					</div>
				</form>
			</div>
		</section>

		<!-- Lista de artículos -->
		<?php if ( have_posts() ) : ?>
			<section class="archive-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/article-card',
						null,
						array(
							'post'          => get_post(),
							'heading'       => 'h2',
							'show_keywords' => true,
						)
					);
				endwhile;
				?>
			</section>
			<?php revistalogos_pagination(); ?>
		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/content-none',
				null,
				array(
					'title' => __( 'No se encontraron artículos.', 'revistalogos' ),
					'text'  => __( 'Puede intentar con otros filtros o consultar el archivo de números.', 'revistalogos' ),
				)
			);
			?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
