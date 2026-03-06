# {{PROJECT_NAME}} — Theme File Structure

**WordPress theme file architecture**

Defines theme file structure: what templates exist and what parts are reused. Official routes in 11; this document defines how they render.

**Depends on:** `03-wordpress-content-model`, `04-screen-map`, `11-url-tree`, `05-information-architecture-navigation`  
**Reference:** `02-corporate-identity`, `06-wireframes`

---

## 1. Templates by function

| Function | Template |
|----------|----------|
| Home | `front-page.php` |
| {{PAGE_1}} | `page-{{slug}}.php` |
| … | … |
| Fallback pages | `page.php` |
| Fallback global | `index.php` |

---

## 2. Custom Post Types

| View | Template |
|------|----------|
| Archive | `archive-{{cpt}}.php` |
| Single | `single-{{cpt}}.php` |

---

## 3. States

| State | File |
|-------|------|
| Not found | `404.php` |

---

## 4. Reusable parts

| File | Function |
|------|----------|
| `header.php` | Entry for `get_header()`; loads `parts/header.php` |
| `footer.php` | Entry for `get_footer()`; loads `parts/footer.php` |
| `parts/header.php` | Header (logo + navigation) |
| `parts/footer.php` | Footer |
| `parts/navigation.php` | Main menu |
| `parts/{{block}}.php` | {{BLOCK_DESCRIPTION}} |

---

## 5. Theme tree

```
theme-{{project-slug}}/
├── style.css              (metadata only, no styles)
├── theme.json             (design tokens)
├── screenshot.png
├── functions.php
├── header.php
├── footer.php
├── index.php
├── front-page.php
├── page-{{slug}}.php
├── single-{{cpt}}.php
├── archive-{{cpt}}.php
├── 404.php
├── assets/
│   ├── css/
│   │   └── main.css
│   ├── js/
│   │   └── main.js
│   ├── fonts/
│   ├── images/
│   └── favicon/
└── parts/
    ├── header.php
    ├── footer.php
    ├── navigation.php
    └── {{block}}.php
```

---

## 6. CSS strategy

| File | Role |
|------|------|
| `style.css` | Theme metadata only (required by WordPress) |
| `theme.json` | Design tokens: palette, typography, spacing |
| `assets/css/main.css` | Real styles: layout, components |

Enqueue `main.css` in `functions.php` via `wp_enqueue_style`.

---

## 7. Best practices

- **Clean templates:** Markup and simple calls; logic in `functions.php` or `inc/`.
- **Single CSS entry:** One `main.css`; no fragmented styles.
- **Reuse via parts:** `get_template_part()` for header, footer, blocks.
- **Security:** Escape output; sanitize input; use nonces for forms.
- **No builder lock-in:** Avoid Elementor/page builders unless explicit decision.
- **Accessibility:** Preserve semantic structure, keyboard nav, contrast (19).

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
