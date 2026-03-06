# Revista de Filosofía LOGO ET SPES — Assets Strategy

**Images, fonts, icons and static files**

Defines how assets are sourced, optimized and used.

**Depends on:** `02-corporate-identity`, `16-content-source-inventory`  
**Reference:** `14-css-architecture`, `12-theme-file-structure`

---

## 1. Image strategy

### Sources

- `content-source/` — Project document may reference cover images, brand assets.
- `assets/img/` — Current maquette assets. Trace to source when possible.
- No images invented; all traceable to source or documented as placeholder.

### Current inventory

| File | Use | Source |
|------|-----|--------|
| `logo-revista.svg` | Header, brand | Brand / 02 |
| `logo-cenfiss.svg` | Institutional reference | CENFISS |
| `favicon.svg` | Favicon | Derived from logo |
| `portada-ejemplo.png` | Issue cover placeholder | Maquette |
| `article-placeholder.svg` | Article card fallback | Maquette |
| `avatar-default.svg` | Author avatar fallback | Maquette |
| `placeholder-banner.jpg` | Banner carousel | Maquette |
| `flyer.jpg` | CENFISS events (carousel) | CENFISS |
| `banner-main.png` | Hero/banner | Maquette |

### Optimization

- **Format:** WebP with JPEG/PNG fallback when needed. SVG for logos, icons.
- **Compression:** Balance quality and size. Issue covers: max ~800px width for display.
- **Responsive:** `srcset` for issue covers, banners when multiple sizes exist.
- **Lazy loading:** For below-fold images (article cards, archive grids).
- **Alt text:** Always fill. Decorative: `alt=""`. See `09-ui-copy-sheet`, `19-accessibility-standards`.

### Naming

- Lowercase, kebab-case
- Descriptive: `portada-vol-12-n2-2025.jpg`, `logo-revista.svg`, `flyer-diplomado-2025.jpg`

---

## 2. Typography

### Fonts

| Use | Family | Source |
|-----|--------|--------|
| Display / headings | Georgia, Times New Roman | System |
| Body | -apple-system, Segoe UI, Roboto, Arial | System |

### Implementation

- **No custom font files.** Uses system fonts. No external font loading.
- **Privacy and performance:** No font CDN or external requests.
- **Fallback:** `serif` for headings, `sans-serif` for body.
- **Future:** If custom fonts are adopted, self-host woff2, subset Latin, `font-display: swap`.

---

## 3. Icons

- **SVG preferred** (scalable, lightweight). Current: favicon, placeholders.
- **Decorative:** `aria-hidden="true"`, `focusable="false"`
- **Meaningful:** Provide accessible name or hide from assistive tech as appropriate.
- **Avoid icon fonts** if possible; inline SVG or sprite.

---

## 4. Favicon

- **File:** `favicon.svg` — Derived from logo
- **Sizes:** SVG covers all. For legacy: 16×16, 32×32, 180×180 (Apple), 192×192
- **Use:** `<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">`

---

## 5. PDFs

| File | Use |
|-----|-----|
| `normas-publicacion.pdf` | Normas page download |
| `politicas-editorial.pdf` | Políticas page download |
| `solicitud-publicacion-declaracion-etica.pdf` | Enviar colaboración form (per project doc) |
| `instrumento-arbitraje.pdf` | Normas (arbitraje form; per project doc) |
| `articulo-01.pdf` | Sample article |
| `numero-v12n2-2025.pdf` | Sample issue |

**Rule:** PDFs in WordPress phase live in media library. Links from pages point to attachment URL.

---

## 6. File locations

| Phase | Location |
|-------|----------|
| Maquette (static) | `assets/img/`, `assets/pdf/`, `assets/css/`, `assets/js/` |
| WordPress | `revistalogos/assets/img/`, `revistalogos/assets/pdf/` (or media library for PDFs) |

---

## 7. Do-not rules

- Do not add images without documenting source in 16.
- Do not use uncompressed large images; optimize before commit.
- Do not inline base64 images in CSS/HTML for non-trivial assets.
- Do not use external font CDNs without explicit project decision.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
