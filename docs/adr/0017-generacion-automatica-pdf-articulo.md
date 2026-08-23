# ADR 0017: Generación automática del PDF de artículo al publicar

## Estado

Aceptada

## Fecha

2026-08-20

## Contexto

El contrato de almacenamiento del PDF de artículo ya está fijado: `pdf_file` es un ID de adjunto de Media Library, un archivo por artículo, MIME `application/pdf` (ADR 0005 §5 / D11; `docs/03`). Hoy el flujo canónico es **manual**: el editor elige o sube el PDF. Borrador y pendiente pueden no tener PDF. Publicar un artículo exige un Author CPT publicado; **no** exige PDF. Ese es el comportamiento vigente en plugin 0.2.6 y no cambia con este ADR.

El propietario quiere, **más adelante**, que al publicar un artículo sin PDF válido el plugin genere uno a partir del contenido canónico del CPT `article`, lo guarde como adjunto normal y deje su ID en `pdf_file`. Si la generación falla, la publicación se bloquea. Un PDF válido existente (manual o ya generado) no se pisa ni se regenera al guardar.

Eso es una decisión de dominio (cuándo generar, qué bloquear, qué no mutar). No es un detalle de plantilla. Debe vivir en `revistalogos-core` (ADR 0005), ser independiente del theme clásico y del futuro FSE (ADR 0015), y no implementarse hasta que exista una Testing Foundation y se construya con TDD.

Restricciones que siguen vigentes:

- **ADR 0005:** plugin dueño del dominio; theme solo presenta; `pdf_file` no se sustituye por URL ni por un segundo campo.
- **ADR 0006:** nativo → código propio → tercero; Composer no es el flujo actual del plugin.
- **ADR 0009 / 0016:** FTPS acotado theme+plugin; sin SSH/WP-CLI usable en producción; PHP efectivo del hosting 8.0.30; LiteSpeed.
- **ADR 0014:** QA local en Docker; el portátil no tiene PHP/Composer nativos.
- **`docs/19`:** HTML accesible primero; un PDF no sustituye la lectura HTML.

## Decisión

### 1. Arquitectura aceptada; implementación aplazada

La arquitectura de este ADR está **aceptada**. Work unit 1 (2026-08-22)
escribió la política de dominio pura. Work unit 2 (2026-08-23) añadió
un adaptador WordPress de solo lectura. El producto **no** exige PDF
al publicar todavía.

**Prerrequisito duro:** una Testing Foundation en el repositorio (estrategia de pruebas, PHPUnit, ubicación y reglas BDD/Gherkin, política TDD, estrategia de integración, reglas Cursor/Claude de testing). Esta función será una de las primeras features significativas en TDD.

Hasta entonces el flujo vigente permanece: PDF opcional, subida manual, publicar sin PDF está permitido.

### 2. Capas

```text
Article CPT
  → política de publicación (plugin)
  → servicio de generación de PDF (plugin)
  → adjunto de Media Library
  → pdf_file (ID)
  → capa de presentación lee pdf_file
```

La presentación (theme clásico hoy; bloques/FSE después) **solo consume** `pdf_file`. El generador **no** depende de `single-article.php`, CSS del theme, `theme.json`, plantillas del Site Editor ni template parts. Usa una representación de impresión/PDF propia del plugin.

Migrar de classic a FSE **no** regenera PDFs, no migra valores de `pdf_file`, no sustituye adjuntos y no cambia la relación pública.

### 3. Invariante de publicación (futuro, tras implementar)

Un artículo **publicado** exigirá:

- al menos un Author CPT publicado (regla ya vigente);
- un PDF válido (`pdf_file` apunta a un adjunto existente `application/pdf`).

Borrador y pendiente pueden seguir sin PDF y **no** generan.

Al publicar:

| Situación | Acción |
| --------- | ------ |
| `pdf_file` ya apunta a un PDF válido | Conservarlo y publicar |
| No hay PDF válido | Intentar generar |
| Generación correcta | Crear adjunto, guardar su ID en `pdf_file`, publicar |
| Generación fallida | **Rechazar** la publicación; el artículo permanece draft/pending; el contenido no se altera; el editor recibe un error accionable |

Tras un fallo, el editor debe poder:

- reintentar la generación automática;
- seleccionar o subir un PDF válido a mano.

La UI (`Intentar generar PDF` / `Seleccionar PDF manualmente`) se diseña en la implementación, no ahora.

La misma política aplica a wp-admin y a REST/Gutenberg. La decisión de «¿hay que generar?» es lógica de dominio; los hooks de WordPress son adaptadores delgados. Gutenberg puede guardar REST y luego el metabox: la decisión es **idempotente** (si ya hay PDF válido, no generar).

### 4. Qué no se hace nunca (v1 y upgrade)

- No pisar en silencio un PDF válido (manual o generado).
- No regenerar un PDF válido solo porque se edita o guarda el artículo.
- No generar en masa ni rellenar `pdf_file` al activar, actualizar o cargar el plugin.
- No despublicar artículos ya publicados que hoy no tienen PDF.
- No borrar adjuntos al desvincular o al sustituir (el editor quita la relación; el archivo permanece, igual que ahora).
- No cambiar el permalink del artículo porque exista o se sustituya un PDF.

La regla dura de PDF se aplica a **acciones futuras de publicación relevantes**, no como migración de upgrade.

Los PDF válidos actuales, **incluidos los placeholders de bootstrap**, cuentan como PDF existente hasta que un editor los sustituya. Este ADR no inventa procedencia (`_les_pdf_origin`, hash) para v1.

### 5. Almacenamiento

Sin cambio de contrato respecto a ADR 0005 §5:

- clave `pdf_file`;
- un entero;
- un adjunto de Media Library;
- MIME `application/pdf`.

La generación crea un adjunto **normal**. La subida manual sigue siendo el fallback.

La fuente canónica del PDF **generado** es el contenido WordPress del artículo (título, cuerpo, autores, metadatos ya existentes). Eso no reescribe la carga editorial actual: hasta implementar, el editor sigue subiendo el PDF de la edición (papel/digital) a mano (`docs/17` §3.2).

### 6. Accesibilidad

El HTML del artículo sigue siendo la representación accesible principal. El PDF generado es una **alternativa descargable**. Este ADR **no** afirma conformidad PDF/UA ni WCAG del archivo generado: las librerías PHP habituales no lo garantizan. `docs/19` sigue gobernando el HTML.

### 7. Fuera de alcance de v1

- PDF integral del número (portada + TOC + editorial + artículos).
- Regeneración automática al editar.
- Colas / generación asíncrona.
- Historial de versiones de PDF.
- Plantillas o tipografía configurables por el editor.
- Servicio de render externo o Chromium.
- Metadatos de procedencia (`_les_pdf_origin`, `_les_pdf_source_hash`) salvo que una regla posterior de regeneración los exija.

Un compositor futuro de PDF de número puede leer artículos y `pdf_file` **sin** cambiar este contrato.

### 8. Elección de librería — aplazada a la implementación

No se elige Dompdf, mPDF ni TCPDF en este ADR. Es una decisión de **implementación** posterior a la Testing Foundation, gobernada por ADR 0006 y por el empaquetado FTPS (ADR 0009): no hay flujo Composer hoy; un `vendor/` tendría que versionarse o cambiar esa política en un ADR nuevo.

Restricciones que la implementación deberá cumplir:

- cabecera actual del plugin `Requires PHP: 7.4`; producción más nueva;
- Unicode / español;
- salida lo bastante determinista para tests;
- viable en LiteSpeed sin SSH;
- representación de impresión propia del plugin, no el CSS del theme.

### 9. Costuras para TDD (dirección, no nombres de clase)

- política pura: ¿hace falta generar?;
- renderer sustituible;
- almacén de adjuntos sustituible;
- adaptadores finos al producto de WordPress;
- el mismo dominio en REST y en `save_post`;
- idempotencia ante el doble guardado Gutenberg.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| PDF opcional también tras implementar (publicar sin archivo; aviso) | El propietario exige PDF en todo artículo publicado; un fallo no puede dejar el registro público sin archivo. |
| Generar en segundo plano tras publicar | Publicaría sin PDF; exige cola/cron en un hosting sin SSH (ADR 0016); peor para TDD y para el editor. |
| Generar en el theme / al renderizar `single-article` | Rompe ADR 0005; ataría el PDF al classic theme y rompería FSE. |
| Regenerar en cada guardado | Peligro de pisar un PDF editorial; no es el requisito. |
| Backfill al actualizar el plugin | Mutaría producción (placeholders, artículos sin PDF, permalinks/adjuntos). Prohibido. |
| Implementar ahora, sin Testing Foundation | El propietario fija TDD y una base de pruebas **antes** de esta feature. |

## Consecuencias

**Beneficios:**

- Un solo contrato público (`pdf_file`) para manual, generado, classic y FSE.
- Publicar no deja artículos sin PDF cuando la feature exista.
- El editor conserva el escape manual.
- La migración FSE no es un proyecto de PDFs.

**Riesgos / costes:**

- Hasta implementar, **sí** se puede publicar sin PDF (comportamiento actual).
- Un generador síncrono puede fallar por memoria/tiempo; el bloqueo de publicación es deliberado.
- Un PDF generado desde HTML no reproducirá la paginación del PDF impreso; la coincidencia bibliográfica con el papel sigue siendo responsabilidad editorial cuando el editor sube el PDF de la edición.
- Añadir una librería PHP exigirá justificar ADR 0006 y el empaquetado.

**Trabajo futuro:**

1. Testing Foundation (prerrequisito). **Cubierto** el 2026-08-20 por ADR 0018 / `docs/23-testing-foundation.md`.
2. Implementar en `revistalogos-core` con TDD, sin cambiar el contrato de `pdf_file`. **WU1** (2026-08-22): política de dominio pura. **WU2** (2026-08-23): adaptador WordPress de solo lectura. **Aún no:** renderer, adjunto de Media Library, orquestación en hooks/REST, UI de error, regla de publicación activa.
3. Elegir librería en una unidad posterior (no en la política pura).
4. PDF de número: ADR o ítem de backlog aparte.

Comportamiento de negocio a preservar: publicar sin PDF genera uno; un PDF válido se conserva; un fallo bloquea la publicación; el editor puede adjuntar a mano; draft/pending no genera; guardar un publicado no regenera; el upgrade no genera; el permalink no cambia; FSE no altera `pdf_file`; no se borra el adjunto al desvincular. WU1 cubre la política pura y `tests/Features/article-pdf-generation.feature` (sin Behat). WU2 cubre el adaptador de solo lectura (`tools/qa-article-pdf-adapter.sh`).

## Referencias

- ADR 0005 (plugin dueño del dominio; PDFs en Media Library)
- ADR 0006 (dependencias)
- ADR 0009 (FTPS acotado)
- ADR 0013 (DOI/ORCID inertes en Fase 3; no bloquean este ADR)
- ADR 0014 (Docker local)
- ADR 0015 (FSE; bloques de dominio en el plugin; presentación consume datos)
- ADR 0016 (cPanel, sin SSH)
- `docs/03-wordpress-content-model` (`pdf_file`)
- `docs/17-implementation-order` (carga editorial actual sigue siendo PDF manual)
- `docs/19-accessibility-standards`
- `docs/adr/BACKLOG.md` (D17)
