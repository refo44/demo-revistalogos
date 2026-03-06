# Revista de Filosofía LOGO ET SPES — Corporate Identity

**Version 1.0**

This document defines the visual and typographic identity system for the site. Values come from the implementation and the project document.

**Canonical source:** `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md`  
**Implementation reference:** `assets/css/tokens.css`, `assets/img/logo-revista.svg`

---

## 1. Color palette

Only the colors defined here exist. These constitute the chromatic core. Do not add additional primary colors.

| Name | Hex | RGB |
|------|-----|-----|
| Azul institucional | #1e3a8a | R: 30, G: 58, B: 138 |
| Azul claro | #3b82f6 | R: 59, G: 130, B: 246 |
| Azul oscuro | #1e40af | R: 30, G: 64, B: 175 |
| Gris neutro | #64748b | R: 100, G: 116, B: 139 |
| Texto principal | #1e293b | R: 30, G: 41, B: 59 |
| Blanco | #ffffff | R: 255, G: 255, B: 255 |
| Enlace | #00b0f0 | R: 0, G: 176, B: 240 |

**Note:** The project document mentions *verde aguamarina degradado* for the cover. The current digital implementation uses the blue palette above. If a formal brand manual is adopted, it prevails.

### 1.1 Semantic order (for implementation)

| Token | Hex | Suggested use |
|-------|-----|---------------|
| `--brand-1` | #1e3a8a | Primary accent, header, CTAs |
| `--brand-2` | #3b82f6 | Secondary accent, hover, links |
| `--brand-3` | #ffffff | Surfaces, backgrounds |
| `--brand-4` | #1e293b | Text, body copy |

### 1.2 Semantic roles (CSS)

```css
:root {
  --brand-1: #1e3a8a;   /* Primary accent */
  --brand-2: #3b82f6;   /* Secondary accent */
  --brand-3: #ffffff;   /* Surfaces, borders */
  --brand-4: #1e293b;   /* Text */

  --bg: var(--brand-3);
  --text: var(--brand-4);
  --text-muted: #64748b;
  --surface: var(--brand-3);
  --border: #e5e7eb;
  --link: #00b0f0;
  --link-hover: var(--brand-2);
  --primary: var(--brand-1);
  --primary-hover: var(--brand-2);
  --header-bg: var(--brand-1);
  --footer-bg: var(--brand-2);
}
```

**Note:** Verify AA contrast for accessibility. See `19-accessibility-standards`.

---

## 2. Typographic system

| Family | Use |
|--------|-----|
| **Georgia** | Titles, brand voice, highlights, logo |
| **System UI** | Body text, navigation, UI |

### 2.1 CSS variables

```css
:root {
  --font-display: "Georgia", "Times New Roman", serif;
  --font-heading: "Georgia", "Times New Roman", serif;
  --font-body: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
```

### 2.2 Scale (from tokens)

- Base: 1rem (16px)
- Body: 1rem, line-height 1.5
- Headings: 1.25rem → 3rem (xl to 5xl)

---

## 3. Logo

- **Revista:** `assets/img/logo-revista.svg` — Header, digital materials
- **CENFISS:** `assets/img/logo-cenfiss.svg` — Institutional reference
- **Favicon:** `assets/img/favicon.svg` — Derived from logo
- **Rule:** Do not alter proportions, color or composition.

---

## 4. Brand essence

| Trait | Description |
|------|-------------|
| Voice | Academic, clear, rigorous. No commercial pressure. |
| Aesthetic | Institutional, calm. Blue palette conveys trust and seriousness. |
| Rhythm | Generous whitespace. Vertical reading flow. No dense grids. |
| Function | Guide the visitor to read, submit, or contact. |

---

## 5. Editorial rhythm

### Body text

- Measure: ~65ch (human reading width)
- Line height: 1.5–1.6
- Space: generous margins, breathing room

### Navigation and UI

- Discrete, without dominating content
- No aggressive CTAs
- Primary CTA: Enviar Colaboración (clear, not pushy)

---

## 6. Layout grammar

| Rule | Meaning |
|------|---------|
| Single column / simple layout | Vertical reading |
| Space | Breathing, calm |
| No dense grids | Not magazine nor e-commerce |
| Clear flow | Guide toward main action |

---

## 7. Final rule

Nothing visual is decided outside this system. In case of conflict with a future brand manual, the brand manual prevails.

---

**Version:** 1.0  
**Source:** PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md, tokens.css  
**Project:** Revista de Filosofía LOGO ET SPES
