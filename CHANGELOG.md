# Changelog

Todos los cambios notables de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto se adhiere a [Versionado Semántico](https://semver.org/lang/es/).
La versión vigente vive en `package.json` (fuente de verdad); ver `VERSION.md`.

## [Sin publicar]

### Changed
- Plugin `revistalogos-core` 0.2.3: `wp revistalogos fixtures bootstrap`
  is now a Volume 1 **editorial** bootstrap (one published issue + sample
  article structure from the static maquette, retargeted to Vol. 1 Nº 1).
  It reuses the existing Author `rafael-eduardo-figueredo-oropeza`, never
  marks or deletes that author, never writes fake DOI/ORCID/ISSN, and
  never overwrites objects whose content has drifted from the bootstrap
  hash (`_les_bootstrap_adopted`). Plan/dry-run writes nothing. Production
  writes still require `--confirm-production` and `--backup`. Test fixtures
  (`fixtures seed`, `_les_fixture=1`) remain disposable and separate.

### Removed
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
  en implementación — un número, un artículo, un autor temporales, sin
  identificadores falsos. No ejecutado en producción. Indexación no se abre
  con fixtures temporales públicos.

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
- Warnings Node.js 20→24 del workflow FTPS (`checkout@v4`, FTP-Deploy-Action@v4.3.6).
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
