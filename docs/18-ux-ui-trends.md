# Revista de Filosofía LOGO ET SPES — Tendencias UX/UI y Sistema Editorial

**Filtro estratégico para validación de diseño e implementación**

Qué adoptar y qué evitar: lectura, claridad, accesibilidad, rendimiento vs. personalización, ruido, invasividad.

**Referencia:** `02-corporate-identity`, `05-information-architecture-navigation`, `06-wireframes`, `07-voice-guide-microcopy-ux`, `14-css-architecture`, `19-accessibility-standards`

---

## 1. Regla de adopción

**Adoptar** lo que mejora:

- Lectura y legibilidad (principal: artículos largos, PDFs, normas)
- Claridad (institucional, académica)
- Accesibilidad (WCAG 2.1 AA mínimo; principios: perceptible, operable, comprensible, robusto)
- Rendimiento (carga rápida, sin frameworks pesados)
- Estabilidad (interfaz consistente entre números y años)

**Evitar** lo que introduce:

- Personalización innecesaria (sin dashboards, sin "para ti")
- Ruido visual (sin feeds, sin bloques de recomendación)
- Comportamiento invasivo (sin popups, sin triggers inteligentes)

---

## 2. Filosofía de diseño

| Principio | Significado |
|-----------|-------------|
| Minimalismo como estructura | No solo estético; claridad estructural. El contenido académico es el elemento dominante; el diseño debe desaparecer detrás del texto. |
| Tipografía primero | La identidad es tipografía + color. Georgia / Times New Roman para títulos; System UI stack para cuerpo (-apple-system, Segoe UI, Roboto, Arial). Las imágenes apoyan, no dominan. |
| Calma sobre impacto | Experiencia sobre golpe visual. Institucional, confiable. |
| Un solo foco por pantalla | Cada vista debe tener una acción primaria clara. |

---

## 3. Tabla de adopción

| Adoptar | Evitar |
|---------|--------|
| Tokens de diseño (`tokens.css`, `02-corporate-identity`) | Colores/tamaños hardcodeados |
| HTML semántico | Div soup |
| Foco visible | `outline: none` sin reemplazo |
| `prefers-reduced-motion` | Animaciones agresivas |
| Rendimiento primero | Frameworks pesados por defecto |
| Jerarquía clara | Grids densos, elementos que compiten |
| Jerarquía tipográfica clara | Variación tipográfica excesiva |
| Microinteracciones (funcionales: toggle nav, enlace saltar) | Movimiento por espectáculo |
| Ancho de lectura 60–70ch (optimiza lectura continua de textos largos) | Bloques de texto a ancho completo |
| Espacio en blanco generoso | Maquetaciones apretadas |

---

## 4. Opcional (evaluar por proyecto)

| Opción | Decisión para LOGO ET SPES |
|--------|----------------------------|
| Modo oscuro | Fuera de alcance. La identidad institucional es clara y el contenido principal es lectura académica. |
| Formas orgánicas | Dosis mínimas solo; preferir geometría limpia. |
| Transiciones sutiles | Sí para feedback funcional (hover, focus). Mantener bajo 250ms. |

---

## 5. Evitar como base

- Experiencias agentic (chatbots, asistentes IA)
- Popups / triggers inteligentes (modales de newsletter, exit intent)
- Movimiento "por espectáculo" (parallax, animaciones de scroll)
- Bloques de recomendación que parezcan feeds
- Carruseles sin propósito claro (si carrusel: un CTA claro, sin auto-play)
- Scroll infinito en artículos o archivos (preferir paginación estable y citable)
- Lenguaje de marketing (ver `07-voice-guide-microcopy-ux`)

---

## 6. Patrones de revista académica

| Patrón | Uso |
|--------|-----|
| Navegación estable por número | Cada número funciona como unidad editorial. |
| Metadatos claros | DOI, ISSN, volumen, número, año visibles. |
| Listo para citar | Copiar cita, descargar PDF. Sin botones "compartir" como principales. |
| Lectura lineal | Flujo vertical. Sin sidebars que compitan con el contenido. |
| Normas visibles | Enlace a Normas, Ética, Políticas desde footer y Enviar colaboración. |
| Contacto accesible | Comité Editorial, CENFISS. Sin info de contacto enterrada. |

---

## 7. Checklist para maqueta

Antes de cerrar Fase 2 (o validar antes de WordPress):

- [ ] Tokens de `02-corporate-identity` aplicados (`tokens.css`)
- [ ] Sin colores hardcodeados (usar `--color-*`, `--brand-*`)
- [ ] Foco visible en todos los interactivos (nav, enlaces, botones, campos de formulario)
- [ ] Contraste AA verificado (`--color-text-primary` sobre `--color-bg-primary`)
- [ ] Ancho de lectura 60–70ch para cuerpo del artículo (optimiza lectura continua de textos largos)
- [ ] Sin animación innecesaria; respetar `prefers-reduced-motion`
- [ ] Landmarks semánticos (header, main, footer, nav)
- [ ] Enlace saltar presente y funcional
- [ ] Voz alineada con `07-voice-guide-microcopy-ux` (sin copy de marketing)
- [ ] Jerarquía tipográfica consistente (H1–H3)
- [ ] Tiempo de carga inicial < 2 s en red estándar

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
