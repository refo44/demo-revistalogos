# {{PROJECT_NAME}} — CSS Architecture

**Layers, tokens and style implementation criteria**

Defines how site CSS is organized: relationship between theme.json, variables and main.css, specificity and naming, accessibility in styles.

**Depends on:** `02-corporate-identity`, `12-theme-file-structure`  
**Reference:** `15-assets-strategy`, `19-accessibility-standards`, `20-layout-principles`

---

## 1. Style system layers

| Layer | Source | Role |
|-------|--------|------|
| Theme metadata | `style.css` | Header only (Theme Name, etc.). No CSS rules. |
| Design tokens | `theme.json` | Palette, typography, spacing, block presets. |
| Variables and roles | `assets/css/main.css` | CSS variables (`--brand-*`, `--text`, `--link`, etc.). Semantic roles. |
| Implementation | `assets/css/main.css` | Layout, components, reading width, spacing. |

No frameworks (Tailwind, Bootstrap). Single entry point. Optional partials if main.css grows.

---

## 2. main.css structure

Suggested order:

1. **Variables (:root)** — Tokens from 02
2. **Base / minimal reset** — Box-sizing, base typography, links
3. **Layout** — Containers, grid, reading width (60–70ch)
4. **Components** — Header, footer, nav, buttons, forms, cards
5. **Page-specific (if needed)** — Minimal; prefer reusable components
6. **Utilities (optional)** — visually-hidden, spacing helpers
7. **States and accessibility** — `:focus-visible`, hover, `prefers-reduced-motion`

---

## 3. Naming and specificity

- **Naming:** Semantic and stable. Prefer function/component names (`.site-header`, `.card`) over appearance.
- **Specificity:** Keep low. Avoid deep nesting and `!important`.
- **IDs:** Do not use for styles; reserve for anchors and ARIA.
- **Inline styles:** Do not style by `[style]`; no inline in editorial content.

---

## 4. theme.json and main.css

- **theme.json** defines colors, typography, spacing for WordPress editor.
- **main.css** uses same decisions via `:root` variables and adds layout/components.
- Components never use raw hex; they consume semantic roles via `var(--text)`, `var(--link)`, etc.

---

## 5. Accessibility in CSS

- **Contrast:** Text and controls must meet WCAG 2.1 AA. Verify combinations from 02.
- **Focus:** Visible style for `:focus-visible` on links, buttons, form controls. No `outline: none` without replacement.
- **Reduced motion:** Respect `prefers-reduced-motion`; reduce or remove animations.
- **Tap/click size:** Adequate size for interactive areas (~44×44px when possible).

Full criteria in `19-accessibility-standards`.

---

## 6. Do-not rules

- Do not add styles in `style.css` (header only).
- Do not introduce CSS frameworks without explicit project decision.
- Do not use `!important` to fix conflicts; fix specificity or load order.
- Do not hardcode colors or font sizes that exist in 02 or theme.json; use variables.

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
