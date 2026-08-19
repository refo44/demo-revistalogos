# Revista de Filosofía LOGO ET SPES

**Versión:** 0.2.0 (canónica en `package.json`; ver `VERSION.md` y `CHANGELOG.md`)

Monorepo de la revista académica (CENFISS): prototipo HTML estático (`static/`,
Fase 2, base visual congelada) y WordPress (`wordpress/`, Fase 3 clásica lista).
Pendiente: FSE (ADR 0015) y corte en `logo-et-spes.cenfiss.net` (ADR 0016).

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
├── tools/                           → Generador del payload de migración
├── docs/                            → Documentación numerada, ADRs, harness Fase 3
├── content-source/                  → Fuente canónica (no versionada)
└── .github/workflows/               → deploy.yml (estático, manual),
                                       pages.yml (espejo beta, automático),
                                       deploy-wordpress.yml (staging, manual)
```

El detalle de las plantillas estáticas y su mapeo a WordPress está en
`docs/17-implementation-order` §2.2 y en la matriz de cobertura de
`docs/fase3-validation-matrix.md`. Estado de ejecución: `docs/fase3-execution-state.md`.

## Cómo usar

**Prototipo estático:** abrir `static/index.html` en un navegador (sin build).

**WordPress local (Docker, ADR 0014):** `docker compose up -d` →
`http://localhost:8080`. Imagen `wordpress:7.0.4-php8.2-apache`. WP-CLI:
`docker compose run --rm wpcli wp <cmd>`. No usar `docker compose down -v`
(borra `db_data` y `wp_data`). Cambiar el tag de imagen no actualiza el core
persistido; hace falta `wp core update` + `wp core update-db`.

**Lint CSS:** `npm run lint:css`.

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
ficticios) no se publica en producción (ADR 0004).

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

Implementación clásica en el repo: theme + plugin, migración institucional,
fixtures, CF7, WP Statistics, búsqueda `/buscar/?q=`, workflow FTPS manual.
Siguiente: FSE en Docker y corte en cPanel `cenfiss2`. No desplegar el HTML
estático (`deploy.yml`) sobre el subdominio una vez instalado WordPress
(ADR 0016).

## Licencia

- **Código** (HTML, CSS, JS, PHP, scripts y configuración): **MIT**. Ver `LICENSE`.
- **Contenido editorial y de publicación**: **Creative Commons Atribución 4.0 Internacional (CC BY 4.0)**. Ver `LICENSE-CONTENT`.

Los materiales de terceros conservan su propia licencia o atribución cuando corresponda.

---

**Desarrollado para CENFISS - Centro de Estudios Filosóficos y Sociales**
