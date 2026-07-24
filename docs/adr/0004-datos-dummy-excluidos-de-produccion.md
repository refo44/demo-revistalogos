# ADR 0004: Datos dummy excluidos de producción

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

La maqueta contiene contenido **demostrativo** para validar la estructura: Vol. 12 Nº 2, números históricos Vol. 11–12, seis artículos de ejemplo, autores y noticias ficticias, e identificadores falsos (`ISSN 1234-5678`, `DOI 10.1234/les.*`, `ORCID 0000-0000-*`). ADR 0001 ya establece que ese dataset es andamiaje, no contenido. `docs/17-implementation-order` §3.1 lista lo «prohibido migrar».

Falta convertir ese principio en un régimen aplicable: qué contenido se excluye, contra qué dataset se desarrolla el theme, y **cómo** se garantiza que lo demostrativo no llegue a producción. Para una revista académica, publicar identificadores falsos (DOI/ISSN inventados) es un daño reputacional serio.

Dos hechos condicionan la decisión:

- **La primera edición se publicará pronto.** Conviene mockear esa primera edición (no todo el histórico) y sustituirla por contenido real al recibirlo.
- **El contenido de WordPress vive en la base de datos**, no en archivos. No hay «archivo que borrar»: la exclusión debe ser una operación de base de datos.

## Decisión

### 0. Alcance: contenido estático vs dinámico

Este ADR aplica **solo al contenido dinámico** (CPTs y posts). El contenido informativo estático es real y se conserva.

| Tipo | Ejemplos | Destino WordPress | Trato |
| ---- | -------- | ----------------- | ----- |
| **Estático / informativo** | Acerca, Normas, Ética, Políticas, Comité Editorial, Enlaces, Contacto, Enviar colaboración | **Páginas** | Contenido real; **se conserva aunque esté incompleto**. No es dummy. |
| **Dinámico / editorial** | Números, artículos, autores, noticias | **CPTs** (issue/article/author) + posts | Demostrativo; excluido de producción. |

Una página «Acerca» incompleta sigue siendo contenido real, no andamiaje.

### 1. Dataset de desarrollo: mock de la primera edición + stubs mínimos

- Se mockea **solo la primera edición**: un `issue` realista con la forma de la edición que llega pronto, sus `article` y sus `author`. Al recibir el contenido real, se **sustituye** (swap), no se rehace.
- No se migra el histórico demostrativo (Vol. 11–12, artículos extra).
- Para probar cualquier **listado o paginación** (archivo de números, de artículos, de autores, índice de noticias) se añade el **número mínimo** de entradas *stub* necesario para ejercitarlo, y no más. Un archivo de un solo elemento no ejercita la segunda página; los stubs cubren ese hueco.
- **Todo** mock y stub lleva los identificadores falsos (`1234-5678`, `10.1234/les.*`, `0000-0000-*`) para ser detectable, y el flag de fixture del punto §2 para ser removible.

### 2. Aplicación de la exclusión (detección + borrado limpio)

Pila de cuatro capas que se cubren mutuamente. Los identificadores falsos hacen el contenido **detectable**; el flag de fixture lo hace **removible** — son cosas distintas.

| Capa | Dónde actúa | Qué captura |
| ---- | ----------- | ----------- |
| `robots.txt` en `Disallow: /` | Serving (ambas implementaciones) | Impide indexación temprana hasta el lanzamiento |
| Validación en staging | Proceso (ambas) | Aprobación editorial antes del corte a producción |
| Grep de identificadores falsos | **Archivos estáticos / salida del theme** | Cadenas demostrativas incrustadas en archivos |
| Flag `_les_fixture` + seed/teardown | **Base de datos + uploads** | Filas demostrativas/stub, su meta, media y relaciones de término |

Mecanismo de borrado limpio (WordPress):

- Cada fixture se marca con post-meta privado `_les_fixture = 1` (y/o término de taxonomía privada `fixture`).
- Las fixtures se crean con una rutina de *seed* (comando WP-CLI o plugin de desarrollo) y se retiran con un *teardown* emparejado que elimina exactamente lo creado, **incluidos adjuntos (media), post-meta y relaciones de término**, sin dejar huérfanos.
- Purga en un solo paso antes del corte, p. ej.:
  `wp post delete $(wp post list --meta_key=_les_fixture --format=ids) --force`
- El grep de identificadores falsos corre después como **verificación** de que nada sobrevivió (una página renderizada o un export de BD sigue siendo grep-verificable).

### 3. Identificadores reales: «pendiente» honesto, nunca falso

- En producción **nunca** aparece un identificador falso (`1234-5678`, `10.1234/les.*`, `0000-0000-*`); un ID falso es exclusivo de fixtures.
- Si al cargar la primera edición un identificador aún no está asignado (ISSN, Depósito Legal, DOI, ORCID), se muestra un marcador **honesto**: «ISSN: en trámite», «DOI pendiente». No se bloquea el lanzamiento por trámites externos.
- **Restricción operativa (Venezuela):** el proceso de **Depósito Legal** exige que el sitio esté en producción un tiempo (al menos ~1 mes) antes de asignar el número. Por tanto, **lanzar con el número en trámite no es una preferencia sino la única secuencia posible**: el lanzamiento (migración terminada + contenido real, `robots.txt` abierto) ocurre primero; el número legal llega después y se **rellena (backfill)** en los metadatos del número, JSON-LD, footer y donde corresponda.
- El backfill de identificadores queda como tarea de seguimiento tras el lanzamiento.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Mockear todo el histórico demostrativo como fixtures | Más contenido dummy que amurallar y más riesgo de fuga; la primera edición llega pronto y el mock debe tener su forma, no la del histórico. |
| No mockear nada; construir solo con contenido real | Estanca la Fase 3 hasta que editorial entregue; deja plantillas sin probar (single-*, paginación). |
| Detección por grep sin borrado por flag | El grep encuentra pero no borra limpio en BD; borrar posts sin teardown deja media/meta/términos huérfanos. |
| Bloquear el lanzamiento hasta tener todos los identificadores | Imposible en Venezuela: el Depósito Legal exige estar en producción antes de asignar el número; retendría contenido terminado por trámites. |
| Placeholders falsos en producción marcados «temporales» | Publicar DOI/ISSN inventados en una revista académica es daño reputacional; «en trámite» es honesto y verificable. |

## Consecuencias

**Beneficios:**

- La Fase 3 avanza con un dataset realista y acotado (la primera edición), y el corte a real es un swap.
- Exclusión aplicada por mecanismos, no por buena voluntad: detección (grep) + borrado limpio (flag/teardown), cubriendo archivos y BD.
- El lanzamiento no queda rehén de trámites de identificadores; los marcadores «en trámite» son honestos.
- Contenido informativo real preservado aunque esté incompleto.

**Riesgos:**

- Disciplina: cada fixture debe llevar identificador falso **y** flag; una fixture sin flag no se purga automáticamente.
- El teardown debe cubrir media y relaciones o deja huérfanos; probar el teardown en staging.
- El backfill de identificadores tras el lanzamiento debe seguirse hasta cerrarse.

**Trabajo futuro:**

- Implementar seed/teardown de fixtures (WP-CLI o plugin de desarrollo) al scaffolding del theme.
- Añadir la purga de fixtures y el grep de verificación al checklist de corte (`docs/17-implementation-order`).
- Registrar el backfill de ISSN/Depósito Legal/DOI/ORCID como tarea post-lanzamiento.
- Estados de UI «en trámite» para identificadores pendientes.

## Referencias

- ADR 0001 (maqueta como base; dataset demostrativo es andamiaje)
- `docs/17-implementation-order` §3.1 (prohibido migrar), §3.2 (carga de la primera edición), checklist de lanzamiento
- `docs/03-wordpress-content-model` (CPTs y campos; ver ADR 0005)
- `robots.txt` (puerta de indexación; brazo de este ADR)
- `docs/migracion-static-wordpress.md` (ledger)
