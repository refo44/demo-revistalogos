# ADR 0021: Apellido bibliográfico y override opcional de Cómo Citar

## Estado

Aceptada

## Fecha

2026-09-15

## Contexto

La caja **Cómo Citar** de `single-article` (APA, BibTeX, Vancouver, Chicago, MLA, Harvard y el archivo RIS) se **calcula al vuelo**. No hay meta persistida. El nombre sale del título del CPT `author` vía `revistalogos_split_name()`: **última palabra = apellido**, el resto = nombres e iniciales.

Esa heurística coincide con nombres de un apellido (`Smith, J.`; el monónimo `Platón`) y con el indexado anglosajón / Web of Science. **Falla el patrón hispano** (dos nombres + dos apellidos): `Ana María Pérez Gómez` se cita `Gómez, A.M.P.` en lugar de `Pérez Gómez, A. M.`. Editar el título del autor en wp-admin actualiza la cita **y** el nombre público; no se puede reordenar el título para «arreglar» la cita sin romper el byline y `/revista/autores/…`.

`docs/03` marcaba `article.citation_format` como solo *computed*. Un autor reportó el error en producción. Issue de análisis: [#64](https://github.com/refo44/demo-revistalogos/issues/64).

Investigación de estilos (comentario en #64, 2026-09-15): APA 7 (§§9.8–9.9 / blog APA Style), MLA Handbook, Chicago (nombres españoles / índice 18.82), NLM *Citing Medicine* / Vancouver, Harvard en variantes ES, ISO 690, RAE (alfabetización de antropónimos) y el parseo correcto de BibTeX/RIS **coinciden** en el apellido hispano: **ambos** apellidos como unidad bibliográfica, invertidos por el **primer** elemento; las iniciales son **solo de pila**. Difieren en el resto de la frase (iniciales vs nombre, `vol.`/`no.`, puntuación), que el builder ya ramifica por formato. Las normas de la revista (`page-normas`) piden «todos los nombres y apellidos» y el formato «Apellidos, Nombres».

Un fallo distinto: el **artículo está bien** (título, DOI, páginas, slug, autores) y **una caja** (p. ej. solo MLA) arma mal un trozo que **no** es el autor. Eso no se corrige editando esos campos ni esperando un deploy. Hace falta un override de **esa** frase.

No se relitiga ADR 0005 (plugin = dominio, theme = presentación), ADR 0013 (DOI/ORCID Fase 4), ADR 0017 (PDF) ni el spike de colapso de la sección ([#15](https://github.com/refo44/demo-revistalogos/issues/15)).

## Decisión

### 1. El builder cambia; un apellido no se rompe

El apellido bibliográfico hispano (ambos) alimenta **todos** los formatos, cada uno según su estándar. Ejemplo con título `Ana María Pérez Gómez` y apellido bibliográfico `Pérez Gómez`:

| Formato | Forma |
| ------- | ----- |
| APA / Harvard | `Pérez Gómez, A. M.` |
| Vancouver | `Pérez Gómez AM` |
| MLA / Chicago | `Pérez Gómez, Ana María` |
| BibTeX / RIS | `Pérez Gómez, Ana María` |

**Un solo apellido sigue la heurística actual** (última palabra = apellido). `Juan Smith` y `Platón` no exigen campo extra. **No** se asume «si hay cuatro palabras, las dos últimas son apellidos» (rompería `Mary Jane Smith` y equivalentes).

### 2. Un campo opcional en el CPT `author`

Meta `citation_surname` (un campo; no `given_name` + `family_name` en esta WU). Etiqueta wp-admin: apellido(s) para citar. Vacío = last-token (As-Is, compatibilidad y un apellido). Relleno (p. ej. `Pérez Gómez`, `Pérez`, `Pérez-Gómez`, `de la Cruz`) = esa cadena es el surname de **todos** los formatos. El título nativo sigue siendo el nombre completo público. Plugin: `register_post_meta` + metabox Autores (ADR 0005). El theme solo consume.

### 3. Override opcional por formato en el CPT `article`

Siete metas, una por caja pública más RIS, p. ej. `citation_override_apa`, `citation_override_bibtex`, `citation_override_vancouver`, `citation_override_chicago`, `citation_override_mla`, `citation_override_harvard`, `citation_override_ris`.

- **Ausente o vacío** = el builder (con `citation_surname` si existe). Un save **no** escribe override. Sin backfill.
- **Relleno** = la web (o el RIS) muestra ese texto. Las otras cajas no se tocan.
- Sirve cuando el artículo está bien y solo una frase de Cómo Citar está mal en un trozo que **no** es el autor.
- Título, DOI, páginas y permalink se siguen corrigiendo en **sus** campos. Parchear solo la cita y no el dato hace divergir ficha y caja.

### 4. Regenerar uno por uno

Cada formato tiene su Regenerar. Borra **ese** override (vacía el meta). Esa caja vuelve al contenido automático. No se copia el texto generado al meta (eso congelaría otra vez la cita). Reset de MLA no borra APA.

### 5. Fuera de esta decisión

Highwire `citation_author` sigue el título completo del autor (Google Scholar). PDF de artículo, colapso visual de Cómo Citar, depósito Crossref y validación DOI/ORCID no cambian aquí. Fase 4 podrá leer `citation_surname` como `person_name/surname` cuando exista.

### 6. Implementación

Issue [#64](https://github.com/refo44/demo-revistalogos/issues/64). Plugin
`revistalogos-core` 0.2.19 (meta + metabox + REST) y theme `revistalogos`
0.2.11 (builder + overrides). TDD: el test de last-token **permanece**;
casos nuevos para `citation_surname` relleno y override por formato.
Gutenberg del CPT `article` según el playbook de [#30](https://github.com/refo44/demo-revistalogos/issues/30)
/ [#35](https://github.com/refo44/demo-revistalogos/issues/35). Sin deploy
implícito (ADR 0020).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Solo reordenar el título del autor | Rompe el nombre público. |
| Heurística «4 tokens = 2 apellidos» | Rompe dos nombres de pila + un apellido; portugueses; partículas. |
| Default `Pérez, A. M.` (solo primer apellido) | No es APA, MLA, Chicago, Vancouver/NLM, ISO 690 ni las normas de la revista. |
| Solo override de texto, sin campo de apellido | El reporte hispano exigiría reescribir hasta 7 cajas en cada artículo de esa persona. |
| Solo campo de apellido, sin override | No cubre «el artículo está bien, solo MLA (u otro) está mal» sin esperar código. |
| Persistir las siete citas en cada save | Congela título/DOI/páginas; contradice override **opcional**. |
| Dos campos given + family en el autor | Mismo resultado en Cómo Citar; más ficha. Crossref puede reutilizar `citation_surname` en Fase 4. |
| Un Regenerar global | Pisaría overrides de formatos que el editor quiso conservar. |
| Override solo en un subconjunto (p. ej. APA+MLA) | MLA era un ejemplo; el mismo fallo puede salir en cualquiera de las 7. |
| Editar Cómo Citar en la ficha Autores | La cita es del artículo; el nombre bibliográfico sí es de la persona (campo §2). |

## Consecuencias

- `docs/03`: `citation_surname` en `author`; las siete `citation_override_*` en `article`; `citation_format` pasa a computed **salvo** override.
- El theme deja de ser la única fuente de la frase cuando hay override; el split con apellido relleno vive en lógica de citación alimentada por meta del plugin.
- Riesgo: overrides desactualizados si cambian páginas/DOI y el editor no pulsa Regenerar en esa caja. Aceptado: es el precio del parche manual.
- La misma persona puede citarse distinto en dos artículos si solo uno tiene override de nombre (el arreglo canónico del hispano es `citation_surname`, no el override).
- Gutenberg: siete metas nuevas en `article` (el CPT del bug #30). Implementación debe sincronizar metabox → REST.
- Issue #64 permanece abierto hasta que esta WU aterrice en `main`; este ADR no la cierra por sí solo.

## Referencias

- Issue [#64](https://github.com/refo44/demo-revistalogos/issues/64) (análisis y decisiones de propietario, 2026-09-15)
- `docs/03-wordpress-content-model` §3 article / author
- ADR [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) (plugin = dominio)
- ADR [0013](0013-identificadores-academicos-doi-orcid.md) / `docs/22` (Fase 4; `surname` Crossref)
- Theme `inc/citations.php`; `tests/Unit/SplitNameTest.php`
- `page-normas` (sistema mixto APA 7; «todos los nombres y apellidos»)
- Spike colapso Cómo Citar: [#15](https://github.com/refo44/demo-revistalogos/issues/15) (independiente)
