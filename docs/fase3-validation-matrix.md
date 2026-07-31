# Fase 3 — Matriz de validación

Evidencia durable de QA de la Fase 3. Estados permitidos: `Pass`, `Fail`,
`Unverified`. Nada se marca `Pass` sin evidencia de ejecución; lo que el entorno
local no puede ejecutar (sin PHP/WP-CLI/WordPress, ver
`docs/fase3-execution-state.md` §Decisions) queda `Unverified` hasta que exista
runtime.

Formato de cada fila: validación, método, resultado, estado, commit probado.

## Nivel 1 — Estático

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Baseline checksums CSS/JS del estático | `shasum -a 256` | 12 hashes registrados en execution-state | Pass | 5fedf8a |
| Paridad CSS tras reorg (static/) | comparación de 12 hashes contra baseline | 12/12 idénticos | Pass | 537c94e |
| Paridad CSS theme vs static | hash agregado de ambos árboles CSS | idénticos (`550dead…`) | Pass | dfb91b8 |
| Paridad `main.js` theme vs static | `cmp` | idénticos byte a byte | Pass | dfb91b8 |
| YAML workflows (deploy, pages, deploy-wordpress) | `ruby -ryaml` | parsean | Pass | dfb91b8 |
| JSON (`theme.json`, `content-payload.json`) | `node JSON.parse` | parsean | Pass | dfb91b8 |
| Sintaxis PHP | — (sin binario PHP/WP-CLI local) | heurístico de balance de llaves/paréntesis OK en 100% de archivos; no sustituye a `php -l` | Unverified | dfb91b8 |
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

Sin runtime WordPress local: todo `Unverified`. Cobertura prevista al disponer
de staging: registro de CPTs/taxonomías, esquemas de meta, guardado de meta
boxes (nonce/capacidad/sanitización), normalización de relaciones, comandos
WP-CLI (`content validate|plan|import|verify`, `fixtures seed|verify|teardown`),
conversión de contenido, creación de menús, adjuntos.

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Todas las de nivel 2 | requiere WP runtime | — | Unverified | dfb91b8 |

## Nivel 3 — Integración

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Activación plugin/theme, permalinks, jerarquía de plantillas, búsqueda, migración, ciclo de fixtures, CF7, WP Statistics, cabeceras | requiere WP runtime + staging | — | Unverified | dfb91b8 |

## Nivel 4 — Regresión de cara al usuario

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Paridad visual static↔WP (móvil/tablet/escritorio/200%/320px), teclado, foco, cookies/almacenamiento, peticiones de red, copy ES | requiere navegador contra WP | — | Unverified | dfb91b8 |

## Matriz de cobertura static → WordPress

Estado: `Implemented` = plantilla escrita y revisada; la validación de runtime
es `Unverified` en todas las filas hasta que exista staging.

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
| single-author.html | single-author.php | article-card | author + artículos vinculados | Implemented | Unverified | ídem; sin display ORCID (Fase 4) |
| single-post.html | single.php | breadcrumbs | post + 2 relacionadas derivadas | Implemented | Unverified | — |
| search.html | page-buscar.php (+ search.php redirect 301) | article/issue/author-card, content-none | Queries::search_query con prioridad documentada (docs/04) | Implemented | Unverified | copy de descripción sin «maqueta estática»; resultados renderizados (la maqueta solo mostraba estado vacío) |
| 404.html | 404.php | — | enlace dinámico a número actual | Implemented | Unverified | — |
