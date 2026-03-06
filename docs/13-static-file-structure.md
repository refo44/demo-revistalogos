# {{PROJECT_NAME}} — Static File Structure

**Project geography**

Where static files live: docs, content-source, theme, assets.

**Depends on:** `12-theme-file-structure`  
**Reference:** `15-assets-strategy`, `16-content-source-inventory`

---

## 1. Root structure

```
{{project-root}}/
├── docs/                  (this documentation)
├── content-source/        (source content before WordPress)
│   └── {{PROJECT_FOLDER}}/
├── theme/                 (WordPress theme)
│   └── theme-{{slug}}/
├── assets/                (static assets for maquette phase)
└── scripts/               (optional: build, optimize)
```

---

## 2. content-source

All canonical content before migration:

- `{{PROJECT_FOLDER}}/` — Project folder
  - `{{DOCX_FILES}}` — Word documents
  - `{{PDF_FILES}}` — Brand manual, etc.
  - `{{IMAGES}}/` — Source images
  - `{{VIDEOS}}/` — If applicable

**Rule:** Content in WordPress should trace back to content-source. No content invented in the admin without a source.

---

## 3. theme

WordPress theme. Structure defined in `12-theme-file-structure`.

---

## 4. assets (maquette phase)

During static maquette (pre-WordPress):

- `assets/css/main.css`
- `assets/js/main.js`
- `assets/images/`
- `assets/fonts/`

In WordPress phase, assets live inside the theme: `theme/.../assets/`.

---

## 5. docs

Documentation. Numbered prefix for order. See `00-order-documents`.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
