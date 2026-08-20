=== Revista LOGO ET SPES — Core ===
Contributors: cenfiss
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin propio (first-party) de la Revista de Filosofía LOGO ET SPES (CENFISS).

== Description ==

Dueño del dominio de publicación de la revista (ADR 0005): el theme
`revistalogos` solo presenta; este plugin define el modelo.

* CPTs: `issue` (revista/numeros), `article` (revista/articulos), `author`
  (revista/autores). El CPT `submission` está aplazado y no existe.
* Taxonomías: `section` (jerárquica, términos iniciales aprobados),
  `article_type` (valores canónicos article/essay/review/editorial con
  etiquetas en español), `keyword`.
* Metadatos nativos con `register_post_meta` (sanitización + autorización)
  y meta boxes nativos; sin field builders.
* Relaciones artículo↔autor (muchos a muchos) y artículo→número (uno) por
  post meta con IDs validados; limpieza al borrar referencias.
* Rol `Managing Editor` de mínimo privilegio, distinto del Editor nativo.
* «Número actual» derivado en consulta (fecha de publicación más reciente).
* Comentarios desactivados globalmente (invariante sin cookies, ADR 0011).
* Integración honeypot para Contact Form 7 (sin reCAPTCHA, sin Flamingo).
* Comandos WP-CLI de migración institucional (`wp revistalogos content
  validate|plan|import|verify`, dry-run por defecto) y de fixtures /
  bootstrap editorial (`wp revistalogos fixtures seed|bootstrap|plan|verify|teardown`).
* El bootstrap de Volume 1 reutiliza el autor canónico por slug, no crea
  autores dummy y no pisa contenido adoptado.

Fase 3: los campos `issn`, `doi` y `orcid` son almacenamiento base inerte;
la validación/visualización DOI-ORCID es Fase 4 (ADR 0013).

== Changelog ==

= 0.2.3 =
* Retira la herramienta temporal de recuperación institucional en wp-admin.
* Convierte `fixtures bootstrap` en bootstrap editorial de Volume 1:
  reutiliza el autor canónico, adopción por hash, sin identificadores falsos.

= 0.2.2 =
* Añade la herramienta temporal de recuperación institucional en wp-admin.

= 0.2.1 =
* Corrige el query var de los singles del CPT author.

= 0.2.0 =
* Alineado con la versión de proyecto 0.2.0.
* `Tested up to` WordPress 7.0 (entorno Docker `wordpress:7.0.4-php8.2-apache`).

= 0.1.0 =
* Modelo de contenido publicado inicial de la Fase 3.
