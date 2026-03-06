# Revista de Filosofía LOGO ET SPES — Arquitectura CSS

**Capas, tokens y criterios de implementación de estilos**

Define cómo se organiza el CSS del sitio: relación entre theme.json, variables y main.css, especificidad y nomenclatura, accesibilidad en estilos.

**Depende de:** `02-corporate-identity`, `12-theme-file-structure`  
**Referencia:** `15-assets-strategy`, `19-accessibility-standards`, `20-layout-principles`

---

## 1. Capas del sistema de estilos

| Capa | Fuente | Rol |
|------|--------|-----|
| Metadatos del tema | `style.css` | Solo cabecera (Theme Name, etc.). Sin reglas CSS. |
| Tokens de diseño | `tokens.css` | Colores, tipografía, espaciado, bordes, sombras, z-index. |
| theme.json | `theme.json` | Paleta, tipografía para el editor de bloques de WordPress. Alinear con tokens. |
| Base | `base.css` | Box-sizing, reset, tipografía, enlaces. |
| Layout | `layout.css` | Contenedor, header, nav, footer, grid. |
| Componentes | `components.css` | Botones, tarjetas, hero, toc, migas de pan, metadatos, formularios. |
| Páginas | `pages/` | Específico de vista: home.css, archive.css, issue.css, article.css, static-pages.css. |
| Utilidades | `utilities.css` | Flow, gap, text, display, flex, visually-hidden, focus-ring. |

Sin frameworks (Tailwind, Bootstrap). Un solo punto de entrada: `main.css` importa todo.

---

## 2. Estructura de main.css

**Orden de importación (en main.css):**

1. `tokens.css` — Variables (:root)
2. `base.css` — Reset, tipografía, enlaces
3. `layout.css` — Contenedor, header, nav, footer, grid
4. `components.css` — Botones, tarjetas, hero, toc, migas de pan, metadatos
5. `pages/` — home.css, archive.css, issue.css, article.css, static-pages.css
6. `utilities.css` — Helpers, visually-hidden, focus-ring

---

## 3. Uso de tokens

| Categoría de token | Variables | Uso |
|--------------------|-----------|-----|
| Colores | `--color-primary`, `--color-text-primary`, `--color-link`, etc. | Todos los componentes consumen vía `var()`. Sin hex crudo en componentes. |
| Tipografía | `--font-family-primary`, `--font-family-heading`, `--font-size-*`, `--line-height-*` | Base y componentes. |
| Espaciado | `--space-1` … `--space-24` | Márgenes, padding, gaps. |
| Layout | `--container-max-width`, `--container-padding` | Contenedor, grid. |
| Bordes | `--border-width`, `--border-radius` | Botones, tarjetas, inputs. |
| Transiciones | `--transition-fast`, `--transition-normal` | Hover, focus. |
| Z-index | `--z-dropdown`, `--z-sticky`, `--z-modal` | Apilamiento. |

**Regla:** Los componentes nunca usan hex crudo. Consumen `var(--color-*)` o roles semánticos.

**Regla de tokens semánticos:** Los componentes deben consumir tokens semánticos (`--color-text-primary`, `--color-surface`, `--color-border`, `--color-link`) y no tokens de marca directamente, salvo en elementos institucionales específicos (logo, cabecera institucional).

**Layout fluido:** Cuando sea posible, usar `clamp()` para tipografía, spacing y contenedores fluidos, sin romper la legibilidad editorial. Ver `20-layout-principles`.

---

## 4. Nomenclatura y especificidad

- **Convención:** Usar BEM de forma pragmática, no dogmática. La prioridad es claridad y mantenibilidad.
- **Ejemplos:** `.header`, `.header__inner`, `.btn`, `.btn--primary`, `.card`, `.article-card`, `.front-page__hero`.
- **Prefijos de página:** Los prefijos de página (`.front-page__*`, `.archive-*`, `.single-article__*`) solo se usan para estructura específica de vista, no para componentes reutilizables.
- **Especificidad:** Mantener baja. Evitar anidación profunda (máx 2–3 niveles). No encadenar clases por contexto cuando el componente puede resolverse con una clase propia (evitar `.header .nav .menu .item a`; preferir `.nav__link`). Sin `!important`.
- **IDs:** No usar para estilos; reservar para anclas y ARIA (`#main-content`).
- **Estilos inline:** No estilizar por `[style]`; sin inline en contenido editorial.

---

## 5. theme.json y tokens.css

- **theme.json** adapta el sistema al editor de bloques de WordPress. Define colores, tipografía, espaciado para el editor. Debe alinear con `02-corporate-identity`.
- **tokens.css** es la referencia operativa para la maqueta y el front-end. Contiene las mismas decisiones que theme.json.
- **Autoridad:** Ambos deben reflejar las mismas decisiones, pero las variables del front-end no deben depender de que WordPress inyecte estilos del editor.
- **Roles semánticos:** Considerar añadir `--brand-1`, `--text`, `--link` en tokens para componentes que necesiten roles semánticos (ver 02).

---

## 6. Accesibilidad en CSS

- **Contraste:** Texto y controles deben cumplir WCAG 2.1 AA. Verificar combinaciones de 02 (ej. `--color-text-primary` sobre `--color-bg-primary`).
- **Foco:** Enlaces de navegación, botones, controles de formulario, paginación y enlaces de tarjetas deben tener estado `:focus-visible` explícito. Implementado en base.css y utilities (`.focus-ring`). Sin `outline: none` sin reemplazo.
- **Movimiento reducido:** Toda animación o transición no esencial debe respetar `prefers-reduced-motion: reduce`.
- **Tamaño de tap/clic:** Áreas interactivas deben aproximarse a 44×44px cuando el contexto lo permita, especialmente en navegación y controles táctiles.
- **visually-hidden:** Usar para texto solo para lectores de pantalla (enlace saltar, etiqueta del toggle de nav).
- **Estados interactivos:** hover, focus-visible, active, disabled y current deben definirse junto al componente correspondiente.

Criterios completos en `19-accessibility-standards`.

---

## 7. Responsabilidades de archivos

| Archivo | Contiene |
|---------|----------|
| tokens.css | Solo definición de variables y, si fuera necesario, aliases semánticos en `:root`. Sin estilos de componentes ni layout. |
| base.css | *, html, body, h1–h6, p, a, listas, reset mínimo. No reset agresivo que rompa accesibilidad o elementos nativos útiles del navegador. |
| layout.css | .container, .header, .nav, .footer, .main-content, grid. |
| components.css | .btn, .card, .hero, .breadcrumbs, .toc, .metadata-box. Inputs, labels, fieldsets, mensajes de validación y botones de formulario. |
| pages/ | home.css, archive.css, issue.css, article.css, static-pages.css. |
| utilities.css | .flow, .gap-*, .text-*, .flex, .visually-hidden, .focus-ring. Deben ser mínimas, predecibles y no reemplazar la semántica de componentes. |

---

## 8. Reglas de no hacer

- No añadir estilos en `style.css` (solo cabecera).
- No introducir frameworks CSS sin decisión explícita del proyecto.
- No usar `!important` para arreglar conflictos; corregir especificidad u orden de carga.
- No hardcodear colores o tamaños de fuente que existan en 02 o tokens; usar variables.
- No añadir nuevas variables de color sin actualizar 02 y tokens.
- No estilizar etiquetas HTML por contexto de página si existe un componente equivalente.
- No mezclar utilidades con estilos estructurales de componente.

---

## 9. Contenido editorial largo

Los estilos deben priorizar lectura prolongada:

- Ancho de línea legible
- Separación clara entre títulos, párrafos, citas y referencias
- Tablas e imágenes integradas sin romper el flujo de lectura
- Estilos consistentes para notas, DOI, ORCID, palabras clave y referencias

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
