# Versionado

**Versión vigente: 0.3.0**

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

Un merge a `main` **no** es un despliegue a producción (ADR 0020). GitHub
Pages sigue publicando el espejo estático en cada push a `main`. El FTPS a
`logo-et-spes.cenfiss.net` solo se dispara **después** de este procedimiento,
desde la etiqueta, no desde HEAD suelto de `main`.

1. Actualizar `"version"` en `package.json`.
2. Mover los cambios de `## [Sin publicar]` a una nueva sección `## [X.Y.Z] — AAAA-MM-DD` en `CHANGELOG.md`.
3. Actualizar «Versión vigente» en este archivo.
4. Si el theme o el plugin cambian en ese release, subir sus cabeceras
   `Version` / `Stable tag` (pueden diferir de X.Y.Z).
5. Commit por PR: `chore(release): vX.Y.Z` (ADR 0019: no pushear a `main`).
6. Etiquetar: `git tag -a vX.Y.Z -m "vX.Y.Z"` y `git push origin vX.Y.Z`.
7. Desplegar theme+plugin desde GitHub Actions → «Deploy WordPress theme+plugin
   to production» → Run workflow **desde esa etiqueta** (Use workflow from:
   Tags). El workflow falla si HEAD no tiene `vMAJOR.MINOR.PATCH` anotada.
   El workflow estático «Deploy to Hostinger» (`deploy.yml`) está **retirado**;
   no recrearlo. No despachar desde `v0.2.0`: producción ya sirve plugin 0.2.8.

## Historial

Ver `CHANGELOG.md`.
