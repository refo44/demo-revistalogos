# {{PROJECT_NAME}} — Corporate Identity

**Version 1.0**

This document defines the visual and typographic identity system for the site. Values come strictly from the brand manual.

**Canonical source:** `content-source/{{PROJECT_FOLDER}}/{{BRAND_MANUAL_FILENAME}}`

---

## 1. Color palette

Only the colors defined in the brand manual exist. These constitute the chromatic core. Do not add additional primary colors.

| Name | Hex | RGB |
|------|-----|-----|
| {{COLOR_1_NAME}} | {{COLOR_1_HEX}} | R: {{R}}, G: {{G}}, B: {{B}} |
| {{COLOR_2_NAME}} | {{COLOR_2_HEX}} | … |
| … | … | … |

### 1.1 Semantic order (for implementation)

| Token | Hex | Suggested use |
|-------|-----|---------------|
| `--brand-1` | {{HEX}} | Primary accent, links, CTAs |
| `--brand-2` | {{HEX}} | Secondary accent, hover |
| `--brand-3` | {{HEX}} | Surfaces, soft backgrounds, borders |
| `--brand-4` | {{HEX}} | Text, header, footer |

### 1.2 Semantic roles (CSS)

```css
:root {
  --brand-1: {{HEX}};   /* Primary accent */
  --brand-2: {{HEX}};   /* Secondary accent */
  --brand-3: {{HEX}};   /* Surfaces, borders */
  --brand-4: {{HEX}};   /* Text */

  --bg: var(--brand-3);
  --text: var(--brand-4);
  --text-muted: var(--brand-2);
  --surface: var(--brand-3);
  --border: var(--brand-3);
  --link: var(--brand-1);
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
| **{{FONT_DISPLAY}}** | Titles, brand voice, highlights |
| **{{FONT_BODY}}** | Body text, navigation |

### 2.1 CSS variables

```css
:root {
  --font-display: "{{FONT_DISPLAY}}", serif;
  --font-heading: "{{FONT_HEADING}}", serif;
  --font-body: "{{FONT_BODY}}", system-ui, sans-serif;
}
```

---

## 3. Logo

- **File:** `content-source/{{PROJECT_FOLDER}}/{{LOGO_FILENAME}}`
- **Favicon:** Derive from logo.
- **Use:** Header, favicon, digital materials.
- **Rule:** Do not alter proportions, color or composition.

---

## 4. Brand essence

| Trait | Description |
|------|-------------|
| Voice | {{VOICE_TRAIT}} |
| Aesthetic | {{AESTHETIC_TRAIT}} |
| Rhythm | {{RHYTHM_TRAIT}} |
| Function | {{FUNCTION_TRAIT}} |

---

## 5. Editorial rhythm

### Body text

- Measure: ~65ch (human reading width)
- Line height: 1.5–1.6
- Space: generous margins, breathing room

### Navigation and UI

- Discrete, without dominating content
- No aggressive CTAs

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

Nothing visual is decided outside this system. Brand manual colors and fonts are the source of truth. In case of conflict, the brand manual prevails.

---

**Version:** 1.0  
**Source:** {{BRAND_MANUAL_FILENAME}}  
**Project:** {{PROJECT_NAME}}
