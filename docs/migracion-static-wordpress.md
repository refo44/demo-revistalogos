# Ledger de migración static ↔ WordPress

Registro de las correcciones **semánticas, de accesibilidad o de SEO técnico**
aplicadas durante la migración (carve-out de ADR 0002 §4 y ADR 0003 §2). Cada
corrección no trivial debe: aplicarse a WordPress, retro-portarse a `static/`,
citarse aquí con su fuente vinculante y validarse en ambas versiones.

Las correcciones que alteren diseño visual o estructura de bloques están
prohibidas; ante ambigüedad, se aplaza solo la corrección afectada.

## Entradas

| # | Fecha | Corrección | Alcance (WP / static) | Fuente vinculante | Validación |
| - | ----- | ---------- | --------------------- | ----------------- | ---------- |
| 1 | 2026-07-31 | El único `<script>` inline del sitio (herramientas de cita de `single-article`) se extrae a un archivo: `static/assets/js/citation.js` (verbatim) y `themes/revistalogos/assets/js/citation.js` (versión data-driven equivalente, sin datos demo incrustados). Prerrequisito de la futura CSP sin `'unsafe-inline'`. Sin cambio visual ni de estructura de bloques. | WP: theme encola `citation.js` solo en `single-article`. Static: `<script src="assets/js/citation.js">` reemplaza el bloque inline. | ADR 0012 §5 (checklist pre-auditoría, ítem 1) | Diff revisado; comportamiento JS idéntico (copiar/exportar/RIS). Runtime: `Unverified` (sin navegador contra WP). |
