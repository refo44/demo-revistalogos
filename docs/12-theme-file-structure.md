# Revista de Filosofía LOGO ET SPES — Theme File Structure

**WordPress theme file architecture**

Defines theme file structure: what templates exist and what parts are reused. Official routes in 11; this document defines how they render.

**Depends on:** `03-wordpress-content-model`, `04-screen-map`, `11-url-tree`, `05-information-architecture-navigation`  
**Reference:** `02-corporate-identity`, `06-wireframes`

---

## 1. Templates by function

| Function | Template |
|----------|----------|
| Home | `front-page.php` |
| Acerca | `page-acerca.php` |
| Contacto | `page-contacto.php` |
| Normas | `page-normas.php` |
| Ética | `page-etica.php` |
| Políticas | `page-politicas.php` |
| Enviar colaboración | `page-enviar-colaboracion.php` |
| Comité Editorial | `page-comite.php` |
| Enlaces | `page-enlaces.php` |
| Fallback pages | `page.php` |
| Fallback global | `index.php` |

---

## 2. Custom Post Types

| CPT | Archive | Single |
|-----|---------|--------|
| issue (numeros) | `archive-issue.php` | `single-issue.php` |
| article (articulos) | `archive-article.php` | `single-article.php` |

---

## 3. Blog (posts)

| View | Template |
|------|----------|
| Noticias index | `home.php` |
| Single post | `single.php` |

---

## 4. States

| State | File |
|-------|------|
| Not found | `404.php` |
| Search results | `search.php` |

---

## 4.1 Taxonomy archives (filtered articles)

Routes `/articulos/seccion/{section}/` and `/articulos/tipo/{type}/` (per `11-url-tree`).

| Option | Template | Use |
|--------|----------|-----|
| A | `archive-article.php` | Single template handles all. Use `get_queried_object()` to detect section/type and adjust title. |
| B | `taxonomy-section.php` | Custom layout when filtering by section. Falls back to archive-article logic. |
| C | `taxonomy-article_type.php` | Custom layout when filtering by type. |

**Recommendation:** Option A. Keep `archive-article.php` as the single archive template; it receives the main query already filtered by taxonomy. Add conditional title/description based on `is_tax()`.

---

## 5. Reusable parts

| File | Function |
|------|----------|
| `header.php` | Entry for `get_header()`; loads `parts/header.php` |
| `footer.php` | Entry for `get_footer()`; loads `parts/footer.php` |
| `parts/header.php` | Header (logo + navigation) |
| `parts/footer.php` | Footer |
| `parts/breadcrumbs.php` | Breadcrumb trail (Inicio → path) |
| `parts/issue-card.php` | Issue card (cover, title, meta, PDF, Ver contenido) |
| `parts/article-card.php` | Article card (title, authors, abstract, PDF, Leer más) |
| `parts/hero-issue.php` | Hero block for current issue (Home) |
| `parts/metadata-box.php` | Article metadata (authors, DOI, keywords, citation) |
| `parts/toc.php` | Table of contents (single issue) |
| `parts/pagination.php` | Prev/next, page numbers (archives, search) |
| `parts/sidebar-card.php` | Sidebar block (related links, info) |

---

## 6. Theme tree

```
revistalogos/
├── style.css              (metadata only, required by WP)
├── theme.json             (design tokens: palette, typography)
├── screenshot.png
├── functions.php
├── header.php
├── footer.php
├── index.php
├── front-page.php
├── home.php
├── single.php
├── search.php
├── page.php
├── page-acerca.php
├── page-contacto.php
├── page-normas.php
├── page-etica.php
├── page-politicas.php
├── page-enviar-colaboracion.php
├── page-comite.php
├── page-enlaces.php
├── archive-issue.php
├── archive-article.php
├── single-issue.php
├── single-article.php
├── comments.php            (minimal; comments disabled for articles)
├── 404.php
├── inc/
│   ├── cpt-issue.php
│   ├── cpt-article.php
│   ├── taxonomies.php
│   └── template-tags.php
├── assets/
│   ├── css/
│   │   ├── main.css       (entry: imports or concatenates)
│   │   ├── tokens.css
│   │   ├── base.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   ├── pages.css
│   │   └── utilities.css
│   ├── js/
│   │   └── main.js
│   ├── img/
│   │   ├── logo-revista.svg
│   │   ├── logo-cenfiss.svg
│   │   ├── favicon.svg
│   │   └── ...
│   └── pdf/               (or media library)
│       └── ...
└── parts/
    ├── header.php
    ├── footer.php
    ├── breadcrumbs.php
    ├── issue-card.php
    ├── article-card.php
    ├── hero-issue.php
    ├── metadata-box.php
    ├── toc.php
    ├── pagination.php
    └── sidebar-card.php
```

---

## 7. CSS strategy

| File | Role |
|------|------|
| `style.css` | Theme metadata only (required by WordPress). No styles. |
| `theme.json` | Design tokens: palette, typography, spacing (block editor). |
| `assets/css/main.css` | Entry point. Imports: tokens, base, layout, components, pages, utilities. |
| `assets/css/tokens.css` | Design tokens (colors, fonts, spacing). |
| `assets/css/base.css` | Resets, typography base. |
| `assets/css/layout.css` | Container, grid, structure. |
| `assets/css/components.css` | Buttons, cards, forms, nav. |
| `assets/css/pages.css` | Page-specific styles. |
| `assets/css/utilities.css` | Utility classes. |

Enqueue `main.css` in `functions.php` via `wp_enqueue_style`. Single entry point.

---

## 8. inc/ (required)

CPT registration and helpers. Keeps `functions.php` clean.

```
inc/
├── cpt-issue.php          (register issue CPT)
├── cpt-article.php        (register article CPT)
├── taxonomies.php         (section, article_type)
└── template-tags.php      (helper functions)
```

Register in `functions.php` via `require_once`. Load order: taxonomies after CPTs.

---

## 8.1 comments.php

Include a minimal `comments.php` even if comments are disabled. WordPress may look for it; an empty or disabled template avoids notices.

```php
<?php
/**
 * Comments template. Required by WordPress.
 * Comments disabled for academic journal.
 */
if ( post_password_required() ) {
	return;
}
// Comments closed. No output needed.
?>
```

---

## 9. Best practices

- **Clean templates:** Markup and simple calls; logic in `functions.php` or `inc/`.
- **inc/ mandatory:** CPTs and taxonomies always in `inc/`; never inline in `functions.php`.
- **Single CSS entry:** One `main.css`; imports or build step for modules.
- **Reuse via parts:** `get_template_part('parts/header')`, `get_template_part('parts/issue-card')`.
- **Security:** Escape output (`esc_html`, `esc_attr`); sanitize input; nonces for forms.
- **No builder lock-in:** Avoid Elementor/page builders unless explicit decision.
- **Accessibility:** Preserve semantic structure, skip link, keyboard nav, contrast (19).

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
