# Revista de Filosofía LOGO ET SPES — Implementation Order

**Agreed sequence to bring the site to the web.** **Do not skip stages.**  
**Version 1.0**

**Depends on:** All prior docs (02–16, 18–20)

---

## Phase 1: Documentation and design — DONE

1. **Complete identity:** Extract palette and typography from brand manual → update `02-corporate-identity`
2. **Wireframes:** Block structure per screen per `06-wireframes` (paper, Figma or HTML)
3. **Validate documentation:** Ensure all docs are aligned
4. **Consult UX/UI trends:** `18-ux-ui-trends` as filter for design decisions

---

## Phase 2: Static maquette — DONE

1. **Responsive maquette** with:
   - Semantic HTML5
   - CSS3 (identity tokens, semantic roles)
   - Minimal JS with `defer` (navigation, forms, accessibility)
2. Content per: `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`, `02-corporate-identity`
3. Assets from `content-source/` copied to `assets/`; images optimized
4. **Validate** against `18-ux-ui-trends` checklist before closing phase
5. **Validate responsive:** Mobile, tablet, desktop before WordPress

### 2.1 HTML structure (base for WordPress)

Maquette uses flat HTML at root. WordPress phase = direct HTML-to-PHP adaptation, no redesign. Final URLs per `11-url-tree`.

```
index.html
page-acerca.html
page-contacto.html
page-normas.html
page-etica.html
page-politicas.html
page-enviar-colaboracion.html
page-comite.html
page-enlaces.html
blog-index.html
archive-issue.html
archive-article.html
single-issue.html
single-article.html
single-post.html
search.html          (to build)
404.html             (to build)
partials/
assets/
```

### 2.2 WordPress correspondence

| Static HTML | WordPress | URL |
|-------------|-----------|-----|
| `index.html` | `front-page.php` | `/` |
| `page-acerca.html` | `page-acerca.php` | `/acerca/` |
| `page-contacto.html` | `page-contacto.php` | `/contacto/` |
| `page-normas.html` | `page-normas.php` | `/normas/` |
| `page-etica.html` | `page-etica.php` | `/etica/` |
| `page-politicas.html` | `page-politicas.php` | `/politicas/` |
| `page-enviar-colaboracion.html` | `page-enviar-colaboracion.php` | `/enviar-colaboracion/` |
| `page-comite.html` | `page-comite.php` | `/comite/` |
| `page-enlaces.html` | `page-enlaces.php` | `/enlaces/` |
| `blog-index.html` | `home.php` | `/noticias/` |
| `archive-issue.html` | `archive-issue.php` | `/numeros/` |
| `archive-article.html` | `archive-article.php` | `/articulos/` |
| `single-issue.html` | `single-issue.php` | `/numeros/{slug}/` |
| `single-article.html` | `single-article.php` | `/articulos/{slug}/` |
| `single-post.html` | `single.php` | `/noticias/{slug}/` |
| `search.html` | `search.php` | `/?s={query}` |
| `404.html` | `404.php` | 404 |

### 2.3 Design invariants

During WordPress migration, do **not** change:

- Block structure
- Visual hierarchy
- Editorial copy
- Identity tokens
- CSS architecture

WordPress adds: content engine, admin, dynamic content. It does not redesign.

---

## Phase 3: WordPress — NEXT

1. **Convert** maquette to WordPress theme per `12-theme-file-structure`
2. Align with `03-wordpress-content-model` and `11-url-tree`; assets in theme per `15-assets-strategy`
3. Implement CPTs: `issue`, `article` (and `post` for noticias)
4. **Deploy:** Staging (optional) and production; configure content and hosting

---

## Page priority

1. **Home** — Hero, main message, primary CTA
2. **Contacto** — Form, social links
3. **Enviar colaboración** — Primary CTA for authors
4. **Normas** — Publication norms, PDFs
5. **Acerca** — Enfoque, alcance, objetivos
6. **Comité Editorial** — Editorial board
7. **Ética** — Ethics norms
8. **Políticas** — Editorial policies
9. **Enlaces** — External links
10. **Noticias** — Blog index

---

## Rule

Static maquette is validated. Proceed to WordPress theme development and deployment.

---

## Pre-launch checklist

- [x] Identity (palette, typography) defined
- [x] All pages maquetted
- [ ] Contact form functional (WordPress)
- [ ] External links verified
- [ ] Accessibility: standards 19 applied (contrast, alt, keyboard, focus, forms)
- [ ] WordPress theme deployed
- [ ] Content migrated / configured

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
