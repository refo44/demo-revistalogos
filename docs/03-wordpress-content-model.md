# Revista de Filosofía LOGO ET SPES — WordPress Content Model

**Version 1.0**

Official content model for the WordPress implementation. Based on platform plan and content sources.

**Depends on:** `01-platform-plan`, `02-corporate-identity`, `04-screen-map`  
**Reference:** `09-ui-copy-sheet`, `11-url-tree`, `12-theme-file-structure`

---

## 1. General schema

### Post types

| Key | Label | Type | Slug | Main use |
|-----|-------|------|------|----------|
| page | Pages | Native | per page | Static institutional and editorial pages |
| post | Posts | Native | noticias | Noticias, CENFISS presente, misceláneas |
| issue | Números | Custom | numeros | Journal volumes (annual issues) |
| article | Artículos | Custom | articulos | Articles, essays, reviews (peer-reviewed content) |

### Fixed vs. dynamic content

| Type | Management |
|------|------------|
| Static pages | page |
| Issues (números) | issue (CPT) |
| Articles, essays, reviews | article (CPT) |
| Noticias, CENFISS presente | post |

---

## 2. Fields per page

### Home (front-page)

- Hero: title, subtitle, short description, primary CTA (link to current issue)
- Block: Current issue card (title, cover, link, PDF)
- Block: Optional banner carousel (events, CENFISS)
- Block: Quick links (Normas, Enviar colaboración)

### acerca (page)

- Title, content (enfoque, alcance, objetivos, origen del nombre Logo et Spes)
- Optional: Editorial board summary

### contacto (page)

- Title, content
- Contact info: email (revista.cenfiss@gmail.com), CENFISS web, address
- Optional: Contact form (or mailto link)

### normas (page)

- Title, content (normas de publicación)
- PDF download: Solicitud de Publicación y Declaración de Ética
- PDF download: Instrumento de Arbitraje
- Links to APA, Vancouver guides

### politicas (page)

- Title, content (políticas editoriales)

### enviar-colaboracion (page)

- Title, content (instructions for authors)
- PDF download: Solicitud de Publicación
- Links to normas, politicas

### comite (page)

- Title, content (Consejo Editorial, Editor General, Editores adjuntos, Árbitros)

### enlaces (page)

- Title, content (enlaces de interés, CENFISS, partners)

---

## 3. Custom Post Types

### issue

| Field | Type | Use |
|-------|------|-----|
| Title | Native | Volume title (e.g. "Filosofía Contemporánea: Nuevas Perspectivas") |
| Content | Native | Editorial text |
| Featured image | Native | Cover image |
| volume_number | number | Vol. 12 |
| issue_number | number | Nº 2 |
| year | number | 2025 |
| date_published | date | Publication date |
| issn | text | ISSN (when available) |
| doi | text | DOI prefix/suffix |
| pdf_url | url | Full issue PDF |
| article_count | number | Optional, for display |

**Relations:** Articles are linked to an issue via post meta or taxonomy.

### article

| Field | Type | Use |
|-------|------|-----|
| Title | Native | Spanish title |
| Content | Native | Full body (or excerpt + PDF link) |
| title_en | text | English title (required per norms) |
| abstract | textarea | Spanish abstract (max 250 words) |
| abstract_en | textarea | English abstract |
| keywords | text | 3–5 keywords, comma-separated |
| article_type | select | article \| essay \| review |
| authors | repeater/relation | Author names, ORCID, affiliation, bio |
| doi | text | Article DOI |
| pages | text | Pagination (e.g. "15-32") |
| pdf_url | url | Article PDF |
| issue | relation | Parent issue |
| section | taxonomy | Metafísica, Ética, Epistemología, etc. |

**Priority to native fields:** Use Title, Content, Featured image when possible. Custom fields complement.

---

## 4. Taxonomies

| Key | Label | Type | Applies to |
|-----|-------|------|------------|
| section | Sección | Hierarchical | article |
| article_type | Tipo | Non-hierarchical | article |

**Section values (examples):** Metafísica, Ética, Epistemología, Filosofía de la Religión, Filosofía Política, Lógica, Historia de la Filosofía, Otros.

---

## 5. Minimum templates

- `front-page.php`: Home
- `page-acerca.php`, `page-contacto.php`, `page-normas.php`, `page-politicas.php`, `page-enviar-colaboracion.php`, `page-comite.php`, `page-enlaces.php`
- `archive-issue.php`: List of numbers
- `single-issue.php`: Single issue
- `archive-article.php`: List of articles (filterable by section, type)
- `single-article.php`: Single article
- `home.php` or `index.php`: Noticias (blog index)
- `single.php`: Single post (noticia)
- `page.php`: Fallback for other pages
- `404.php`: Not found

---

## 6. External integrations

| Resource | Implementation |
|----------|----------------|
| Email (revista.cenfiss@gmail.com) | mailto links or contact form plugin |
| PDF forms | Media library; links from normas, enviar-colaboracion |
| CENFISS site (cenfiss.net) | External links in footer, enlaces |
| DOI resolver | Optional: link to doi.org for article DOIs |

---

## 7. Content accessibility requirements

Without replacing `19-accessibility-standards`, this model requires:

- **Images:** Always fill alt text. Decorative: `alt=""`.
- **Informative video:** Subtitles or transcript when content is informative.
- **Editorial structure:** Each page has a unique H1 and clear H2/H3 hierarchy.

---

## 8. Guiding principle

Everything in this model exists for: **diffusing and divulging philosophical thought through an academic, open-access publication**. No marketing layers or funnels. Only clarity and purpose.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
