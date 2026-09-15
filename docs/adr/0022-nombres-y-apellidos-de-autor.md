# ADR 0022: Nombres y apellidos de autor como dos atributos almacenados

## Estado

Aceptada

Sustituye [ADR 0021](0021-apellido-bibliografico-y-override-como-citar.md) **§2** (un solo `citation_surname` opcional; no given+family en aquella WU). Siguen vigentes 0021 §1 (no heurística de 4 tokens), §3–4 (override por formato y Regenerar) y §5 (Highwire / PDF / Fase 4 fuera).

## Fecha

2026-09-15

## Contexto

ADR 0021 añadió `citation_surname` opcional en el CPT `author`. El título nativo sigue siendo el nombre público. Vacío = last-token. Relleno (p. ej. `León Albino`) = apellido bibliográfico al **final** del título. Eso cubre el patrón hispano de ambos apellidos cuando coinciden con el sufijo.

No cubre dos cosas que el propietario aceptó el 2026-09-15 ([#67](https://github.com/refo44/demo-revistalogos/issues/67)):

1. **Cita más corta que el nombre público.** Título `Rafael Eduardo Figueredo Oropeza` y apellido para citar `Figueredo`: la ficha y el byline conservan Oropeza; APA debe ser `Figueredo, R.E.`, no `Figueredo, R.E.F.O.`. Hoy el splitter solo recorta un apellido que es **sufijo** del título; si va en medio, el resto se vuelve «nombre de pila».
2. **Nombres y Apellidos como ficha**, no cuatro slots hispanos. `citation_surname` permanece **opcional** (cita más corta que los apellidos de la persona).

Vol. 1 Nº 1 ya tiene varios `citation_surname` rellenos a mano. No se pide que el editor vuelva a teclearlos. Producción no tiene WP-CLI; no es `fixtures seed`. El backfill **no** corre solo en `maybe_upgrade`: si fallara a medias, no habría forma de deshacer. El propietario exige un **ajuste temporal** con restauración, retirado en el deploy siguiente (mismo patrón que `Bootstrap_Admin` en 0.2.6).

## Decisión

### 1. Título público intacto

`post_title` sigue siendo el nombre completo visible (byline, `/revista/autores/…`, Highwire `citation_author`). **No** se reconstruye a partir de Nombres + Apellidos. Un apellido omitido en la cita puede seguir en el título.

### 2. Nombres y Apellidos obligatorios; `citation_surname` opcional

Tres metas (no cuatro slots hispanos). Plugin registra y muestra (ADR 0005); el theme solo consume.

| UI (wp-admin Autores) | Meta | Obligatorio | Rol |
| --- | --- | --- | --- |
| Nombres | `given_names` (nuevo) | **Sí**, ≥1 token, en **ficha nueva** | Nombres de pila. |
| Apellido(s) | `family_names` (nuevo) | **Sí**, ≥1 token, en **ficha nueva** | Apellidos de la persona (pueden ser dos: `León Albino`). |
| Apellido(s) para citar | `citation_surname` (0021) | **No** | Si está relleno, Cómo Citar usa **este** valor. Si está vacío, usa `family_names`. |

Autores **ya existentes** no se bloquean al guardar hasta que el editor cree una ficha nueva o rellene a mano / Apply. Un monónimo (`Platón`) no encaja en «un nombre y un apellido»: queda fuera; no se relaja la regla.

No se reconstruye el título desde estos campos.

### 3. Cómo Citar

- given = `given_names` si hay; si no, palabras del título *antes* del apellido efectivo.
- surname = `citation_surname` si hay; si no, `family_names`; si no, last-token del título.
- Palabras del título *después* del apellido de la cita no entran en iniciales.

Ejemplos:

| Título (no se toca) | Nombres | Apellidos | Para citar | APA |
| --- | --- | --- | --- | --- |
| Sofía Camila León Albino | Sofía Camila | León Albino | (vacío) | `León Albino, S.C.` |
| Rafael Eduardo Figueredo Oropeza | Rafael Eduardo | Figueredo Oropeza | `Figueredo` | `Figueredo, R.E.` |
| Jairo Pérez | Jairo | Pérez | (vacío) | `Pérez, J.` |

### 4. Backfill por ajuste temporal (snapshot + restaurar)

Dos deploys etiquetados (ADR 0020). **No** se aplica al activar ni en `maybe_upgrade`.

**Deploy N** (esta WU): campos Nombres / Apellidos + Cómo Citar + un ajuste wp-admin (Ajustes → LOGO ET SPES o Tools; nonce + capability). Acciones:

1. **Snapshot** (antes de escribir): por cada `author`, guardar `given_names`, `family_names` y `citation_surname` (ausente = vacío). Un segundo Apply no pisa un snapshot ya tomado.
2. **Apply:** recorre autores. **No pisar** meta ya rellena. `family_names` vacío → copiar `citation_surname` si hay; si no, último token del título. `given_names` vacío → palabras del título *antes* de ese apellido efectivo. **`citation_surname` no se escribe** (sigue opcional, tal cual). Si Apply aborta a medias, **Restore** automático.
3. **Restore:** vuelve el snapshot (incluidos vacíos). El título no se toca. Disponible hasta el deploy N+1.

Vol. 1: Daniela / Sofía / Juan Pablo / Yahira / Ybrahim / Jairo / José Tadeo copian su `citation_surname` a `family_names` y lo conservan como «para citar» si ya estaba. Luis Felipe / José Sánchez / Iwan Mascolo reciben last-token en `family_names`; `citation_surname` sigue vacío. Nombres se deriva en todos.

**Deploy N+1:** se retiran el ajuste, la UI, la option de snapshot y el código de Apply/Restore. No queda un interruptor permanente. Quien no haya Apply en N, en N+1 ya no tiene la herramienta (relleno a mano o no se backfillea).

Sin `fixtures seed`. Sin heurística de 4 tokens. Apply re-run no pisa meta ya rellena ni el snapshot.

### 5. Fuera de esta decisión

Overrides `citation_override_*` y Regenerar (0021). Highwire, PDF, depósito Crossref. Colapso visual Cómo Citar ([#15](https://github.com/refo44/demo-revistalogos/issues/15)). FSE. Cuatro campos de antropónimo. Reconstruir el título desde los dos metas.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Seguir solo con `citation_surname` y título (0021 §2) | No hay Nombres almacenado; el splitter en medio del título sigue mal (`Figueredo, R.E.F.O.`). |
| Cuatro slots (nombre, segundo nombre?, apellido1, apellido2?) | Forma solo hispana; Crossref igual pide dos; el «citar solo el primero» sigue exigiendo otra regla. |
| Componer el título desde Nombres + Apellidos | El byline perdería Oropeza si Apellidos es solo `Figueredo` (opción A; el propietario eligió B). |
| Backfill con «dos últimas palabras = apellidos» | Misma heurística de 4 tokens rechazada en 0021. |
| Dejar el backfill a edición manual en wp-admin | El propietario no quiere reescribir el catálogo; producción no tiene WP-CLI. |
| `maybe_upgrade` automático al subir el plugin | Si falla a medias no hay Restore; el propietario exige un ajuste explícito y retirarlo en el deploy siguiente. |
| Dejar el ajuste para siempre | Un puente de una vez (como `Bootstrap_Admin`); el deploy N+1 lo borra. |
| `fixtures seed` en producción | ADR 0004; nombres demo, no editoriales. |

## Consecuencias

- `docs/03`: `given_names` y `family_names` (obligatorios en ficha nueva); `citation_surname` opcional; título nativo intacto.
- Fase 4: `given_names` → `given_name`; `citation_surname` o, si vacío, `family_names` → `surname`.
- Riesgo: título, Apellidos y «para citar» pueden divergir. Aceptado: título = público; cita = `citation_surname` o `family_names`.
- Metabox Autores: los tres campos visibles en la columna principal. Parte de la WU.
- Issue [#67](https://github.com/refo44/demo-revistalogos/issues/67). Dos FTPS: N (campos + ajuste) y N+1 (retirar el ajuste). Sin deploy implícito (ADR 0020).

## Referencias

- Issue [#67](https://github.com/refo44/demo-revistalogos/issues/67) (decisiones de propietario, 2026-09-15)
- ADR [0021](0021-apellido-bibliografico-y-override-como-citar.md) (parcialmente sustituida §2)
- ADR [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md)
- `docs/03-wordpress-content-model` §3 author
- `docs/22` Crossref `person_name`
- Vol. 1 Nº 1: [logo-et-spes.cenfiss.net/revista/numeros/vol-1-n-1/](https://logo-et-spes.cenfiss.net/revista/numeros/vol-1-n-1/)
