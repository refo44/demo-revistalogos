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
- **2026-08-20:** imagen `wordpress:7.1.0-php8.2-apache`;
  core local **7.1**; PHP 8.2; MariaDB `mariadb:11`. Evidencia histórica
  del baseline anterior.
- **2026-08-21:** imagen `wordpress:7.1.0-php8.3-apache`;
  core local **7.1**; PHP **8.3**; MariaDB `mariadb:11` sin cambio.
  En esa fecha producción seguía en PHP **8.0.30**.
  `config.platform.php` sigue **8.2.0**.
- **2026-08-22 (baseline vigente):** local/CI PHP **8.3** (WP 7.1);
  producción `logo-et-spes.cenfiss.net` PHP **8.3** (WP 7.1), vía
  CloudLinux PHP Selector + Site Isolation (no MultiPHP Manager).
  `config.platform.php` **8.2.0**. `Requires PHP: 7.4`.
  Las filas de 7.0.4 / PHP 8.2 / producción 8.0.30 de abajo son
  evidencia histórica.

`Pass (local)` no sustituye la validación en el hosting real (cPanel
`cenfiss2` / ADR 0016) para lo que depende de ese entorno (FTPS,
`.htaccess`/LiteSpeed, versión PHP del hosting, HTTPS, cabeceras).
Corte 2026-08-19: WP 7.0.4 en `https://logo-et-spes.cenfiss.net`; PHP
efectivo **8.0.30**; transfer FTPS OK. Snapshot:
`docs/operations/produccion-wordpress.md`.

- **2026-08-19, recuperación institucional (working tree):** plugin
  `revistalogos-core` **0.2.2**; QA en proyecto Docker aislado
  `revistalogos-recovery-qa` sobre WordPress 7.0.4/PHP 8.2/MariaDB 11,
  puerto 8081. El harness destruyó solo sus volúmenes efímeros al terminar;
  no modificó la BD Docker principal ni producción. Evidencia:
  `tools/qa-content-recovery-admin.sh`.

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
| Despliegue WP solo desde etiqueta `vX.Y.Z` (ADR 0020) | revisión de `deploy-wordpress.yml` + `./tools/require-production-release-tag.sh` en HEAD de `main` (sin tag) y contra el commit de `v0.2.0` | job `require-release-tag` antes de FTPS; script exit 1 en HEAD suelto; `v0.2.0` anotada coincide con el gate. Sin disparo `on.push` de tags. | Pass (working tree) | working tree 2026-08-24 |
| Sin secretos en el repo | grep de credenciales; secretos solo como `${{ secrets.* }}` | limpio | Pass | dfb91b8 |
| Generador de payload | `node tools/generate-content-payload.mjs` | 12 entradas, 3 semillas de media; integridad estricta de `etica` en verde; cobertura canónica normas 18/27, politicas 10/18 (informativa, pendiente de confirmación editorial) | Pass | 5c1697b |
| Sintaxis PHP de recuperación institucional | `php -l` en Docker sobre migrador, admin temporal y comando CLI | 0 errores de sintaxis | Pass (working tree) | working tree 2026-08-19 |
| PHPUnit unit suite (Testing Foundation + ADR 0017 WU1) | `composer test:unit` / `./tools/run-phpunit.sh` (PHP **8.3**, sin WordPress) | 6 tests / 12 assertions (`revistalogos_split_name` + política PDF keep/generate/block) | Pass (working tree) | working tree 2026-08-22 |
| PHP syntax gate (`php -l`) | `./tools/php-lint.sh` / `composer lint:php` sobre plugin, theme y `tests/` (PHP **8.3**, 64 archivos) | 0 errores de sintaxis | Pass (working tree) | working tree 2026-08-23 |
| Composer lockfile platform compatibility | `composer install --no-interaction --prefer-dist --no-progress` sin `--ignore-platform-reqs` (`config.platform.php` 8.2.0; `doctrine/instantiator` 2.0.0) | install exit 0 | Pass (working tree) | working tree 2026-08-21 |
| Composer lockfile audit | `composer audit --locked` (PHPUnit + transitivas; no WP/npm/hosting) | 0 advisories | Pass (working tree) | working tree 2026-08-20 |
| YAML `dependabot.yml` + `test.yml` | `ruby -ryaml` | parsean | Pass (working tree) | working tree 2026-08-21 |
| Tests cache action Node 24 | `actions/cache@v5` en `test.yml`; mismos `path`/`key`/`restore-keys` | declarado | Pass (working tree) | working tree 2026-08-21 |

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
| Guard: demo seed bloqueado en production | `WORDPRESS_CONFIG_EXTRA` production + `fixtures seed --apply` | rechazado; `--confirm-production` no lo desbloquea | Pass (local) | working tree 2026-08-19 |
| `wp revistalogos fixtures bootstrap` (dry-run y `--apply`) | WP-CLI local | 1 author + 1 issue + 1 article draft; `_les_fixture=1` kind `bootstrap`; DOI/ORCID/ISSN vacíos; no pisa un issue no-fixture; re-run idempotente; `teardown --kind=bootstrap` no borra el no-fixture | Pass (local) | working tree 2026-08-19 (superseded by 0.2.3 Volume 1 bootstrap) |
| Guard: bootstrap `--apply` en production sin confirm | WP-CLI local con entorno production | rechazado: exige `--confirm-production` y `--backup` | Pass (local) | working tree 2026-08-19 |
| Retirada UI recovery + migrador durable | Docker aislado `revistalogos-bootstrap-qa` | `Content_Recovery_Admin` ausente; Tools page no registrada; `content validate/import/verify` OK | Pass (working tree) | working tree 2026-08-19 |
| Volume 1 editorial bootstrap | `tools/qa-editorial-bootstrap.sh` Docker aislado puerto 8082 | plan sin escritura; 1 issue + 7 articles; Rafael reutilizado y sin marcadores; orden TOC; sin DOI/ORCID/ISSN; idempotente; adopción no pisada; teardown no borra adoptado/Rafael/Pages; HTTP 200 | Pass (working tree) | working tree 2026-08-19 |
| UI temporal Volume 1 bootstrap | `tools/qa-volume1-bootstrap-admin.sh` Docker aislado puerto 8083 | admin 200 / no-admin 403 / anónimo 302; nonce 403; plan sin escritura; Rafael 0/>1 bloquea; colisión issue/article bloquea; confirmación obligatoria; sin backup/force/teardown; apply+verify; adopción no pisada; Pages intactas; HTTP 200 | Pass (working tree) 2026-08-19. **Retirada en 0.2.6:** el harness ahora comprueba que `Bootstrap_Admin` está ausente. | working tree 2026-08-20 |
| Plugin 0.2.6 picker de autores + PDF + Gutenberg | `tools/qa-article-editorial-ux.sh` Docker aislado puerto 8084 | catálogo no precargado; REST `/wp/v2/author` acotado; authors int[]; publish exige autor publicado; Gutenberg on; PDF MIME; CTA `:visited` contraste AA (tokens). 320px/200% zoom: **PASS static/preflight, NOT LIVE-VERIFIED** (comprobar en navegador tras deploy). Re-run WU2 2026-08-23: PASS (regla de autor y PDF picker intactas). | Pass (working tree) 2026-08-23 | working tree 2026-08-23 |
| ADR 0017 WU2 adaptador PDF de solo lectura | `tools/qa-article-pdf-adapter.sh` Docker aislado puerto 8085 | KEEP_EXISTING con PDF válido; GENERATE_REQUIRED si falta/0/adjunto ausente/JPEG; sin escritura; publish sin PDF sigue permitido; guard de autor intacto; upgrade no muta. Re-run WU3 2026-08-23: PASS (carga `load_modules` con WU3). | Pass (working tree) 2026-08-23 | working tree 2026-08-23 |
| ADR 0017 WU3 orquestación + seam de renderer | PHPUnit `ArticlePdfGenerationOrchestratorTest` (puro) | KEEP_EXISTING no llama renderer; GENERATE_REQUIRED + bytes → allow; vacío → block; sin persistencia; sin hooks. Adapter harness re-run: publish sin PDF sigue permitido. | Pass (working tree) | working tree 2026-08-23 |
| ADR 0017 WU4 renderer Dompdf + Composer plugin | PHPUnit `ArticlePdfDompdfRendererTest`; `tools/qa-article-pdf-renderer.sh` Docker aislado puerto 8086 | HTML UTF-8 → bytes `%PDF-` en memoria en contenedor Apache `wordpress` (no solo WP-CLI); Unicode ES; WU3 acepta artifact; sin adjunto/`pdf_file`; publish sin PDF sigue permitido; guard de autor intacto | Pass (working tree) | working tree 2026-08-23 |
| ADR 0017 WU5 persistencia Media Library | `tools/qa-article-pdf-persistence.sh` Docker aislado puerto 8087 | bytes Dompdf → adjunto `application/pdf` + parent Article + archivo `%PDF-` + `pdf_file` = ID; artifact/artículo inválido → `WP_Error` sin escritura; artículo sigue draft; publish sin PDF sigue permitido; guard de autor intacto | Pass (working tree) | working tree 2026-08-23 |
| ADR 0017 WU6A source + composición explícita | `tools/qa-article-pdf-composition.sh` Docker aislado puerto 8088 | HTML local título/cuerpo/autores; Gutenberg sin comentarios de bloque; generate → adjunto `%PDF-` + `pdf_file` ID; KEEP_EXISTING no reemplaza; artículo draft; candidate title/body; publish sin generate no crea PDF; guard de autor intacto | Pass (working tree) | working tree 2026-08-23 |
| ADR 0017 WU6B enforcement de publicación | `tools/qa-article-pdf-publication-enforcement.sh` Docker aislado puerto 8089 | opción ausente = OFF; Settings → LOGO ET SPES / manage_options; OFF classic+REST publican sin PDF (delta 0); toggle no muta; ON genera +1; KEEP delta 0; manual same-request no genera; REST +1 no +2; autor bloquea antes de generar; fallo persistencia no publica; aviso classic + WP_Error REST; save publicado/draft/pending no genera | Pass (working tree) | working tree 2026-08-23 |
| Diseño editorial separata PDF (issue #10, BACKLOG 3) | PHPUnit `ArticlePdfEditorialTemplateTest` (puro) + suite WordPress `ArticlePdfEditorialSourceBuilderTest` (`tools/run-phpunit-wp.sh`, wp-phpunit 7.1, Docker aislado puerto 8090) + re-run `tools/qa-article-pdf-composition.sh` | Plantilla opción 1 «Clásico filológico» en escala de grises: masthead bibliográfico (Vol./N.º/año/pp./ISSN/sección desde issue publicado), título EN, afiliación/ORCID inertes, DOI inerte, fechas en español, resúmenes y palabras clave; campos vacíos omitidos sin etiquetas huérfanas; mínimo título+cuerpo+autores sigue generando; issue no publicado no se cita; candidate title/body conserva contexto almacenado; body `do_blocks`+`wp_kses_post` sin `<!-- wp:` ni `<script`; KEEP/default OFF/guard de autor intactos (composition re-run); muestra visual Dompdf desde artículo real local | Pass (working tree) | working tree 2026-08-28 |
| Ausencia Bootstrap_Admin 0.2.6 | `tools/qa-volume1-bootstrap-admin.sh` Docker aislado puerto 8083 | clase/archivo/Tools ausentes; plugin carga; upgrade 0.2.5→0.2.6 no muta Volume 1/Rafael/Pages; CLI plan/verify/teardown permanece | Pass (working tree) 2026-08-20 | working tree 2026-08-20 |
| Ciclo completo de fixtures | `teardown --apply` → `verify` → `seed --apply` → `verify` | teardown limpio de posts/media/términos propios; verify reporta 0; reseed 39; verify OK | Pass (local) | dfb91b8 |
| Registro de CPTs/taxonomías con slugs ADR 0008 | activación + resolución de `/revista/numeros|articulos|autores/` y términos de fixtures | archivos y singles de issue/article 200 (dfb91b8). Single CPT `author`: 404 histórico 2026-08-18; `query_var=journal_author` corrige el single localmente (ver `tools/qa-author-permalinks.sh`) | Pass (local) | working tree 2026-08-19 |
| Meta boxes admin (nonce/capacidad/sanitización), esquemas REST, limpieza de relaciones al borrar | requiere ejercicio manual en admin/REST | — | Unverified | dfb91b8 |
| Acceso a herramienta temporal | HTTP autenticado: administrador, suscriptor y anónimo | administrador 200; suscriptor 403; anónimo redirigido a login | Pass (local) | working tree 2026-08-19 |
| Nonce de herramienta temporal | POST real a Tools sin nonce | rechazado con HTTP 403 antes de enviar cabecera admin; cero importación | Pass (local) | working tree 2026-08-19 |
| `Validate and Plan` sin escritura | hash SHA-256 de filas completas de posts/postmeta, opciones de lectura/theme mods y tablas de términos/relaciones antes/después | hash before/after idéntico (assert automático); 12 slugs `MISSING`, 12 acciones `create` | Pass (local) | working tree 2026-08-19 |
| Preflight `MANUAL EXISTING` | Page local `normas` sin `_les_source_key`, plan + intento de importación | bloqueado; no creó `normas-2`; Page de prueba eliminada | Pass (local) | working tree 2026-08-19 |
| Preflight `AMBIGUOUS` | Page local `acerca` con `_les_source_key=wrong-source` y Page `etica` contaminada con `_les_fixture=1` | ambos bloqueados; no creó `acerca-2`; objetos efímeros eliminados con el entorno aislado | Pass (local) | working tree 2026-08-19 |
| Guards de importación admin | POST sin evidencia, sin confirmación, sin plan firmado, parámetro `force=1` inyectado | cada ausencia bloquea; UI sin campo force; controlador fija `import_report(true, false)` | Pass (local) | working tree 2026-08-19 |
| Error runtime de media | `upload_path` efímero apuntado a un archivo no escribible durante el import admin | errores visibles; Verify FAIL; no se ejecutan etapas de Pages/settings; 0 Pages y 0 adjuntos migrados | Pass (local) | working tree 2026-08-19 |
| Importación institucional admin | POST real con plan vigente, evidencia local y confirmación | 12 Pages, 3 adjuntos, 3 menús; 21 items/títulos y 3 locations exactos; 4 opciones de lectura exactas; 5 marcadores `_les_source_*` en cada Page; ningún `_les_fixture`; usuarios y CPT issue/article/author sin cambios | Pass (local) | working tree 2026-08-19 |
| Verify + idempotencia admin | Verify automático, acción Verify separada y nuevo Validate/Plan | 15/15 OK (12 Pages + 3 media); 12 `MIGRATION OWNED`; re-plan 12 `skip`; 0 missing/stale/drifted/contaminated | Pass (local) | working tree 2026-08-19 |

## Nivel 3 — Integración

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Activación de plugin y theme | WP-CLI local | `revistalogos-core` activo, theme `Revista LOGO ET SPES` activo, sin fatales (debug.log vacío) | Pass (local) | dfb91b8 |
| Permalinks «Nombre de la entrada» + jerarquía de plantillas | `wp rewrite structure '/%postname%/'` + curl de 15 URLs clave (portada, noticias, 8 institucionales, privacidad, buscar, 3 archivos CPT) | 15/15 HTTP 200; front-page, archive-issue, single-issue y contacto verificados renderizando en navegador | Pass (local) | dfb91b8 |
| Búsqueda `/buscar/?q=` | curl + render | 200 y render de resultados con fixtures sembradas | Pass (local) | dfb91b8 |
| CF7 en página de contacto (ADR 0010) | instalación CF7 6.1.6 + `wp option update revistalogos_contact_form_id` | formulario CF7 renderizado en `/contacto/` (marcadores `wpcf7` presentes); envío de correo no probado (sin SMTP local) | Pass (local) | dfb91b8 |
| WP Statistics instalado y sirviendo assets localmente (ADR 0011) | instalación 14.16.10 + inspección de HTML | activo; tracker JS servido desde el propio sitio; configuración operativa de `docs/operations/third-party-plugins.md` pendiente en producción | Pass (local) | dfb91b8 |
| Despliegue FTPS a producción (workflow WU11) | GitHub Actions run #1, Environment `wordpress-production` | Jobs theme + plugin **Success** (~27 s). Theme y plugin activos en `logo-et-spes.cenfiss.net`. QA de paridad/cookies/CF7/cabeceras en el hosting sigue abierta. | Pass (transfer) | 8ebc8ee |
| Despliegue plugin 0.2.8 (hotfix Gutenberg `pdf_file`) | `workflow_dispatch` `deploy-wordpress.yml` + `curl` de `readme.txt` live | Primer Success con 0.2.8: [run 32698488419](https://github.com/refo44/demo-revistalogos/actions/runs/32698488419) (`d9bf6d2`, 2026-08-24 06:43 UTC). Live `Stable tag: 0.2.8` (`Last-Modified: 2026-08-24 06:44:05 GMT`). Re-runs [32785940259](https://github.com/refo44/demo-revistalogos/actions/runs/32785940259) y [32799914335](https://github.com/refo44/demo-revistalogos/actions/runs/32799914335) Success. Theme `0.2.1`. Smoke Gutenberg/exigencia ON **Unverified** (checkpoint cerrado por el propietario, [issue #9](https://github.com/refo44/demo-revistalogos/issues/9)). | Pass (transfer) | d9bf6d2 |
| Cabeceras de seguridad del hosting (ADR 0012) | requiere curl al hosting real | — | Unverified | dfb91b8 |
| Smoke HTTP tras recuperación institucional | `curl` contra Docker aislado | 200 en `/`, `/normas/`, `/enviar-colaboracion/`, `/acerca/`, `/contacto/`, `/noticias/`, `/etica/`, `/politicas/`, `/comite-editorial/`, `/privacidad/`, `/buscar/`, `/enlaces/` | Pass (local) | working tree 2026-08-19 |

## Nivel 4 — Regresión de cara al usuario

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Cero cookies en front-end (ADR 0011) | `curl -I` sobre `/`, `/revista/numeros/`, `/buscar/?q=`, `/contacto/` | 0 cabeceras `Set-Cookie` en las cuatro | Pass (local) | dfb91b8 |
| Sin recursos externos en el front-end (ADR 0011) | grep de `src`/`srcset` en HTML renderizado de portada | 0 recursos de hosts externos (los hosts externos presentes son solo `href` de enlaces y JSON-LD/comentarios) | Pass (local) | dfb91b8 |
| Smoke visual (escritorio) | screenshots de portada, archivo de números y single de número en navegador | renderizan con el diseño del theme, navegación migrada y fixtures | Pass (local) | dfb91b8 |
| Smoke post-upgrade WordPress 7.0.4 | curl + navegador sobre portada, nav, archivos/singles CPT, páginas institucionales, `/buscar/?q=`, 404, media, login wp-admin; `wp core version` / `php -v` / MariaDB | Core 7.0.4, PHP 8.2.33, MariaDB 11.8.8; theme y plugin activos; 200 en portada, issues, articles, institucionales, búsqueda, 404; media JPEG/PDF 200. Single CPT `author` 404 histórico; arreglo local `query_var=journal_author` (plugin 0.2.1, no desplegado). Placeholder de número sin cover corregido a JPEG real. | Pass (local) | working tree 2026-08-19 |
| Smoke post-upgrade WordPress 7.1 | `wp core version` / PHP; HTTP archivos CPT; harnesses aislados `qa-article-editorial-ux.sh`, `qa-editorial-bootstrap.sh`, `qa-volume1-bootstrap-admin.sh`; `qa-author-permalinks.sh` | Core **7.1**, PHP **8.2.33** sin cambio; plugin 0.2.6 y theme 0.2.1 activos; portada/archivos 200; Gutenberg, picker REST, guards de publicación, PDF, bootstrap y teardown sin PHP Warning/Notice/Deprecated nuevos. | Pass (local) | working tree 2026-08-20 |
| Smoke local PHP 8.3 (WP 7.1) | Recreate contenedor wordpress (sin `down -v`); `wp core version` / `php -v`; HTTP archivos; `qa-author-permalinks.sh`; aislados `qa-editorial-bootstrap.sh`, `qa-article-editorial-ux.sh`, `qa-volume1-bootstrap-admin.sh` | Core **7.1**, PHP **8.3.33**; 57 posts conservados; plugin 0.2.6 y theme 0.2.1 activos; archivos 200; Gutenberg, picker REST, guards, PDF, bootstrap y teardown sin PHP Warning/Notice/Deprecated. Producción no tocada en esa unidad. | Pass (local) | working tree 2026-08-21 |
| Migración PHP producción 8.0.30 → 8.3 | **Validación manual del propietario** en `https://logo-et-spes.cenfiss.net` (no es `qa-*.sh` ni PHPUnit). CloudLinux PHP Selector + Site Isolation; solo ese dominio. | Portada y archivos `/revista/numeros/`, `/revista/articulos/`, `/revista/autores/` OK; wp-admin OK; Gutenberg Article OK; picker de autores OK; Media Library OK; PDF existente OK; Site Health «Bueno»; desapareció el aviso de PHP 8.0.30 obsoleto; sin errores/warnings visibles de la migración. Recomendaciones residuales de Site Health **no** se resolvieron aquí. | Pass (working tree) | working tree 2026-08-22 |
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
| single-author.html | single-author.php | article-card | author + artículos vinculados | Implemented | Unverified | ídem; sin display ORCID (Fase 4). Pretty permalink `/revista/autores/{slug}/`: 404 histórico (query var nativa `author`); arreglo local `query_var=journal_author` (plugin 0.2.1). Archivo y REST ya resolvían. **No desplegado.** |
| single-post.html | single.php | breadcrumbs | post + 2 relacionadas derivadas | Implemented | Unverified | — |
| search.html | page-buscar.php (+ search.php redirect 301) | article/issue/author-card, content-none | Queries::search_query con prioridad documentada (docs/04) | Implemented | Unverified | copy de descripción sin «maqueta estática»; resultados renderizados (la maqueta solo mostraba estado vacío) |
| 404.html | 404.php | — | enlace dinámico a número actual | Implemented | Unverified | — |
