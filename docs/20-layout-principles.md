# Revista de Filosofía LOGO ET SPES — Principios de Maquetación

Cierra el sistema visual: ancho de lectura, ritmo vertical, espacio en blanco, relación tipografía/imagen, grid y comportamiento responsive. Aplica a maqueta estática y WordPress.

**Depende de:** `02-corporate-identity`, `06-wireframes`, `14-css-architecture`  
**Referencia:** `17-implementation-order`, `18-ux-ui-trends`, `19-accessibility-standards`

---

## 1. Ancho de lectura

- **Objetivo:** 60–70 caracteres por línea (`ch`) en texto de cuerpo. Objetivo: ~65ch (según `02-corporate-identity`).
- **Implementación:** Contenedor de contenido con `max-width` en `ch` (ej. `65ch`). Cuerpo del artículo, normas, contenido editorial.
- **Contenedor general:** El texto vive en un contenedor centrado. Sin texto de borde a borde. El hero puede usar ancho completo; la lectura no.
- **Regla:** Sin líneas infinitas; el sitio es legible y sereno, no denso.

---

## 2. Ritmo vertical

- **Consistencia:** Márgenes y padding siguen la escala de espaciado (`--space-1` … `--space-24` en `tokens.css`).
- **Respiración:** Espaciado generoso entre bloques; el contenido no está "pegado".
- **Jerarquía:** Más espacio antes/después de H1/hero que entre párrafos.
- **Regla:** Un solo sistema de espaciado en `layout.css` y `components.css`; sin valores arbitrarios por página.
- **Entre páginas:** Ritmo consistente en todas las páginas.

---

## 3. Uso del espacio en blanco

- **Blanco activo:** El espacio vacío es parte del diseño. No llenar por llenar.
- **Agrupación:** El espacio en blanco separa grupos lógicos (header / hero / sección / footer).
- **Contraste:** Zonas densas (texto, listas) equilibradas con zonas abiertas (hero, entre secciones).
- **Regla:** No sacrificar espacio en blanco para "meter más contenido".

---

## 4. Relación tipografía / imagen

- **Tipografía primero:** La identidad es tipografía + color. Las imágenes apoyan, no dominan.
- **Evitar competencia:** Sin bloques donde imagen y texto compitan por prominencia.
- **Proporción:** Cuando texto e imagen comparten un bloque, definir proporción clara (50/50, 2/3–1/3) según `06-wireframes`.
- **Texto sobre imagen:** Asegurar contraste (overlay, sombra o zona sólida). Criterios en `19-accessibility-standards`.
- **Alt y contexto:** Toda imagen con alt significativo; la relación también es semántica.

---

## 5. Sistema de grid

- **Base:** Sitio organizado en un grid simple que alinea los bloques principales.
- **Función:** Coherencia horizontal entre header, contenido y footer.
- **Centro:** Contenido en un contenedor centrado y alineado (`--container-max-width: 1200px`, `--container-padding`).
- **Ancho de lectura:** 60–70ch vive dentro de ese contenedor.
- **Proporciones:** Estables al combinar texto/imagen (1/2–1/2, 2/3–1/3).
- **Regla:** Sin grids distintos por página. Un sistema para todo el sitio.
- **Responsive:** El grid se simplifica en móvil (columna única); ritmo y legibilidad preservados.

---

## 6. Comportamiento responsive

- **Regla clave:** Responsive no significa rediseñar; significa la misma experiencia con menos ancho.
- **Estructura:** Multi-columna → columna única.
- **Ancho de lectura:** Se detiene en `ch`; se vuelve fluido con márgenes cómodos.
- **Ritmo vertical:** Mantenido o aumentado para touch.
- **Orden de bloques:** Igual que wireframes; lo más importante primero.
- **Imágenes:** Se adaptan al ancho del contenedor sin recortar información esencial.
- **Interactivos:** Fáciles de tocar; espacio suficiente alrededor (~44×44px cuando sea posible).
- **Contenido:** No oculto en móvil. Mismo territorio, más estrecho.

---

## 7. Bloques editoriales

| Bloque | Principio de maquetación |
|--------|--------------------------|
| Cuerpo del artículo | Columna única, max-width 65ch, centrado |
| Hero del número | Ancho completo o contenido; portada + meta lado a lado en escritorio |
| Tarjetas de número/artículo | Grid o lista; altura de tarjeta consistente; metadatos claros |
| Caja de metadatos | Compacta; legible; sin competir con contenido principal |
| Migas de pan | Inline; espacio mínimo sobre el contenido |

---

## 8. Resumen

| Principio | Regla breve |
|-----------|-------------|
| Ancho de lectura | 60–70ch; contenedor centrado; hero puede usar ancho completo |
| Ritmo vertical | Una sola escala (`--space-*`); generoso; consistente entre páginas |
| Espacio en blanco | Activo; pausa narrativa; no llenar |
| Tipografía / imagen | Tipografía primero; sin competencia; sin drama |
| Grid | Un sistema; contenedor → lectura; sin grid por página |
| Responsive | Simplificar, no rediseñar; mismo territorio, menos ancho |

### Invariantes

Estos principios:

- Se validan en la maqueta estática
- Se mantienen en WordPress
- No se modifican por página o contenido

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
