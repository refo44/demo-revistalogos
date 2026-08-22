---
phase: "Fase 3"
status: "classic_in_production"
current_work_unit: "Dependabot + actions/cache@v5; owner review; no commit/push/deploy; ADR 0017 still unimplemented"
current_branch: "main"
last_verified_commit: "8ebc8ee"
last_checkpoint_commit: "8ebc8ee"
updated_at: "2026-08-21"
next_action: "Owner review of Dependabot + cache@v5 working tree. No commit/push/deploy unless asked. Verify Dependabot alerts/security updates in GitHub UI. Do not implement ADR 0017."
blocked: false
---

# Fase 3 execution state

`last_verified_commit` y `last_checkpoint_commit` **no** son «HEAD actual» ni
«último cambio documental». Son el último commit **ya en Git** contra el que
hubo evidencia de harness/QA o un checkpoint de código durable. Hoy:
`8ebc8ee` (workflow de producción FTPS; `Pass (transfer)` del primer deploy).
Un commit posterior de solo docs (p. ej. retirar `deploy.yml`) **no** los
mueve. Tras commitear la fila de matriz «Retirada workflow estático»,
sustituir «pendiente» por el hash real; estos dos campos se actualizan solo
si hay nueva QA de runtime o un checkpoint de harness, no un hash inventado.

Estado de ejecución durable de la Fase 3 (WordPress). Cualquier sesión futura debe
poder reanudar el trabajo desde este archivo sin historial de chat, siguiendo el
protocolo de reanudación del final.

## Current objective

Implementar la Fase 3 completa según `docs/17-implementation-order` y los
ADR: reorganización del monorepo (ADR 0007), plugin `revistalogos-core`
(ADR 0005), theme `revistalogos` (ADR 0001–0003; FSE según ADR 0015,
pendiente), migración institucional determinista, fixtures (solo Docker),
integraciones aprobadas (CF7, WP Statistics), búsqueda, metadatos académicos
de Fase 3 y workflow manual de despliegue FTPS (ADR 0009). El corte in situ
(ADR 0016) está hecho; producción sirve el theme clásico.

## Current strategy

Ejecución por unidades de trabajo (WU) con commits pequeños y revisables, en el
orden de `docs/17-implementation-order`:

| WU | Alcance | Commit previsto |
| -- | ------- | --------------- |
| WU0 | Harness (este archivo + matriz + ledger + runbooks) | `docs: add fase 3 harness` |
| WU1 | Reorg monorepo `static/` + `wordpress/` (tag `pre-fase3-reorg`) | `refactor: move static site to static/` |
| WU2 | Scaffold `revistalogos-core` | `feat(plugin): scaffold` |
| WU3 | Modelo de contenido (CPTs, taxonomías, meta, rol, relaciones) | `feat(plugin): content model` |
| WU4 | Scaffold theme + assets de presentación | `feat(theme): scaffold` |
| WU5 | Migración de plantillas (familias) | `feat(theme): templates` |
| WU6 | Generador + importador de contenido institucional | `feat(plugin): content migration` |
| WU7 | Fixtures seed/verify/teardown | `feat(plugin): fixtures` |
| WU8 | Integración CF7 + operaciones WP Statistics | `feat(plugin): integrations` |
| WU9 | Búsqueda `/buscar/?q=` y URLs | dentro de WU3/WU5 |
| WU10 | Metadatos académicos (Highwire, Schema.org, OG) | `feat(theme): metadata` |
| WU11 | Workflow manual FTPS WordPress | `ci: manual wordpress deploy workflow` |
| WU12 | Gate final + correcciones de documentación | `docs: ...` (separado) |

Runtime local: Docker (ADR 0014). La QA de niveles 2–4 no dependiente del
hosting se registra como `Pass (local)` en la matriz. La QA de nivel 1
(sintaxis, greps, checksums, YAML/JSON) se ejecuta siempre. El corte in situ
en `logo-et-spes.cenfiss.net` (ADR 0016) **ya se ejecutó** el 2026-08-19 con
el theme **clásico**; FSE (ADR 0015) queda para después, primero en Docker.
Hechos de producción: `docs/operations/produccion-wordpress.md`.

## Acceptance criteria

Los de `docs/17-implementation-order` y los ADR de Fase 3. Resumen operativo:

- Separación static/wordpress según ADR 0007 sin romper el despliegue estático.
- Theme solo presentación; plugin dueño del dominio; sin CPT `submission`.
- CSS byte a byte idéntico al estático (checksums abajo).
- Migración institucional determinista, idempotente y con verificación de texto.
- Fixtures aisladas con `_les_fixture = 1` e identificadores falsos detectables.
- Despliegue WordPress solo `workflow_dispatch`, acotado a theme + plugin.
- Nada se declara verificado sin evidencia de ejecución.

## Completed work

- **WU0 (2026-07-31):** lectura de fuentes vinculantes; creación de artefactos
  durables; snapshot del repositorio; baseline de checksums CSS/JS (abajo).
  Commit `df3ad90`.
- **WU1 (2026-07-31):** reorganización del monorepo (ADR 0007). Tag de rollback
  `pre-fase3-reorg`; `git mv` de HTML, `assets/`, `partials/`, `.htaccess`,
  `robots.txt` y `sitemap.xml` a `static/`; estructura `wordpress/wp-content/`
  creada; `deploy.yml` apunta a `static/`; nuevo `pages.yml` para el espejo
  beta (pendiente acción del propietario en Settings → Pages); scripts de
  stylelint actualizados; `wordpress/wp-content/uploads/` ignorado. QA: YAML
  parse OK, `git diff --check` OK, 12/12 checksums CSS/JS idénticos tras el
  movimiento, estructura de imagen plana preservada.
- **WU2 (2026-07-31):** scaffold de `revistalogos-core` (ADR 0005). Bootstrap
  con guard de acceso directo, constante de versión, hooks de
  activación/desactivación (flush de rewrite solo ahí) y rutina de upgrade
  idempotente; sin efectos secundarios al incluir, sin dependencia del theme.
  Commit `8e5ebfc`.
- **WU3 (2026-07-31, mismo commit que WU2):** modelo de contenido. CPTs
  `issue`/`article`/`author` con slugs `revista/*` (ADR 0008, docs/11) y
  capabilities por CPT; sin CPT `submission` (aplazado, ADR 0005 §4).
  Taxonomías `section` (jerárquica, términos iniciales aprobados),
  `article_type` (valores canónicos en inglés, etiquetas admin en español) y
  `keyword`. `register_post_meta` con sanitización, auth callbacks y esquemas
  REST; `issn`/`doi`/`orcid` almacenados solo como campos inertes (ADR 0013 —
  sin validación ni URLs derivadas, límite de Fase 4). Meta boxes nativas con
  nonce y chequeo de capacidad; relaciones artículo↔autores (many-to-many) y
  artículo↔número (many-to-one) normalizadas y limpiadas al borrar posts
  referenciados. Rol Managing Editor de mínimo privilegio, distinto del
  Editor nativo. Query del número actual derivada (más reciente por
  `date_published`, sin flag almacenado) y queries de dominio acotadas para
  el theme. Comentarios desactivados globalmente (invariante cero-cookies,
  ADR 0011) e integración de honeypot con CF7 guardada tras `class_exists`
  (ADR 0010). Commit `8e5ebfc`.
- **WU4 (2026-07-31):** scaffold del theme `revistalogos` + assets de
  presentación. `style.css` solo cabecera; `theme.json` restrictivo (paleta
  aprobada, colores/gradientes/duotono/tamaños de fuente/espaciado
  personalizados desactivados, ADR 0003 §1). `functions.php`: soporte de
  title-tag/thumbnail, ubicaciones de menú, `main.css` como único punto de
  entrada de hoja de estilos, `main.js` diferido en el footer, dequeue
  auditado de estilos de bloque nativos no usados (ADR 0003 §3), fallback de
  favicon y aviso admin + fallbacks seguros de front-end si
  `revistalogos-core` está inactivo. CSS/JS copiados byte a byte desde
  `static/` (SHA-256 agregado verificado igual; espacios en blanco finales
  preexistentes preservados deliberadamente). Shell de sitio con paridad
  estática: header con skip link y navegación primaria congelada (menu
  walker con clases `nav__*`, enlace dinámico al número actual, fallback
  hardcodeado), footer con listas de enlaces menú-o-fallback, breadcrumbs,
  paginación y estado vacío, `page.php` como renderizador institucional
  compartido vía `the_content()`, `index.php` de respaldo, `404.php`,
  `comments.php` desactivado. Commit `b46d313`.
- **WU5 (2026-07-31, mismo commit que WU4, mas `1cd5ab0`):** migración de
  plantillas por familias — todos los archivos/singles de
  `issue`/`article`/`author`/`post`, taxonomías `article_type`/`keyword`/
  `section`, todas las páginas institucionales (`page-acerca`, `page-etica`,
  `page-normas`, `page-politicas`, `page-enlaces`,
  `page-enviar-colaboracion`, `page-comite-editorial`, `page-contacto`),
  `privacy-policy.php`, `home.php`, `front-page.php`, `404.php`. Script de
  citación extraído de inline a `assets/js/citation.js` +
  `inc/citations.php`, con lógica de enqueue en el theme (commit `1cd5ab0`).
- **WU9:** ruta de búsqueda resuelta dentro de WU3/WU5 — `page-buscar.php`
  (`/buscar/?q=`) como plantilla principal y `search.php` como delegado fino
  para `/?s=` (ver «Decisions and assumptions», ya no es una WU separada).
- **WU10 (2026-07-31):** metadatos académicos — Highwire Press tags,
  JSON-LD Schema.org y Open Graph (`inc/metadata-output.php`, 250 líneas).
  Incluido en `b46d313` + `1cd5ab0`.

- **WU6 (2026-07-31):** migración institucional en dos pasos. Generador Node
  (`tools/generate-content-payload.mjs`, sin dependencias) → payload
  versionado + semillas de media en `resources/` del plugin; `etica`
  convertida literalmente del canon con verificación estricta de texto
  (falla ante divergencia); resto de páginas desde el cuerpo estático
  validado con enlaces reescritos, PDFs tokenizados a adjuntos y avatares a
  assets del theme; cobertura canónica informativa: normas 18/27,
  politicas 10/18 párrafos verbatim (pendiente confirmación editorial).
  Importador WP-CLI `wp revistalogos content validate|plan|import|verify`,
  dry-run por defecto, `--apply` para escribir, producción exige
  `--confirm-production` + `--backup`; identidad `_les_source_*`,
  detección de deriva (fuente cambiada vs ediciones manuales; `--force`
  solo reafirma campos poseídos), ajustes de sitio y menús idempotentes
  sin pisar menús del propietario. Commit `5c1697b`.
- **WU7 (2026-07-31):** fixtures seed/verify/teardown (`da375c6`): primera
  edición mockeada Vol. 1 Nº 1 + stubs de paginación, todo con
  `_les_fixture=1`, claves estables idempotentes e identificadores falsos
  detectables; guard de producción; teardown limpio de posts/media/meta/
  términos propios.
- **WU8 (2026-07-31):** integraciones — honeypot CF7 en el plugin (WU2),
  opción `revistalogos_contact_form_id` documentada, inventario operativo
  de CF7/WP Statistics en `docs/operations/third-party-plugins.md`.
- **WU11 (2026-07-31; producción 2026-08-19):** workflow manual FTPS
  (`.github/workflows/deploy-wordpress.yml`, solo `workflow_dispatch`,
  acotado a theme+plugin, sin delete/mirror). Renombrado a producción
  (`wordpress-production`, secretos `PRODUCTION_*`). Primer run real
  **Success** (~27 s, theme + plugin). Commit de workflow `8ebc8ee`.
- **WU12 (2026-07-31):** gate final nivel 1 completo (ver matriz),
  correcciones de documentación (README árbol monorepo, nota de reorg en
  `docs/13`), matriz de cobertura completa con 20 pantallas
  `Implemented`.
- **Post-WU12 (2026-08-18):** upgrade del runtime Docker local de WordPress
  6.8.3 a **7.0.4** (`wordpress:7.0.4-php8.2-apache`) sin destruir `db_data`
  ni `wp_data`; core en el volumen actualizado con `wp core update
  --version=7.0.4` + `wp core update-db`. PHP 8.2.33; MariaDB 11 sin cambio.
  Theme y plugin `Tested up to: 7.0`, versión de proyecto **0.2.0**.
  Placeholder `placeholder-banner.jpg` sustituido por un JPEG real.
  `screenshot.png` (1200×900) en la raíz del theme para Apariencia → Temas.
  Eliminado el prompt maestro de agente FABLE5 (decisiones en ADR y `docs/17`).

- **Corte producción clásica (2026-08-19):** WordPress 7.0.4 instalado con
  Softaculous in situ en `logo-et-spes.cenfiss.net` (BD nueva, sin tocar
  `cenfiss.net` ni `test.cenfiss.net`). FTPS run #1 **Success** (theme +
  plugin). Activación **en wp-admin**, no en CI. WordPress clásico live en
  producción; carga de contenido editorial real iniciada y actualmente en
  proceso desde wp-admin (**no** completa). Existe un administrador
  asignado a esa gestión (identidad no documentada). Fixtures **no**
  importados. Indexación: verificar (no asumir abierta). Abrirla es
  decisión explícita del propietario, no un efecto del deploy; el 100 %
  del contenido editorial **no** es prerequisito. FSE no bloqueó el corte.
  Launch gate, snapshot y runbook:
  `docs/operations/produccion-wordpress.md`,
  `docs/operations/wordpress-manual-deployment.md`.
- **Recovery institucional temporal (working tree, 2026-08-19):**
  `revistalogos-core` 0.2.2 añade Herramientas → Institutional Content
  Import, solo `manage_options`, POST+nonce, primer plan read-only firmado,
  clasificación de 12 slugs, bloqueo `MANUAL EXISTING`/`AMBIGUOUS`,
  evidencia de backup + confirmación, import siempre sin force y Verify.
  Reutiliza `Content_Migrator`; CLI refactorizado para consumir los mismos
  reportes de validación/plan/import. QA completa en proyecto Docker aislado:
  12 Pages, 3 media, 3 menús, settings, 15/15 verify, re-plan idempotente y
  12 rutas HTTP 200. El harness calculó SHA-256 de posts/postmeta, opciones
  de lectura/theme mods y tablas de términos antes/después del plan y exigió
  igualdad exacta.
  No se tocó producción, no se ejecutó workflow, no hubo commit/push.
- **Recuperación institucional en producción (2026-08-19, declaración del
  propietario):** Pages importadas, Verify pasó, navegación pública
  restaurada. Contenido institucional permanente. UI temporal retirada en
  el working tree 0.2.3; migrador/CLI conservados.
- **Volume 1 editorial bootstrap (working tree, 2026-08-19):** plugin
  0.2.3 adapta `fixtures bootstrap` a estructura editorial reutilizable
  (no disposable). Autor canónico reutilizado; adopción por hash. QA
  aislada: `tools/qa-editorial-bootstrap.sh`. No deploy, no ejecución en
  producción.

## Active work

- Recuperación institucional **completada en producción** (2026-08-19,
  declaración del propietario): Pages importadas, Verify pasó, navegación
  pública restaurada. Las Pages institucionales son contenido real y
  permanente. La UI temporal Tools → Institutional Content Import está
  **retirada en este working tree** (plugin 0.2.3); `Content_Migrator` y
  `wp revistalogos content *` se conservan. No hay cleanup que borre Pages.
- Volume 1 editorial bootstrap implementado (plugin 0.2.3+): dominio
  `Fixtures` + CLI. **0.2.4** añade Tools → Volume 1 Editorial Bootstrap
  como puente temporal (hosting sin SSH/WP-CLI usable). Reutiliza plan/
  bootstrap/verify. Sin teardown, sin force, sin campo de backup (excepción
  de propietario para esta operación). Confirmación explícita obligatoria.
  **No ejecutado en producción. No desplegado.** Retirar la UI tras Verify
  y QA de front.
- WordPress clásico live en producción (`https://logo-et-spes.cenfiss.net`);
  carga de contenido editorial real iniciada y actualmente en proceso desde
  wp-admin (**no** completa). Existe un administrador asignado a esa
  gestión; no documentar identidad ni credenciales. No importar el dataset
  demo de fixtures (`wp revistalogos fixtures seed`).
- QA del theme clásico en producción. Backlog operativo en
  `docs/operations/produccion-wordpress.md` (permalinks, PHP 8.0.30 vs
  MultiPHP 8.2, plugins Softaculous, CF7, WP Statistics, cookies, restos
  HTML, indexación a verificar, FSE después en Docker).

## QA status of completed work

**Actualización 2026-07-31:** existe runtime WordPress local vía Docker
(ADR 0014, `docker-compose.yml`). Ejecutado y registrado en la matriz como
`Pass (local)`: `php -l` (59 archivos, 0 errores), activación de
plugin/theme, permalinks, migración completa
(`content validate|plan|import --apply|verify`, 12/12 OK), idempotencia del
re-plan, guard de producción del importador, ciclo completo de fixtures
(teardown/seed/verify, 39 objetos), CF7 renderizando en `/contacto/`,
WP Statistics activo con assets locales, 15 URLs clave en 200, cero cookies
y cero recursos externos en front-end. Permanece `Unverified`: protocolo
completo de paridad visual (nivel 4), meta boxes/REST/limpieza de
relaciones, y todo lo dependiente del hosting real (FTPS, cabeceras,
cPanel `cenfiss2`) — ver `docs/fase3-validation-matrix.md`.

**Actualización 2026-08-18:** smoke sobre WordPress 7.0.4 en Docker
(`Pass (local)` en la matriz): core 7.0.4, PHP 8.2.33, MariaDB 11.8.8,
theme y plugin activos, portada/archivos issue-article/institucionales/
búsqueda/404/media/login OK. Excepción conocida hasta plugin 0.2.1:
single CPT `author` 404 (colisión de query var; corregido en código local
2026-08-19, **no desplegado** — ver Failures).

**Actualización 2026-08-19:** primer deploy FTPS a producción **Success**
(run #1, ~27 s). Transferencia OK; activación en wp-admin (el workflow no
activa). PHP efectivo del hosting: **8.0.30** (wp-admin); MultiPHP lista
Inherited 8.2 — discrepancia abierta. QA de paridad visual, cookies, CF7,
WP Statistics, caché y cabeceras en el hosting sigue `Unverified`. No se
importaron fixtures. Upload success ≠ sitio verificado.

**Actualización recovery 2026-08-19 (working tree):** QA
`tools/qa-content-recovery-admin.sh` en Docker aislado WordPress 7.0.4:
administrador 200, suscriptor 403, anónimo→login, POST sin nonce 403;
Validate/Plan sin escritura (hash relevante idéntico); missing,
migration-owned, colisión manual y ambigua; guards de backup/confirmación/
plan; force no expuesto; error runtime de media detiene Pages/settings;
import 12 Pages + 3 media + 3 menús/settings;
marcadores `_les_source_*`, cero `_les_fixture`; cero cambios en
issue/article/author/users; Verify 15/15; re-plan 12 skip; 12 rutas 200.
Volúmenes QA efímeros retirados al terminar. Producción no consultada ni
modificada.

**Actualización Volume 1 bootstrap 2026-08-19 (working tree):** QA
`tools/qa-editorial-bootstrap.sh` en Docker aislado
`revistalogos-bootstrap-qa` (puerto 8082, WordPress 7.0.4): plugin carga
sin `Content_Recovery_Admin`; `content validate/import/verify` OK; plan
sin escritura; bootstrap 1 issue + 7 articles; Rafael reutilizado y sin
marcadores; HTTP 200 institucionales + archivos + singles; adopción no
pisada; teardown no borra adoptado/Rafael/Pages. Volúmenes QA efímeros
retirados al terminar. Producción no consultada ni modificada.

## Validation evidence

Ver `docs/fase3-validation-matrix.md`. Baseline de paridad CSS/JS del estático
(SHA-256, tomado en `5fedf8a`, 2026-07-31):

```text
0b3c6452f805be5925b11895cc79d03f17159a4bcced6d7d189f9f58abdd65ce  assets/css/base.css
6737e0335487bae090c86263257304e7669590fadbc3c203b1e9029d0a871125  assets/css/components.css
01bf4101d184bf8d15b980600c3c92703c0c626dbf99280270a1f6fdd7a0e4a8  assets/css/layout.css
d20aefbafb03b4c8bd36f82d4bfe583f65ffbcde4c4555d0c24666308f8d89ab  assets/css/main.css
30fe2260367a0585940c4bcf5da0abc98c78aa4144925e5f346108023ed29252  assets/css/tokens.css
fdffc0ebfebda05b806996f2a50186106406142c5c0c3fffd670466bd5996907  assets/css/utilities.css
52e5adb59a72a9a3f84f3149f5879910e33b1280dbb12b8d75a3ee5d8cf2d884  assets/css/pages/archive.css
7f0d80a7caf91d5e1e9be0501b4abd915cddd35a8cf9cc20253f79031afc32a0  assets/css/pages/article.css
48c58fab6ea848edc07cdc2227ff4f678199d56e378a2896a005ed9b6cfb70fd  assets/css/pages/home.css
7ae2ab58e6acdb14d27911e32b198f9b366a978aeef64bec09628baee3f3b807  assets/css/pages/issue.css
d75e7fbd757c5402c2d4a94e6836883819579ca1245daa142c0b235555e69b93  assets/css/pages/static-pages.css
2bd3e97fa31242fbc70536d1c602c8ff31f3a780f23957f72a7c5b5b118f7f79  assets/js/main.js
```

## Failures and root causes

- **Single CPT `author` (`/revista/autores/{slug}/`) HTTP 404** (2026-08-18,
  WP 7.0.4; **corregido en código local 2026-08-19, no desplegado**): el CPT
  se registra como `author` y la query var por defecto chocaba con la nativa
  de WordPress (`index.php?author={slug}` se interpreta como usuario). El
  archivo `/revista/autores/` y REST `wp/v2/author/{id}` sí resolvían.
  Arreglo: `query_var=journal_author` en `Content_Types`; flush en
  `Plugin::maybe_upgrade` al pasar a plugin `0.2.1`. Tras desplegar hay que
  dejar que corra el upgrade (primera petición o wp-admin) o guardar
  Enlaces permanentes. **No desplegado en esta tarea.**
- **`placeholder-banner.jpg` no era un JPEG** (corregido 2026-08-18): el
  archivo era un data URI de SVG con extensión `.jpg`; Apache lo servía
  como `image/jpeg` y el navegador mostraba imagen rota en `issue-card`
  cuando el número no tiene thumbnail (fixture stub B). Sustituido por un
  JPEG real 400×300.
- **`PHP Warning: Constant WP_DEBUG already defined`:** la imagen oficial
  define `WP_DEBUG` en `wp-config.php` y `WORDPRESS_CONFIG_EXTRA` lo
  redefine. Preexistente del compose; no introducido por el tag 7.0.4.

## Decisions and assumptions

- **Herramientas (2026-07-31):** PHP, WP-CLI, Composer y actionlint **no** están
  instalados en la máquina de desarrollo; Node 20.17 y npm 10.8 sí. No se
  instala toolchain global sin autorización. **Superado el mismo día** por el
  entorno Docker local (ADR 0014): `docker-compose.yml` provee WordPress
  **7.0.4** (`wordpress:7.0.4-php8.2-apache`) + PHP 8.2 + MariaDB 11 +
  WP-CLI sin toolchain global. El core vive en `wp_data`; un cambio de tag
  exige `wp core update --version=…` + `wp core update-db` (no
  `docker compose down -v`). La QA de runtime no dependiente del hosting
  se ejecuta localmente. El sitio público es ya WordPress en
  `logo-et-spes.cenfiss.net` (ADR 0016, corte 2026-08-19); Docker sigue
  siendo el entorno de desarrollo. PHP de producción observado: 8.0.30.
- El generador de payload de migración se implementa en **Node** (herramienta
  del repo, sin dependencias nuevas), porque es la única runtime disponible y el
  payload es un artefacto local versionado; el importador es PHP/WP-CLI dentro
  de `revistalogos-core` y no depende de rutas del repositorio.
- `docs/12-theme-file-structure` §8 sitúa los CPT en `inc/` del theme; ADR 0005
  §3 lo ajusta explícitamente: se sigue ADR 0005 (CPTs en el plugin).
- `docs/11` fija la ruta preferida `/buscar/?q=` (`page-buscar.php`);
  `docs/17` §2.2 mapea `search.html → search.php`. Se sigue `docs/11`,
  manteniendo `search.php` como delegado fino para `/?s=` sin crear
  variantes indexables en competencia.

## Documentation discrepancies

Registradas para corrección en commit de documentación separado (WU12):

1. **GitHub Pages tras la reorg (material):** el espejo beta de GitHub Pages
   publica hoy desde la **raíz** de `main` (Pages «deploy from branch»;
   `_config.yml` con `baseurl: /demo-revistalogos`). Al mover el estático a
   `static/`, Pages dejará de servir el sitio (la raíz ya no tendrá HTML).
   Resolución aplicada: se añade un workflow de Pages (`.github/workflows/pages.yml`,
   push a `main`, publica `static/`) que preserva el comportamiento automático
   deliberado (backlog 2026-07-28). **Acción del propietario pendiente:**
   cambiar en Settings → Pages la fuente de «Deploy from a branch» a
   «GitHub Actions». Hasta entonces el espejo queda roto o sirviendo la raíz.
2. **`docs/13-static-file-structure`** describe el estático en la raíz; tras la
   reorg queda obsoleto (actualización separada).
3. **`docs/15-assets-strategy`/`docs/12`** documentan subcarpetas
   `assets/img/logos/`, `placeholders/`, etc.; la estructura real es **plana**
   (`assets/img/*.svg|jpg|png`). Se preserva la estructura plana (ADR 0007 /
   reorg a `static/`); reorganizar imágenes requiere decisión explícita.
4. **`docs/03` §3** almacena `article.doi_url` como campo; ADR 0013/`docs/22`
   lo definen **computado, no almacenado**. Gana ADR 0013: en Fase 3 solo se
   registra almacenamiento inerte de `issue.issn`, `issue.doi`, `article.doi`,
   `author.orcid`; sin `doi_url`/`orcid_url` almacenados ni validación (Fase 4).
5. **`docs/03` §1/§3/§6/§8 y `docs/12` §2/§4.0** describen el CPT `submission`,
   rol Author y área privada: **aplazados** por ADR 0005 §4; no se implementan
   en Fase 3 (el texto de los docs ya lo señala vía ADR, no requiere edición).
6. **`docs/16` §1** lista `assets/img/` con nombres sin subcarpetas pero
   `docs/15` §1 con subcarpetas — misma discrepancia que (3).
7. **`content-source/` está en `.gitignore`** (línea `/content-source/`): el
   material canónico existe solo en el working tree local, no en Git. El
   generador de migración lo trata como entrada local y el payload generado
   (versionado) como artefacto reproducible con checksum de la fuente.

## Blockers

- Ninguno duro para la QA de producción clásica. FSE (ADR 0015) queda
  **después** de estabilizar este corte; no bloqueó la instalación.
- Acción del propietario: cambiar la fuente de GitHub Pages a «GitHub
  Actions» (ver discrepancia 1). No bloquea la QA de producción.

## Repository state

- Rama: `main`; tag publicado: `v0.1.0`. Versión de proyecto **0.2.0**
  (canónica en `package.json`); tag Git `v0.2.0` pendiente (véase `VERSION.md`).
  Plugin `revistalogos-core` **0.2.6** y theme `revistalogos` **0.2.1** en el
  working tree (picker de autores, retiro de `Bootstrap_Admin`, CTA `:visited`);
  versión de proyecto 0.2.0 sin cambio.
- Despliegues: WordPress de la revista en `logo-et-spes.cenfiss.net`
  (`deploy-wordpress.yml`, `workflow_dispatch`, Environment
  `wordpress-production`, cuenta FTP `deploy_revista@…`). El workflow
  estático `deploy.yml` está **retirado**. GitHub Pages automático
  desde `static/` (`pages.yml`) sigue como espejo beta. Panel: cPanel
  `cenfiss2`, no Hostinger.

## Files changed

Se actualiza al cierre de cada WU. WU0: artefactos del harness (execution-state,
matriz, ledger, runbooks).

**2026-08-18 (versión 0.2.0):** `package.json` / `VERSION.md` / `CHANGELOG.md` /
`README.md`; cabeceras Version del theme y plugin; `screenshot.png`; docs 00,
12, 13, 15, 17, README de docs, matriz, inventario de terceros, `CLAUDE.md`,
este archivo. Tag Git `v0.2.0` pendiente de publicación (véase `VERSION.md`).

**2026-08-19 (corte + runbook de producción):** snapshot
`docs/operations/produccion-wordpress.md`; runbook canónico
`docs/operations/wordpress-manual-deployment.md` (PRE/DEPLOY/POST/ROLLBACK);
ADR 0009/0015/0016/0014 (notas de implementación); README, `CLAUDE.md`,
`docs/17`, CHANGELOG, BACKLOG, matriz.

**2026-08-19 (recovery institucional, working tree):** migrador y CLI
compartidos; nueva clase admin temporal; bootstrap/version/readme del plugin;
harness `tools/qa-content-recovery-admin.sh`; CHANGELOG; matriz; snapshot de
operaciones y este estado. Sin commit, push ni deploy.

**2026-08-19 (UI temporal Volume 1 bootstrap, working tree):** plugin 0.2.4
`Bootstrap_Admin` + `tools/qa-volume1-bootstrap-admin.sh`; helpers de plan/
verify en `Fixtures`; docs de operaciones. Sin commit, push ni deploy.

**2026-08-20 (plugin 0.2.6 / theme 0.2.1, working tree):** picker de autores
con REST de núcleo; retiro de `Bootstrap_Admin`; CTA `:visited` en
`components.css`; harnesses `qa-article-editorial-ux.sh` y
`qa-volume1-bootstrap-admin.sh` (ausencia de UI). 320px / 200% zoom del CTA:
solo preflight estático de CSS (**NOT LIVE-VERIFIED**; verificación manual
tras deploy).

**2026-08-20 (ADR 0017, working tree):** arquitectura de generación automática
del PDF de artículo al publicar **aceptada**; implementación **aplazada**
hasta Testing Foundation + TDD. El código de 0.2.6 no cambia. PDF sigue
opcional y manual en producción.

**2026-08-20 (infra, working tree):** baseline local Docker alineada a
WordPress **7.1** (`wordpress:7.1.0-php8.2-apache`); PHP 8.2.33 sin cambio.
QA aislada + permalinks: PASS. Workflows: `actions/checkout@v5`,
`FTP-Deploy-Action@v4.4.0`, Pages `upload-pages-artifact@v5` /
`deploy-pages@v5`. Sin deploy, sin commit. Testing Foundation: ver párrafo
siguiente.

**2026-08-20 (Testing Foundation, working tree):** ADR 0018 + `docs/23`;
PHPUnit 9.6 (Composer `require-dev` en la raíz); `composer test:unit` /
`./tools/run-phpunit.sh`; 2 unit tests de `revistalogos_split_name`; CI
`.github/workflows/test.yml` (sin secretos, sin deploy). Harnesses
`tools/qa-*.sh` conservados. ADR 0017 **no** implementado. Commit y push:
`9fc88b1`.

**2026-08-21 (PHP syntax gate + Composer audit):** `tools/php-lint.sh` +
`composer lint:php` (`php -l` nativo, PHP 8.2). `composer audit --locked`
/ `composer audit:deps` en el gate rápido y en CI (lint → audit → units).
Solo lockfile Composer de raíz; no garantizado offline. 0 advisories. Sin
PHPStan/Psalm/PHPCS ni escáner extra. ADR 0017 **no** implementado. Sin
deploy. Commit y push: `85b843b`.

**2026-08-21 (CI lockfile vs platform, working tree):** `composer install`
fallaba (exit 2) porque `doctrine/instantiator` 2.1.0 exige `php ^8.4` y
`config.platform.php` es 8.2.0. Lock generado con `--ignore-platform-reqs`
vía `composer:2` (PHP 8.5). Reparación: instantiator **2.0.0** (`php ^8.1`);
PHPUnit 9.6.36 sin cambio; wrapper local sin `--ignore-platform-reqs`. ADR
0017 **no** implementado. Commit: `f057412`.

**2026-08-21 (Dependabot + cache@v5, working tree):**
`.github/dependabot.yml` (Composer + GitHub Actions, semanal, límite 5,
sin auto-merge). `actions/cache@v5` en `test.yml`. Deploy FTPS sigue
`workflow_dispatch`. Alerts/Security Updates: verificar en GitHub UI. ADR
0017 **no** implementado. Sin commit, push ni deploy.

**2026-08-21 (PHP 8.3 local + CI, working tree):** runtime canónico
Docker `wordpress:7.1.0-php8.3-apache` + `wordpress:cli-php8.3`; CI
`php-version: "8.3"`. `config.platform.php` permanece **8.2.0**.
`Requires PHP: 7.4` sin cambio. Producción **sigue 8.0.30**. Volúmenes
primarios conservados (recreate del contenedor wordpress, sin
`down -v`). QA: lint 61, PHPUnit 2/2, harnesses permalinks / bootstrap /
editorial-ux / bootstrap-admin PASS. ADR 0017 **no** implementado. Sin
commit, push, deploy ni cambio de cPanel.

## Next exact action

La implementación **clásica** está live en producción
(`https://logo-et-spes.cenfiss.net`, WordPress 7.1, PHP **8.0.30**).
Recuperación institucional **ya hecha** (Pages reales permanentes). Carga
editorial real en proceso desde wp-admin (**no** completa). Docker local:
`http://localhost:8080` (WordPress 7.1, PHP **8.3**).

Siguiente acción priorizada — **revisión del propietario de la alineación
PHP 8.3 local/CI; no commit/push/deploy; no cambiar PHP de producción;
no implementar ADR 0017 ahora**:

1. revisar `.github/dependabot.yml` y `actions/cache@v5`; verificar en
   GitHub UI Dependabot alerts y security updates;
2. deploy de plugin `revistalogos-core` 0.2.6 y theme `revistalogos` 0.2.1
   sigue siendo decisión aparte del propietario;
3. tras deploy: verificar 320px / 200% zoom de CTAs en navegador
   (**NOT LIVE-VERIFIED**);
4. no re-ejecutar bootstrap ni teardown en producción;
5. Bootstrap_Admin ya no forma parte del plugin.
6. ADR 0017 (PDF automático) puede arrancar con TDD **después** de aceptar
   esta foundation; no en esta pausa.

No importar el dataset demo de fixtures. After ordinary theme QA,
FSE remains deferred:

1. No importar el dataset demo de fixtures.
2. QA del theme clásico (portada, nav, CSS/JS, CPTs, páginas, 404) sobre
   el sitio live, **sin** dataset dummy. Registrar en
   `docs/fase3-validation-matrix.md`.
3. **No** recrear `deploy.yml`. **No** tocar `cenfiss.net` ni
   `test.cenfiss.net`.
4. Indexación: **verificar** (no asumir abierta). Abrirla es decisión
   explícita del propietario, no un efecto del deploy. El 100 % del
   contenido editorial **no** es prerequisito. Launch gate:
   `docs/operations/produccion-wordpress.md`. **No** abrirla en esta
   reanudación.
5. Luego, cuando el rollback al dummy ya no haga falta: limpiar restos HTML
   del document root; revisar PHP 8.0.30 vs MultiPHP 8.2; evaluar plugins
   Softaculous; instalar/configurar CF7 y WP Statistics; vigilar SpeedyCache.
6. **FSE (ADR 0015) después**, incremental, **primero en Docker**.

D12b (checks CI de seguridad/stylelint ligados al deploy) sigue pendiente
de la auditoría (ADR 0012 §6). El workflow `test.yml` (PHPUnit unitario)
es Testing Foundation (ADR 0018), no cierra D12b.

Acciones del propietario pendientes (no bloquean la QA):

- Cambiar la fuente de GitHub Pages a «GitHub Actions» (Settings → Pages).
- Revisar las divergencias canon↔maqueta de normas (18/27) y politicas
  (10/18) reportadas por el generador y decidir si el cuerpo de esas
  páginas debe regenerarse desde el canon literal (como `etica`).

## Resume procedure

```bash
git status --short && git branch --show-current && git log -5 --oneline
```

1. Leer `CLAUDE.md`, este archivo y los `docs/` / ADR de la WU activa.
   No tratar `.cursor/` como fuente versionada (está gitignored).
2. Comparar rama con Git. `last_verified_commit` / `last_checkpoint_commit`
   son el último checkpoint **verificado**, no tienen que igualar HEAD si
   HEAD es solo docs. No inventar un hash; actualizarlos tras QA o tras un
   commit de harness, no por el working tree sucio.
3. Inspeccionar cambios sin commitear; no descartar nada ajeno.
4. Releer las fuentes vinculantes de la WU activa.
5. Re-ejecutar la última QA aplicable (`docs/fase3-validation-matrix.md`).
6. Continuar desde «Next exact action».
