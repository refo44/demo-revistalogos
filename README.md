# Revista de Filosofía LOGO ET SPES

**Versión:** 0.3.0 (canónica en `package.json`; ver `VERSION.md` y `CHANGELOG.md`)

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
├── .sonarcloud.properties           → Alcance SonarQube Cloud (Automatic Analysis)
└── .github/workflows/               → test.yml (lint/audit/units),
                                       deploy-wordpress.yml (producción WP, manual),
                                       pages.yml (espejo estático beta, automático)
```

El detalle de las plantillas estáticas y su mapeo a WordPress está en
`docs/17-implementation-order` §2.2 y en la matriz de cobertura de
`docs/fase3-validation-matrix.md`. Estado de ejecución: `docs/fase3-execution-state.md`.

## Cómo usar

**WordPress local (Docker, ADR 0014):** `docker compose up -d` →
`http://localhost:8080`. Imagen `wordpress:7.1.0-php8.3-apache`. WP-CLI:
`docker compose run --rm wpcli wp <cmd>`. No usar `docker compose down -v`
(borra `db_data` y `wp_data`). Cambiar el tag de imagen no actualiza el core
persistido; hace falta `wp core update` + `wp core update-db`.

**Producción WordPress:** `https://logo-et-spes.cenfiss.net`. Solo
`deploy-wordpress.yml` (manual). No hay staging WordPress. Runbook completo:
`docs/operations/wordpress-manual-deployment.md`.

Dos reglas que evitan los dos errores que este flujo permite (ADR 0020):

1. **Mergear a `main` no despliega.** Producción sale de un *release
   etiquetado*, no de «lo último de `main`». Antes de desplegar: subir
   `"version"` en `package.json`, pasar `CHANGELOG.md` de
   `## [Sin publicar]` a `## [X.Y.Z]`, actualizar `VERSION.md`, subir la
   versión que declara el theme o el plugin **si cambiaron** (`Version:` en
   el `style.css` del theme; la constante `REVISTALOGOS_CORE_VERSION` en el
   plugin), aterrizar por PR `chore(release): vX.Y.Z`, y solo entonces:

   ```bash
   git fetch --tags origin main
   git tag -a vX.Y.Z -m "vX.Y.Z" origin/main && git push origin vX.Y.Z
   ```

   El `fetch` no es ceremonia: `origin/main` es una copia local y, si está
   vieja, la etiqueta acaba en un commit que no es el del release.

2. **Al lanzar el workflow, en *Use workflow from* elegir `Tags → vX.Y.Z`,
   nunca `main`.** El gate (`tools/require-production-release-tag.sh`) exige
   una etiqueta anotada en HEAD, así que despachar desde `main` falla y
   nada se sube — molesto, pero seguro.

**El otro error era peor, y hasta hace poco nada lo frenaba:** el gate
comprobaba que *exista* una etiqueta anotada `vX.Y.Z` en HEAD, no que su
contenido correspondiera a esa versión. Una etiqueta bien formada sobre el
commit equivocado —sobre una rama de trabajo en vez de sobre el commit del
release— pasaba, y habría desplegado el código equivocado en silencio,
reinstalando sobre producción una versión de plugin anterior a la que ya
sirve. Ocurrió el 2026-08-29 con `v0.3.0`.

Desde ese mismo día el gate lo verifica: exige además que `package.json`
declare exactamente la versión de la etiqueta y que el commit etiquetado
sea alcanzable desde `main`. Aun así conviene comprobarlo **antes** de
etiquetar, por dos razones: el gate falla cuando la etiqueta ya está
publicada y el run despachado, y deshacer eso obliga a borrarla y recrearla
en local y en el remoto; y hay algo que el gate no puede saber, que es qué
versión de plugin corre producción ahora mismo.

```bash
git fetch --tags origin main
```

```bash
git show vX.Y.Z:package.json | grep '"version"'
```

```bash
git merge-base --is-ancestor vX.Y.Z origin/main && echo "en main" || echo "NO está en main"
```

**Prototipo estático:** abrir `static/index.html` en un navegador (sin build).
El espejo beta es GitHub Pages (`pages.yml`). El workflow estático
«Deploy to Hostinger» (`deploy.yml`) **está retirado**; no recrearlo.

**Lint CSS:** `nvm use` (respeta `.nvmrc`) y luego `npm run lint:css`.
`package.json` exige `node >=20.19.0` por stylelint 17; con una versión
inferior npm avisa `EBADENGINE`.

**Tests (ADR 0018, `docs/23-testing-foundation.md`, `docs/24-project-testing-standard.md`):** PHP syntax
`./tools/php-lint.sh` / `composer lint:php` (`php -l` only). Composer
lockfile audit: `composer audit:deps` (`composer audit --locked`; PHPUnit
and its transitives only — not WordPress, npm, or hosting). Units:
`composer test:unit` or `./tools/run-phpunit.sh`. Fast gate: `composer test`
(lint → audit → units; not `qa-*.sh`). On this laptop (no native PHP) use
the `./tools/*.sh` wrappers and `composer:2` for audit. WordPress workflows
remain isolated `tools/qa-*.sh`. Root Composer is **dev/test only**. Dependabot: weekly Composer + GitHub
Actions PRs, no auto-merge; CI + owner review. `composer audit --locked`
stays the lockfile advisory check. SonarQube Cloud is **Automatic Analysis**
(GitHub App, project `refo44_demo-revistalogos`); scope is
`.sonarcloud.properties` (plugin + theme). Not a PHPUnit coverage gate
and does not close D12b. See `docs/23-testing-foundation.md`.

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
