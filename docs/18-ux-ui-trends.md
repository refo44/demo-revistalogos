# {{PROJECT_NAME}} — UX/UI Trends and Editorial System

**Strategic filter for design and implementation validation**

What to adopt and what to avoid: reading, clarity, accessibility, performance vs. personalization, noise, invasiveness.

**Reference:** `02-corporate-identity`, `14-css-architecture`, `19-accessibility-standards`

---

## 1. Adoption rule

**Adopt** what improves:

- Reading and legibility
- Clarity
- Accessibility
- Performance

**Avoid** what introduces:

- Unnecessary personalization
- Visual noise
- Invasive behavior

---

## 2. Design philosophy

| Principle | Meaning |
|-----------|---------|
| Minimalism as structure | Not aesthetic only; structural clarity. |
| Typography first | Identity is typography + color. Images support, do not dominate. |
| Calm over impact | Experience over visual punch. |
| Single focus | One clear primary action per context. |

---

## 3. Adoption table

| Adopt | Avoid |
|-------|-------|
| Design tokens | Hardcoded colors/sizes |
| Semantic HTML | Div soup |
| Focus visible | `outline: none` without replacement |
| `prefers-reduced-motion` | Aggressive animations |
| Performance-first | Heavy frameworks by default |
| Clear hierarchy | Dense grids, competing elements |
| Micro-interactions (functional) | Motion for show |

---

## 4. Optional (evaluate per project)

- Dark mode
- Organic shapes (minimal doses)
- Subtle transitions

---

## 5. Avoid as base

- Agentic experiences
- Popups / smart triggers
- Motion "for show"
- Recommendation blocks that feel like feeds
- Carousels without clear purpose

---

## 6. Checklist for maquette

Before closing Phase 2:

- [ ] Tokens from 02 applied
- [ ] No hardcoded colors
- [ ] Focus visible on all interactives
- [ ] Contrast AA verified
- [ ] Reading width 60–70ch
- [ ] No unnecessary animation
- [ ] Semantic landmarks (header, main, footer, nav)

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
