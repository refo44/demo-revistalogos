# ADR 0019: Proteger `main` con ruleset y trunk-based ligero

## Estado

Aceptada

## Fecha

2026-08-24

## Contexto

`main` era la rama por defecto y recibía **push directo**. No había
protección clásica ni [conjunto de reglas](https://docs.github.com/es/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/about-rulesets).
GitHub mostraba el aviso *Your main branch isn't protected*. Quien tenía
write (`refo44` admin, `cenfissclases-svg` write) podía force-pushear o
borrar `main`.

El repositorio es **público** (`refo44/demo-revistalogos`). Los rulesets de
**rama** están disponibles en repos públicos con GitHub Free. Los rulesets
de **inserción** (tamaño/ruta de archivo) no aplican aquí: son de plan Team
sobre repos privados/internos, y no hacen falta.

Hoy hay **un solo desarrollador activo**, pero el código también pertenece
a los colaboradores con write. Si el desarrollador activo pierde el acceso,
ellos deben poder seguir. Ellos no deben poder bloquearle, ni él a ellos.
GitFlow (`develop`, ramas de release largas) no aplica. El modelo es
[Trunk-Based Development](https://trunkbaseddevelopment.com/): un solo
trunk, ramas cortas, CI antes de integrar. Los nombres de rama siguen
[Conventional Branch 1.1.0](https://conventionalbranch.org/) y los
mensajes de commit [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/),
ambos como **convención de proceso**, no como regla de GitHub.

`main` es el trunk y debe permanecer desplegable. Producción sigue
**manual** (`workflow_dispatch`, ADR 0009); GitHub Pages sigue publicando
el espejo estático en cada llegada a `main` (deliberado). El workflow
`Tests` (`.github/workflows/test.yml`) ya corre en PR y en push a `main`.
El check que GitHub reporta se llama
`PHP lint, Composer audit, and unit (PHP 8.3)`.

Este ADR no relitiga ADR 0009.

## Decisión

### 1. Trunk-Based Development (equipo pequeño)

Un solo trunk: `main`. Integración por [ramas cortas + PR](https://trunkbaseddevelopment.com/short-lived-feature-branches/)
(CI y registro del cambio **antes** de aterrizar). No se commitea directo
a `main`: el ruleset lo impide para todos por igual.

- Sin `develop`. Conventional Branch lista `develop` como nombre de trunk;
  aquí **no se crea**. El único trunk es `main`.
- Sin ramas de release largas. Se publica **desde el trunk** (ADR 0009:
  `workflow_dispatch` eligiendo `main`). `release/` del spec existe; no
  se usa como GitFlow.
- Una rama = un cambio pequeño; se mergea en cuanto CI está verde.

### 2. Nadie bloquea a nadie; nadie es irreemplazable en el merge

El candado es el **check de Tests**, no una persona.

- Approvals de GitHub = **0**. Ni CODEOWNERS, ni «el último que pusheó
  debe ser aprobado por otro», ni un número de reviews que deje el merge
  a merced de un revisor ausente.
- Quien tenga **write** abre un PR, espera CI verde y mergea. El admin
  (`refo44`) y el colaborador write recorren **el mismo camino**. Si uno
  pierde el acceso, el otro sigue.
- Un *Request changes* se puede descartar con permiso write (comportamiento
  de GitHub). No se añade ninguna regla que lo convierta en veto permanente.
- Lista de omisión del ruleset **vacía**. Nadie pushea directo a `main`,
  tampoco el administrador. Emergencia: desactivar o borrar el ruleset,
  no un bypass permanente para una cuenta.
- **No** se subirá a 1 approval «cuando haya un segundo autor». Eso
  permitiría que uno bloquee al otro y dejaría el repo parado si queda
  una sola persona.

Ajustar *Settings* del repo (ruleset, secretos, Pages) sigue exigiendo
admin. Eso no impide mergear código. Trasladar el repo a una organización
si la cuenta personal desaparece es un asunto de GitHub, fuera de este ADR.

### 3. Conventional Branch 1.1.0 (convención, no ruleset)

Estructura: `<type>/<description>`. Minúsculas, números, guiones; sin
guiones ni puntos seguidos, iniciales ni finales; sin espacios ni
guiones bajos.

**Prefijos de propósito** (preferidos: describen el trabajo):

`feat/` o `feature/` · `fix/` o `bugfix/` · `hotfix/` · `chore/`

**Prefijos de agente** (permitidos; [v1.1.0](https://conventionalbranch.org/)):
`cursor/`, `ai/`, `claude/`, `copilot/`, `codex/`. Preferir el prefijo de
propósito cuando el tipo de trabajo sea claro (`chore/protect-main-ruleset`,
no `cursor/protect-main-ruleset`).

**Excepción documentada:** Dependabot usa `dependabot/…`. No está en la
gramática del spec; se acepta y **no** se restringe por nombre en GitHub
(un patrón `feat/*` rompería esos PRs).

No se instala `commit-check` ni `commit-check-action` en esta unidad
(YAGNI; Dependabot). Tampoco se añade una regla de metadatos de rama al
ruleset.

### 4. Conventional Commits 1.0.0 (convención, no linter)

Formato: `<type>[optional scope]: <description>` según
[Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/).
Cuerpo opcional (el *por qué*) tras una línea en blanco. `BREAKING CHANGE:`
en footer, o `!` tras el tipo/ámbito.

**Tipos de este repo:** `feat`, `fix` (significado del spec); además
`docs`, `chore`, `refactor`, `ci`, `test`, `perf`, `revert` (conjunto
Angular / `@commitlint/config-conventional`, ya usados en el historial).
Ámbito opcional, en minúsculas, p. ej. `feat(plugin):`, `docs(adr):`.
Descripción en minúsculas, imperativo, sin punto final.

Esto **no** dispara un bump automático de SemVer ni un CHANGELOG generado.
Las versiones de plugin/theme/`VERSION.md` siguen siendo explícitas.

No se instala commitlint ni se añade `commit_message_pattern` al ruleset
(rompería `Merge pull request #N` y no es el candado; el candado es Tests).
Dependabot ya suele emitir `chore(deps): …`; se acepta.

**Merge preferido: squash**, para que en `main` quede un solo mensaje
convencional por PR (el FAQ del spec lo recomienda). Merge commit y rebase
siguen permitidos en GitHub; el botón *Create a merge commit* no produce
un asunto Conventional Commits y se evita cuando se pueda.

### 5. Ruleset de rama

No protección clásica (y no ambos: se apilarían y ganaría la versión más
restrictiva). Nombre: `Protect main (trunk-based)`. Destino:
`refs/heads/main`. Cumplimiento: **Activo**.

Reglas:

- bloquear borrado de `main`;
- bloquear force-push (`non_fast_forward`);
- exigir Pull Request; approvals = **0**;
- exigir el check `PHP lint, Composer audit, and unit (PHP 8.3)`
  (GitHub Actions, `integration_id` 15368);
- **no** exigir que la rama esté al día con `main` al inicio.

GitHub activa por defecto *Require an additional approval for unattributed
Copilot pull requests*. Si está activa, suma +1 approval (0 → 1) para PRs
de Copilot sin atribución; desactivarla si se quiere mantener 0 siempre.

Identificador: ruleset `21337399`
([vista](https://github.com/refo44/demo-revistalogos/rules/21337399)).

### 6. Agentes

Cursor no commitea, no pushea, no mergea ni despliega salvo petición
explícita del propietario. Nombra ramas según §3 y redacta mensajes
según §4.

La revisión automática de Copilot (plan Pro, comentario, no bloquea)
usa `.github/copilot-instructions.md`. Es un apuntador a estos ADR,
no una segunda fuente de verdad. Si carga skills, usa
`.github/skills/code-review/SKILL.md`, que apunta al mismo archivo;
no añade política. No hace falta un MCP extra (el de GitHub basta).

Los comentarios de review (humano o Copilot) siguen
[Conventional Comments](https://conventionalcomments.org/):
`<label> [decorations]: <subject>`. Por defecto `(non-blocking)`.
`(blocking)` es guía para el autor, no un candado de GitHub.

### 7. Etiquetas de issues y PRs

Un PR admite varias etiquetas. Se usan **tres ejes** que conviven; una
etiqueta pertenece a uno solo.

**Tipo (`type: *`) — obligatoria, exactamente una, en PRs e issues.**
`type: feat`, `type: fix`, `type: docs`, `type: chore`, `type: refactor`,
`type: ci`, `type: test`, `type: perf`, `type: revert`. Son los nueve tipos
de §4: la etiqueta **es** el prefijo del título hecho filtrable, no un
criterio nuevo que discutir en cada PR. Por eso se deriva sola.

**Estado de backlog (`next` / `planned` / `deferred`) — solo issues, como
máximo una.** Refleja la sección de `docs/adr/BACKLOG.md` donde vive el
ítem. Es **estado, no historia**: al cerrar el issue se retira. Un issue
resuelto que conserva `next` miente sobre lo que queda por hacer. Nunca en
un PR: un PR no vive en el backlog.

**Complementarias — opcionales, las que hagan falta.** Los defaults de
GitHub (`documentation`, `bug`, `enhancement`, `help wanted`,
`good first issue`, `question`, `duplicate`, `invalid`, `wontfix`) y las
que pone Dependabot (`dependencies`, `github_actions`, `javascript`). Se
ponen a mano —salvo las de Dependabot— y conviven con `type: *`. La regla
es que aporten: `dependencies` dice algo que `type: chore` no dice, y
`help wanted` o `question` no tienen equivalente en ningún otro eje.
`documentation`, `bug` y `enhancement` son casi sinónimos de `type: docs`,
`type: fix` y `type: feat`; usarlas está bien, pero como decisión, no como
duplicado automático.

**Se aplican solas** (`.github/workflows/labels.yml`, sin secretos ni
acciones de terceros): `type: *` se deriva del título al abrir, reabrir,
retitular o sacar de draft un PR, retirando las `type: *` que sobren;
`next`/`planned`/`deferred` se retiran al cerrar un issue. El workflow no
añade ni quita ninguna etiqueta del tercer eje. Es aplicación, no candado:
no bloquea el merge, igual que `Release pending` (ADR 0020). Si el título
no lleva un prefijo válido, avisa y no inventa etiqueta.

Retirar el estado de backlog **no** cierra el ítem: sigue haciendo falta
moverlo en `docs/adr/BACKLOG.md`, cerrar el issue y anotar `CHANGELOG.md`.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Protección clásica de rama | Menos flexible; el aviso de GitHub apunta a rulesets. |
| Ruleset + protección clásica a la vez | Se superponen; aplica la regla más estricta y confunde. |
| GitFlow / `develop` / release largas | Choca con [TBD](https://trunkbaseddevelopment.com/). |
| Commit directo a `main` (TBD de equipo muy pequeño) | Sin PR no hay check obligatorio ni rastro; el aviso de GitHub no se cierra. |
| 1 approval obligatorio | Uno puede bloquear al otro; si queda una sola persona, el repo se para. |
| Bypass de administrador | `main` seguiría abierta para `refo44`; el colaborador no tiene el mismo camino. |
| Restringir nombres de rama en el ruleset | Rompe `dependabot/*`; el spec se aplica por convención. |
| Usar `documentation`/`enhancement`/`bug` **como** eje de tipo | Los nombres no casan con los prefijos de §4: obligaría a traducir `docs:` → `documentation` y `feat:` → `enhancement` en cada PR, y no cubre `ci`, `test`, `chore`, `refactor`, `perf` ni `revert`. Como eje complementario sí se admiten. |
| Etiquetar a mano, solo documentado | Es justo el paso que ya se demostró olvidable: 18 de los 22 PRs sin etiqueta de tipo y dos con estado de backlog que no les correspondía. |
| Check que bloquee el merge si falta etiqueta | El candado es Tests (§2). Una etiqueta derivable del título no justifica un segundo candado, y bloquearía PRs correctos por un título aún sin pulir. |
| Acción de terceros (`actions/labeler`, `pr-labeler`) | Dependencia y superficie extra para un `sed` y dos llamadas a `gh` que ya vienen en el runner. |
| `commit-check-action` / commitlint ahora | Dependencia de CI extra; no cubre Dependabot ni merge commits; YAGNI. |
| Forzar patrón de mensaje en el ruleset | Rompe *Merge pull request #N*; el spec se aplica al commitear y al squash. |
| Ruleset de inserción (push) | Plan Team / repos privados; no es el problema. |
| Commits firmados / merge queue / CODEOWNERS | YAGNI; CODEOWNERS podría bloquear. |
| Exigir rama al día (`strict`) | Rebase eterno con PRs de Dependabot apilados. |

## Consecuencias

**Beneficios:** no hay push directo a `main`; force-push y borrado
bloqueados; cada cambio deja un PR y Tests verde. Write basta para seguir
si una cuenta desaparece. Pages sigue automático **después del merge**.
El deploy de producción no cambia.

**Riesgos / costes:** con 0 approvals, write + CI verde = merge. Eso es
deliberado (continuidad), no un defecto a «arreglar» con reviews
obligatorias. Un `git push origin main` **falla** para todos. Los nombres
`dependabot/*` no cumplen el spec; se toleran. El repo sigue bajo una
cuenta personal: perder esa cuenta es un riesgo de GitHub, no de este
ruleset.

**Trabajo futuro:** activar «branch up to date» solo si el desfase se
vuelve un problema. No añadir approvals obligatorias.

## Estado de implementación (2026-08-24)

Nota factual; no sustituye las decisiones de este ADR.

- Ruleset `Protect main (trunk-based)` (`21337399`) activo, sin bypass.
- Ajuste de repositorio (Settings → General → Pull Requests; **no** es
  una regla del ruleset): [*Automatically delete head branches*](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/configuring-pull-request-merges/managing-the-automatic-deletion-of-branches)
  (`delete_branch_on_merge`) **activado**. GitHub borra la rama head del
  PR al mergear. No borra `main` (el ruleset lo impide). No borra
  etiquetas `vX.Y.Z`. Las cinco ramas remotas ya mergeadas que habían
  quedado se eliminaron el mismo día.

## Estado de implementación (2026-08-29)

Nota factual; no sustituye las decisiones de este ADR.

- Creadas las nueve etiquetas `type: *`.
- Backfill de los 22 PRs existentes: 18 derivadas del prefijo del título;
  cuatro a mano (#6, #7, #8, #14) por ser anteriores a este ADR y no llevar
  prefijo Conventional Commits.
- Retiradas `next` de [PR #17](https://github.com/refo44/demo-revistalogos/pull/17)
  y `planned` de [PR #16](https://github.com/refo44/demo-revistalogos/pull/16):
  eran estado de backlog en PRs. Retirada `next` del
  [issue #9](https://github.com/refo44/demo-revistalogos/issues/9), cerrado
  desde el 2026-08-28.
- Workflow `Labels` (`.github/workflows/labels.yml`), dos jobs, sin
  secretos y sin checkout del código del PR.

## Referencias

- [Trunk-Based Development](https://trunkbaseddevelopment.com/)
- [Conventional Branch 1.1.0](https://conventionalbranch.org/)
- [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/)
- [Conventional Comments](https://conventionalcomments.org/)
- [Administrar etiquetas](https://docs.github.com/es/issues/using-labels-and-milestones-to-track-work/managing-labels)
- [Acerca de los conjuntos de reglas](https://docs.github.com/es/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/about-rulesets)
- [Creación de conjuntos de reglas de un repositorio](https://docs.github.com/es/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/creating-rulesets-for-a-repository)
- [Reglas disponibles](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/available-rules-for-rulesets)
- ADR 0009 (despliegue; no se modifica)
- ADR 0018 / `docs/23-testing-foundation.md` (workflow `Tests`)
- ADR 0020 (el ref de `workflow_dispatch` de producción es la etiqueta
  `vX.Y.Z`, no HEAD suelto de `main` tras cada merge; no relitiga este ADR)
- `docs/adr/BACKLOG.md` § Trabajo pendiente aceptado, ítem 2 (origen)
- [Copilot code review — instrucciones](https://docs.github.com/en/copilot/how-tos/copilot-on-github/use-copilot-agents/copilot-code-review#customize-reviews-with-custom-instructions)
  (`.github/copilot-instructions.md`)
- [Agent skills](https://docs.github.com/en/copilot/how-tos/copilot-on-github/customize-copilot/customize-cloud-agent/add-skills)
  (`.github/skills/code-review/SKILL.md`)
