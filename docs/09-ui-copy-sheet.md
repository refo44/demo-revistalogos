# Revista de Filosofía LOGO ET SPES — UI Copy Sheet

**Single source of truth for microcopy, navigation and system**

Buttons, menus, messages, forms and states. Voice criteria in `07-voice-guide-microcopy-ux`; allowed/forbidden terms in `08-voice-dictionary`.

**Depends on:** `07-voice-guide-microcopy-ux`, `08-voice-dictionary`  
**Reference:** `04-screen-map`, `05-information-architecture-navigation`, `06-wireframes`

**Central rule:** All copy must sound institutional and academic. No marketing, urgency or promotional language.

---

## 1. Global navigation

### Main menu (header)

- Inicio
- Número Actual
- Números Publicados
- Enviar colaboración
- Acerca
- Noticias
- Contacto
- Enlaces
- CENFISS (external)

### Footer

**Enlaces Rápidos:**
- Número Actual
- Archivos
- Artículos
- Enviar Colaboración
- Búsqueda

**Normas Editoriales:**
- Normas de Publicación
- Ética Editorial
- Políticas
- Comité Editorial

**Contacto:** CENFISS, email, web

**Footer bottom:** © 2025 CENFISS. Licencia Creative Commons Atribución 4.0 Internacional.

---

## 2. Home

### Hero

- **Title:** LOGO ET SPES
- **Subtitle:** Revista de Filosofía y Teología
- **Description:** La Revista de Filosofía LOGO ET SPES adscrita, auspiciada y editada por el Centro de Filosofía para la Investigación \<Stanislao Strba\> - CENFISS, es una publicación digital venezolana enfocada en el pensamiento filosófico multidisciplinar. Es de acceso abierto; arbitrada bajo la modalidad \<doble anónimo o doble ciego\>; con periodicidad anual. Sus páginas están disponibles para difundir investigaciones originales -de autores nacionales e internacionales- que coadyuven a promover el desarrollo de todas las áreas de la Filosofía.
- **Button:** Ver número actual

### Sidebar (Noticias)

- **Title:** Noticias
- **Button:** Ver todas las noticias

### Sidebar (CENFISS)

- **Title:** CENFISS
- **Text:** Centro de Filosofía para la Investigación Stanislao Strba. Ubicación: Estado de Carabobo, Valencia, Venezuela
- **Button:** Visitar Web

---

## 3. Page headers (archive-header pattern)

| Page | Title | Description |
|------|-------|-------------|
| Archivo de Números | Archivo de Números | Archivo completo de números publicados de la revista Logos et Spes. Accede a todos los volúmenes y números desde 2010 hasta la actualidad. |
| Archivo de Artículos | Archivo de Artículos | Archivo completo de artículos publicados en Logos et Spes. Busca y accede a todos los artículos de filosofía organizados por sección, autor y fecha. |
| Enviar Colaboración | Enviar Colaboración | Instrucciones para enviar artículos, ensayos y reseñas a LOGO ET SPES. |
| Acerca | Acerca de | Conoce el enfoque, alcance y objetivos de LOGO ET SPES, revista de filosofía multidisciplinar del Centro de Filosofía para la Investigación Stanislao Strba (CENFISS). |
| Noticias | Noticias | Noticias, eventos y reflexiones sobre filosofía, teología y la revista académica LOGO ET SPES. |

---

## 4. Archive filters (artículos)

- **Label Buscar:** Buscar
- **Placeholder:** Título, autor, palabras clave...
- **Label Sección:** Sección
- **Option default:** Todas las secciones
- **Label Año:** Año
- **Option default:** Todos los años
- **Submit:** Buscar
- **Reset:** Limpiar filtros

---

## 5. Contact form

- **Section title:** Enviar Mensaje
- **Fields:** Nombre completo *, Email *, Asunto *, Mensaje *
- **Button:** Enviar Mensaje
- **Confirmation:** (mailto; no confirmation message if using mailto)

---

## 6. Buttons (by context)

| Context | Primary | Secondary |
|---------|---------|-----------|
| Home hero | Ver número actual | — |
| Issue card | 📄 PDF completo | Ver contenido |
| Article card | 📄 PDF | Leer más |
| Single issue | Descargar PDF del número completo | Ver PDF |
| Single article | Descargar PDF del artículo | Ver PDF, Ver número completo |
| Normas | Descargar | Ver PDF |
| Search filters | Buscar | Limpiar filtros |

---

## 7. Breadcrumbs

- **Home:** Inicio
- **Archives:** Inicio → Archivos
- **Single issue:** Inicio → Archivos → [Issue title]
- **Single article:** Inicio → Archivos → [Issue] → [Article title]
- **Static pages:** Inicio → [Page title]

---

## 8. States and errors

| Situation | Text |
|-----------|------|
| No search results | No se encontraron resultados para su búsqueda. Puede intentar con otros términos o explorar el archivo de artículos. |
| Empty archive (issues) | Aún no hay números publicados. Consulte las normas para enviar una colaboración. |
| Empty archive (articles) | Aún no hay artículos publicados. Consulte el archivo de números o las normas para enviar una colaboración. |
| 404 | La página que busca no existe. Puede volver al inicio o ver el número actual. |
| Send error | No fue posible enviar el mensaje. Intente de nuevo o contacte al Consejo Editorial por correo electrónico. |
| Technical error | No fue posible completar la acción. Intente de nuevo o contacte al Consejo Editorial. |

---

## 9. Accessibility labels

| Element | Text |
|---------|------|
| Skip link | Saltar al contenido principal |
| Nav toggle | Menú |
| Carousel prev | Slide anterior |
| Carousel next | Siguiente slide |
| Carousel indicator | Ir al slide [n] |
| PDF download (issue) | Descargar PDF completo del Vol. [X] Nº [Y] ([year]) |
| PDF download (article) | Descargar PDF del artículo '[title]' |

---

## 10. External links

- **CENFISS:** Opens in new tab. Use `rel="noopener noreferrer"`.
- **Creative Commons:** Opens in new tab.
- **APA / Vancouver guides:** Opens in new tab.
- **DOI links:** Opens doi.org in new tab.
- **ORCID links:** Opens orcid.org in new tab.

**Notice for screen readers:** Add visually hidden "(se abre en nueva pestaña)" or use `target="_blank"` with appropriate `rel`.

---

## 11. Accessibility

**Alt text:** Describe images clearly. Decorative images: `alt=""`.

**Notices:** "Opens in new tab" for external links. Use `aria-label` where link text is insufficient.

Full criteria in `19-accessibility-standards`.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
