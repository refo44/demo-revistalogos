# Versionado

**Versión vigente: 0.1.0**

## Fuente de verdad

El número de versión canónico vive en **`package.json`** (`"version"`). Todo lo demás
—etiquetas de Git, `CHANGELOG.md`, este documento— debe reflejar ese valor.

## Esquema

Versionado Semántico `MAJOR.MINOR.PATCH` ([semver.org](https://semver.org/lang/es/)):

| Parte | Cuándo se incrementa |
| ----- | -------------------- |
| **MAJOR** | Cambios incompatibles. **Incluye cambios en la estructura de URLs** (se fijará en el ADR de política de URLs, D7). |
| **MINOR** | Funcionalidad o contenido nuevo compatible hacia atrás. |
| **PATCH** | Correcciones que no cambian la estructura ni la interfaz. |

Antes de 1.0.0 la API/estructura se considera inestable; los cambios pueden ocurrir en `MINOR`.

## Etiquetas de Git

Cada versión publicada se etiqueta con el prefijo `v`: `v0.1.0`, `v0.2.0`, …
La etiqueta apunta al commit que deja `package.json`, `CHANGELOG.md` y este archivo en esa versión.

## Procedimiento de publicación

1. Actualizar `"version"` en `package.json`.
2. Mover los cambios de `## [Sin publicar]` a una nueva sección `## [X.Y.Z] — AAAA-MM-DD` en `CHANGELOG.md`.
3. Actualizar «Versión vigente» en este archivo.
4. Commit: `chore(release): vX.Y.Z`.
5. Etiquetar: `git tag -a vX.Y.Z -m "vX.Y.Z"` y `git push origin vX.Y.Z`.
6. Desplegar al host manualmente desde GitHub Actions → «Deploy to Hostinger» → Run workflow.

## Historial

Ver `CHANGELOG.md`.
