# Orden de documentos (prefijo numérico)

Los documentos en `docs/` usan un prefijo de dos dígitos (`01-`, `02-`, …) para mantener un orden fijo. El orden sigue la **jerarquía de dependencias**: de "por qué" a "cómo", y de estrategia a implementación.

---

## Idioma del proyecto

Todos los documentos de arquitectura del sitio se redactan en español. Los términos técnicos de programación pueden mantenerse en inglés (nombres de archivos, slugs, metadatos académicos estándar).

---

## Criterio del orden

| Rango | Criterio | Justificación |
|-------|----------|---------------|
| **01** | Plan maestro | El plan de plataforma define el territorio; precede y guía todo lo demás. |
| **02** | Identidad | Paleta, tipografía y manual de marca definen los criterios visuales. |
| **03–05** | Qué existe | Modelo de contenido WordPress, mapa de pantallas y arquitectura de información. Define *qué* contenido y *qué* pantallas existen. |
| **06** | Wireframes | Estructura de pantalla: jerarquía, bloques y flujo de lectura por vista. No diseño visual; apoya mapa de pantallas y navegación. |
| **07–09** | Voz y copy | Guía de voz, diccionario de términos, hoja de copy UI. El copy depende de la identidad; la navegación los usa después. |
| **10** | Experiencia del visitante | Recorrido del usuario. Cómo se recorre el sitio y cómo la persona llega a la acción principal. |
| **11–12** | Navegación y tema | Árbol de URLs y estructura de archivos del tema. Dependen de pantallas, copy y recorridos. |
| **13** | Estructura de archivos estáticos | Geografía del proyecto: docs, content-source, tema, assets. Dónde viven los archivos estáticos. |
| **14** | Arquitectura CSS | Capas (theme.json, main.css), tokens, nomenclatura, especificidad, accesibilidad en estilos. |
| **15–17** | Implementación técnica | Assets, inventario de fuentes de contenido, orden de implementación. Convertir arquitectura en código. |
| **18** | Criterios contemporáneos | Tendencias UX/UI aplicadas al sistema. Filtro estratégico para validación de diseño e implementación. |
| **19** | Accesibilidad | Estándares únicos de accesibilidad: estrategia, diseño, HTML semántico, ARIA, contenido editorial, checklist y pruebas. WCAG 2.1/2.2 AA. |
| **20** | Principios de maquetación | Ancho de lectura, ritmo vertical, uso del espacio en blanco, relación tipografía/imagen. Cierra el sistema visual antes de escribir HTML. |
| **21** | Diagrama de recorrido del usuario | Diagrama Mermaid de flujos del visitante. Validación visual de 10-user-journey. |

---

## Regla de dependencias

Ningún documento puede depender de uno con número mayor. Las referencias cruzadas siempre apuntan a documentos con prefijo menor o igual. Si se añade un documento nuevo, asignar el siguiente número disponible y revisar referencias.

---

## Lista ordenada (nombres de documentos)

1. `01-platform-plan`
2. `02-corporate-identity`
3. `03-wordpress-content-model`
4. `04-screen-map`
5. `05-information-architecture-navigation`
6. `06-wireframes`
7. `07-voice-guide-microcopy-ux`
8. `08-voice-dictionary`
9. `09-ui-copy-sheet`
10. `10-user-journey`
11. `11-url-tree`
12. `12-theme-file-structure`
13. `13-static-file-structure`
14. `14-css-architecture`
15. `15-assets-strategy`
16. `16-content-source-inventory`
17. `17-implementation-order`
18. `18-ux-ui-trends`
19. `19-accessibility-standards`
20. `20-layout-principles`
21. `21-user-journey-diagram`

---

## Fuentes de contenido

Todo el contenido proviene de:

- `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Documento del proyecto (estructura, políticas, normas, formularios)
- `assets/pdf/` — Normas, políticas, artículo de ejemplo y número
- `assets/img/` — Logos, favicon, placeholders

Ver `16-content-source-inventory` para el mapeo completo.

---

## Trazabilidad

| Requisito / lluvia de ideas | Doc(s) |
|-----------------------------|--------|
| Revista académica, acceso abierto | 01, 02 |
| Arbitraje doble ciego, normas editoriales | 01, 03, 07 |
| CPTs: número, artículo | 03, 12 |
| Pantallas y vistas | 04, 06 |
| Navegación, migas de pan, IA | 05, 11 |
| Voz, microcopy, CTAs | 07, 08, 09 |
| Recorridos del usuario | 10, 21 |
| Estructura del tema, plantillas | 12, 13 |
| CSS, tokens, maquetación | 02, 14, 20 |
| Assets, inventario de contenido | 15, 16 |
| Fases de implementación | 17 |
| Filtro UX/UI, accesibilidad | 18, 19 |

### Documento del proyecto → docs

| Ítem del doc del proyecto | Doc(s) |
|---------------------------|--------|
| Portada, Sumario, Editorial, Artículos | 03, 04, 06 |
| Normas de Publicación, Ética, Políticas | 04, 07, 09 |
| Formularios (Solicitud, Arbitraje) | 16, 17 |
| Origen del nombre Logo et Spes | 02, 04 |
| Simulación teórica del sitio | 04, 06, 11 |
| Acceso abierto, arbitraje doble ciego | 01, 07 |

---

## Directrices para cualquier sitio web

Estos principios aplican independientemente del tipo de sitio (comunidad, autor, blog, portfolio, e-commerce):

- **Estrategia primero:** Definir propósito y audiencia antes del diseño.
- **Identidad como restricción:** El manual de marca es la fuente de verdad; ninguna decisión visual fuera de él.
- **El contenido impulsa la estructura:** Modelar contenido antes de construir pantallas.
- **Consistencia de voz:** Un solo tono en navegación, copy y estados de error.
- **Accesibilidad por defecto:** WCAG AA desde el inicio, no como añadido posterior.
- **Estático antes que dinámico:** Validar con maqueta HTML/CSS antes de WordPress.
- **Una sola fuente de verdad:** Un documento por tema; evitar duplicación.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
