# Revista de Filosofía LOGO ET SPES — Identidad Corporativa

**Versión 1.0**

Este documento define el sistema de identidad visual y tipográfica del sitio. Los valores provienen de la implementación y del documento del proyecto.

**Fuente canónica:** `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md`  
**Referencia de implementación:** `assets/css/tokens.css`, `assets/img/logo-revista.svg`

---

## 1. Paleta de colores

Solo existen los colores aquí definidos. Constituyen el núcleo cromático. No añadir colores primarios adicionales.

| Nombre | Hex | RGB |
|--------|-----|-----|
| Azul institucional | #1e3a8a | R: 30, G: 58, B: 138 |
| Azul claro | #3b82f6 | R: 59, G: 130, B: 246 |
| Azul oscuro | #1e40af | R: 30, G: 64, B: 175 |
| Gris neutro | #64748b | R: 100, G: 116, B: 139 |
| Texto principal | #1e293b | R: 30, G: 41, B: 59 |
| Blanco | #ffffff | R: 255, G: 255, B: 255 |
| Enlace | #00b0f0 | R: 0, G: 176, B: 240 |

**Nota:** El documento original del proyecto menciona un fondo "verde aguamarina degradado" para la portada impresa. La plataforma digital adopta la paleta azul definida arriba para garantizar consistencia, accesibilidad y mantenibilidad a largo plazo. Si CENFISS adopta posteriormente un manual de marca formal, ese manual prevalecerá.

### 1.1 Tokens de marca y semánticos

Separar tokens de marca de tokens semánticos para mantenibilidad.

**Tokens de marca:**

```css
--color-brand-primary: #1e3a8a;
--color-brand-secondary: #3b82f6;
```

**Tokens semánticos:**

```css
--color-bg: #ffffff;
--color-text: #1e293b;
--color-text-muted: #64748b;
--color-border: #e5e7eb;
--color-link: #00b0f0;
--color-link-hover: #3b82f6;
```

**Mapeo legacy** (para compatibilidad con `tokens.css` existente): `--brand-1` = primary, `--brand-2` = secondary.

### 1.2 Regla de accesibilidad

Todas las combinaciones de color deben cumplir las ratios de contraste WCAG 2.1 AA. El texto de cuerpo debe alcanzar al menos 4.5:1 de contraste. Ver `19-accessibility-standards`.

---

## 2. Sistema tipográfico

| Familia | Uso |
|---------|-----|
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
|-------|--------|
| H1 | 2.5–3rem |
| H2 | 2rem |
| H3 | 1.5rem |
| H4 | 1.25rem |
| Cuerpo | 1rem |
| Pequeño | 0.875rem |

Base: 1rem (16px). Line-height del cuerpo: 1.5.

---

## 3. Logo

- **Revista:** `assets/img/logo-revista.svg` — Header, materiales digitales
- **CENFISS:** `assets/img/logo-cenfiss.svg` — Referencia institucional
- **Favicon:** `assets/img/favicon.svg` — Derivado del logo

**Reglas:** No alterar proporciones, color ni composición.

**Reglas de uso del logo:**

- Espacio libre mínimo: 1× altura del logo alrededor de la marca.
- No colocar el logo sobre fondos de bajo contraste.
- No aplicar degradados, sombras ni distorsiones.

---

## 4. Esencia de marca

| Rasgo | Descripción |
|-------|-------------|
| Voz | Académica, clara, rigurosa. Sin presión comercial. |
| Estética | Institucional, serena. La paleta azul transmite confianza y seriedad. |
| Autoridad | El lenguaje visual debe transmitir seriedad académica y credibilidad editorial. |
| Ritmo | Espacio en blanco generoso. Flujo de lectura vertical. Sin grids densos. |
| Función | Guiar al visitante a leer, enviar o contactar. |

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

---

## 6. Gramática de maquetación

| Regla | Significado |
|-------|-------------|
| Columna única / maquetación simple | Lectura vertical |
| Espacio | Respirar, calma |
| Sin grids densos | Ni revista ni e-commerce |
| Flujo claro | Guiar hacia la acción principal |
| Las imágenes apoyan el texto | Las imágenes deben apoyar el texto, no dominarlo. El sitio prioriza la lectura sobre el espectáculo visual. |

---

## 7. Iconografía

Los iconos deben ser simples, basados en líneas y neutros. Evitar estilos decorativos o juguetones. Los iconos apoyan la navegación pero no deben competir con el texto.

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

**Versión:** 1.0  
**Fuente:** PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md, tokens.css  
**Proyecto:** Revista de Filosofía LOGO ET SPES
