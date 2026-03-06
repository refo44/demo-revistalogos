# Revista de Filosofía LOGO ET SPES — Platform Master Plan

**Version 1.0**

The corporate identity defined in `02-corporate-identity` is the closed visual layer.

This document defines the system that allows Revista de Filosofía LOGO ET SPES to exist, be discovered, be traversed and endure as a living space.

This site is not a commercial funnel, a landing page, or a marketing-oriented publication.  
It is an academic digital publication platform for philosophical thought.

The problem this plan addresses is not aesthetic. It is structural and orientational:

- What is philosophical knowledge in this digital space?
- What is the main action or outcome?
- How does the visitor reach it without losing orientation?

**Canonical sources:**

- `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Project document (structure, policies, editorial norms)
- Add specific source files (brand manual, PDFs) as needed

---

## Operating principles

| Principle | Operational meaning |
|-----------|----------------------|
| **Open access** | All content is free. No cost to authors or readers. Knowledge is accessible to the academic community and the public. |
| **Academic rigor** | Double-blind peer review. Compliance with editorial policies, publication norms, and ethics. Antiplagiarism. |
| **Orientation** | Each screen offers a clear next step or exit. No maze. The visitor always knows how to reach the main action (read, submit, contact). |
| **Institutional anchor** | CENFISS identity is visible. Footer and key pages reinforce trust, ISSN, Depósito Legal, and contact. |

---

## The eight layers of the system

The site is designed as a system of independent, complementary layers. Each layer has a specific function.

---

### Layer 1. Identity

Defines how the site looks and feels.

Includes:

- Typography
- Color palette
- Visual rhythm
- Use of whitespace
- Editorial hierarchy
- Aesthetic consistency

**Status:** Closed. Defined in `02-corporate-identity`.

---

### Layer 2. Information architecture

Defines what exists within the system.

| Section | Function |
|---------|----------|
| Inicio | Home. Hero, current issue, quick access to main content. |
| Revista | Número actual, archivos, artículos, autores, comité editorial. |
| Normas | Normas de publicación, ética editorial, políticas, enviar colaboración. |
| Acerca | Enfoque, alcance, objetivos, origen del nombre Logo et Spes. |
| Contacto | Institutional contact, editorial board, CENFISS. |
| Noticias | CENFISS presente, events, misceláneas. |

---

### Layer 3. Content model

Defines what each entity is within the system.

| Type | Function |
|------|----------|
| Issue (número) | A volume of the journal. Contains editorial, articles, essays, misceláneas. |
| Article | Peer-reviewed research (6,500–8,000 words). Abstract, keywords, references. |
| Essay | Peer-reviewed argumentative text (5,000–7,500 words). |
| Review (reseña) | Critical review of a book, conference, film, etc. (500–700 words). |
| Editorial | Editorial board statement per issue. |
| Author | Author profile. Name, affiliation, ORCID, bio. |
| Misceláneas | Reseñas, CENFISS presente, número anterior. |

---

### Layer 4. Navigation grammar

Defines how the site is traversed.

Elements:

- Main menu (4–6 items recommended): Inicio, Revista, Normas, Acerca, Contacto, Noticias
- Primary CTA: Enviar Colaboración (for authors)
- Footer as institutional anchor: ISSN, Depósito Legal, CENFISS, Creative Commons, contact

**Rule:** The visitor always knows how to reach the main action or contact.

---

### Layer 5. Publication strategy

Defines the living rhythm of the site.

Allows:

- Annual issue publication (ordinary and eventual extraordinary numbers)
- Editorial updates (editorial, misceláneas, CENFISS presente)
- New article/essay/review submissions without development intervention
- Form downloads (Solicitud de Publicación, Instrumento de Arbitraje)

**Technical principle:** The system must allow constant updates without development intervention.

---

### Layer 6. Editorial system

Defines how the site communicates.

Includes:

- Voice and tone: Academic, clear, rigorous. No commercial pressure.
- Clear, consistent language: Spanish primary; titles in Spanish and English.
- No commercial pressure: Open access, no fees, no marketing funnels.

---

### Layer 7. Infrastructure

The silent but critical base.

Must guarantee:

- Stability
- Security
- Backups
- Staging environment
- Scalability

---

### Layer 8. Legal and institutional anchor

- ISSN and Depósito Legal (Biblioteca Nacional de Venezuela)
- Creative Commons (Attribution, non-commercial)
- Privacy and confidentiality policy (authors, reviewers, visitors)
- Ethics declaration (COPE alignment)
- Copyright policy (Venezuelan law, SAPI)

---

## Accessibility (cross-cutting layer)

Not an add-on. A structural condition of the system. Defined in `19-accessibility-standards`.

Must cross:

- Identity
- Navigation
- Content
- Visual design

---

## What the system allows

Without touching code, the site owner can:

- Publish new issues (editorial, articles, essays, misceláneas)
- Update CENFISS presente and event information
- Manage author and reviewer data
- Update norms, policies, and forms

Without modifying the central structure.

---

## What this system is NOT

It is not:

- A funnel
- A landing page
- A commercial site
- A marketing-oriented CMS

It is an academic digital publication platform for philosophical thought.

---

## Status

| Layer | Status | Notes |
|-------|--------|-------|
| Identity | In use, doc pending | `tokens.css` has palette and typography. `02-corporate-identity` still has placeholders. |
| Architecture | Implemented | Pages exist: Inicio, Revista (issue/article archives), Normas, Acerca, Contacto, Noticias. |
| Content model | Implemented | Issue, article, essay, review views. Single/archive templates. |
| Navigation | Implemented | Header nav, footer, primary CTA (Enviar Colaboración). |
| Publication | Static only | No CMS. Content is hardcoded. Forms link to PDFs. |
| Editorial | Implemented | Voice and tone in place. No commercial pressure. |
| Infrastructure | Partial | Live at GitHub Pages. `deploy.sh` references non-existent `prototype/`. GitLab CI expects `public/`. No 404.html. |
| Legal | Partial | CC license in footer. Privacy/ethics pages exist. ISSN placeholder. |

### Implementation gaps

- **404 page:** Missing. Add `404.html` for broken links.
- **Deploy script:** `deploy.sh` checks for `prototype/front-page.html`; site lives at root with `index.html`.
- **Docs 02–20:** Filled for project. Traceability in `00-order-documents`.
- **WordPress:** Not started. Static maquette is the current deliverable.

---

**Version:** 1.0  
**Depends on:** content-source  
**Project:** Revista de Filosofía LOGO ET SPES
