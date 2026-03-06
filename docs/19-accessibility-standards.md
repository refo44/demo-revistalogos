# {{PROJECT_NAME}} — Accessibility Standards

**Single document for accessibility standards**  
**Version 1.0**

Strategy, principles, design rules, semantic HTML, ARIA, editorial content, implementation checklist and testing. Aligned with WCAG 2.1/2.2 Level AA.

**Depends on:** `01-platform-plan`, `02-corporate-identity`, `17-implementation-order`, `18-ux-ui-trends`

---

## 1. Strategy

- **Commitment:** Anyone can read, orient and understand content without friction.
- **Reference:** WCAG 2.1 Level AA (minimum) or WCAG 2.2 Level AA (recommended).
- **Philosophy:** Accessibility as part of design and content, not an afterthought.

---

## 2. Principles (WCAG)

| Principle | Goal |
|-----------|------|
| **Perceptible** | Information and UI are presentable so people can perceive them. |
| **Operable** | UI is operable (keyboard, enough time, no seizures, navigable). |
| **Understandable** | Information and UI use are understandable. |
| **Robust** | Content is reliably interpretable by user agents and assistive tech. |

---

## 3. Users to consider

| Profile | Impact |
|---------|--------|
| Visual | Blindness, low vision, color blindness → semantics, labels, alt, contrast, zoom |
| Auditory | Deafness → captions, transcripts |
| Motor | No mouse, low precision → keyboard, large targets, tab order |
| Cognitive | Dyslexia, ADHD, slow processing → clear language, simple hierarchy |
| Neurological | Photosensitivity, migraines → no flashing, respect `prefers-reduced-motion` |

---

## 4. Design rules

- **Legible typography:** Comfortable sizes, clear hierarchy (H1–H3).
- **Contrast:** Minimum AA (4.5:1 normal text, 3:1 large text).
- **Targets:** Adequate clickable areas (~44×44px when possible).
- **Color:** Do not rely on color alone for meaning.
- **Language:** Clear and direct (aligned with voice guide).

### Minimum rules (high impact, low cost)

- **Skip link:** "Skip to content" visible on focus.
- **Language:** `lang` on `<html>`; consistent per page.
- **Focus order:** Logical, predictable tab order.
- **Focus visible:** No `outline: none` without replacement (`:focus-visible`).
- **Reflow and zoom:** Usable at 200% zoom and 320px width.
- **Landmarks:** One `<main>` per page; consistent header/nav/main/footer.
- **Headings:** No skipping (H1→H3); hierarchy follows content.
- **Links:** Distinguishable without color alone.
- **Forms:** Labels, autocomplete, clear error messages.

---

## 5. HTML and ARIA

### Base

1. **Semantic HTML first.** Use native elements (`button`, `a`, `input`, `nav`, `main`, etc.).
2. **ARIA only** when HTML is insufficient (dynamic UI, custom components).
3. **Do not use ARIA to fix bad markup.**

### When to use ARIA

- Accessible name when no visible text
- State (expanded, selected, pressed, invalid)
- Relationships (controls, labelledby, describedby)
- Live regions for dynamic announcements

### When NOT to use ARIA

- `div` with `role="button"` when a `button` works
- Redundant roles
- `aria-hidden` on focusable content
- `aria-label` when visible label exists
- `tabindex` positive (1, 2, …)

### Icon buttons

Provide accessible name: (1) visible text, (2) `.visually-hidden` text, (3) `aria-labelledby`, (4) `aria-label` as last resort. Decorative icons: `aria-hidden="true"`, `focusable="false"`.

---

## 6. Editorial content

- **Headings:** H1–H3 ordered; one H1 per page.
- **Alt text:** All informative images; descriptive and concise. Decorative: `alt=""`.
- **Links:** Descriptive text (avoid "here", "click", "more" without context).
- **External links:** Indicate "opens in new tab" when `target="_blank"`.
- **Media:** Captions or transcript for informative video/audio.

---

## 7. Implementation checklist

- [ ] Skip link visible on focus
- [ ] `lang` on `<html>`
- [ ] Full keyboard navigation; logical tab order
- [ ] Focus visible; no `outline: none` without replacement
- [ ] Usable at 200% zoom and 320px width
- [ ] Images with appropriate alt
- [ ] Forms with real labels; errors linked (aria-describedby, aria-invalid)
- [ ] No aggressive animation; respect `prefers-reduced-motion`
- [ ] No flashing content
- [ ] AA contrast on text, links, buttons
- [ ] Unique `<title>` per page; landmarks and headings correct

---

## 8. Testing

- **Keyboard:** Tab through all interactives; no focus traps.
- **Focus visible:** Clear on links, buttons, inputs.
- **Lighthouse:** No critical accessibility failures.
- **Forms:** Label associated, error visible and readable.
- **Screen reader (recommended):** Test key flows (nav, contact form).

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}  
**Standard:** WCAG 2.1 / 2.2 (W3C)
