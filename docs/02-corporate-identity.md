# Revista de Filosofía LOGO ET SPES — Identidad Corporativa

Versión: 1.5

Este documento define el sistema de identidad visual y tipográfica del sitio. Sigue de forma estricta la fuente canónica y traduce al front-end solo los valores necesarios para una implementación web consistente y accesible.

**Fuente canónica:** `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md`  
**Referencia de implementación:** `assets/css/tokens.css`, `assets/img/logo-revista.png`

---

## 1. Paleta de colores

La única instrucción cromática explícita de la fuente canónica es: `Fondo: <color verde aguamarina degradado>.` La referencia cromática original describe un fondo degradado verde-aguamarina. Para la implementación digital se interpreta esa intención como una gama editorial azul-aguamarina coherente con el color institucional de la revista.

| Nombre | Hex | Uso |
| --- | --- | --- |
| Fondo general frío | #f8fafc | fondo principal del sitio |
| Blanco de superficie | #ffffff | contenedores, tarjetas |
| Azul petróleo institucional | #18597c | navegación, títulos |
| Azul editorial suave | #e7f2f8 | fondos de bloque |
| Azul de enlace | #1abaf0 | enlaces y estados |
| Amarillo de énfasis | #ffbf00 | botones principales |
| Gris neutro | #afabac | bordes y metadatos |
| Texto principal | #1e293b | texto |

**Regla:** `#18597c` es el color institucional principal. `#f8fafc` es el fondo general recomendado. `#ffffff` se reserva para superficies, contenedores y tarjetas. Los demás colores actúan como soporte, acento, neutral o estado y deben usarse con moderación.

**Nota de implementación:** Como la fuente canónica describe la intención cromática pero no fija códigos hexadecimales, los valores de esta tabla traducen esa intención a una paleta digital coherente que reutiliza colores definidos para el proyecto y mantiene accesibilidad. El color de borde derivado recomendado para UI es `#d9d6d7`.

**Uso recomendado del azul editorial suave (`#e7f2f8`):** cajas informativas, bloques de normas, citas destacadas y paneles. No debe competir con el texto principal ni desplazar al color institucional.

**Nota de alcance:** Los colores de estado (`success`, `warning`, `error`) son tokens semánticos de interfaz. No forman parte de la paleta institucional.

### 1.1 Tokens de marca y semánticos

Separar tokens de marca de tokens semánticos para mantenibilidad.

**Tokens de marca:**

```css
--color-brand-primary: #18597c;
--color-brand-accent: #1abaf0;
--color-brand-soft: #e7f2f8;
--color-brand-highlight: #ffbf00;
```

**Tokens semánticos:**

```css
--color-bg: #f8fafc;
--color-surface: #ffffff;
--color-soft: #e7f2f8;
--color-text: #1e293b;
--color-text-muted: #64748b;
--color-border: #d9d6d7;
--color-link: #1abaf0;
--color-link-hover: #18597c;
```

**Mapeo legacy** (para compatibilidad con `tokens.css` existente): `--brand-1` = primary, `--brand-2` = secondary.

### 1.2 Regla de accesibilidad

Todas las combinaciones de color deben cumplir las ratios de contraste WCAG 2.1 AA. El texto de cuerpo debe alcanzar al menos 4.5:1 de contraste. El texto principal (`#1e293b`) sobre fondo general (`#f8fafc`) y sobre superficie blanca (`#ffffff`) supera ampliamente la ratio de contraste requerida por WCAG 2.1 AA. Ver `19-accessibility-standards`.

**Regla visual:** La interfaz editorial no debe usar gradientes como fondo dominante ni como recurso de marca. El color institucional debe aplicarse en tonos sólidos y con uso moderado.

---

## 2. Sistema tipográfico

| Familia | Uso |
| --- | --- |
| **Georgia** | Títulos, voz de marca, énfasis editorial |
| **System UI** | Texto largo de cuerpo, navegación, UI (mejora legibilidad en pantalla) |

Usar Georgia para títulos y énfasis editorial. Usar System UI para texto largo de cuerpo para mejorar la legibilidad en pantalla.

### 2.1 Variables CSS

```css
:root {
  --font-display: "Georgia", "Times New Roman", serif;
  --font-heading: "Georgia", "Times New Roman", serif;
  --font-body: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
```

### 2.2 Escala tipográfica

| Nivel | Tamaño |
| --- | --- |
| H1 | 2.5–3rem |
| H2 | 2rem |
| H3 | 1.5rem |
| H4 | 1.25rem |
| Cuerpo | 1rem |
| Pequeño | 0.875rem |

Base: 1rem (16px). Line-height del cuerpo: 1.6.

---

## 3. Logo

- **Revista:** `assets/img/logo-revista.png` — Header, materiales digitales
- **CENFISS:** `assets/img/logo-cenfiss.svg` — Referencia institucional
- **Favicon:** `assets/img/favicon.png` — Derivado del logo

**Reglas:** No alterar proporciones, color ni composición.

**Reglas de uso del logo:**

- Espacio libre mínimo: 1× altura del logo alrededor de la marca.
- No colocar el logo sobre fondos de bajo contraste.
- No aplicar degradados, sombras ni distorsiones.

---

## 4. Esencia de marca

- La identidad visual debe transmitir serenidad editorial.
- El contenido filosófico es el centro de la experiencia.
- El color organiza la información, pero nunca domina la página.
- La voz visual debe sentirse académica, clara, rigurosa y sin presión comercial.
- El lenguaje visual debe transmitir seriedad académica y credibilidad editorial.

---

## 5. Ritmo editorial

### Texto de cuerpo

- Medida: ~65ch (ancho de lectura humano)
- Altura de línea: 1.5–1.6
- Espacio: márgenes generosos, espacio para respirar
- Longitud de párrafo: 3–6 líneas recomendadas para lectura académica cómoda

### Navegación y UI

- Discreta, sin dominar el contenido
- Sin CTAs agresivos
- CTA principal: Enviar Colaboración (claro, no insistente)

### Regla cromática editorial

El sitio debe permanecer mayoritariamente neutro. Aproximadamente 90-95% de la interfaz debe sentirse blanca o casi blanca. Los colores de marca organizan la información pero no deben saturar la interfaz.

---

## 6. Gramática de maquetación

| Regla | Significado |
| --- | --- |
| Columna única / maquetación simple | Lectura vertical |
| Espacio | Respirar, calma |
| Sin grids densos | Ni revista ni e-commerce |
| Flujo claro | Guiar hacia la acción principal |
| Las imágenes apoyan el texto | Las imágenes deben apoyar el texto, no dominarlo. El sitio prioriza la lectura sobre el espectáculo visual. |

---

## 7. Iconografía

Los iconos deben ser SVG simples y accesibles. Evitar estilos decorativos o juguetones. Los iconos apoyan la navegación pero no deben competir con el texto. Los iconos decorativos deben marcarse como `aria-hidden="true"`.

---

## 8. Escala de espaciado

```css
--space-xs: 0.25rem;
--space-sm: 0.5rem;
--space-md: 1rem;
--space-lg: 2rem;
--space-xl: 4rem;
```

Alinear con `tokens.css` y `14-css-architecture`.

---

## 9. Regla final

Todas las decisiones visuales deben derivar de este sistema. Si CENFISS adopta posteriormente un manual de marca institucional formal, ese manual reemplaza este documento.

---

**Versión:** 1.5  
**Fuente:** PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md, tokens.css  
**Proyecto:** Revista de Filosofía LOGO ET SPES
