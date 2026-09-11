<?php
/**
 * Withdraw the public CC BY 4.0 grant from institutional page bodies.
 *
 * @package Revistalogos_Core
 */

namespace Revistalogos_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Idempotent HTML rewrite for Contacto and Políticas.
 */
class Withdrawn_Cc_By_Notices {

	/**
	 * Replace the two published CC BY sentences. Leaves other mentions alone.
	 *
	 * @param string $html Page HTML.
	 * @return string
	 */
	public static function rewrite_html( $html ) {
		$html = (string) $html;

		$html = preg_replace(
			'#El contenido editorial publicado en este sitio se distribuye bajo <a href="https://creativecommons\.org/licenses/by/4\.0/"[^>]*>Creative Commons Atribución 4\.0 Internacional</a>\.#u',
			'El contenido editorial publicado en este sitio: todos los derechos reservados.',
			$html
		);

		$html = preg_replace(
			'#El usuario consultante puede hacer uso del contenido, siempre y cuando cite la autoría y la fuente conforme a la licencia <a href="https://creativecommons\.org/licenses/by/4\.0/"[^>]*>Creative Commons Atribución 4\.0 Internacional \(CC BY 4\.0\)</a>,#u',
			'El contenido editorial está reservado. El usuario consultante no puede reproducirlo ni reutilizarlo sin autorización previa de CENFISS o del titular,',
			$html
		);

		return $html;
	}

	/**
	 * Rewrite published Contacto and Políticas if they still grant CC BY.
	 *
	 * @return int Pages updated.
	 */
	public static function apply_to_pages() {
		$updated = 0;

		foreach ( array( 'contacto', 'politicas' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( ! $page instanceof \WP_Post ) {
				continue;
			}

			$rewritten = self::rewrite_html( $page->post_content );
			if ( $rewritten === $page->post_content ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => $rewritten,
				)
			);
			++$updated;
		}

		return $updated;
	}
}
