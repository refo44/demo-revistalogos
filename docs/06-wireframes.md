# Revista de Filosofía LOGO ET SPES — Wireframes

**Version 1.0**

Screen structure: hierarchy, blocks and reading flow per view. Not visual design; supports screen map and navigation.

**Depends on:** `04-screen-map`, `05-information-architecture-navigation`  
**Reference:** `02-corporate-identity`, `20-layout-principles`

---

## Purpose

Wireframes define:

- Block hierarchy per screen
- Reading flow
- Component placement
- Responsive behavior (mobile → desktop)

They do **not** define colors, typography or final visual design. Those come from `02-corporate-identity`.

---

## Global structure (all pages)

| Block | Order | Content / function |
|-------|-------|---------------------|
| Skip link | 0 | Skip to main content (accessibility) |
| Header | 1 | Logo, main nav |
| Main | 2 | Page-specific content |
| Footer | 3 | Revista info, enlaces rápidos, normas, contacto, CC |

---

## Per-screen structure

### Home

| Block | Order | Content / function |
|-------|-------|---------------------|
| Hero | 1 | Title (LOGO ET SPES), subtitle, short description, primary CTA (Ver número actual) |
| Banner carousel | 2 | Optional. Events, CENFISS. Slides with image, title, CTA. |
| Sidebar grid | 3 | Noticias (list + link), Colaboración (info), CENFISS (link). Cards. |

**Note:** Sections by discipline (Metafísica, Ética, etc.) are optional; may be hidden or shown.

---

### Single issue

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Archivos → [Issue title] |
| Issue header | 2 | Cover image, title (Vol. X Nº Y), meta (ISSN, DOI, date), description, actions (PDF download, Ver PDF) |
| Table of contents | 3 | Sections (Metafísica, Ética, etc.). Per section: article links with authors, pages, DOI. |

---

### Single article

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Archivos → [Issue] → [Article title] |
| Article header | 2 | Title (ES/EN), authors, meta (volume, pages, DOI, dates) |
| Metadata box | 3 | Authors, affiliations, ORCID, DOI, pages, section, keywords, dates, suggested citation |
| Article content | 4 | Resumen, Abstract, Palabras clave, body (sections), referencias |
| Article actions | 5 | PDF download, Leer más (if excerpt) |

---

### Archive issues

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Archivos |
| Archive header | 2 | Title, description |
| Issue grid | 3 | Cards: cover, volume, title, meta, description, stats, PDF + Ver contenido |

---

### Archive articles

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Artículos |
| Archive header | 2 | Title, description |
| Filters | 3 | Search (title, author, keywords), section select, year select, Buscar, Limpiar |
| Article grid | 4 | Cards: title, subtitle (EN), authors, meta (DOI, pages, section, year), abstract, keywords, PDF + Leer más |

---

### Enviar colaboración

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Enviar Colaboración |
| Page header | 2 | Title, description |
| Content main | 3 | Numbered instructions (login/register, normas, form, email). Warning box. |
| Sidebar | 4 | Enlaces útiles: Normas, Políticas, Comité, Contacto |

---

### Normas, Políticas, Ética

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → [Page title] |
| Page header | 2 | Title, description |
| Content | 3 | Editorial sections (H2, H3), PDF download buttons, links to APA/Vancouver |
| Sidebar | 4 | Optional. Quick links, related pages. |

---

### Acerca

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Acerca |
| Page header | 2 | Title, description |
| Content main | 3 | Reseña, Línea Editorial (Enfoque, Objetivos, Principios, Valores, Criterios), Fundamento Normativo |
| Sidebar | 4 | Información de la Revista (ISSN, etc.), links |

---

### Contacto

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Contacto |
| Page header | 2 | Title, description |
| Content | 3 | CENFISS info, address, email, web. Optional: contact form. |

---

### Comité Editorial

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Comité Editorial |
| Page header | 2 | Title, description |
| Content | 3 | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. List or cards. |

---

### Enlaces

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Enlaces |
| Page header | 2 | Title, description |
| Content | 3 | List of external links (CENFISS, partners, resources). |

---

### Noticias (blog index)

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Noticias |
| Archive header | 2 | Title, description |
| Post list | 3 | Cards: date, author, title, excerpt, Leer más |

---

### Single post (noticia)

| Block | Order | Content / function |
|-------|-------|---------------------|
| Breadcrumbs | 1 | Inicio → Noticias |
| Post header | 2 | Date, author, title |
| Post content | 3 | Full body |
| Post footer | 4 | Optional: related posts, back to Noticias |

---

### Search results

| Block | Order | Content / function |
|-------|-------|---------------------|
| Search header | 1 | Query, result count |
| Results list | 2 | Articles/issues/posts matching query. Same card pattern as archives. |
| Empty state | 3 | If no results: message, link to Inicio, browse links |

---

### 404

| Block | Order | Content / function |
|-------|-------|---------------------|
| Message | 1 | "Página no encontrada" or similar |
| Actions | 2 | Link to Inicio, link to Número actual |

---

## Responsive rules

- **Mobile:** Single column; blocks stack vertically; same order as desktop. Nav collapses to toggle. Sidebar moves below main content.
- **Tablet:** Same as mobile; may allow 2-column grid for cards where space permits.
- **Desktop:** Main + sidebar layout where applicable (Acerca, Enviar colaboración, Normas). Card grids: 2–3 columns.

**Rule:** Responsive does not redesign; it adapts proportions. Same content, less width.

---

## Component inventory

| Component | Use |
|-----------|-----|
| Hero | Home. Title, subtitle, CTA. |
| Breadcrumbs | All content pages. Inicio → path. |
| Archive header | Archives. Title, description. |
| Issue card | Archive issues, Home. Cover, title, meta, PDF, Ver contenido. |
| Article card | Archive articles, single-issue TOC. Title, authors, abstract, PDF, Leer más. |
| Metadata box | Single article. Structured key-value list. |
| Sidebar card | Acerca, Enviar colaboración, Normas. Related links, info. |
| Editorial section | Static pages. H2/H3, paragraphs, lists. |
| Pagination | Archives, search. Prev/next, page numbers. |

---

## Deliverable

- Paper sketches, or
- Figma / HTML wireframes
- Per `04-screen-map` screens

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
