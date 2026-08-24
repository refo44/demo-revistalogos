# Changelog

Todos los cambios notables de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto se adhiere a [Versionado Semántico](https://semver.org/lang/es/).
La versión vigente vive en `package.json` (fuente de verdad); ver `VERSION.md`.

## [Sin publicar]

### Added
- ADR 0017 work unit 6B: admin-configurable Article PDF publication
  enforcement (`Article_Pdf_Publication_Settings`,
  `Article_Pdf_Publication_Enforcer`). Option
  `revistalogos_article_pdf_publication_enforcement` defaults OFF
  (missing option = OFF). Settings → LOGO ET SPES. Classic and
  REST/Gutenberg share the setting. ON preserves a valid PDF,
  generates a missing one from the publication candidate, and
  blocks publish on failure. Toggling does not backfill or mutate
  Articles. Isolated QA:
  `tools/qa-article-pdf-publication-enforcement.sh`. No plugin
  version bump.

- ADR 0017 work unit 6A: Article PDF source HTML
  (`Article_Pdf_WordPress_Source_Builder`) and explicit end-to-end
  composition (`Article_Pdf_WordPress_Generator`). Isolated QA:
  `tools/qa-article-pdf-composition.sh`. Valid existing `pdf_file` is
  preserved; missing PDF builds local HTML → Dompdf → Media Library.
  Publication enforcement remains inactive. Future enforcement will be
  a wp-admin setting defaulting to OFF (WU6B). No plugin version bump.

- ADR 0017 work unit 5: persist generated article PDF bytes as a
  normal Media Library `application/pdf` attachment and set Article
  `pdf_file` to that attachment ID
  (`Article_Pdf_WordPress_Persister`). Isolated QA:
  `tools/qa-article-pdf-persistence.sh`. Invalid artifact or
  non-Article ID returns `WP_Error` and writes nothing. Same-call
  rollback only. Still unwired: no publication hook, no REST change,
  no source builder. Publish with a valid Author and no PDF remains
  allowed. No plugin version bump.

- ADR 0017 work unit 4: real Dompdf renderer
  (`Dompdf_Article_Pdf_Renderer`, `dompdf/dompdf` ^3.1.6) owned by
  plugin-local Composer (`composer.json` + tracked lock, generated
  `vendor/` ignored). Isolated QA:
  `tools/qa-article-pdf-renderer.sh`. CI installs/audits plugin
  Composer. Deploy packages `vendor/` with `--no-dev` before FTPS
  (`setup-php` is the Actions runner only; production PHP stays 8.3).
  Still unwired: no Media Library write, no publication-rule change.
  No plugin version bump.

- ADR 0017 work unit 3: generation orchestration and replaceable
  renderer seam (`Article_Pdf_Generation_Orchestrator`,
  `Article_Pdf_Renderer`). KEEP_EXISTING skips rendering;
  GENERATE_REQUIRED calls the renderer; empty output is generation
  failure and blocks publication via the WU1 policy. No real PDF
  library, no Media Library write, no publication hooks. Plugin
  `require_once` only (loaded, not wired). No plugin version bump.

- ADR 0017 work unit 2: read-only WordPress adapter
  (`Article_Pdf_WordPress_Adapter`) that inspects `pdf_file` and returns
  the WU1 keep/generate decision. Isolated QA:
  `tools/qa-article-pdf-adapter.sh`. No hooks, no renderer, no attachment
  creation, no publication-rule change. Plugin source adds the class and
  a `require_once`; publication/runtime behavior is unchanged (loaded,
  not wired). No plugin version bump.

- ADR 0017 work unit 1: pure domain publication/PDF policy in
  `revistalogos-core` (`Article_Pdf_Publication_Policy`). Gherkin
  `tests/Features/article-pdf-generation.feature` (Spanish, not
  executable). PHPUnit covers keep-existing / generate-required /
  block-on-failure / allow-on-success. No WordPress hook, no renderer,
  no Media Library creation, no publication-rule change. Plugin source
  adds the class and a `require_once`; publication/runtime behavior is
  unchanged (loaded, not wired). No plugin version bump. ADR 0017 remains
  accepted and only partially started.

- Dependabot (`.github/dependabot.yml`): weekly Composer and GitHub
  Actions PRs, limit 5 each, **no auto-merge**. Does not cover npm or
  Docker. GitHub Dependabot alerts/security updates require owner
  verification in the repository UI. ADR 0017 remains unimplemented.

- Tests workflow uses `actions/cache@v5` (Node 24). Same path/key/
  restore-keys. No deploy-workflow change.

- Composer lockfile security audit in the fast gate: native
  `composer audit --locked` / `composer audit:deps`. `composer test` runs
  lint → audit → PHPUnit. CI `test.yml` audits before units. Covers root
  Composer dev/test deps only (not WordPress, npm, or hosting). No extra
  scanner package. No runtime bump. ADR 0017 remains unimplemented.

- First-party PHP syntax gate: native `php -l` via `tools/php-lint.sh` and
  `composer lint:php`. No PHPStan/Psalm/PHPCS. No runtime bump.
  ADR 0017 remains unimplemented.

- Testing Foundation (ADR 0018, `docs/23-testing-foundation.md`): PHPUnit
  9.6 via root Composer **dev-only**, `tests/Unit` proof tests,
  `tests/Features/` for Gherkin (no Behat), `./tools/run-phpunit.sh`, CI
  workflow `test.yml` (unit only, no production secrets). Does not bump
  plugin/theme runtime versions. ADR 0017 remains unimplemented.

- Plugin `revistalogos-core` 0.2.6: searchable Author picker (WordPress
  REST `/wp/v2/author`, on demand, published Authors, bounded results).
  Storage remains `authors` int[]. Gutenberg stays enabled; Save → Publish.

- Plugin `revistalogos-core` 0.2.5: author checkboxes with an explicit
  empty state; publish requires at least one published Author CPT
  (draft/pending may have none); Article/Issue PDF Media Library picker
  with `application/pdf` sanitization. Gutenberg remains the Article
  editor; the publication rule is server-side (post.php and REST).
  Isolated QA: `tools/qa-article-editorial-ux.sh`. No formal test suite.

- Plugin `revistalogos-core` 0.2.4: temporary wp-admin tool Tools →
  Volume 1 Editorial Bootstrap (`Bootstrap_Admin`). Hosting has no usable
  SSH/WP-CLI path; the screen is an execution bridge over the existing
  `Fixtures::plan` / `bootstrap` / `verify` methods. No teardown, no force,
  no backup-evidence field (owner exception for this Volume 1 operation
  only). Explicit confirmation remains mandatory. Remove after production
  bootstrap and frontend verification.

### Fixed
- Composer lockfile vs PHP platform: `doctrine/instantiator` 2.1.0
  (`php ^8.4`) replaced with 2.0.0 so `composer install` respects
  `config.platform.php` 8.2.0 on CI PHP 8.2. PHPUnit remains 9.6.36.
  `tools/run-phpunit.sh` no longer uses `--ignore-platform-reqs`.
  No runtime bump. ADR 0017 remains unimplemented.

### Changed
- Production PHP for `logo-et-spes.cenfiss.net`: **8.0.30 → 8.3**
  (owner, cPanel CloudLinux PHP Selector, Site Isolation,
  per-domain). `cenfiss.net` and `test.cenfiss.net` unchanged.
  MultiPHP Manager was not the path used. WordPress stays **7.1**.
  `config.platform.php` stays **8.2.0**. `Requires PHP: 7.4`
  unchanged. No plugin/theme/project version bump. Documentation
  close only; this changelog entry does not execute the hosting
  change. ADR 0017 remains unimplemented.
- Local Docker and CI runtime: PHP 8.2 → **8.3**
  (`wordpress:7.1.0-php8.3-apache`, `wordpress:cli-php8.3`,
  `test.yml` `php-version: "8.3"`). WordPress stays **7.1**. MariaDB 11
  unchanged. `config.platform.php` stays **8.2.0**. Plugin/theme
  `Requires PHP: 7.4` unchanged. Production was still PHP **8.0.30**
  when this local/CI alignment landed. No runtime version bump.
  ADR 0017 remains unimplemented.
- Baseline local Docker: WordPress 7.0.4 → **7.1**
  (`wordpress:7.1.0-php8.2-apache`). PHP 8.2 y MariaDB 11 sin cambio.
- GitHub Actions a runtime Node 24: `actions/checkout@v5`,
  `SamKirkland/FTP-Deploy-Action@v4.4.0`, `actions/upload-pages-artifact@v5`,
  `actions/deploy-pages@v5`. Semántica FTPS sin cambio. Sin deploy en este
  mantenimiento.
- Theme `revistalogos` 0.2.1: CTA `.btn` anchors keep an accessible
  foreground in `:link` and `:visited`. Global `a:visited` no longer
  overrides button-like CTAs. Ordinary content links are unchanged.

- Plugin `revistalogos-core` 0.2.3: `wp revistalogos fixtures bootstrap`
  is now a Volume 1 **editorial** bootstrap (owner Option 2: one published
  issue + sample article structure adapted from the static Vol. 12 Nº 2
  maquette, retargeted to Vol. 1 Nº 1). It reuses the existing Author
  `rafael-eduardo-figueredo-oropeza`, never marks or deletes that author,
  never writes fake DOI/ORCID/ISSN or dummy page ranges, and never
  overwrites objects whose content has drifted from the bootstrap hash
  (`_les_bootstrap_adopted`). Plan/dry-run writes nothing. Production
  writes still require `--confirm-production` and `--backup`. Test fixtures
  (`fixtures seed`, `_les_fixture=1`) remain disposable and separate.

### Removed
- Plugin `revistalogos-core` 0.2.6: temporary wp-admin tool Tools →
  Volume 1 Editorial Bootstrap (`Bootstrap_Admin`). Production bootstrap
  already completed; Fixtures domain and CLI remain. No teardown.

- Temporary wp-admin tool Tools → Institutional Content Import
  (`Content_Recovery_Admin`). Institutional recovery in production
  completed successfully; Pages already imported are real content. Durable
  `Content_Migrator` and `wp revistalogos content validate|plan|import|verify`
  are unchanged. No cleanup deletes imported Pages.

### Fixed
- CPT `author` singles at `/revista/autores/{slug}/` 404ed because the
  default query var collided with WordPress’s native user `author` var.
  Registration now uses `query_var=journal_author`. Public URL unchanged.
  One rewrite flush is required after this plugin version lands.

### Added
- WP-CLI `wp revistalogos fixtures plan`: read-only Volume 1 bootstrap plan.

### Documentación
- ADR 0017 (2026-08-20): generación automática del PDF de artículo al
  publicar, arquitectura aceptada, implementación aplazada (Testing
  Foundation + TDD). El PDF sigue siendo opcional y manual hasta entonces.
- Corte a WordPress clásico en producción (2026-08-19): snapshot
  `docs/operations/produccion-wordpress.md`; runbook canónico PRE/DEPLOY/POST/
  ROLLBACK en `docs/operations/wordpress-manual-deployment.md`; notas de
  implementación en ADR 0009/0015/0016 (sin reescribir decisiones).
- Despliegue: Environment `wordpress-production`, FTPS acotado, jaula FTP,
  rutas remotas relativas, activación distinta de la transferencia.
- Producción: WordPress clásico live en producción; carga de contenido
  editorial real iniciada y actualmente en proceso desde wp-admin (**no**
  completa). Existe un administrador wp-admin asignado a esa gestión
  (identidad no documentada). Dataset demo de fixtures: no importar.
  Indexación: no asumir abierta.
- Decisión de propietario 2026-08-19: bootstrap editorial restringido
  (`wp revistalogos fixtures bootstrap`) permitido como excepción a ADR 0004
  en implementación — primero como 1+1+1 borradores; **Option 2** (misma
  fecha) lo convierte en Volume 1 editable adaptando campos de presentación
  de la maqueta Vol. 12 Nº 2, sin identificadores falsos ni autores dummy.
  No ejecutado en producción. Indexación no se abre con fixtures demo
  públicas.

### Retirado
- Workflow estático `.github/workflows/deploy.yml` («Deploy to Hostinger»).
  Producción de código: solo `deploy-wordpress.yml`. GitHub Pages (`pages.yml`)
  se conserva.

### Por hacer
- Resolver el backlog de decisiones en ADR (ver `docs/adr/BACKLOG.md`): queda **D12b** (momento de automatización CI/CD), a decidir tras la auditoría profesional.
- Verificar tras el próximo despliegue que `http://` devuelve 301 y que las cuatro cabeceras nuevas llegan al navegador.
- Confirmar con el proveedor el plazo de conservación de los registros de acceso y completar el marcador `[Por confirmar]` del aviso de privacidad.
- Someter el aviso de privacidad a asesoría legal antes de abrir la indexación.
- Investigar el Programa de Sponsors de Crossref para Venezuela/Latinoamérica y confirmar el coste real de membresía DOI con el volumen del primer número (ADR 0013 §2.1 — puede avanzar ya, sin esperar a la Fase 4).
- Tramitar el ISSN electrónico (e-ISSN) ante la Biblioteca Nacional, en paralelo al DOI y sin depender de él (ADR 0013, ADR 0004).
- Designar quién en CENFISS gestiona las solicitudes de acceso/corrección/baja de datos de autor frente a Crossref, y revisar `page-politicas` §6 y la Solicitud de Publicación/Declaración de Ética con asesoría legal (ADR 0013 §6).
- QA de producción clásica en `https://logo-et-spes.cenfiss.net` (permalinks, cookies, privacidad, restos HTML del estático) **sin** importar fixtures ni pisar el contenido cargado en wp-admin.
- Revisar PHP efectivo 8.0.30 vs MultiPHP Inherited 8.2; evaluar plugins Softaculous; instalar CF7 y WP Statistics.
- Confirmar en el próximo `workflow_dispatch` FTPS (y en el próximo push a
  `main` de Pages) que desaparecen las anotaciones Node.js 20. La
  configuración ya apunta a acciones Node 24; este mantenimiento no dispara
  esos workflows.
- Borrar en GitHub (Repository secrets) `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_PORT`, `FTP_REMOTE_DIR` tras retirar `deploy.yml`. No tocar `PRODUCTION_*`.
- Bootstrap FSE en Docker (ADR 0015); el corte WordPress (ADR 0016) ya está hecho con el theme clásico.

## [0.2.0] — 2026-08-18

Fase 3 WordPress en el monorepo, runtime Docker local en WordPress 7.0.4
y alineación de metadatos del theme/plugin.

### Añadido
- Monorepo `static/` + `wordpress/` (ADR 0007).
- Plugin first-party `revistalogos-core` (CPTs, taxonomías, meta, rol, migración, fixtures) y theme clásico `revistalogos`.
- Entorno local Docker (ADR 0014): `wordpress:7.0.4-php8.2-apache`, PHP 8.2, MariaDB 11, WP-CLI.
- Workflow manual FTPS de theme+plugin y espejo GitHub Pages desde `static/`.
- ADR 0010–0016 (contacto, analítica, cabeceras, DOI/ORCID, Docker, FSE, topología cPanel `cenfiss2`).
- `page-privacidad.html` — aviso de privacidad del sitio, **provisional**.
- `.htaccess` — redirección HTTPS y cabeceras reversibles (ADR 0012).
- `docs/22-identificadores-academicos-doi-orcid.md`.
- `screenshot.png` (1200×900) para Apariencia → Temas.

### Corregido
- URLs canónicas del HTML estático a `logo-et-spes.cenfiss.net`.
- Aviso de privacidad: declara GitHub Pages como segundo alojamiento.
- `.htaccess` versionado (el workflow de despliegue ya no era un no-op).
- `placeholder-banner.jpg` era un data URI de SVG con extensión `.jpg`; ahora es un JPEG real (fallback de números sin portada).

### Cambiado
- Core WordPress local 6.8.3 → **7.0.4**; theme y plugin `Tested up to: 7.0`.
- Enlace «Privacidad» del footer al aviso dedicado; `sitemap.xml` incluye `/page-privacidad`.
- Eliminado el prompt maestro de agente `docs/FABLE5-Fase3-WordPress-Master-Prompt-v4.md` (alcance ya cubierto por ADR y `docs/17`).
- README y `docs/13`/`docs/15` alineados al layout `static/` + `wordpress/` (ADR 0007); `docs/17` deja de marcar la Fase 3 como «SIGUIENTE».

### Notas
- El contenido dummy (Vol. 12 Nº 2, ISSN/DOI/ORCID ficticios) no se publica en producción (ADR 0004).
- Single CPT `author` (`/revista/autores/{slug}/`) sigue en 404 por colisión de query var; archivo y REST sí resuelven.

## [0.1.0] — 2026-07-23

Primera versión etiquetada. Línea base del prototipo estático «WP-ready»,
con la infraestructura de gobierno del proyecto en su sitio.

### Añadido
- Maqueta estática multipágina (HTML/CSS/JS) mapeada 1:1 a la *template hierarchy* de WordPress.
- Marco de ADR en `docs/adr/` (README, plantilla, backlog de decisiones D1–D12).
- ADR 0001 — Maqueta estática como base definitiva (Aceptada).
- ADR 0002 — WordPress como adaptación sin rediseño (Aceptada).
- `sitemap.xml` para el dominio `logo-et-spes.cenfiss.net`.
- `CHANGELOG.md` y `VERSION.md` (higiene de versiones).

### Eliminado
- `deploy.sh` obsoleto (hacía `git push` y referenciaba GitHub Pages y una ruta `prototype/` inexistente). El despliegue al host se dispara manualmente desde GitHub Actions.

### Notas
- El contenido editorial de la maqueta es demostrativo y **no** se publica en producción (ver `docs/17-implementation-order` §3.1).
- `robots.txt` permanece en `Disallow: /` mientras el sitio es prototipo.

[Sin publicar]: https://github.com/refo44/demo-revistalogos/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/refo44/demo-revistalogos/releases/tag/v0.2.0
[0.1.0]: https://github.com/refo44/demo-revistalogos/releases/tag/v0.1.0
