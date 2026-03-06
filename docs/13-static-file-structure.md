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
├── assets/                (CSS, JS, imágenes, PDFs)
├── partials/              (bloques HTML reutilizables)
├── index.html             (Home)
├── page-*.html            (páginas estáticas)
├── single-*.html          (número individual, artículo, entrada)
├── archive-*.html         (archivo de números, artículos)
├── blog-index.html        (índice de Noticias)
├── search.html            (resultados de búsqueda; por construir)
├── 404.html               (no encontrado; por construir)
├── _config.yml            (Jekyll, si se usa)
├── deploy.sh
├── update_navigation.py
├── update_produccion_links.py
├── .gitignore
└── README.md
```

### Objetivo (fase WordPress)

```
revistalogos/
├── docs/
├── content-source/
├── revistalogos/          (tema WordPress)
│   └── (ver 12-theme-file-structure)
└── (opcional) scripts/   (build, optimizar)
```

---

## 2. content-source

Todo el contenido canónico antes de la migración:

- `PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Documento del proyecto (estructura, políticas, normas editoriales, modelo de portada)
- Añadir: manual de marca (si existe), imágenes fuente, PDFs fuente

**Regla:** El contenido en WordPress debe trazarse a content-source. No inventar contenido en el admin sin una fuente.

**Nota:** `content-source/` puede estar en `.gitignore` si el documento del proyecto es grande o sensible.

---

## 3. Maqueta HTML (fase actual)

| Archivo | Mapea a |
|---------|---------|
| `index.html` | `front-page.php` |
| `page-acerca.html` | `page-acerca.php` |
| `page-contacto.html` | `page-contacto.php` |
| `page-normas.html` | `page-normas.php` |
| `page-etica.html` | `page-etica.php` |
| `page-politicas.html` | `page-politicas.php` |
| `page-enviar-colaboracion.html` | `page-enviar-colaboracion.php` |
| `page-comite.html` | `page-comite.php` |
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
| `partials/header.html` | `parts/header.php` |
| `partials/footer.html` | `parts/footer.php` |
| `partials/breadcrumbs.html` | `parts/breadcrumbs.php` |
| `partials/issue-card.html` | `parts/issue-card.php` |
| `partials/article-card.html` | `parts/article-card.php` |
| `partials/hero-issue.html` | `parts/hero-issue.php` |
| `partials/metadata-box.html` | `parts/metadata-box.php` |
| `partials/toc.html` | `parts/toc.php` |
| `partials/pagination.html` | `parts/pagination.php` |

---

## 5. assets (fase maqueta)

| Ruta | Contenido |
|------|-----------|
| `assets/css/main.css` | Entrada; importa tokens, base, layout, components, pages, utilities |
| `assets/css/tokens.css` | Tokens de diseño |
| `assets/css/base.css` | Reset, tipografía |
| `assets/css/layout.css` | Contenedor, grid |
| `assets/css/components.css` | Botones, tarjetas, formularios, nav |
| `assets/css/pages.css` | Estilos específicos de página |
| `assets/css/utilities.css` | Clases de utilidad |
| `assets/js/main.js` | Toggle de nav, enlace saltar, acordeones opcionales |
| `assets/img/` | logo-revista.svg, logo-cenfiss.svg, favicon.svg, placeholders |
| `assets/pdf/` | PDFs de ejemplo (normas, artículo, número) |

En la fase WordPress, los assets viven dentro del tema: `revistalogos/assets/`.

---

## 6. docs

Documentación. Prefijo numérico para el orden. Ver `00-order-documents`.

---

## 7. Ruta de migración

1. **Maqueta:** HTML en raíz, `partials/`, `assets/`. Validar contra `04-screen-map`, `06-wireframes`, `19-accessibility-standards`.
2. **Creación del tema:** Crear carpeta del tema `revistalogos/`. Copiar estructura de `12-theme-file-structure`.
3. **HTML → PHP:** Convertir cada `.html` a la plantilla `.php` correspondiente. Reemplazar includes de partials con `get_template_part()`.
4. **Assets:** Mover `assets/` al tema. Actualizar rutas en encolado de `functions.php`.
5. **Registro de CPTs:** Añadir `inc/cpt-issue.php`, `inc/cpt-article.php`, taxonomías.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
