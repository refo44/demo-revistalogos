# Revista de Filosofía LOGO ET SPES — User Journey

Describes how a real person moves through the site. Not a conversion flow; a series of encounters with **philosophical content and editorial processes**.

Validates: information architecture, navigation, microcopy, screen hierarchy.

**Depends on:** `04-screen-map`, `05-information-architecture-navigation`  
**Reference:** `07-voice-guide-microcopy-ux`, `09-ui-copy-sheet`, `19-accessibility-standards`

---

## 1. Person arrives and wants to read the current issue

**Goal:** Enter without friction. Reach content quickly.

**Path:**

- Home
- → Reads hero and main message (enfoque, acceso abierto, arbitraje)
- → Clicks "Ver número actual"
- → Single issue: sees TOC, downloads PDF or reads articles
- → Clicks article in TOC → Single article
- → Downloads PDF or reads full text

**Exit points:** Breadcrumbs (Inicio, Archivos), footer links, nav menu.

---

## 2. Person explores to understand the journal

**Goal:** Understand what LOGO ET SPES is, its scope and how to participate.

**Path:**

- Home
- → Acerca (enfoque, alcance, objetivos, origen del nombre)
- → Reads content
- → Enviar colaboración or Contacto

**Alternative:** Home → Normas → Políticas → Enviar colaboración.

---

## 3. Person wants to submit an article

**Goal:** Find instructions and send a collaboration.

**Path:**

- Home or menu
- → Enviar colaboración
- → Reads numbered instructions (login, normas, form, email)
- → Downloads Solicitud de Publicación
- → Clicks mailto (revista.cenfiss@gmail.com) or Contacto
- → Sends work

**Supporting path:** Enviar colaboración → Normas → Políticas (to verify requirements before submitting).

---

## 4. Person searches for a specific article or topic

**Goal:** Find content by section, author or keyword.

**Path:**

- Home or menu
- → Archivo de Artículos
- → Uses filters (Buscar, Sección, Año)
- → Clicks Buscar
- → Clicks article card → Single article
- → Downloads PDF or reads

**Alternative:** Archivo de Números → Single issue → TOC → Single article.

---

## 5. Person arrives from outside (direct link)

**Goal:** Not get lost when arriving via Google, DOI link or shared URL.

**Path:**

- Arrives from Google, DOI, or shared link
- → Lands on single article, single issue, or static page
- → Sees header (nav) and footer
- → Breadcrumbs offer path: Inicio → Archivos → [current]
- → Navigates to Home, Número actual, Enviar colaboración or Contacto

**No page is a dead end.** Every screen has: nav, footer, breadcrumbs (where applicable).

---

## 6. Person wants to contact the editorial board

**Goal:** Reach the Consejo Editorial.

**Path:**

- Home or menu
- → Contacto
- → Reads contact info (email, CENFISS web, address)
- → Clicks mailto or fills contact form
- → Sends message

**Alternative:** Enviar colaboración → mailto in instructions. Comité → Contacto.

---

## 7. Validation rule

**A journey is correct if:**

1. The person can understand **what the journal is and how to read or submit** in fewer than three clicks.
2. There is always a "next" or "back" (breadcrumbs, nav, footer).
3. They never land on a dead screen (404 has links; empty states have exits).
4. They never feel they entered an app or store (institutional, academic tone).

---

## 8. Journey accessibility

The journey must be possible for anyone, regardless of physical, sensory or cognitive abilities.

Accessibility does not define alternate paths. It ensures the same journey can be traversed by:

- People with visual disabilities (screen readers, alt text, semantic structure)
- People with low vision (contrast, resize, focus indicators)
- People who navigate with keyboard (skip link, focus order, no traps)
- People with hearing difficulties (no essential info in audio only)
- People with high cognitive load (clear labels, consistent structure, no jargon in UI)

Technical and editorial criteria in `19-accessibility-standards`.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
