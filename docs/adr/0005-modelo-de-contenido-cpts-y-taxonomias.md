# ADR 0005: Modelo de contenido — CPTs, taxonomías y plugin propio

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

`docs/03-wordpress-content-model` (v1.1) define un modelo de contenido detallado: tipos de entrada, CPTs, taxonomías, campos, roles y permalinks. Falta ratificarlo como decisión vinculante y resolver lo que el documento deja abierto: **cómo se implementan los campos personalizados** y **dónde viven las definiciones de contenido**, bajo dos restricciones del proyecto.

**Restricciones:**

- **Presupuesto:** no hay presupuesto más allá del hosting y el dominio ya pagados. **Ningún plugin ni servicio de pago.**
- **Preferencia de propiedad:** ante la duda, código propio antes que dependencia de terceros; los subsistemas que requieran desarrollo se construyen como plugin propio.
- **Alcance temporal:** la primera edición llega pronto y la cargan los editores directamente; el sistema de envíos de autores no es necesario para publicarla (ver ADR 0004).

## Decisión

### 1. Se ratifica el modelo de contenido de `03-wordpress-content-model`

Tipos: `page`, `post`, y CPTs `issue` (numeros), `article` (articulos), `author` (autores), `submission` (envios, privado). Taxonomías: `section` (jerárquica), `article_type`, `keyword`; `philosopher` opcional/aplazada. Regla dura: **un envío no es un artículo**; un envío aceptado genera un artículo publicado. «Número actual» y conteos son **derivados, no almacenados**.

### 2. Campos personalizados: nativos, sin plugin de terceros

Los campos custom se implementan con **WordPress nativo** (`register_post_meta` + meta boxes mínimos o el panel de campos del editor de bloques). **No** se usa ACF ni ningún plugin de campos, ni de pago ni gratuito.

- La relación muchos-a-muchos `article ↔ author` y `article → issue` se resuelve con post-meta que almacena IDs (equivalente nativo de un campo de relación).
- Se prioriza Title/Content/Featured image nativos; los campos custom complementan.

### 3. Las definiciones de contenido viven en un plugin propio: `revistalogos-core`

Los CPTs, taxonomías, roles y campos se registran en un **plugin propio `revistalogos-core`**, no en el tema.

- Motivo: el contenido y sus tipos deben **sobrevivir a un cambio de tema**. CPTs en el tema desaparecen del admin si el tema se cambia o rehace; en un plugin persisten.
- El **tema** queda solo con presentación (plantillas, CSS/JS), coherente con ADR 0002/0003.
- Esto **ajusta** `docs/12-theme-file-structure` §8: `inc/cpt-*.php` se trasladan a `revistalogos-core`; el `inc/` del tema conserva solo helpers de plantilla (`template-tags.php`).
- El plugin se versiona en el repositorio junto al tema (su despliegue se define en el ADR de despliegue, D8).

### 4. Alcance por fases

- **Fase 3 (ahora):** modelo de **contenido publicado** — `issue`, `article`, `author`, taxonomías, campos, metadatos académicos (Google Scholar/Crossref), rol **Managing Editor** custom (para no chocar con el "Editor" nativo). Suficiente para que los editores carguen y publiquen la primera edición.
- **Fase posterior (aplazada):** subsistema de **envíos y portal de autor** — CPT `submission`, login/registro, rol `Author`, subida de manuscrito + versión anonimizada, flujo de 7 estados, paneles de editor. Se construirá como **plugin propio** (extendiendo `revistalogos-core` o como segundo plugin). No bloquea el lanzamiento de la primera edición.

### 5. Ratificaciones puntuales

- **`author` como CPT** (no usuarios de WordPress): perfiles públicos reutilizables, archivos de autor, relación muchos-a-muchos. Un autor *acreditado* (`author`) y un autor *remitente* (usuario WP en `submission.author_user`, fase posterior) son registros distintos; la duplicación es deliberada.
- **PDFs → Media Library** (número completo y PDF por artículo). **Cierra el punto D11** del backlog (no se usa `assets/pdf/` del tema para contenido editorial).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| ACF Pro | De pago; fuera de presupuesto. |
| ACF free | Gratuito y cubriría el modelo (campos Relationship/Post Object son free), pero añade dependencia de terceros con gobernanza incierta (cambio de propiedad y fork en WP.org, 2024); para una revista que debe ser estable y citable durante años, se prefiere código propio. |
| Meta Box / Pods / CarbonFields | Misma objeción de dependencia de terceros; sin ventaja decisiva sobre nativo para este modelo acotado. |
| CPTs en el tema (`inc/`, según doc 12 §8) | El contenido no sobreviviría a un cambio de tema; las definiciones deben ir en plugin. |
| Construir el sistema de envíos ahora | Subsistema grande no requerido por la primera edición; estancaría la Fase 3 y retrasaría el arranque del plazo de Depósito Legal (ADR 0004). |
| `author` como usuarios de WordPress | Los autores acreditados no son cuentas; mezclarlo con remitentes complica permisos y archivos públicos. |

## Consecuencias

**Beneficios:**

- Cero coste añadido y cero dependencia de terceros para el núcleo de contenido; todo propiedad del proyecto.
- El contenido sobrevive a cambios de tema (definiciones en plugin).
- Fase 3 acotada al contenido publicado: la primera edición puede lanzarse antes.
- Separación limpia tema (presentación) / plugin (contenido), alineada con ADR 0002/0003.

**Riesgos / costes:**

- Más código a mano: meta boxes y guardado/validación de campos sin la UX de ACF. Mitigable con helpers propios reutilizables.
- Mantener `revistalogos-core` es responsabilidad del proyecto (sin comunidad de plugin detrás).
- El sistema de envíos aplazado debe planificarse explícitamente para no quedar olvidado.

**Trabajo futuro:**

- Scaffolding de `revistalogos-core` (CPTs, taxonomías, rol Managing Editor, `register_post_meta`, meta boxes).
- Metadatos académicos (`citation_*`) para Google Scholar en `single-article`.
- Actualizar `docs/12-theme-file-structure` §8 (CPTs → plugin) y `docs/03-wordpress-content-model` (nota de implementación nativa + plugin).
- Planificar la fase de envíos/portal de autor como plugin propio.

## Referencias

- `docs/03-wordpress-content-model` (v1.1) — modelo ratificado
- `docs/12-theme-file-structure` §8 (ajustado: CPTs a plugin)
- ADR 0002, ADR 0003 (tema = presentación)
- ADR 0004 (datos dummy; primera edición; Depósito Legal)
- Backlog D8 (despliegue del plugin propio), D11 (PDFs → Media Library, cerrado aquí)
