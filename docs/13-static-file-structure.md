# Revista de Filosofía LOGO ET SPES — Static File Structure

**Project geography**

Where static files live: docs, content-source, maquette (or theme), assets.

**Depends on:** `12-theme-file-structure`  
**Reference:** `15-assets-strategy`, `16-content-source-inventory`

---

## 1. Root structure

### Current (maquette phase)

```
revistalogos/
├── docs/                  (documentation, 00–20)
├── content-source/        (source content before WordPress)
│   └── PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md
├── assets/                (CSS, JS, images, PDFs)
├── partials/              (reusable HTML blocks)
├── index.html             (Home)
├── page-*.html            (static pages)
├── single-*.html          (single issue, article, post)
├── archive-*.html         (archive issues, articles)
├── blog-index.html        (Noticias index)
├── search.html            (search results; to build)
├── 404.html               (not found; to build)
├── _config.yml            (Jekyll, if used)
├── deploy.sh
├── update_navigation.py
├── update_produccion_links.py
├── .gitignore
└── README.md
```

### Target (WordPress phase)

```
revistalogos/
├── docs/
├── content-source/
├── revistalogos/          (WordPress theme)
│   └── (see 12-theme-file-structure)
└── (optional) scripts/    (build, optimize)
```

---

## 2. content-source

All canonical content before migration:

- `PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Project document (structure, policies, editorial norms, portada model)
- Add: brand manual (if exists), source images, source PDFs

**Rule:** Content in WordPress should trace back to content-source. No content invented in the admin without a source.

**Note:** `content-source/` may be in `.gitignore` if the project document is large or sensitive.

---

## 3. Maquette HTML (current phase)

| File | Maps to |
|------|---------|
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

## 4. partials (maquette)

| File | Maps to |
|------|---------|
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

## 5. assets (maquette phase)

| Path | Content |
|------|---------|
| `assets/css/main.css` | Entry; imports tokens, base, layout, components, pages, utilities |
| `assets/css/tokens.css` | Design tokens |
| `assets/css/base.css` | Reset, typography |
| `assets/css/layout.css` | Container, grid |
| `assets/css/components.css` | Buttons, cards, forms, nav |
| `assets/css/pages.css` | Page-specific styles |
| `assets/css/utilities.css` | Utility classes |
| `assets/js/main.js` | Nav toggle, skip link, optional accordions |
| `assets/img/` | logo-revista.svg, logo-cenfiss.svg, favicon.svg, placeholders |
| `assets/pdf/` | Sample PDFs (normas, artículo, número) |

In WordPress phase, assets live inside the theme: `revistalogos/assets/`.

---

## 6. docs

Documentation. Numbered prefix for order. See `00-order-documents`.

---

## 7. Migration path

1. **Maquette:** HTML at root, `partials/`, `assets/`. Validate against `04-screen-map`, `06-wireframes`, `19-accessibility-standards`.
2. **Theme creation:** Create `revistalogos/` theme folder. Copy structure from `12-theme-file-structure`.
3. **HTML → PHP:** Convert each `.html` to corresponding `.php` template. Replace partial includes with `get_template_part()`.
4. **Assets:** Move `assets/` into theme. Update paths in `functions.php` enqueue.
5. **CPT registration:** Add `inc/cpt-issue.php`, `inc/cpt-article.php`, taxonomies.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
