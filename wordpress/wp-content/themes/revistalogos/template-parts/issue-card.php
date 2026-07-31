<?php
/**
 * Issue card, static-parity with partials/issue-card.html.
 *
 * Args:
 * - post: WP_Post issue (required).
 * - heading: heading tag for the title (h2 in archives, h3 on the front
 *   page, matching the static hierarchy). Default h2.
 * - featured: adds the front-page featured class. Default false.
 * - show_stats: derived stats line (archive variant). Default false.
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_issue = isset( $args['post'] ) ? get_post( $args['post'] ) : null;

if ( ! $revistalogos_issue ) {
	return;
}

$revistalogos_heading    = isset( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h2';
$revistalogos_featured   = ! empty( $args['featured'] );
$revistalogos_show_stats = ! empty( $args['show_stats'] );

$revistalogos_issue_id  = $revistalogos_issue->ID;
$revistalogos_label     = revistalogos_issue_label( $revistalogos_issue_id );
$revistalogos_year      = absint( get_post_meta( $revistalogos_issue_id, 'year', true ) );
$revistalogos_issn      = get_post_meta( $revistalogos_issue_id, 'issn', true );
$revistalogos_doi       = get_post_meta( $revistalogos_issue_id, 'doi', true );
$revistalogos_pdf_url   = revistalogos_meta_attachment_url( $revistalogos_issue_id );
$revistalogos_permalink = get_permalink( $revistalogos_issue );
$revistalogos_title     = get_the_title( $revistalogos_issue );

$revistalogos_card_class = 'card issue-card' . ( $revistalogos_featured ? ' front-page__featured-issue' : '' );
?>
<article class="<?php echo esc_attr( $revistalogos_card_class ); ?>">
	<div class="issue-card__cover-container">
		<?php if ( has_post_thumbnail( $revistalogos_issue ) ) : ?>
			<?php
			echo get_the_post_thumbnail(
				$revistalogos_issue,
				'large',
				array(
					'class' => 'issue-card__cover',
					/* translators: %s: issue label. */
					'alt'   => sprintf( __( 'Portada %s', 'revistalogos' ), $revistalogos_label ),
				)
			);
			?>
		<?php else : ?>
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/placeholder-banner.jpg' ); ?>" alt="" class="issue-card__cover">
		<?php endif; ?>
	</div>

	<header class="card__header">
		<div class="issue-card__volume"><?php echo esc_html( $revistalogos_label ); ?></div>
		<<?php echo $revistalogos_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="card__title issue-card__title">
			<a href="<?php echo esc_url( $revistalogos_permalink ); ?>" class="issue-card__link">
				<?php echo esc_html( $revistalogos_title ); ?>
			</a>
		</<?php echo $revistalogos_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="issue-card__meta">
			<?php if ( $revistalogos_year ) : ?>
				<span><strong><?php esc_html_e( 'Año:', 'revistalogos' ); ?></strong> <?php echo esc_html( (string) $revistalogos_year ); ?></span>
				&bull;
			<?php endif; ?>
			<span><strong>ISSN:</strong> <?php echo $revistalogos_issn ? esc_html( $revistalogos_issn ) : esc_html__( 'Próximamente', 'revistalogos' ); ?></span>
			<?php if ( $revistalogos_doi ) : ?>
				&bull;
				<span><strong>DOI:</strong> <?php echo esc_html( $revistalogos_doi ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<div class="card__content">
		<?php if ( has_excerpt( $revistalogos_issue ) || '' !== trim( $revistalogos_issue->post_content ) ) : ?>
			<p class="issue-card__description">
				<?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $revistalogos_issue ) ) ); ?>
			</p>
		<?php endif; ?>

		<?php
		if ( $revistalogos_show_stats ) :
			$revistalogos_articles = revistalogos_issue_articles( $revistalogos_issue_id );
			$revistalogos_count    = count( $revistalogos_articles );

			$revistalogos_sections = array();
			foreach ( $revistalogos_articles as $revistalogos_article_item ) {
				$revistalogos_terms = get_the_terms( $revistalogos_article_item, 'section' );
				if ( is_array( $revistalogos_terms ) ) {
					foreach ( $revistalogos_terms as $revistalogos_term ) {
						$revistalogos_sections[ $revistalogos_term->term_id ] = true;
					}
				}
			}

			if ( $revistalogos_count > 0 ) :
				?>
				<div class="issue-card__stats">
					<span><strong>
					<?php
					/* translators: %d: number of articles. */
					echo esc_html( sprintf( _n( '%d artículo', '%d artículos', $revistalogos_count, 'revistalogos' ), $revistalogos_count ) );
					?>
					</strong></span>
					<?php if ( count( $revistalogos_sections ) > 0 ) : ?>
						&bull;
						<span><strong>
						<?php
						/* translators: %d: number of sections. */
						echo esc_html( sprintf( _n( '%d sección', '%d secciones', count( $revistalogos_sections ), 'revistalogos' ), count( $revistalogos_sections ) ) );
						?>
						</strong></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<footer class="card__footer">
		<?php if ( $revistalogos_pdf_url ) : ?>
			<a href="<?php echo esc_url( $revistalogos_pdf_url ); ?>" class="btn btn--primary btn--small"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: issue label. */ __( 'Descargar PDF completo del %s', 'revistalogos' ), $revistalogos_label ) ); ?>">
				<span aria-hidden="true">📄</span> <?php esc_html_e( 'PDF completo', 'revistalogos' ); ?>
			</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( $revistalogos_permalink ); ?>" class="btn btn--secondary btn--small">
			<?php esc_html_e( 'Ver contenido', 'revistalogos' ); ?>
		</a>
	</footer>
</article>
