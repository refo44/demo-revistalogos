# Revista de Filosofía LOGO ET SPES — Estructura de Archivos Estáticos

**Geografía del proyecto**

Dónde viven los archivos estáticos: docs, content-source, maqueta (o tema), assets.

**Depende de:** `12-theme-file-structure`  
**Referencia:** `15-assets-strategy`, `16-content-source-inventory`

---

> **Nota de la Fase 3 (2026-07-31):** con la reorganización del monorepo
> (ADR 0007, tag `pre-fase3-reorg`) la maqueta estática dejó la raíz y vive
> completa bajo **`static/`** (`static/index.html`, `static/assets/`,
> `static/partials/`, `static/.htaccess`, `static/robots.txt`,
> `static/sitemap.xml`). Las rutas «raíz» descritas más abajo se leen hoy
> con el prefijo `static/`. El código WordPress vive en
> `wordpress/wp-content/` (theme `revistalogos` y plugin
> `revistalogos-core`). La estructura plana real de `static/assets/img/`
> se preserva tal cual; las subcarpetas documentadas en `docs/15` §1 son
> aspiracionales y su reorganización requiere decisión explícita
> (discrepancia registrada en `docs/fase3-execution-state.md`).
>
> **Clasificación (2026-08-19):** `static/` **sigue vigente** como prototipo
> Fase 2 y referencia visual de paridad (ADR 0001). No está obsoleto porque
> WordPress esté en producción. El espejo beta es GitHub Pages. Los HTML
> residuales en el document root de `logo-et-spes.cenfiss.net` son leftover
> del corte (deuda operativa), distintos de este árbol versionado. Ver
> `docs/operations/produccion-wordpress.md` § Referencias estáticas.

## 1. Estructura raíz

### Actual (fase maqueta)

```
revistalogos/
├── docs/                  (documentación, 00–20)
├── LICENSE                (licencia del código del repositorio: MIT)
├── LICENSE-CONTENT        (licencia del contenido editorial: CC BY 4.0)
├── content-source/        (contenido fuente antes de WordPress)
│   └── PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md
├── assets/                (CSS, JS, imágenes, fuentes, PDFs)
│   ├── css/
│   ├── js/
│   ├── img/
│   ├── fonts/
│   └── pdf/
├── partials/              (bloques HTML reutilizables)
├── scripts/
│   ├── deploy.sh
│   ├── update_navigation.py
│   └── update_produccion_links.py
├── index.html             (Home)
├── page-*.html            (páginas estáticas)
├── single-*.html          (número individual, artículo, entrada)
├── archive-*.html         (archivo de números, artículos)
├── noticias.html        (índice de Noticias)
├── search.html            (resultados de búsqueda; por construir)
├── 404.html               (no encontrado; por construir)
├── _config.yml            (solo si se usa GitHub Pages con Jekyll; opcional)
├── package.json           (tooling del proyecto: scripts, engines, devDependencies)
├── .gitignore
└── README.md
```

### Objetivo (fase WordPress)

```
revistalogos/
├── static/                          (maqueta congelada, ADR 0007)
├── docs/
├── content-source/
└── wordpress/wp-content/
    ├── themes/revistalogos/         (ver 12-theme-file-structure)
    └── plugins/revistalogos-core/
```

---

## 2. content-source

Todo el contenido canónico antes de la migración:

- `PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Documento del proyecto (estructura, políticas, normas editoriales, modelo de portada)
- Añadir: manual de marca (si existe), imágenes fuente, PDFs fuente

**Regla:** El contenido en WordPress debe trazarse a content-source. No inventar contenido en el admin sin una fuente. WordPress es el sistema de publicación; content-source es la fuente editorial. Esto evita que el CMS se convierta en la fuente de verdad.

**Nota:** `content-source/` puede estar en `.gitignore` si el documento del proyecto es grande o sensible.

---

## 3. Maqueta HTML (fase actual)

Los nombres de archivos siguen la jerarquía de plantillas de WordPress para facilitar la migración.

| Archivo | Mapea a |
|---------|---------|
| `index.html` | `front-page.php` |
| `page-acerca.html` | `page-acerca.php` |
| `page-contacto.html` | `page-contacto.php` |
| `page-normas.html` | `page-normas.php` |
| `page-etica.html` | `page-etica.php` |
| `page-politicas.html` | `page-politicas.php` |
| `page-enviar-colaboracion.html` | `page-enviar-colaboracion.php` |
| `page-comite-editorial.html` | `page-comite-editorial.php` |
| `page-enlaces.html` | `page-enlaces.php` |
| `noticias.html` | `home.php` |
| `archive-issue.html` | `archive-issue.php` |
| `archive-article.html` | `archive-article.php` |
| `single-issue.html` | `single-issue.php` |
| `single-article.html` | `single-article.php` |
| `single-post.html` | `single.php` |
| `search.html` | `search.php` |
| `404.html` | `404.php` |

---

## 4. partials (maqueta)

| Archivo | Mapea a |
|---------|---------|
| `partials/header.html` | `template-parts/header.php` |
| `partials/footer.html` | `template-parts/footer.php` |
| `partials/breadcrumbs.html` | `template-parts/breadcrumbs.php` |
| `partials/issue-card.html` | `template-parts/issue-card.php` |
| `partials/article-card.html` | `template-parts/article-card.php` |
| `partials/hero-current-issue.html` | `template-parts/hero-current-issue.php` |
| `partials/metadata-box.html` | `template-parts/metadata-box.php` |
| `partials/toc.html` | `template-parts/toc.php` |
| `partials/pagination.html` | `template-parts/pagination.php` |

**Nota:** `article-card.html` debe usar "Ver artículo" como acción principal (no "Leer más").

---

## 5. assets (fase maqueta)

| Ruta | Contenido |
|------|-----------|
| `assets/css/main.css` | Entrada; importa tokens, base, layout, components, pages, utilities |
| `assets/css/tokens.css` | Tokens de diseño |
| `assets/css/base.css` | Reset, tipografía |
| `assets/css/layout.css` | Contenedor, grid |
| `assets/css/components.css` | Botones, tarjetas, formularios, nav |
| `assets/css/pages/` | home.css, archive.css, issue.css, article.css, static-pages.css |
| `assets/css/utilities.css` | Clases de utilidad |
| `assets/js/main.js` | Toggle de nav, enlace saltar, acordeones opcionales |
| `assets/img/` | logos/, portada-numeros/, autores/, placeholders/, banners/ (ver 15-assets-strategy) |
| `assets/fonts/` | Fuentes web (si se usan) |
| `assets/pdf/` | PDFs de ejemplo (normas, artículo, número) |

En la fase WordPress, los assets de presentación viven en `wordpress/wp-content/themes/revistalogos/assets/`.

---

## 6. docs

Documentación. Prefijo numérico para el orden. Ver `00-order-documents`.

---

## 6.1 README

El README debe indicar el propósito del repositorio. Incluir:

Este repositorio contiene:

- Documentación del proyecto (`docs/`)
- Maqueta HTML del sitio (`static/`)
- Contenido fuente editorial (`content-source/`)
- Theme WordPress (`wordpress/wp-content/themes/revistalogos/`) y plugin `revistalogos-core`
- Política de licencias del repositorio (`LICENSE` para código, `LICENSE-CONTENT` para contenido)

---

## 7. Ruta de migración

1. **Maqueta:** HTML en raíz, `partials/`, `assets/`. Validar contra `04-screen-map`, `06-wireframes`, `19-accessibility-standards`.
2. **Creación del tema:** Theme en `wordpress/wp-content/themes/revistalogos/` según `12-theme-file-structure`; dominio en el plugin `revistalogos-core` (ADR 0005).
3. **HTML → PHP:** Convertir cada `.html` a la plantilla `.php` correspondiente. Reemplazar includes HTML por `get_template_part('template-parts/header')`, `get_template_part('template-parts/footer')`, etc.
4. **Assets:** Mover `assets/` al tema. Actualizar rutas en encolado de `functions.php`.
5. **Registro de CPTs:** Añadir `inc/cpt-issue.php`, `inc/cpt-article.php`, taxonomías.

---

**Versión:** 1.1  
**Proyecto:** Revista de Filosofía LOGO ET SPES 0.2.0
