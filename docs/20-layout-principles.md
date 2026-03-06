# Revista de Filosofía LOGO ET SPES — Layout Principles

Closes the visual system: reading width, vertical rhythm, whitespace, typography/image relationship, grid and responsive behavior. Applies to static maquette and WordPress.

**Depends on:** `02-corporate-identity`, `06-wireframes`, `14-css-architecture`  
**Reference:** `17-implementation-order`, `18-ux-ui-trends`, `19-accessibility-standards`

---

## 1. Reading width

- **Goal:** 60–70 characters per line (`ch`) in body text. Target: ~65ch (per `02-corporate-identity`).
- **Implementation:** Content container with `max-width` in `ch` (e.g. `65ch`). Article body, norms, editorial content.
- **General container:** Text lives in a centered container. No edge-to-edge text. Hero may use full width; reading does not.
- **Rule:** No endless lines; the site is legible and calm, not dense.

---

## 2. Vertical rhythm

- **Consistency:** Margins and padding follow the spacing scale (`--space-1` … `--space-24` in `tokens.css`).
- **Breathing:** Generous spacing between blocks; content is not "stuck".
- **Hierarchy:** More space before/after H1/hero than between paragraphs.
- **Rule:** Single spacing system in `layout.css` and `components.css`; no arbitrary values per page.
- **Cross-page:** Rhythm consistent across all pages.

---

## 3. Use of whitespace

- **Active white:** Empty space is part of the design. Do not fill for filling.
- **Grouping:** Whitespace separates logical groups (header / hero / section / footer).
- **Contrast:** Dense zones (text, lists) balanced with open zones (hero, between sections).
- **Rule:** Do not sacrifice whitespace to "fit more content".

---

## 4. Typography / image relationship

- **Typography first:** Identity is typography + color. Images support, do not dominate.
- **Avoid competition:** No blocks where image and text compete for prominence.
- **Proportion:** When text and image share a block, define clear proportion (50/50, 2/3–1/3) per `06-wireframes`.
- **Text on image:** Ensure contrast (overlay, shadow or solid zone). Criteria in `19-accessibility-standards`.
- **Alt and context:** Every image with meaningful alt; relationship is semantic too.

---

## 5. Grid system

- **Base:** Site organized on a simple grid aligning main blocks.
- **Function:** Horizontal coherence between header, content and footer.
- **Center:** Content in a centered, aligned container (`--container-max-width: 1200px`, `--container-padding`).
- **Reading width:** 60–70ch lives inside that container.
- **Proportions:** Stable when combining text/image (1/2–1/2, 2/3–1/3).
- **Rule:** No different grids per page. One system for the whole site.
- **Responsive:** Grid simplifies on mobile (single column); rhythm and legibility preserved.

---

## 6. Responsive behavior

- **Key rule:** Responsive does not mean redesign; it means the same experience with less width.
- **Structure:** Multi-column → single column.
- **Reading width:** Stops at `ch`; becomes fluid with comfortable margins.
- **Vertical rhythm:** Maintained or increased for touch.
- **Block order:** Same as wireframes; most important first.
- **Images:** Adapt to container width without cropping essential information.
- **Interactives:** Easy to tap; sufficient space around (~44×44px when possible).
- **Content:** Not hidden on mobile. Same territory, narrower.

---

## 7. Editorial blocks

| Block | Layout principle |
|-------|------------------|
| Article body | Single column, 65ch max-width, centered |
| Issue hero | Full-width or contained; cover + meta side-by-side on desktop |
| Issue/Article cards | Grid or list; consistent card height; clear metadata |
| Metadata box | Compact; readable; not competing with primary content |
| Breadcrumbs | Inline; minimal space above content |

---

## 8. Summary

| Principle | Short rule |
|-----------|------------|
| Reading width | 60–70ch; centered container; hero may use full width |
| Vertical rhythm | Single scale (`--space-*`); generous; consistent across pages |
| Whitespace | Active; narrative pause; do not fill |
| Typography / image | Typography first; no competition; no drama |
| Grid | One system; container → reading; no grid per page |
| Responsive | Simplify, do not redesign; same territory, less width |

### Invariants

These principles:

- Are validated in the static maquette
- Are maintained in WordPress
- Are not modified per page or content

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
