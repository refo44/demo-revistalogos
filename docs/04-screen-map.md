# Revista de Filosofía LOGO ET SPES — Screen Map

List of what screens exist. Does not describe design; only what views to build. Single source for "what views to build". Content of each is in 03; theme structure in 12.

**Depends on / reference:** `01-platform-plan`, `03-wordpress-content-model`, `05-information-architecture-navigation`  
**Block structure per screen:** `06-wireframes`

---

## Fixed pages

| Page | Function |
|------|----------|
| Home | Hero, current issue, primary CTA. Entry point to read and submit. |
| Acerca | Enfoque, alcance, objetivos, origen del nombre Logo et Spes. |
| Contacto | Institutional contact, editorial board, CENFISS, email. |
| Normas | Normas de publicación, PDF downloads, links to APA/Vancouver. |
| Ética | Normas de ética editorial (COPE alignment). |
| Políticas | Políticas editoriales. |
| Enviar colaboración | Instructions for authors, forms, primary CTA for submission. |
| Comité Editorial | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. |
| Enlaces | Enlaces de interés, CENFISS, partners. |
| Noticias | Blog index. CENFISS presente, events, misceláneas. |

---

## Conditional pages

| Page | Condition |
|------|-----------|
| Search results | User submits search query. |
| Archive filtered | User filters by section/article_type (articles, issues). |

---

## Content views (single)

| View | Use |
|------|-----|
| Single issue | Single issue (número). Editorial, article list, PDF download. |
| Single article | Single article, essay or review. Full text or PDF, metadata, authors. |
| Single post | Single noticia. CENFISS presente, events. |

---

## Archive views

| View | Use |
|------|-----|
| Archive issues | List of all published numbers. |
| Archive articles | List of articles. Filter by section, type, issue. |
| Archive authors | List of authors (optional; may link from article metadata). |

---

## States

| State | Description |
|-------|-------------|
| Empty search | No results for query. Offer link to home or browse. |
| Empty archive | No issues/articles yet. Informational message. |
| 404 | Page does not exist. Link to home. |

---

## Global elements (components, not screens)

**Header:** Logo, main nav (Inicio, Número Actual, Números Publicados, Enviar colaboración, Acerca, Noticias, Contacto, Enlaces), CENFISS link.

**Footer:** Revista info (ISSN, DOI), Enlaces rápidos, Normas editoriales, Contacto, CC license.

Present on all pages.

---

## Total screens to build

**Main pages (static):** Home, Acerca, Contacto, Normas, Ética, Políticas, Enviar colaboración, Comité Editorial, Enlaces, Noticias

**Archives:** Archive issues, Archive articles

**Content views:** Single issue, Single article, Single post

**Conditional:** Search results, Filtered archives

**States:** 404, Empty search, Empty archive

**Global components:** Header, Footer

---

## WordPress template mapping

| View | Template |
|------|----------|
| Home | `front-page.php` |
| Acerca | `page-acerca.php` |
| Contacto | `page-contacto.php` |
| Normas | `page-normas.php` |
| Ética | `page-etica.php` |
| Políticas | `page-politicas.php` |
| Enviar colaboración | `page-enviar-colaboracion.php` |
| Comité Editorial | `page-comite.php` |
| Enlaces | `page-enlaces.php` |
| Noticias | `home.php` or `index.php` |
| Archive issues | `archive-issue.php` |
| Archive articles | `archive-article.php` |
| Single issue | `single-issue.php` |
| Single article | `single-article.php` |
| Single post | `single.php` |
| Search | `search.php` |
| 404 | `404.php` |
| Header | `parts/header.php` |
| Footer | `parts/footer.php` |

---

## What the site does NOT have

- User dashboard or account management
- E-commerce or payments
- Comments on articles
- Social feed or social login
- Advertising or sponsored content

Helps scope and avoid creep.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
