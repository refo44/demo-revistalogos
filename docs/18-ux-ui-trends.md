# Revista de Filosofía LOGO ET SPES — UX/UI Trends and Editorial System

**Strategic filter for design and implementation validation**

What to adopt and what to avoid: reading, clarity, accessibility, performance vs. personalization, noise, invasiveness.

**Reference:** `02-corporate-identity`, `14-css-architecture`, `19-accessibility-standards`

---

## 1. Adoption rule

**Adopt** what improves:

- Reading and legibility (primary: long-form articles, PDFs, norms)
- Clarity (institutional, academic)
- Accessibility (WCAG 2.1 AA minimum)
- Performance (fast load, no heavy frameworks)

**Avoid** what introduces:

- Unnecessary personalization (no dashboards, no "for you")
- Visual noise (no feeds, no recommendation blocks)
- Invasive behavior (no popups, no smart triggers)

---

## 2. Design philosophy

| Principle | Meaning |
|-----------|---------|
| Minimalism as structure | Not aesthetic only; structural clarity. Academic content is the focus. |
| Typography first | Identity is typography + color. Georgia for headings; system UI for body. Images support, do not dominate. |
| Calm over impact | Experience over visual punch. Institutional, trustworthy. |
| Single focus | One clear primary action per context: "Ver número actual", "Enviar colaboración". |

---

## 3. Adoption table

| Adopt | Avoid |
|-------|-------|
| Design tokens (`tokens.css`, `02-corporate-identity`) | Hardcoded colors/sizes |
| Semantic HTML | Div soup |
| Focus visible | `outline: none` without replacement |
| `prefers-reduced-motion` | Aggressive animations |
| Performance-first | Heavy frameworks by default |
| Clear hierarchy | Dense grids, competing elements |
| Micro-interactions (functional: nav toggle, skip link) | Motion for show |
| Reading width 60–70ch | Full-width text blocks |
| Generous whitespace | Cramped layouts |

---

## 4. Optional (evaluate per project)

| Option | Decision for LOGO ET SPES |
|--------|---------------------------|
| Dark mode | Not in scope. Institutional brand is light. |
| Organic shapes | Minimal doses only; prefer clean geometry. |
| Subtle transitions | Yes for functional feedback (hover, focus). Keep under 250ms. |

---

## 5. Avoid as base

- Agentic experiences (chatbots, AI assistants)
- Popups / smart triggers (newsletter modals, exit intent)
- Motion "for show" (parallax, scroll animations)
- Recommendation blocks that feel like feeds
- Carousels without clear purpose (if carousel: one clear CTA, no auto-play)
- Marketing language (see `07-voice-guide-microcopy-ux`)

---

## 6. Academic journal patterns

| Pattern | Use |
|---------|-----|
| Clear metadata | DOI, ISSN, volume, issue, year visible. |
| Citation-ready | Copy citation, download PDF. No "share" buttons as primary. |
| Linear reading | Vertical flow. No sidebars competing with content. |
| Norms visible | Link to Normas, Ética, Políticas from footer and Enviar colaboración. |
| Contact accessible | Editorial board, CENFISS. No buried contact info. |

---

## 7. Checklist for maquette

Before closing Phase 2 (or validating before WordPress):

- [ ] Tokens from `02-corporate-identity` applied (`tokens.css`)
- [ ] No hardcoded colors (use `--color-*`, `--brand-*`)
- [ ] Focus visible on all interactives (nav, links, buttons, form fields)
- [ ] Contrast AA verified (`--color-text-primary` on `--color-bg-primary`)
- [ ] Reading width 60–70ch for article body
- [ ] No unnecessary animation; respect `prefers-reduced-motion`
- [ ] Semantic landmarks (header, main, footer, nav)
- [ ] Skip link present and functional
- [ ] Voice aligned with `07-voice-guide-microcopy-ux` (no marketing copy)

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
