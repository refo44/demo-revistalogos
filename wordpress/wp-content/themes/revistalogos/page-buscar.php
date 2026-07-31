<?php
/**
 * Search page (/buscar/?q=), static-parity with search.html: header,
 * form and results with the documented type priority (docs/04) and an
 * accessible empty state. No custom rewrites; q is a plain GET param.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_query_string = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$revistalogos_paged        = isset( $_GET['pg'] ) ? max( 1, absint( wp_unslash( $_GET['pg'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$revistalogos_results = null;

if ( '' !== $revistalogos_query_string && revistalogos_core_active() ) {
	$revistalogos_results = Revistalogos_Core\Queries::search_query( $revistalogos_query_string, $revistalogos_paged );
}
?>
<main id="main-content" class="main-content" tabindex="-1">
	<div class="container">
		<?php revistalogos_breadcrumbs( array( array( 'label' => __( 'Búsqueda', 'revistalogos' ) ) ) ); ?>

		<header class="search-header">
			<h1 class="archive-header__title"><?php esc_html_e( 'Búsqueda', 'revistalogos' ); ?></h1>
			<p class="archive-header__description"><?php esc_html_e( 'Busque artículos, números, autores o noticias de la revista.', 'revistalogos' ); ?></p>
		</header>

		<form class="search-form" role="search" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
			<label class="visually-hidden" for="search-query"><?php esc_html_e( 'Buscar', 'revistalogos' ); ?></label>
			<input class="search-form__input" type="search" id="search-query" name="q" value="<?php echo esc_attr( $revistalogos_query_string ); ?>" placeholder="<?php esc_attr_e( 'Título, autor, palabras clave...', 'revistalogos' ); ?>">
			<button class="btn btn--primary search-form__button" type="submit"><?php esc_html_e( 'Buscar', 'revistalogos' ); ?></button>
		</form>

		<?php if ( '' === $revistalogos_query_string ) : ?>
			<?php // No query yet: only the form, like the static screen. ?>
		<?php elseif ( $revistalogos_results && $revistalogos_results->have_posts() ) : ?>
			<section class="search-results" aria-label="<?php esc_attr_e( 'Resultados de búsqueda', 'revistalogos' ); ?>">
				<h2 class="visually-hidden"><?php esc_html_e( 'Resultados', 'revistalogos' ); ?></h2>
				<div class="archive-grid">
					<?php
					while ( $revistalogos_results->have_posts() ) :
						$revistalogos_results->the_post();
						$revistalogos_result_type = get_post_type();

						if ( 'article' === $revistalogos_result_type ) {
							get_template_part( 'template-parts/article-card', null, array( 'post' => get_post(), 'heading' => 'h3' ) );
						} elseif ( 'issue' === $revistalogos_result_type ) {
							get_template_part( 'template-parts/issue-card', null, array( 'post' => get_post(), 'heading' => 'h3' ) );
						} elseif ( 'author' === $revistalogos_result_type ) {
							get_template_part( 'template-parts/author-card', null, array( 'post' => get_post() ) );
						} else {
							?>
							<article class="card">
								<header class="card__header">
									<h3 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								</header>
								<div class="card__content"><?php the_excerpt(); ?></div>
							</article>
							<?php
						}
					endwhile;
					wp_reset_postdata();
					?>
				</div>

				<?php if ( $revistalogos_results->max_num_pages > 1 ) : ?>
					<nav class="pagination" aria-label="<?php esc_attr_e( 'Navegación de páginas', 'revistalogos' ); ?>">
						<div class="pagination__info">
							<?php
							/* translators: 1: current page, 2: total pages. */
							printf( esc_html__( 'Página %1$d de %2$d', 'revistalogos' ), (int) $revistalogos_paged, (int) $revistalogos_results->max_num_pages );
							?>
						</div>
						<ul class="pagination__list">
							<?php for ( $revistalogos_p = 1; $revistalogos_p <= (int) $revistalogos_results->max_num_pages; $revistalogos_p++ ) : ?>
								<?php
								$revistalogos_page_url = add_query_arg(
									array(
										'q'  => rawurlencode( $revistalogos_query_string ),
										'pg' => $revistalogos_p,
									),
									get_permalink()
								);
								?>
								<li class="pagination__item">
									<?php if ( $revistalogos_p === $revistalogos_paged ) : ?>
										<a href="<?php echo esc_url( $revistalogos_page_url ); ?>" class="pagination__link pagination__link--current" aria-current="page" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: page number. */ __( 'Página %d, página actual', 'revistalogos' ), $revistalogos_p ) ); ?>"><?php echo esc_html( (string) $revistalogos_p ); ?></a>
									<?php else : ?>
										<a href="<?php echo esc_url( $revistalogos_page_url ); ?>" class="pagination__link" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: page number. */ __( 'Página %d', 'revistalogos' ), $revistalogos_p ) ); ?>"><?php echo esc_html( (string) $revistalogos_p ); ?></a>
									<?php endif; ?>
								</li>
							<?php endfor; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</section>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
