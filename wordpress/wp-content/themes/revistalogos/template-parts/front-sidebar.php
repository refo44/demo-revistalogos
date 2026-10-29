<?php
/**
 * Front-page sidebar grid: latest news, Enviar colaboración and CENFISS
 * cards. Shared by front-page.php and the revistalogos/front-sidebar
 * block. News are core posts, not a journal type.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_recent_news = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'no_found_rows'  => true,
	)
);
?>
<section class="front-page__sidebar">
	<div class="sidebar-grid">
		<!-- Noticias Card -->
		<div class="sidebar-card">
			<h3><?php esc_html_e( 'Noticias', 'revistalogos' ); ?></h3>
			<?php if ( $revistalogos_recent_news ) : ?>
				<ul class="news-list">
					<?php foreach ( $revistalogos_recent_news as $revistalogos_news_item ) : ?>
						<li class="news-item">
							<a href="<?php echo esc_url( get_permalink( $revistalogos_news_item ) ); ?>" class="news-link"><?php echo esc_html( get_the_title( $revistalogos_news_item ) ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p><?php esc_html_e( 'Todavía no hay noticias publicadas.', 'revistalogos' ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( home_url( '/noticias/' ) ); ?>" class="btn btn--small btn--outline mt-4"><?php esc_html_e( 'Ver noticias', 'revistalogos' ); ?></a>
		</div>

		<!-- Enviar colaboración Card -->
		<div class="sidebar-card">
			<h3><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></h3>
			<p><?php esc_html_e( 'Consulte las normas editoriales, prepare los archivos requeridos y envíe su manuscrito conforme al proceso definido por la revista.', 'revistalogos' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/enviar-colaboracion/' ) ); ?>" class="btn btn--small btn--outline"><?php esc_html_e( 'Enviar colaboración', 'revistalogos' ); ?></a>
		</div>

		<!-- CENFISS Card -->
		<div class="sidebar-card">
			<h3>CENFISS</h3>
			<p><?php esc_html_e( 'Centro de Filosofía para la Investigación Stanislao Strba', 'revistalogos' ); ?></p>
			<p><strong><?php esc_html_e( 'Ubicación:', 'revistalogos' ); ?></strong> <?php esc_html_e( 'Estado de Carabobo, Valencia, Venezuela', 'revistalogos' ); ?></p>
			<a href="https://cenfiss.net" target="_blank" rel="noopener noreferrer" class="btn btn--small"><?php esc_html_e( 'Ir al sitio', 'revistalogos' ); ?><span class="visually-hidden"> (se abre en nueva pestaña)</span></a>
		</div>
	</div>
</section>
