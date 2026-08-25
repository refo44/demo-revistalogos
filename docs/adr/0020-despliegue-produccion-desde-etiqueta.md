# ADR 0020: Despliegue de producción solo desde versión etiquetada

## Estado

Aceptada

## Fecha

2026-08-24

## Contexto

`main` recibe merges frecuentes (docs, Dependabot, Copilot, hotfixes del
plugin). El espejo estático de GitHub Pages **sí** debe mostrar cada
llegada a `main` (decisión del propietario, 2026-07-28; ADR 0012). El
WordPress de `logo-et-spes.cenfiss.net` **no**.

Eso no estaba escrito como candado. ADR 0009 fija FTPS acotado y
`workflow_dispatch` manual; no dice *cuándo* dispararlo. ADR 0019 §1 dice
publicar eligiendo `main`. `VERSION.md` ya describe etiquetar `vX.Y.Z` y
después desplegar, pero era procedimiento, no gate. Resultado: se disparó
`deploy-wordpress.yml` tras merges que no eran un release (incluido el
plugin 0.2.8 tres veces, y PRs de solo docs).

Producción ya sirve plugin **0.2.8** sin una etiqueta de proyecto que
identifique ese transfer. `v0.1.0` y `v0.2.0` existen; `v0.2.0` es
**anterior** al 0.2.8 live. Re-disparar desde `v0.2.0` **bajaría** el
plugin.

Este ADR no relitiga el mecanismo FTPS (0009), la topología (0016), el
trunk (0019) ni Pages automático.

## Decisión

### 1. Merge a `main` ≠ despliegue a producción

Integrar un PR no autoriza ni exige un `workflow_dispatch` de
`deploy-wordpress.yml`. GitHub Pages sigue publicando el prototipo en cada
push a `main`. Cursor y Copilot no deben sugerir desplegar producción
tras cada merge.

### 2. Producción = un release versionado y etiquetado

Antes de FTPS a `logo-et-spes.cenfiss.net`:

1. Subir `"version"` en `package.json` (fuente canónica, `VERSION.md`).
2. Pasar `CHANGELOG.md` de `## [Sin publicar]` a `## [X.Y.Z] — AAAA-MM-DD`.
3. Actualizar `VERSION.md`.
4. Si el theme o el plugin cambian en ese release, subir sus cabeceras
   `Version` / `Stable tag` (pueden diferir del número de proyecto).
5. Aterrizar en `main` por PR (`chore(release): vX.Y.Z`).
6. Etiqueta **anotada** `vX.Y.Z` en ese commit: `git tag -a vX.Y.Z -m "vX.Y.Z"`
   y `git push origin vX.Y.Z`.
7. Actions → Deploy WordPress theme+plugin to production → Run workflow
   **desde esa etiqueta** (Use workflow from: Tags), no desde HEAD suelto
   de `main`.

La identidad del deploy es la etiqueta de proyecto `vX.Y.Z`, no «lo último
de `main`» ni solo el `Version` del plugin.

### 3. El workflow rechaza HEAD sin esa etiqueta

`.github/workflows/deploy-wordpress.yml` falla si HEAD no tiene una
etiqueta anotada que coincida con `vMAJOR.MINOR.PATCH`. Sigue siendo solo
`workflow_dispatch`: **no** se añade disparo por `push` de tags, ni
`schedule`, ni promoción automática. Eso conservaría ADR 0009 §5.

### 4. Sin ramas `release/` largas

El commit etiquetado vive en el trunk (o es un commit ya en `main`). No se
introducen ramas GitFlow. `hotfix/` corto + PR + tag de PATCH sigue válido.

### 5. Excepción histórica

Los FTPS anteriores a este ADR, incluido plugin 0.2.8 en producción, no se
retiquetan. El próximo FTPS exige un **nuevo** `vX.Y.Z` (p. ej. `v0.2.1`)
en un commit que incluya este gate. No despachar desde `v0.2.0`.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Desplegar tras cada merge a `main` | Confunde preview (Pages) con producción; el live no es un espejo de docs. |
| Disparo automático al pushear un tag | Rompe el disparo solo manual de ADR 0009 §5. |
| Etiquetas `plugin-0.2.8` / livianas | `VERSION.md` ya usa `vX.Y.Z` anotadas; un segundo esquema no aporta. |
| Ramas `release/` largas | GitFlow; rechazado en ADR 0019. |
| Solo documentar, sin gate en el workflow | Ya estaba en `VERSION.md` y no se cumplía. |

## Consecuencias

**Beneficios:** cada FTPS de producción es un release trazable; los merges
de docs/CI/Copilot no empujan código al hosting; rollback de código = volver
a despachar una etiqueta conocida (ADR 0009, rollback A).

**Riesgos / costes:** un `workflow_dispatch` sobre `main` sin etiqueta
falla (es el candado). Hay que hacer un PR de release para el próximo
envío. Plugin 0.2.8 live no tiene tag de proyecto hasta ese release.
Las cabeceras del plugin/theme pueden no coincidir con `package.json`
(ya ocurría: proyecto 0.2.0, plugin 0.2.8).

**Trabajo futuro:** no auto-bumpear SemVer desde Conventional Commits. No
instalar commitlint ni semantic-release. D12b (checks extra de CI) sigue
pendiente de la auditoría.

## Referencias

- `VERSION.md` (procedimiento de publicación)
- [ADR 0009](0009-mecanismo-y-alcance-del-despliegue.md) (FTPS, `workflow_dispatch`; no se sustituye)
- [ADR 0016](0016-topologia-hosting-cpanel.md)
- [ADR 0019](0019-proteger-main-trunk-based.md) (trunk; el ref de deploy deja de ser «elegir `main`»)
- `docs/operations/wordpress-manual-deployment.md`
- `.github/workflows/deploy-wordpress.yml`
- `tools/require-production-release-tag.sh`
