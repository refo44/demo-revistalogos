# {{PROJECT_NAME}} — Implementation Order

**Agreed sequence to bring the site to the web.** **Do not skip stages.**  
**Version 1.0**

**Depends on:** All prior docs (02–16, 18–20)

---

## Phase 1: Documentation and design

1. **Complete identity:** Extract palette and typography from brand manual → update `02-corporate-identity`
2. **Wireframes:** Block structure per screen per `06-wireframes` (paper, Figma or HTML)
3. **Validate documentation:** Ensure all docs are aligned
4. **Consult UX/UI trends:** `18-ux-ui-trends` as filter for design decisions

---

## Phase 2: Static maquette

1. **Responsive maquette** with:
   - Semantic HTML5
   - CSS3 (identity tokens, semantic roles)
   - Minimal JS with `defer` (navigation, forms, accessibility)
2. Content per: `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`, `02-corporate-identity`
3. Assets from `content-source/` copied to `assets/`; images optimized
4. **Validate** against `18-ux-ui-trends` checklist before closing phase
5. **Validate responsive:** Mobile, tablet, desktop before WordPress

### 2.1 HTML structure (base for WordPress)

Maquette must use same route structure as final site. WordPress phase = direct HTML-to-PHP adaptation, no redesign.

```
/index.html
/{{route_1}}/index.html
/{{route_2}}/index.html
…
/404.html
/assets/
```

### 2.2 Future WordPress correspondence

| Static HTML | WordPress |
|-------------|-----------|
| `/index.html` | `front-page.php` |
| `{{route}}/index.html` | `page-{{slug}}.php` or `archive-{{cpt}}.php` |
| `/404.html` | `404.php` |

### 2.3 Design invariants

During WordPress migration, do **not** change:

- Block structure
- Visual hierarchy
- Editorial copy
- Identity tokens
- CSS architecture

WordPress adds: content engine, admin, dynamic content. It does not redesign.

---

## Phase 3: WordPress

1. **Convert** maquette to WordPress theme per `12-theme-file-structure`
2. Align with `03-wordpress-content-model` and `11-url-tree`; assets in theme per `15-assets-strategy`
3. Implement CPTs if required
4. **Deploy:** Staging (optional) and production; configure content and hosting

---

## Page priority

1. **Home** — Hero, main message, primary CTA
2. **Contact** — Form, social links
3. **{{PAGE_2}}** — {{PRIORITY_REASON}}
4. …

---

## Rule

Do not write WordPress theme code or deploy until the static maquette is validated.

---

## Pre-launch checklist

- [ ] Identity (palette, typography) defined
- [ ] All pages maquetted
- [ ] Contact form functional
- [ ] External links verified
- [ ] Accessibility: standards 19 applied (contrast, alt, keyboard, focus, forms)

---

**Version:** 1.0  
**Project:** {{PROJECT_NAME}}
