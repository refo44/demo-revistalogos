# {{PROJECT_NAME}} — Assets Strategy

**Images, fonts, icons and static files**

Defines how assets are sourced, optimized and used.

**Depends on:** `02-corporate-identity`, `16-content-source-inventory`  
**Reference:** `14-css-architecture`, `12-theme-file-structure`

---

## 1. Image strategy

### Sources

- `content-source/{{PROJECT_FOLDER}}/{{IMAGES_FOLDER}}/`
- No images invented; all traceable to source.

### Optimization

- Format: WebP with JPEG/PNG fallback when needed
- Compression: Balance quality and size
- Responsive: `srcset` for different viewport sizes
- Lazy loading: For below-fold images

### Naming

- Lowercase, kebab-case
- Descriptive: `hero-community.jpg`, `event-retreat-2024.jpg`

---

## 2. Typography

### Fonts

| Use | Family | Source |
|-----|--------|--------|
| Display / headings | {{FONT}} | {{SOURCE}} |
| Body | {{FONT}} | {{SOURCE}} |

### Implementation

- Self-host when possible (privacy, performance)
- Subset if feasible (Latin, etc.)
- Format: woff2 preferred
- Load: `font-display: swap` or `optional`

---

## 3. Icons

- SVG preferred (scalable, lightweight)
- Inline or sprite; avoid icon fonts if possible
- Decorative: `aria-hidden="true"`, `focusable="false"`

---

## 4. Favicon

- Derive from logo
- Sizes: 16×16, 32×32, 180×180 (Apple), 192×192, 512×512 (PWA if needed)

---

## 5. File locations

| Phase | Location |
|-------|----------|
| Maquette (static) | `assets/images/`, `assets/fonts/` |
| WordPress | `theme/.../assets/images/`, `theme/.../assets/fonts/` |

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
