# Revista de Filosofía LOGO ET SPES — Estándares de Accesibilidad

**Documento único para estándares de accesibilidad**  
**Versión 1.0**

Estrategia, principios, reglas de diseño, HTML semántico, ARIA, contenido editorial, checklist de implementación y pruebas. Alineado con WCAG 2.1/2.2 Nivel AA.

**Depende de:** `01-platform-plan`, `02-corporate-identity`, `17-implementation-order`, `18-ux-ui-trends`  
**Referencia:** `07-voice-guide-microcopy-ux`, `09-ui-copy-sheet` (etiquetas, mensajes de error)

---

## 1. Estrategia

- **Compromiso:** Cualquiera puede leer, orientarse y entender el contenido sin fricción.
- **Referencia:** WCAG 2.1 Nivel AA (mínimo) o WCAG 2.2 Nivel AA (recomendado).
- **Filosofía:** La accesibilidad como parte del diseño y el contenido, no como añadido posterior.

---

## 2. Principios (WCAG)

| Principio | Objetivo |
|-----------|----------|
| **Perceptible** | La información y la UI se presentan de forma que las personas puedan percibirlas. |
| **Operable** | La UI es operable (teclado, tiempo suficiente, sin convulsiones, navegable). |
| **Comprensible** | La información y el uso de la UI son comprensibles. |
| **Robusto** | El contenido se interpreta de forma fiable por agentes de usuario y tecnología asistiva. |

---

## 3. Usuarios a considerar

| Perfil | Impacto |
|--------|---------|
| Visual | Ceguera, baja visión, daltonismo → semántica, etiquetas, alt, contraste, zoom |
| Auditivo | Sordera → subtítulos, transcripciones |
| Motor | Sin ratón, baja precisión → teclado, objetivos grandes, orden de tabulación |
| Cognitivo | Dislexia, TDAH, procesamiento lento → lenguaje claro, jerarquía simple |
| Neurológico | Fotosensibilidad, migrañas → sin parpadeos, respetar `prefers-reduced-motion` |

---

## 4. Reglas de diseño

- **Tipografía legible:** Tamaños cómodos, jerarquía clara (H1–H3).
- **Contraste:** Mínimo AA (4.5:1 texto normal, 3:1 texto grande).
- **Objetivos:** Áreas clicables adecuadas (~44×44px cuando sea posible).
- **Color:** No depender solo del color para el significado.
- **Lenguaje:** Claro y directo (alineado con `07-voice-guide-microcopy-ux`).

### Reglas mínimas (alto impacto, bajo costo)

- **Enlace saltar:** "Saltar al contenido principal" visible al enfocar (según `09-ui-copy-sheet`).
- **Idioma:** `lang` en `<html>`; consistente por página.
- **Orden de foco:** Lógico, predecible.
- **Foco visible:** Sin `outline: none` sin reemplazo (`:focus-visible`).
- **Reflujo y zoom:** Usable al 200% de zoom y 320px de ancho.
- **Landmarks:** Un `<main>` por página; header/nav/main/footer consistentes.
- **Encabezados:** Sin saltos (H1→H3); jerarquía sigue el contenido.
- **Enlaces:** Distinguibles sin depender solo del color.
- **Formularios:** Etiquetas, autocompletado, mensajes de error claros.

---

## 5. HTML y ARIA

### Base

1. **HTML semántico primero.** Usar elementos nativos (`button`, `a`, `input`, `nav`, `main`, etc.).
2. **ARIA solo** cuando HTML sea insuficiente (UI dinámica, componentes custom).
3. **No usar ARIA para arreglar markup malo.**

### Cuándo usar ARIA

- Nombre accesible cuando no hay texto visible
- Estado (expandido, seleccionado, pulsado, inválido)
- Relaciones (controls, labelledby, describedby)
- Regiones live para anuncios dinámicos

### Cuándo NO usar ARIA

- `div` con `role="button"` cuando un `button` funciona
- Roles redundantes
- `aria-hidden` en contenido enfocable
- `aria-label` cuando existe etiqueta visible
- `tabindex` positivo (1, 2, …)

### Botones de icono

Proporcionar nombre accesible: (1) texto visible, (2) texto `.visually-hidden`, (3) `aria-labelledby`, (4) `aria-label` como último recurso. Iconos decorativos: `aria-hidden="true"`, `focusable="false"`.

---

## 6. Contenido editorial

- **Encabezados:** H1–H3 ordenados; un H1 por página.
- **Texto alt:** Todas las imágenes informativas; descriptivo y conciso. Decorativas: `alt=""`.
- **Enlaces:** Texto descriptivo (evitar "aquí", "clic", "más" sin contexto).
- **Enlaces externos:** Indicar "se abre en nueva pestaña" cuando `target="_blank"`.
- **Media:** Subtítulos o transcripción para video/audio informativo.
- **PDFs:** Proporcionar resumen HTML o alternativa accesible cuando el PDF sea contenido principal (ej. normas, políticas).

---

## 7. Checklist de implementación

- [ ] Enlace saltar visible al enfocar ("Saltar al contenido principal")
- [ ] `lang` en `<html>`
- [ ] Navegación completa por teclado; orden de tabulación lógico
- [ ] Foco visible; sin `outline: none` sin reemplazo
- [ ] Usable al 200% de zoom y 320px de ancho
- [ ] Imágenes con alt apropiado
- [ ] Formularios con etiquetas reales; errores vinculados (aria-describedby, aria-invalid)
- [ ] Sin animación agresiva; respetar `prefers-reduced-motion`
- [ ] Sin contenido parpadeante
- [ ] Contraste AA en texto, enlaces, botones
- [ ] `<title>` único por página; landmarks y encabezados correctos

---

## 8. Pruebas

- **Teclado:** Tabular por todos los interactivos; sin trampas de foco.
- **Foco visible:** Claro en enlaces, botones, inputs.
- **Lighthouse:** Sin fallos críticos de accesibilidad.
- **Formularios:** Etiqueta asociada, error visible y legible.
- **Lector de pantalla (recomendado):** Probar flujos clave (nav, formulario de contacto).

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES  
**Estándar:** WCAG 2.1 / 2.2 (W3C)
