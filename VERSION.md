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
4. Si el theme o el plugin cambian en ese release, subir la versión que
   declara cada uno (pueden diferir de X.Y.Z):

   | Componente | Dónde | Quién la lee |
   |---|---|---|
   | Theme | `Version:` en `style.css` | wp-admin → Apariencia |
   | Plugin | `Version:` en `revistalogos-core.php` | wp-admin → Plugins |
   | Plugin | `REVISTALOGOS_CORE_VERSION` en ese mismo archivo | `Plugin::maybe_upgrade()` |
   | Plugin | `Stable tag:` en `readme.txt` | metadatos del plugin |

   **Los tres del plugin deben quedar iguales.** `maybe_upgrade()` compara
   la constante y wp-admin muestra la cabecera: si divergen, el panel
   informa de una versión que no es la que decide si el upgrade corre. El
   theme no tiene `Stable tag:` ni `readme.txt`; no buscarlos.
5. Commit por PR: `chore(release): vX.Y.Z` (ADR 0019: no pushear a `main`).
6. Etiquetar **sobre el commit del release ya en `main`**, con las refs
   recién traídas:

   ```bash
   git fetch --tags origin main
   git tag -a vX.Y.Z -m "vX.Y.Z" origin/main
   git push origin vX.Y.Z
   ```

   Etiquetar el HEAD local sin comprobar dónde está apunta a la rama en la
   que se esté trabajando; sin el `fetch`, `origin/main` puede estar vieja y
   apuntar a un commit anterior al release. Desde el 2026-08-29 el gate del
   deploy rechaza una etiqueta así —comprueba que `package.json` declare la
   versión de la etiqueta y que el commit esté en `main`—, pero para
   entonces ya está publicada, y corregirla obliga a borrarla y recrearla en
   local y en el remoto.
7. Desplegar theme+plugin desde GitHub Actions → «Deploy WordPress theme+plugin
   to production» → Run workflow **desde esa etiqueta** (Use workflow from:
   Tags). El workflow falla si HEAD no tiene `vMAJOR.MINOR.PATCH` anotada.
   El workflow estático «Deploy to Hostinger» (`deploy.yml`) está **retirado**;
   no recrearlo. Nunca despachar desde una etiqueta anterior a lo que
   producción ya sirve: reinstalaría una versión más vieja del plugin.

## Historial

Ver `CHANGELOG.md`.
