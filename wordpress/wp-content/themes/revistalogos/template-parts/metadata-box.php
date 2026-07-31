<?php
/**
 * Article metadata box, static-parity with partials/metadata-box.html.
 * The ORCID row of the prototype is deferred to Fase 4 (ADR 0013):
 * identifier display is out of Fase 3 scope, so the row is absent.
 *
 * Args:
 * - article_id: article post ID (required).
 *
 * @package Revistalogos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$revistalogos_article_id = isset( $args['article_id'] ) ? absint( $args['article_id'] ) : 0;

if ( 0 === $revistalogos_article_id ) {
	return;
}

$revistalogos_authors  = revistalogos_article_authors( $revistalogos_article_id );
$revistalogos_doi      = get_post_meta( $revistalogos_article_id, 'doi', true );
$revistalogos_pages    = get_post_meta( $revistalogos_article_id, 'pages', true );
$revistalogos_sections = get_the_terms( $revistalogos_article_id, 'section' );
$revistalogos_keywords = get_the_terms( $revistalogos_article_id, 'keyword' );
$revistalogos_received = get_post_meta( $revistalogos_article_id, 'received_date', true );
$revistalogos_accepted = get_post_meta( $revistalogos_article_id, 'accepted_date', true );
$revistalogos_pubdate  = get_post_meta( $revistalogos_article_id, 'publication_date', true );

$revistalogos_date_format = get_option( 'date_format' );

$revistalogos_affiliations = array();
foreach ( $revistalogos_authors as $revistalogos_author ) {
	$revistalogos_aff = get_post_meta( $revistalogos_author->ID, 'afiliacion', true );
	if ( $revistalogos_aff ) {
		$revistalogos_affiliations[ $revistalogos_author->ID ] = $revistalogos_aff;
	}
}
?>
<section class="metadata-box" aria-labelledby="metadata-title">
	<h2 id="metadata-title" class="metadata-box__title"><?php esc_html_e( 'Información del Artículo', 'revistalogos' ); ?></h2>

	<dl class="metadata-box__list">
		<?php if ( $revistalogos_authors ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Autores', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value">
					<?php
					$revistalogos_out = array();
					$revistalogos_n   = 1;
					foreach ( $revistalogos_authors as $revistalogos_author ) {
						$revistalogos_link = sprintf(
							'<a href="%s" class="metadata-box__value--link">%s</a>',
							esc_url( get_permalink( $revistalogos_author ) ),
							esc_html( get_the_title( $revistalogos_author ) )
						);

						if ( isset( $revistalogos_affiliations[ $revistalogos_author->ID ] ) ) {
							$revistalogos_link .= '<sup>' . (int) $revistalogos_n . '</sup>';
							$revistalogos_n++;
						}

						$revistalogos_out[] = $revistalogos_link;
					}
					echo implode( ', ', $revistalogos_out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $revistalogos_affiliations ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Afiliaciones', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value">
					<?php
					$revistalogos_out = array();
					$revistalogos_n   = 1;
					foreach ( $revistalogos_affiliations as $revistalogos_aff ) {
						$revistalogos_out[] = '<sup>' . (int) $revistalogos_n . '</sup>' . esc_html( $revistalogos_aff );
						$revistalogos_n++;
					}
					echo implode( '<br>', $revistalogos_out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</dd>
			</div>
		<?php endif; ?>

		<div class="metadata-box__item">
			<dt class="metadata-box__label">DOI</dt>
			<dd class="metadata-box__value"><?php echo $revistalogos_doi ? esc_html( $revistalogos_doi ) : esc_html__( 'Próximamente', 'revistalogos' ); ?></dd>
		</div>

		<?php if ( $revistalogos_pages ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Páginas', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( $revistalogos_pages ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $revistalogos_sections ) && $revistalogos_sections ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Sección', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( implode( ', ', wp_list_pluck( $revistalogos_sections, 'name' ) ) ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $revistalogos_keywords ) && $revistalogos_keywords ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Palabras clave', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( implode( ', ', wp_list_pluck( $revistalogos_keywords, 'name' ) ) ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( $revistalogos_received ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Recibido', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_received ) ) ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( $revistalogos_accepted ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Aceptado', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_accepted ) ) ); ?></dd>
			</div>
		<?php endif; ?>

		<?php if ( $revistalogos_pubdate ) : ?>
			<div class="metadata-box__item">
				<dt class="metadata-box__label"><?php esc_html_e( 'Publicado', 'revistalogos' ); ?></dt>
				<dd class="metadata-box__value"><?php echo esc_html( date_i18n( $revistalogos_date_format, strtotime( $revistalogos_pubdate ) ) ); ?></dd>
			</div>
		<?php endif; ?>

		<div class="metadata-box__item">
			<dt class="metadata-box__label"><?php esc_html_e( 'Cita sugerida', 'revistalogos' ); ?></dt>
			<dd class="metadata-box__value"><?php echo esc_html( revistalogos_article_citation( $revistalogos_article_id ) ); ?></dd>
		</div>
	</dl>
</section>
