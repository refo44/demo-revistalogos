<?php
/**
 * Issues archive, static-parity with archive-issue.html.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$revistalogos_issue_archive = get_post_type_archive_link( 'issue' );
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
				array( 'label' => __( 'Números publicados', 'revistalogos' ) ),
			)
		);
		?>

		<header class="archive-header">
			<h1 class="archive-header__title"><?php esc_html_e( 'Archivo de Números', 'revistalogos' ); ?></h1>
			<p class="archive-header__description">
				<?php esc_html_e( 'Archivo completo de números publicados de la revista Logos et Spes. Accede a todos los volúmenes y números publicados.', 'revistalogos' ); ?>
			</p>
		</header>

		<?php if ( have_posts() ) : ?>
			<section class="archive-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part(
						'template-parts/issue-card',
						null,
						array(
							'post'       => get_post(),
							'heading'    => 'h2',
							'show_stats' => true,
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
					'title' => __( 'Todavía no hay números publicados.', 'revistalogos' ),
					'text'  => __( 'Vuelva pronto para consultar la primera edición.', 'revistalogos' ),
				)
			);
			?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
