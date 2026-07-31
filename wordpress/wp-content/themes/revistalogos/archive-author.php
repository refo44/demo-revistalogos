<?php
/**
 * Authors archive. The static screen (archive-author.html) is a
 * deliberate empty-state placeholder awaiting editorial records; the
 * dynamic list reuses the existing card/archive-grid components and
 * keeps the static empty state when no author exists.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_issue_archive   = get_post_type_archive_link( 'issue' );
$revistalogos_article_archive = get_post_type_archive_link( 'article' );
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
				array( 'label' => __( 'Autores', 'revistalogos' ) ),
			)
		);
		?>

		<header class="archive-header">
			<h1 class="archive-header__title"><?php esc_html_e( 'Archivo de Autores', 'revistalogos' ); ?></h1>
			<p class="archive-header__description"><?php esc_html_e( 'Autoras y autores que han publicado en LOGO ET SPES, con su afiliación y sus artículos.', 'revistalogos' ); ?></p>
		</header>

		<?php if ( have_posts() ) : ?>
			<section class="archive-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/author-card', null, array( 'post' => get_post() ) );
				endwhile;
				?>
			</section>
			<?php revistalogos_pagination(); ?>
		<?php else : ?>
			<section class="empty-state">
				<h2><?php esc_html_e( 'El directorio de autores se integrará con los registros editoriales del sitio.', 'revistalogos' ); ?></h2>
				<p><?php esc_html_e( 'Los perfiles de autor se publican junto con los artículos de cada número.', 'revistalogos' ); ?></p>
				<div class="error-page__actions">
					<?php if ( $revistalogos_article_archive ) : ?>
						<a href="<?php echo esc_url( $revistalogos_article_archive ); ?>" class="btn btn--primary"><?php esc_html_e( 'Ver artículos', 'revistalogos' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--secondary"><?php esc_html_e( 'Ir a Inicio', 'revistalogos' ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
