# Mockups Fase 1 — issue #10 (diseño editorial del PDF de artículo)

Carpeta **desechable**. No es runtime del plugin; no se commitea; se borra
al cerrar la Fase 1.

- `opcion-1-clasico-filologico.html` / `.pdf` — separata clásica centrada
- `opcion-2-institucional-cenfiss.html` / `.pdf` — masthead + cabecera corrida
- `opcion-3-contemporaneo-sobrio.html` / `.pdf` — moderno alineado a la izquierda
- `render.php` — script desechable que genera los PDF con el Dompdf **existente**
  del plugin (mismas opciones que `class-dompdf-article-pdf-renderer.php`:
  remote OFF, PHP OFF, DejaVu, A4 vertical). No cambia código del plugin.

Regenerar (Docker, ADR 0014):

```bash
docker compose run --rm --no-deps -v "$PWD/tmp/article-pdf-mockups:/mockups" wpcli php /mockups/render.php
```

Decisión del propietario (2026-08-28): el PDF es **blanco y negro / escala
de grises**. Sin `#18597c` ni ningún color; solo negros y grises neutros.

Artículo ficticio de muestra. Los identificadores `XXXX` (ISSN/DOI/ORCID)
son marcadores de posición para ubicar el campo; no son identificadores
y no deben llegar nunca a producción (ADR 0004, ADR 0013).

Hallazgos Dompdf comprobados aquí (relevantes para la Fase 2):

1. Un elemento `float` dentro de `position: fixed` (cabecera/pie corridos)
   **se filtra al flujo principal** y sangra todo el contenido; y si la línea
   flotada excede la medida, Dompdf entra en bucle de páginas (110–198
   páginas). Solución estable: maquetar cabecera/pie corridos con `<table>`
   dentro del elemento fijo, nunca con floats.
2. El titulillo corrido debe tener una regla de truncado (títulos largos
   desbordan la línea del pie).
3. `.pagenum:before { content: counter(page) }` funciona sin PHP embebido.
4. DejaVu Serif (regular/bold/italic) está empaquetada en el vendor del
   plugin: títulos serif viables.
