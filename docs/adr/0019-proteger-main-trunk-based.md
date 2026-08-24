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
Copilot pull requests*. Con 0 approvals **no tiene efecto**; se deja el
default.

Identificador: ruleset `21337399`
([vista](https://github.com/refo44/demo-revistalogos/rules/21337399)).

### 6. Agentes

Cursor no commitea, no pushea, no mergea ni despliega salvo petición
explícita del propietario. Nombra ramas según §3 y redacta mensajes
según §4.

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
vuelve un problema. Borrar la rama al mergear es un ajuste de repo
aparte. No añadir approvals obligatorias.

## Referencias

- [Trunk-Based Development](https://trunkbaseddevelopment.com/)
- [Conventional Branch 1.1.0](https://conventionalbranch.org/)
- [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/)
- [Acerca de los conjuntos de reglas](https://docs.github.com/es/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/about-rulesets)
- [Creación de conjuntos de reglas de un repositorio](https://docs.github.com/es/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/creating-rulesets-for-a-repository)
- [Reglas disponibles](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/available-rules-for-rulesets)
- ADR 0009 (despliegue; no se modifica)
- ADR 0018 / `docs/23-testing-foundation.md` (workflow `Tests`)
- `docs/adr/BACKLOG.md` § Trabajo pendiente aceptado, ítem 2 (origen)
