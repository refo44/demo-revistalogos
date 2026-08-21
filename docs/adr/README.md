# Architecture Decision Records (ADR)

Registro de decisiones técnicas y estructurales del proyecto **Revista de Filosofía LOGO ET SPES** (CENFISS — Centro de Estudios Filosóficos y Sociales).

Los ADR capturan el **contexto**, la **decisión** y las **consecuencias** de elecciones que podrían cuestionarse o revertirse meses después sin recordar por qué se tomaron. Los documentos numerados de `docs/` explican *cómo* se construye el sitio; los ADR explican *por qué* se decidió construirlo así.

---

## Cuándo crear un ADR

Crear un ADR cuando una decisión:

- afecte la arquitectura, el despliegue, la seguridad o la estructura de contenido;
- implique elegir entre varias alternativas razonables;
- establezca una restricción que deba mantenerse en fases posteriores (p. ej. invariantes de la migración a WordPress);
- pueda resultar difícil de entender sin conocer su contexto original.

No crear un ADR para detalles de implementación reversibles sin coste ni para lo que ya explica un documento numerado.

---

## Estados

| Estado | Significado |
| ------ | ----------- |
| **Propuesta** | En discusión; aún no vincula al proyecto. |
| **Aceptada** | Decisión vigente; la implementación y la documentación deben alinearse con ella. |
| **Rechazada** | Alternativa descartada; se conserva como registro histórico. |
| **Sustituida** | Reemplazada por un ADR posterior; enlazar ambos. |
| **Obsoleta** | Ya no aplica al contexto actual; conservar por trazabilidad. |

**Regla de inmutabilidad:** un ADR **aceptado** no se edita para cambiar su sentido. Si la arquitectura cambia, se crea un **nuevo** ADR que sustituye al anterior, se actualiza el estado del previo a **Sustituida** y se enlazan de forma bidireccional.

---

## Formato

Cada ADR es un archivo Markdown numerado:

```text
docs/adr/NNNN-titulo-en-kebab-case.md
```

Usar `TEMPLATE.md` como punto de partida. Estructura mínima: Estado · Fecha · Contexto · Decisión · Alternativas consideradas · Consecuencias · Referencias.

---

## Índice

| ADR | Título | Estado |
| --- | ------ | ------ |
| [0001](0001-maqueta-estatica-como-base-definitiva.md) | Maqueta estática como base definitiva | Aceptada |
| [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) | WordPress como adaptación sin rediseño | Aceptada |
| [0003](0003-css-y-tokens-invariantes-en-la-migracion.md) | CSS y tokens invariantes en la migración | Aceptada |
| [0004](0004-datos-dummy-excluidos-de-produccion.md) | Datos dummy excluidos de producción | Aceptada |
| [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) | Modelo de contenido — CPTs, taxonomías y plugin propio | Aceptada |
| [0006](0006-politica-de-dependencias-de-plugins.md) | Política de dependencias de plugins de terceros | Aceptada |
| [0007](0007-layout-del-monorepo-static-y-wordpress.md) | Layout del monorepo — static/ y wordpress/ | Aceptada |
| [0008](0008-politica-de-urls.md) | Política de URLs | Aceptada |
| [0009](0009-mecanismo-y-alcance-del-despliegue.md) | Mecanismo y alcance del despliegue | Aceptada |
| [0010](0010-formulario-de-contacto.md) | Formulario de contacto (Contact Form 7) | Aceptada |
| [0011](0011-analitica-y-privacidad.md) | Analítica y privacidad | Aceptada |
| [0012](0012-cabeceras-de-seguridad.md) | Cabeceras de seguridad HTTP | Aceptada |
| [0013](0013-identificadores-academicos-doi-orcid.md) | Identificadores académicos — DOI y ORCID | Aceptada |
| [0014](0014-entorno-local-de-desarrollo-docker.md) | Entorno local de desarrollo WordPress con Docker (`wordpress:7.0.4-php8.2-apache`) | Aceptada |
| [0015](0015-block-theme-fse-site-editor.md) | Block theme, Gutenberg y Site Editor | Aceptada |
| [0016](0016-topologia-hosting-cpanel.md) | Topología de hosting (cPanel cenfiss2) | Aceptada |
| [0017](0017-generacion-automatica-pdf-articulo.md) | Generación automática del PDF de artículo al publicar | Aceptada |

> El backlog de decisiones a resolver se lleva en `docs/adr/BACKLOG.md`. Cada decisión resuelta añade su fila a esta tabla y se retira del backlog.

---

## Relación con otros documentos

- **`17-implementation-order`:** orden de fases y criterios de cierre; los ADR fijan sus invariantes.
- **`docs/` numerados:** guías de implementación; deben respetar los ADR en estado **Aceptada**.
- **`docs/adr/BACKLOG.md`:** lista de decisiones pendientes de convertir en ADR.

La implementación y la documentación general del proyecto deben mantenerse alineadas con los ADR en estado **Aceptada**.

---

**Proyecto:** Revista de Filosofía LOGO ET SPES
