# Revista de Filosofía LOGO ET SPES — Estructura de Archivos Estáticos

**Geografía del proyecto**

Dónde viven los archivos estáticos: docs, content-source, maqueta (o tema), assets.

**Depende de:** `12-theme-file-structure`  
**Referencia:** `15-assets-strategy`, `16-content-source-inventory`

---

## 1. Estructura raíz

### Actual (fase maqueta)

```
revistalogos/
├── docs/                  (documentación, 00–20)
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
├── blog-index.html        (índice de Noticias)
├── search.html            (resultados de búsqueda; por construir)
├── 404.html               (no encontrado; por construir)
├── _config.yml            (solo si se usa GitHub Pages con Jekyll; opcional)
├── .gitignore
└── README.md
```

### Objetivo (fase WordPress)

```
revistalogos/
├── docs/
├── content-source/
└── theme-revistalogos/    (tema WordPress)
    └── (ver 12-theme-file-structure)
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
| `blog-index.html` | `home.php` |
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

En la fase WordPress, los assets viven dentro del tema: `theme-revistalogos/assets/`.

---

## 6. docs

Documentación. Prefijo numérico para el orden. Ver `00-order-documents`.

---

## 6.1 README

El README debe indicar el propósito del repositorio. Incluir:

Este repositorio contiene:

- Documentación del proyecto (`docs/`)
- Maqueta HTML del sitio
- Contenido fuente editorial (`content-source/`)
- Tema WordPress (cuando se implemente, en `theme-revistalogos/`)

---

## 7. Ruta de migración

1. **Maqueta:** HTML en raíz, `partials/`, `assets/`. Validar contra `04-screen-map`, `06-wireframes`, `19-accessibility-standards`.
2. **Creación del tema:** Crear carpeta del tema `theme-revistalogos/`. Copiar estructura de `12-theme-file-structure`.
3. **HTML → PHP:** Convertir cada `.html` a la plantilla `.php` correspondiente. Reemplazar includes HTML por `get_template_part('template-parts/header')`, `get_template_part('template-parts/footer')`, etc.
4. **Assets:** Mover `assets/` al tema. Actualizar rutas en encolado de `functions.php`.
5. **Registro de CPTs:** Añadir `inc/cpt-issue.php`, `inc/cpt-article.php`, taxonomías.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
