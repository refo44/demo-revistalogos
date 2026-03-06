# Revista de Filosofía LOGO ET SPES — Information Architecture and Navigation

**Navigation map and live links**  
**Version 1.0**

This document defines what links leave each screen, where they go, in what order, and which must not exist. It does not describe design or layout. It connects the system with the real visitor experience.

**Reference:** `04-screen-map`, `06-wireframes`, `10-user-journey`, `09-ui-copy-sheet`

---

## 1. Structural principles

| Link type | Function |
|-----------|----------|
| **Primary** | Continue reading or enter main content. One clear focus per context. |
| **Secondary** | Context or ordered exit from current flow. |
| **Forbidden** | Noise that breaks the journey. |

**Central rule:** Each screen must offer a next step or a clear exit. Never a maze.

---

## 2. Global navigation

**Quantity rule:** Menu of 4–6 items recommended. Current implementation has 8; consider grouping (e.g. Revista submenu) to reduce cognitive load.

### Header

| Link | Destination |
|------|-------------|
| Inicio | Home |
| Número Actual | Current issue (single-issue) |
| Números Publicados | Archive issues |
| Enviar colaboración | Submit page (primary CTA) |
| Acerca | About page |
| Noticias | Blog index |
| Contacto | Contact page |
| Enlaces | Links page |
| CENFISS | cenfiss.net (external) |

### Footer

| Link | Destination |
|------|-------------|
| Número Actual | Current issue |
| Archivos | Archive issues |
| Artículos | Archive articles |
| Enviar Colaboración | Submit page |
| Búsqueda | Search |
| Normas de Publicación | Normas page |
| Ética Editorial | Ética page |
| Políticas | Políticas page |
| Comité Editorial | Comité page |
| Contacto | Contact, CENFISS web, email |
| Creative Commons | CC license (external) |

---

## 3. Per-screen links

| Screen | Primary link(s) | Secondary link(s) |
|--------|-----------------|-------------------|
| Home | Ver número actual | Enviar colaboración, Normas, Acerca |
| Número Actual (single-issue) | PDF download, article links in TOC | Archive issues, Inicio (breadcrumb) |
| Números Publicados | Each issue card → single-issue | Inicio, Enviar colaboración |
| Single article | PDF, Read full text | Parent issue, Archive articles, Inicio (breadcrumb) |
| Archive articles | Each article card → single-article | Filter by section/type, Inicio |
| Enviar colaboración | mailto, PDF form, Normas, Políticas | Comité, Contacto |
| Normas | PDF downloads, APA/Vancouver links | Políticas, Enviar colaboración |
| Políticas | — | Normas, Enviar colaboración |
| Acerca | — | Enviar colaboración, Contacto |
| Contacto | mailto, CENFISS web | Enviar colaboración |
| Comité | — | Enviar colaboración, Normas |
| Enlaces | External links (CENFISS, partners) | Contacto |
| Noticias | Each post → single post | Inicio, Enviar colaboración |
| Single post | — | Noticias, Inicio |

---

## 4. States

### Empty / no results

| Link | Destination |
|------|-------------|
| Ir a Inicio | Home |
| Ver todos los números | Archive issues |
| Ver todos los artículos | Archive articles |

### 404

| Link | Destination |
|------|-------------|
| Volver al inicio | Home |
| Ver número actual | Single issue |

**Never** leave a screen without an exit.

---

## 5. Breadcrumb rules

- **Single issue:** Inicio → Archivos → [Issue title]
- **Single article:** Inicio → Archivos → [Issue] → [Article title]
- **Static pages:** Inicio → [Page title]

Breadcrumbs provide secondary exit; always link to Inicio.

---

## 6. Final rule

If a link does not push toward:

- The main content (read)
- The main action (submit)
- Contact or next step

**it does not exist.**

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
