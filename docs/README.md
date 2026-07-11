# Revista de Filosofía LOGO ET SPES — Documentación

Documentación para el sitio web de la revista académica. CENFISS (Centro de Filosofía para la Investigación Stanislao Strba), Valencia, Venezuela.

**Fuente canónica:** `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md`

---

## Lista de documentos

| # | Documento | Propósito |
|---|-----------|-----------|
| 00 | order-documents | Orden de documentos y reglas de dependencia |
| 01 | platform-plan | Plan maestro, capas, principios |
| 02 | corporate-identity | Paleta, tipografía, logo, esencia de marca |
| 03 | wordpress-content-model | Tipos de entrada, taxonomías, campos |
| 04 | screen-map | Qué pantallas existen |
| 05 | information-architecture-navigation | Enlaces por pantalla, estructura del menú |
| 06 | wireframes | Estructura de bloques por vista |
| 07 | voice-guide-microcopy-ux | Cómo habla el sitio |
| 08 | voice-dictionary | Términos permitidos / prohibidos |
| 09 | ui-copy-sheet | Botones, menús, mensajes |
| 10 | user-journey | Rutas del visitante y validación |
| 11 | url-tree | Rutas canónicas |
| 12 | theme-file-structure | Arquitectura del tema WordPress |
| 13 | static-file-structure | Geografía del proyecto |
| 14 | css-architecture | Tokens, capas, nomenclatura |
| 15 | assets-strategy | Imágenes, fuentes, iconos |
| 16 | content-source-inventory | Archivos fuente y mapeo |
| 17 | implementation-order | Fases, maqueta → WordPress |
| 18 | ux-ui-trends | Filtro adopción / evitación |
| 19 | accessibility-standards | WCAG, checklist, pruebas |
| 20 | layout-principles | Ancho de lectura, ritmo, responsive |
| 21 | user-journey-diagram | Diagrama Mermaid de recorridos |

---

## Directrices

- **Estrategia primero:** Definir propósito y audiencia antes del diseño.
- **Identidad como restricción:** El manual de marca es la fuente de verdad.
- **El contenido impulsa la estructura:** Modelar contenido antes de construir pantallas.
- **Consistencia de voz:** Un solo tono en navegación, copy y errores.
- **Accesibilidad por defecto:** WCAG AA desde el inicio.
- **Estático antes que dinámico:** Validar con maqueta HTML/CSS antes de WordPress.
- **Una sola fuente de verdad:** Un documento por tema.

### Estado del contenido de la maqueta

- La maqueta HTML está validada como referencia visual y estructural, no como edición publicada.
- El contenido institucional proviene de `content-source/` y puede cargarse en páginas WordPress respetando literalmente la fuente.
- Los números, artículos, editoriales, autores y noticias son contenido dinámico administrado en WordPress.
- El Vol. 12 Nº 2, los números históricos, artículos, autores, noticias, ISSN, DOI, ORCID, portadas y paginación actuales son demostrativos y no deben migrarse a producción.
- La primera edición real se cargará desde el PDF final y los metadatos oficiales entregados por el equipo editorial.
- La clasificación detallada y el checklist de sustitución están en `16-content-source-inventory` y `17-implementation-order`.

---

## Licencias del repositorio

- **Código del repositorio** (`HTML`, `CSS`, `JS`, scripts, configuración): licencia `MIT`. Ver `../LICENSE`.
- **Contenido editorial y de publicación**: licencia `CC BY 4.0`. Ver `../LICENSE-CONTENT`.
- **Materiales de terceros** mantienen su licencia o atribución específica cuando aplique.

---

## Regla de dependencias

Ningún documento puede depender de uno con número mayor. Ver `00-order-documents` para la justificación completa.

---

**Versión:** 1.1
**Proyecto:** Revista de Filosofía LOGO ET SPES
