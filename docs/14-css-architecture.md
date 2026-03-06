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
| Páginas | `pages.css` | Específico de página (front-page, archive, single-issue, single-article). |
| Utilidades | `utilities.css` | Flow, gap, text, display, flex, visually-hidden, focus-ring. |

Sin frameworks (Tailwind, Bootstrap). Un solo punto de entrada: `main.css` importa todo.

---

## 2. Estructura de main.css

**Orden de importación (en main.css):**

1. `tokens.css` — Variables (:root)
2. `base.css` — Reset, tipografía, enlaces
3. `layout.css` — Contenedor, header, nav, footer, grid
4. `components.css` — Botones, tarjetas, hero, toc, migas de pan, metadatos
5. `pages.css` — Específico de página
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

---

## 4. Nomenclatura y especificidad

- **Convención:** Estilo BEM. Bloque (`header`), elemento (`header__logo`), modificador (`btn--primary`).
- **Ejemplos:** `.header`, `.header__inner`, `.btn`, `.btn--primary`, `.card`, `.article-card`, `.front-page__hero`.
- **Especificidad:** Mantener baja. Evitar anidación profunda (máx 2–3 niveles). Sin `!important`.
- **IDs:** No usar para estilos; reservar para anclas y ARIA (`#main-content`).
- **Estilos inline:** No estilizar por `[style]`; sin inline en contenido editorial.

---

## 5. theme.json y tokens.css

- **theme.json** define colores, tipografía, espaciado para el editor de bloques de WordPress. Debe alinear con `02-corporate-identity`.
- **tokens.css** contiene las mismas decisiones. Fuente única para maqueta no-WP; ambos deben mantenerse sincronizados.
- **Roles semánticos:** Considerar añadir `--brand-1`, `--text`, `--link` en tokens para componentes que necesiten roles semánticos (ver 02).

---

## 6. Accesibilidad en CSS

- **Contraste:** Texto y controles deben cumplir WCAG 2.1 AA. Verificar combinaciones de 02 (ej. `--color-text-primary` sobre `--color-bg-primary`).
- **Foco:** Estilo visible para `:focus-visible` en enlaces, botones, controles de formulario. Implementado en base.css y utilities (`.focus-ring`). Sin `outline: none` sin reemplazo.
- **Movimiento reducido:** Respetar `prefers-reduced-motion: reduce`; reducir o eliminar animaciones. Añadir donde existan transiciones.
- **Tamaño de tap/clic:** Tamaño adecuado para áreas interactivas (~44×44px cuando sea posible). Botones, enlaces de nav.
- **visually-hidden:** Usar para texto solo para lectores de pantalla (enlace saltar, etiqueta del toggle de nav).

Criterios completos en `19-accessibility-standards`.

---

## 7. Responsabilidades de archivos

| Archivo | Contiene |
|---------|----------|
| tokens.css | Solo variables :root. Sin selectores. |
| base.css | *, html, body, h1–h6, p, a, listas, reset mínimo. |
| layout.css | .container, .header, .nav, .footer, .main-content, grid. |
| components.css | .btn, .card, .hero, .breadcrumbs, .toc, .metadata-box, formularios. |
| pages.css | .front-page__*, .archive-*, .single-issue__*, .single-article__*, .editorial-section. |
| utilities.css | .flow, .gap-*, .text-*, .flex, .visually-hidden, .focus-ring. |

---

## 8. Reglas de no hacer

- No añadir estilos en `style.css` (solo cabecera).
- No introducir frameworks CSS sin decisión explícita del proyecto.
- No usar `!important` para arreglar conflictos; corregir especificidad u orden de carga.
- No hardcodear colores o tamaños de fuente que existan en 02 o tokens; usar variables.
- No añadir nuevas variables de color sin actualizar 02 y tokens.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
