# Revista de Filosofía LOGO ET SPES — CSS Architecture

**Layers, tokens and style implementation criteria**

Defines how site CSS is organized: relationship between theme.json, variables and main.css, specificity and naming, accessibility in styles.

**Depends on:** `02-corporate-identity`, `12-theme-file-structure`  
**Reference:** `15-assets-strategy`, `19-accessibility-standards`, `20-layout-principles`

---

## 1. Style system layers

| Layer | Source | Role |
|-------|--------|------|
| Theme metadata | `style.css` | Header only (Theme Name, etc.). No CSS rules. |
| Design tokens | `tokens.css` | Colors, typography, spacing, borders, shadows, z-index. |
| theme.json | `theme.json` | Palette, typography for WordPress block editor. Align with tokens. |
| Base | `base.css` | Box-sizing, reset, typography, links. |
| Layout | `layout.css` | Container, header, nav, footer, grid. |
| Components | `components.css` | Buttons, cards, hero, toc, breadcrumbs, metadata, forms. |
| Pages | `pages.css` | Page-specific (front-page, archive, single-issue, single-article). |
| Utilities | `utilities.css` | Flow, gap, text, display, flex, visually-hidden, focus-ring. |

No frameworks (Tailwind, Bootstrap). Single entry point: `main.css` imports all.

---

## 2. main.css structure

**Import order (in main.css):**

1. `tokens.css` — Variables (:root)
2. `base.css` — Reset, typography, links
3. `layout.css` — Container, header, nav, footer, grid
4. `components.css` — Buttons, cards, hero, toc, breadcrumbs, metadata
5. `pages.css` — Page-specific
6. `utilities.css` — Helpers, visually-hidden, focus-ring

---

## 3. Token usage

| Token category | Variables | Use |
|----------------|-----------|-----|
| Colors | `--color-primary`, `--color-text-primary`, `--color-link`, etc. | All components consume via `var()`. No raw hex in components. |
| Typography | `--font-family-primary`, `--font-family-heading`, `--font-size-*`, `--line-height-*` | Base and components. |
| Spacing | `--space-1` … `--space-24` | Margins, padding, gaps. |
| Layout | `--container-max-width`, `--container-padding` | Container, grid. |
| Borders | `--border-width`, `--border-radius` | Buttons, cards, inputs. |
| Transitions | `--transition-fast`, `--transition-normal` | Hover, focus. |
| Z-index | `--z-dropdown`, `--z-sticky`, `--z-modal` | Stacking. |

**Rule:** Components never use raw hex. They consume `var(--color-*)` or semantic roles.

---

## 4. Naming and specificity

- **Convention:** BEM-like. Block (`header`), element (`header__logo`), modifier (`btn--primary`).
- **Examples:** `.header`, `.header__inner`, `.btn`, `.btn--primary`, `.card`, `.article-card`, `.front-page__hero`.
- **Specificity:** Keep low. Avoid deep nesting (max 2–3 levels). No `!important`.
- **IDs:** Do not use for styles; reserve for anchors and ARIA (`#main-content`).
- **Inline styles:** Do not style by `[style]`; no inline in editorial content.

---

## 5. theme.json and tokens.css

- **theme.json** defines colors, typography, spacing for WordPress block editor. Must align with `02-corporate-identity`.
- **tokens.css** holds the same decisions. Single source for non-WP maquette; both must stay in sync.
- **Semantic roles:** Consider adding `--brand-1`, `--text`, `--link` in tokens for components that need semantic roles (see 02).

---

## 6. Accessibility in CSS

- **Contrast:** Text and controls must meet WCAG 2.1 AA. Verify combinations from 02 (e.g. `--color-text-primary` on `--color-bg-primary`).
- **Focus:** Visible style for `:focus-visible` on links, buttons, form controls. Implemented in base.css and utilities (`.focus-ring`). No `outline: none` without replacement.
- **Reduced motion:** Respect `prefers-reduced-motion: reduce`; reduce or remove animations. Add where transitions exist.
- **Tap/click size:** Adequate size for interactive areas (~44×44px when possible). Buttons, nav links.
- **visually-hidden:** Use for screen-reader-only text (skip link, nav toggle label).

Full criteria in `19-accessibility-standards`.

---

## 7. File responsibilities

| File | Contains |
|------|----------|
| tokens.css | :root variables only. No selectors. |
| base.css | *, html, body, h1–h6, p, a, lists, minimal reset. |
| layout.css | .container, .header, .nav, .footer, .main-content, grid. |
| components.css | .btn, .card, .hero, .breadcrumbs, .toc, .metadata-box, forms. |
| pages.css | .front-page__*, .archive-*, .single-issue__*, .single-article__*, .editorial-section. |
| utilities.css | .flow, .gap-*, .text-*, .flex, .visually-hidden, .focus-ring. |

---

## 8. Do-not rules

- Do not add styles in `style.css` (header only).
- Do not introduce CSS frameworks without explicit project decision.
- Do not use `!important` to fix conflicts; fix specificity or load order.
- Do not hardcode colors or font sizes that exist in 02 or tokens; use variables.
- Do not add new color variables without updating 02 and tokens.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
