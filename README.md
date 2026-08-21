# Revista de Filosofía LOGO ET SPES

**Versión:** 0.2.0 (canónica en `package.json`; ver `VERSION.md` y `CHANGELOG.md`)

Monorepo de la revista académica (CENFISS): prototipo HTML estático (`static/`,
Fase 2, base visual congelada) y WordPress (`wordpress/`, Fase 3 clásica).
Producción: `https://logo-et-spes.cenfiss.net` ya live (WordPress 7.1, theme
clásico `revistalogos` + `revistalogos-core` activos). Carga de contenido
editorial real iniciada y actualmente en proceso desde wp-admin (**no**
completa). Dataset demo de fixtures: no importar. Bootstrap editorial
restringido (`wp revistalogos fixtures bootstrap`) permitido como
excepción de propietario 2026-08-19, **no ejecutado** aquí. Indexación:
no asumir abierta; no abrir con fixtures temporales públicos.
Pendiente: FSE (ADR 0015), primero en Docker. Snapshot:
`docs/operations/produccion-wordpress.md`.

## Objetivo

La maqueta estática mapea 1:1 a la *template hierarchy* y a los Custom Post
Types de WordPress (números y artículos) y a las páginas institucionales.
El plugin `revistalogos-core` es dueño del dominio; el theme `revistalogos`
solo presenta (ADR 0005).

## Estructura

Monorepo con dos implementaciones delimitadas (ADR 0007):

```
revistalogos/
├── static/                          → Referencia estática congelada (Fase 2)
│   ├── index.html, page-*.html, archive-*.html, single-*.html,
│   │   noticias.html, search.html, 404.html
│   ├── partials/                    → Simulan template parts de WP
│   ├── assets/                      → css/ (tokens→main), js/, img/, pdf/
│   └── .htaccess, robots.txt, sitemap.xml
├── wordpress/
│   └── wp-content/
│       ├── themes/revistalogos/     → Theme clásico (solo presentación)
│       └── plugins/revistalogos-core/ → CPTs, taxonomías, campos, roles,
│                                        migración de contenido y fixtures
├── tests/                           → PHPUnit unitario + Gherkin (ADR 0018)
├── tools/                           → Payload generator, PHPUnit wrapper, qa-*.sh
├── docs/                            → Documentación numerada, ADRs, harness Fase 3
├── content-source/                  → Fuente canónica (no versionada)
└── .github/workflows/               → deploy-wordpress.yml (producción WP, manual),
                                       pages.yml (espejo estático beta, automático)
```

El detalle de las plantillas estáticas y su mapeo a WordPress está en
`docs/17-implementation-order` §2.2 y en la matriz de cobertura de
`docs/fase3-validation-matrix.md`. Estado de ejecución: `docs/fase3-execution-state.md`.

## Cómo usar

**WordPress local (Docker, ADR 0014):** `docker compose up -d` →
`http://localhost:8080`. Imagen `wordpress:7.1.0-php8.2-apache`. WP-CLI:
`docker compose run --rm wpcli wp <cmd>`. No usar `docker compose down -v`
(borra `db_data` y `wp_data`). Cambiar el tag de imagen no actualiza el core
persistido; hace falta `wp core update` + `wp core update-db`.

**Producción WordPress:** `https://logo-et-spes.cenfiss.net`. Solo
`deploy-wordpress.yml` (manual). No hay staging WordPress. Ver
`docs/operations/wordpress-manual-deployment.md`.

**Prototipo estático:** abrir `static/index.html` en un navegador (sin build).
El espejo beta es GitHub Pages (`pages.yml`). El workflow estático
«Deploy to Hostinger» (`deploy.yml`) **está retirado**; no recrearlo.

**Lint CSS:** `npm run lint:css`.

**Tests (ADR 0018, `docs/23-testing-foundation.md`):** PHP syntax
`./tools/php-lint.sh` / `composer lint:php` (`php -l` only). Composer
lockfile audit: `composer audit:deps` (`composer audit --locked`; PHPUnit
and its transitives only — not WordPress, npm, or hosting). Units:
`composer test:unit` or `./tools/run-phpunit.sh`. Fast gate: `composer test`
(lint → audit → units; not `qa-*.sh`). On this laptop (no native PHP) use
the `./tools/*.sh` wrappers and `composer:2` for audit. WordPress workflows
remain isolated `tools/qa-*.sh`. Root Composer is **dev/test only**. Dependabot: weekly Composer + GitHub
Actions PRs, no auto-merge; CI + owner review. `composer audit --locked`
stays the lockfile advisory check.

## Características de diseño

- Paleta sobria: aguamarina institucional y grises neutros
- Tipografía de sistema (Georgia / Arial)
- Grid responsivo sin frameworks; breakpoints 640 / 768 / 1024 / 1280
- Accesibilidad: contraste AA, foco visible, skip links
- Hero del número vigente, TOC por sección, tarjetas con metadatos, 404 y búsqueda

## Marcadores WP-placeholders (solo en `static/`)

El HTML estático incluye comentarios que marcan las zonas equivalentes a loops
y template tags de WordPress (`WP:LOOP_ISSUES_*`, `WP:THE_TITLE`, etc.).

## Modelo de contenido (WordPress)

CPTs reales en el plugin: `issue`, `article`, `author` (el CPT `submission`
está aplazado, ADR 0005). Taxonomías: `section`, `article_type`, `keyword`.
ISSN / DOI / ORCID se almacenan inertes en Fase 3; validación y URLs derivadas
son Fase 4 (ADR 0013). El contenido dummy (Vol. 12 Nº 2, identificadores
ficticios) no se publica como verdad editorial ni como `fixtures seed`
(ADR 0004). Excepción Option 2: el bootstrap Volume 1 puede adaptar campos
de presentación de esa maqueta como placeholders `_les_bootstrap*`.

## SEO y datos estructurados

Cada HTML estático lleva `<title>` único, meta description, Open Graph,
Twitter Cards, `rel="canonical"` y JSON-LD (Periodical, PublicationIssue,
ScholarlyArticle). El theme replica metadatos Highwire, Schema.org y OG.

## Criterios de la maqueta (Fase 2)

- Páginas navegables desde el menú y las migas
- Descarga de PDF en número y artículo
- Tabla de contenidos del número agrupada por sección
- Ficha de artículo con títulos ES/EN, autores, ORCID, DOI, palabras clave
- HTML5, contraste AA, CSS/JS listos para enqueue (`main.css`, `main.js`)

## WordPress (Fase 3)

WordPress clásico live en producción; carga de contenido editorial real
iniciada y actualmente en proceso desde wp-admin
(`logo-et-spes.cenfiss.net`; **no** completa).
Theme + plugin por FTPS manual (Environment `wordpress-production`). Fixtures
solo en Docker (ADR 0004). `static/` sigue como referencia visual (Fase 2) y
espejo Pages. El deploy estático a cPanel (`deploy.yml`) está **retirado**.
Runbook: `docs/operations/wordpress-manual-deployment.md`.
Siguiente: no pisar la carga en curso; QA de producción; FSE después, primero
en Docker (ADR 0015).

## Licencia

- **Código** (HTML, CSS, JS, PHP, scripts y configuración): **MIT**. Ver `LICENSE`.
- **Contenido editorial y de publicación**: **Creative Commons Atribución 4.0 Internacional (CC BY 4.0)**. Ver `LICENSE-CONTENT`.

Los materiales de terceros conservan su propia licencia o atribución cuando corresponda.

---

**Desarrollado para CENFISS - Centro de Estudios Filosóficos y Sociales**
