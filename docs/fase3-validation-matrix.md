# Fase 3 — Matriz de validación

Evidencia durable de QA de la Fase 3. Estados permitidos: `Pass`, `Fail`,
`Pass (local)`, `Pass (transfer)`, `Pass (working tree)`, `Unverified`.
Nada se marca `Pass` sin evidencia de ejecución.

`Pass (working tree)` = comprobado en el árbol de trabajo, **sin commit**
todavía. No inventar un hash futuro.

Desde 2026-07-31 existe runtime WordPress local vía Docker (ADR 0014):
`Pass (local)` = evidencia ejecutada en ese entorno.

- **2026-07-31:** WordPress 6.8.3, PHP 8.2, MariaDB 11, `WP_ENVIRONMENT_TYPE=local`,
  código del commit `dfb91b8` (+ `docker-compose.yml` entonces sin commitear).
- **2026-08-18:** imagen `wordpress:7.0.4-php8.2-apache`; core persistido en
  `wp_data` actualizado a **7.0.4** (`wp core update --version=7.0.4` +
  `wp core update-db`; cambiar el tag no basta). PHP 8.2.33; MariaDB 11.8.8
  (`mariadb:11` sin cambio). Theme `revistalogos` y plugin `revistalogos-core`
  activos. `Tested up to: 7.0` en cabeceras de theme y plugin.
  Versión de proyecto **0.2.0** (theme `revistalogos` y plugin
  `revistalogos-core`). Terceros en Docker: Contact Form 7 **6.1.7**,
  WP Statistics **14.16.10**.

`Pass (local)` no sustituye la validación en el hosting real (cPanel
`cenfiss2` / ADR 0016) para lo que depende de ese entorno (FTPS,
`.htaccess`/LiteSpeed, versión PHP del hosting, HTTPS, cabeceras).
Corte 2026-08-19: WP 7.0.4 en `https://logo-et-spes.cenfiss.net`; PHP
efectivo **8.0.30**; transfer FTPS OK. Snapshot:
`docs/operations/produccion-wordpress.md`.

Formato de cada fila: validación, método, resultado, estado, commit probado.

## Nivel 1 — Estático

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Baseline checksums CSS/JS del estático | `shasum -a 256` | 12 hashes registrados en execution-state | Pass | 5fedf8a |
| Paridad CSS tras reorg (static/) | comparación de 12 hashes contra baseline | 12/12 idénticos | Pass | 537c94e |
| Paridad CSS theme vs static | hash agregado de ambos árboles CSS | idénticos (`550dead…`) | Pass | dfb91b8 |
| Paridad `main.js` theme vs static | `cmp` | idénticos byte a byte | Pass | dfb91b8 |
| YAML workflows (deploy, pages, deploy-wordpress) | `ruby -ryaml` | parsean (incluye el `deploy.yml` estático de entonces) | Pass | dfb91b8 |
| Retirada workflow estático | inspección de `.github/workflows/` + `ruby -ryaml` | `deploy.yml` ausente; `pages.yml` y `deploy-wordpress.yml` parsean | Pass (working tree) | 4a9fcd7 |
| JSON (`theme.json`, `content-payload.json`) | `node JSON.parse` | parsean | Pass | dfb91b8 |
| Sintaxis PHP | `php -l` (PHP 8.2, contenedor wpcli, ADR 0014) sobre los 59 archivos PHP de plugin+theme | 0 errores de sintaxis | Pass (local) | dfb91b8 |
| stylelint | `npm run lint:css` | 16 errores, todos preexistentes en el CSS congelado (verificado contra tag `pre-fase3-reorg`); no se corrigen por inmutabilidad ADR 0003 | Pass (preexistentes aceptados) | dfb91b8 |
| `git diff --check` | git | sin conflictos de espacios introducidos (los del CSS/SVG copiados preexisten en el asset congelado) | Pass | dfb91b8 |
| Identificadores falsos fuera de fixtures | grep `1234-5678\|10.1234/les\|0000-0000-` en wordpress/ | 0 apariciones fuera de `fixtures/`; payload institucional con 0 | Pass | dfb91b8 |
| Sin CPT `submission`, rol Author, `mi-cuenta` | grep | 0 apariciones | Pass | dfb91b8 |
| Sin ACF/Flamingo/reCAPTCHA/GA4/HSTS/CSP | grep | solo comentarios que documentan la prohibición | Pass | dfb91b8 |
| Sin `orcid_url`/`doi_url` almacenados ni código Crossref | grep | solo comentario que documenta el límite de Fase 4 | Pass | dfb91b8 |
| Theme sin registro de dominio | grep register_post_type/taxonomy/add_role/register_post_meta en theme | 0 | Pass | dfb91b8 |
| Flush de rewrite solo en activación/desactivación | grep `flush_rewrite_rules` | 2 apariciones, ambas en Plugin::activate/deactivate | Pass | dfb91b8 |
| Sin URLs de producción hardcodeadas en PHP | grep `logo-et-spes\|github.io` | 0 | Pass | dfb91b8 |
| Despliegue WP solo manual y acotado | revisión de `deploy-wordpress.yml` | solo `workflow_dispatch`; rutas acotadas theme/plugin; sin delete/mirror | Pass | dfb91b8 |
| Sin secretos en el repo | grep de credenciales; secretos solo como `${{ secrets.* }}` | limpio | Pass | dfb91b8 |
| Generador de payload | `node tools/generate-content-payload.mjs` | 12 entradas, 3 semillas de media; integridad estricta de `etica` en verde; cobertura canónica normas 18/27, politicas 10/18 (informativa, pendiente de confirmación editorial) | Pass | 5c1697b |

## Nivel 2 — Componente

Ejecutado en el runtime local (ADR 0014) el 2026-07-31. Queda `Unverified` lo
no ejercitado por CLI/front: guardado de meta boxes en admin
(nonce/capacidad/sanitización), esquemas REST de meta y normalización de
relaciones al borrar posts referenciados.

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| `wp revistalogos content validate` | WP-CLI local | payload v1 válido; 12 entradas, 3 semillas de media; warnings informativos esperados (footnote, cobertura normas 18/27 y politicas 10/18) | Pass (local) | dfb91b8 |
| `wp revistalogos content plan` (estado limpio) | WP-CLI local | 12 create + 3 media import + 4 ajustes de sitio + 3 menús, dry-run sin escritura | Pass (local) | dfb91b8 |
| `wp revistalogos content import --apply` | WP-CLI local | 12 entradas creadas, 3 media importados, portada/página de posts/privacidad asignadas, 3 menús creados y asignados a sus ubicaciones | Pass (local) | dfb91b8 |
| `wp revistalogos content verify` | WP-CLI local | 12/12 OK («All migrated objects verified») | Pass (local) | dfb91b8 |
| Idempotencia del importador | re-ejecución de `plan` tras `import --apply` | 12/12 `skip unchanged`; ajustes de sitio `unchanged`; menús existentes «left untouched (owner-managed)» | Pass (local) | dfb91b8 |
| Guard de producción del importador (ADR 0004) | `import --apply` con entorno reportando `production` (wpcli sin `WP_ENVIRONMENT_TYPE`) | rechazado: «Production import requires --confirm-production and --backup» | Pass (local) | dfb91b8 |
| `wp revistalogos fixtures seed --apply` + `verify` | WP-CLI local | 39 objetos de fixture creados y verificados | Pass (local) | dfb91b8 |
| Ciclo completo de fixtures | `teardown --apply` → `verify` → `seed --apply` → `verify` | teardown limpio de posts/media/términos propios; verify reporta 0; reseed 39; verify OK | Pass (local) | dfb91b8 |
| Registro de CPTs/taxonomías con slugs ADR 0008 | activación + resolución de `/revista/numeros|articulos|autores/` y términos de fixtures | archivos y singles de issue/article 200 (dfb91b8). 2026-08-18: single CPT `author` 404 (query var nativa; archivo y REST OK) — ver cobertura `single-author` | Pass (local) | dfb91b8 |
| Meta boxes admin (nonce/capacidad/sanitización), esquemas REST, limpieza de relaciones al borrar | requiere ejercicio manual en admin/REST | — | Unverified | dfb91b8 |

## Nivel 3 — Integración

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Activación de plugin y theme | WP-CLI local | `revistalogos-core` activo, theme `Revista LOGO ET SPES` activo, sin fatales (debug.log vacío) | Pass (local) | dfb91b8 |
| Permalinks «Nombre de la entrada» + jerarquía de plantillas | `wp rewrite structure '/%postname%/'` + curl de 15 URLs clave (portada, noticias, 8 institucionales, privacidad, buscar, 3 archivos CPT) | 15/15 HTTP 200; front-page, archive-issue, single-issue y contacto verificados renderizando en navegador | Pass (local) | dfb91b8 |
| Búsqueda `/buscar/?q=` | curl + render | 200 y render de resultados con fixtures sembradas | Pass (local) | dfb91b8 |
| CF7 en página de contacto (ADR 0010) | instalación CF7 6.1.6 + `wp option update revistalogos_contact_form_id` | formulario CF7 renderizado en `/contacto/` (marcadores `wpcf7` presentes); envío de correo no probado (sin SMTP local) | Pass (local) | dfb91b8 |
| WP Statistics instalado y sirviendo assets localmente (ADR 0011) | instalación 14.16.10 + inspección de HTML | activo; tracker JS servido desde el propio sitio; configuración operativa de `docs/operations/third-party-plugins.md` pendiente en producción | Pass (local) | dfb91b8 |
| Despliegue FTPS a producción (workflow WU11) | GitHub Actions run #1, Environment `wordpress-production` | Jobs theme + plugin **Success** (~27 s). Theme y plugin activos en `logo-et-spes.cenfiss.net`. QA de paridad/cookies/CF7/cabeceras en el hosting sigue abierta. | Pass (transfer) | 8ebc8ee |
| Cabeceras de seguridad del hosting (ADR 0012) | requiere curl al hosting real | — | Unverified | dfb91b8 |

## Nivel 4 — Regresión de cara al usuario

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Cero cookies en front-end (ADR 0011) | `curl -I` sobre `/`, `/revista/numeros/`, `/buscar/?q=`, `/contacto/` | 0 cabeceras `Set-Cookie` en las cuatro | Pass (local) | dfb91b8 |
| Sin recursos externos en el front-end (ADR 0011) | grep de `src`/`srcset` en HTML renderizado de portada | 0 recursos de hosts externos (los hosts externos presentes son solo `href` de enlaces y JSON-LD/comentarios) | Pass (local) | dfb91b8 |
| Smoke visual (escritorio) | screenshots de portada, archivo de números y single de número en navegador | renderizan con el diseño del theme, navegación migrada y fixtures | Pass (local) | dfb91b8 |
| Smoke post-upgrade WordPress 7.0.4 | curl + navegador sobre portada, nav, archivos/singles CPT, páginas institucionales, `/buscar/?q=`, 404, media, login wp-admin; `wp core version` / `php -v` / MariaDB | Core 7.0.4, PHP 8.2.33, MariaDB 11.8.8; theme y plugin activos; 200 en portada, issues, articles, institucionales, búsqueda, 404; media JPEG/PDF 200. **Excepción:** single CPT `author` 404 (ver cobertura `single-author`). Placeholder de número sin cover corregido a JPEG real. | Pass (local) | working tree 2026-08-18 |
| Paridad visual static↔WP (móvil/tablet/escritorio/200%/320px), teclado, foco, almacenamiento, copy ES | protocolo completo de paridad, pendiente | — | Unverified | dfb91b8 |

## Matriz de cobertura static → WordPress

Estado: `Implemented` = plantilla escrita y revisada. Smoke local (ADR 0014,
2026-07-31): las 15 URLs clave devuelven 200 y front-page, archive-issue,
single-issue y page-contacto renderizan verificados en navegador. La columna
Validation se mantiene `Unverified` por fila hasta ejecutar el protocolo
completo de paridad visual (nivel 4).

| Static source | WordPress template | Shared part | Dynamic source | Status | Validation | Known differences |
| ------------- | ------------------ | ----------- | -------------- | ------ | ---------- | ----------------- |
| index.html | front-page.php | issue-card, article-card | current issue query, 3 artículos recientes, 4 noticias | Implemented | Unverified | portada demo sustituida por featured image; sin datos → secciones colapsan |
| noticias.html | home.php | pagination, content-none | posts page query | Implemented | Unverified | — |
| page-acerca.html | page-acerca.php | content-institutional-page | page content (payload static) | Implemented | Unverified | — |
| page-contacto.html | page-contacto.php | content-institutional-page + región CF7 | page content + CF7 (`revistalogos_contact_form_id`) | Implemented | Unverified | formulario `mailto:` de la maqueta reemplazado por CF7/fallback (ADR 0010) |
| page-enlaces.html | page-enlaces.php | content-institutional-page | page content | Implemented | Unverified | — |
| page-enviar-colaboracion.html | page-enviar-colaboracion.php | content-institutional-page | page content + adjuntos por token | Implemented | Unverified | — |
| page-etica.html | page-etica.php | content-institutional-page | texto canónico literal (content-source) | Implemented | Unverified | cuerpo = canon literal, no el resumen demo (mandato docs/03 §2) |
| page-normas.html | page-normas.php | content-institutional-page | page content + adjuntos por token | Implemented | Unverified | cobertura canónica 18/27 párrafos verbatim — divergencias pendientes de confirmación editorial |
| page-politicas.html | page-politicas.php | content-institutional-page | page content | Implemented | Unverified | cobertura canónica 10/18 — ídem |
| page-privacidad.html | privacy-policy.php | content-institutional-page | página de Ajustes → Privacidad | Implemented | Unverified | — |
| page-comite-editorial.html | page-comite-editorial.php | content-institutional-page | page content | Implemented | Unverified | avatares vía token de asset del theme |
| archive-issue.html | archive-issue.php | issue-card, pagination, content-none | WP_Query issue | Implemented | Unverified | stat «páginas» omitida (sin fuente de datos); descripción sin rango de años demo |
| single-issue.html | single-issue.php | toc, breadcrumbs | issue + artículos vinculados + editorial inline | Implemented | Unverified | stat «páginas» omitida |
| archive-article.html | archive-article.php (+ taxonomy-*.php delegados) | article-card, pagination, content-none | WP_Query article; filtros con query vars nativas (s/section/year) | Implemented | Unverified | filtros funcionales (en la maqueta eran decorativos) |
| single-article.html | single-article.php | metadata-box, breadcrumbs | article + autores + issue + citas generadas | Implemented | Unverified | fila ORCID de la maqueta ausente (display de identificadores = Fase 4, ADR 0013); script de citas externo (ledger #1) |
| archive-author.html | archive-author.php | author-card, pagination | WP_Query author | Implemented | Unverified | la maqueta era estado vacío deliberado; lista dinámica con componentes card existentes |
| single-author.html | single-author.php | article-card | author + artículos vinculados | Implemented | Unverified | ídem; sin display ORCID (Fase 4). **Pretty permalink `/revista/autores/{slug}/` 404:** el CPT se registra como `author` y choca con la query var nativa de WordPress; el archivo `/revista/autores/` y REST `wp/v2/author` sí resuelven. Observado 2026-08-18 en WP 7.0.4; no se cambió el plugin en el upgrade. |
| single-post.html | single.php | breadcrumbs | post + 2 relacionadas derivadas | Implemented | Unverified | — |
| search.html | page-buscar.php (+ search.php redirect 301) | article/issue/author-card, content-none | Queries::search_query con prioridad documentada (docs/04) | Implemented | Unverified | copy de descripción sin «maqueta estática»; resultados renderizados (la maqueta solo mostraba estado vacío) |
| 404.html | 404.php | — | enlace dinámico a número actual | Implemented | Unverified | — |
