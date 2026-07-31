---
phase: "Fase 3"
status: "in_progress"
current_work_unit: "WU6 — Generador e importador de contenido institucional"
current_branch: "main"
last_verified_commit: "1cd5ab0"
last_checkpoint_commit: "1cd5ab0"
updated_at: "2026-07-31"
next_action: "Ejecutar WU6: generador de payload (Node) + importador (PHP/WP-CLI) de contenido institucional"
blocked: false
---

# Fase 3 execution state

Estado de ejecución durable de la Fase 3 (WordPress). Cualquier sesión futura debe
poder reanudar el trabajo desde este archivo sin historial de chat, siguiendo el
protocolo de reanudación del final.

## Current objective

Implementar la Fase 3 completa según el prompt maestro
(`docs/FABLE5-Fase3-WordPress-Master-Prompt-v4.md`): reorganización del monorepo
(ADR 0007), plugin `revistalogos-core` (ADR 0005), theme clásico `revistalogos`
(ADR 0001-0003), migración institucional determinista, fixtures, integraciones
aprobadas (CF7, WP Statistics), búsqueda, metadatos académicos de Fase 3 y
workflow manual de despliegue FTPS (ADR 0009).

## Current strategy

Ejecución por unidades de trabajo (WU) con commits pequeños y revisables, en el
orden de `docs/17-implementation-order` y del prompt maestro:

| WU | Alcance | Commit previsto |
| -- | ------- | --------------- |
| WU0 | Harness (este archivo + matriz + ledger + runbooks) | `docs: add fase 3 harness` |
| WU1 | Reorg monorepo `static/` + `wordpress/` (tag `pre-fase3-reorg`) | `refactor: move static site to static/` |
| WU2 | Scaffold `revistalogos-core` | `feat(plugin): scaffold` |
| WU3 | Modelo de contenido (CPTs, taxonomías, meta, rol, relaciones) | `feat(plugin): content model` |
| WU4 | Scaffold theme + assets de presentación | `feat(theme): scaffold` |
| WU5 | Migración de plantillas (familias) | `feat(theme): templates` |
| WU6 | Generador + importador de contenido institucional | `feat(plugin): content migration` |
| WU7 | Fixtures seed/verify/teardown | `feat(plugin): fixtures` |
| WU8 | Integración CF7 + operaciones WP Statistics | `feat(plugin): integrations` |
| WU9 | Búsqueda `/buscar/?q=` y URLs | dentro de WU3/WU5 |
| WU10 | Metadatos académicos (Highwire, Schema.org, OG) | `feat(theme): metadata` |
| WU11 | Workflow manual FTPS WordPress | `ci: manual wordpress deploy workflow` |
| WU12 | Gate final + correcciones de documentación | `docs: ...` (separado) |

Sin runtime PHP/WordPress local (ver Herramientas), toda QA de niveles 2-4 queda
`Unverified` y se registra así en la matriz; la QA de nivel 1 (sintaxis, greps,
checksums, YAML/JSON) se ejecuta siempre.

## Acceptance criteria

Los del prompt maestro §6 (Definition of success). Resumen operativo:

- Separación static/wordpress según ADR 0007 sin romper el despliegue estático.
- Theme solo presentación; plugin dueño del dominio; sin CPT `submission`.
- CSS byte a byte idéntico al estático (checksums abajo).
- Migración institucional determinista, idempotente y con verificación de texto.
- Fixtures aisladas con `_les_fixture = 1` e identificadores falsos detectables.
- Despliegue WordPress solo `workflow_dispatch`, acotado a theme + plugin.
- Nada se declara verificado sin evidencia de ejecución.

## Completed work

- **WU0 (2026-07-31):** lectura de fuentes vinculantes; creación de artefactos
  durables; snapshot del repositorio; baseline de checksums CSS/JS (abajo).
  Commit `df3ad90`.
- **WU1 (2026-07-31):** reorganización del monorepo (ADR 0007). Tag de rollback
  `pre-fase3-reorg`; `git mv` de HTML, `assets/`, `partials/`, `.htaccess`,
  `robots.txt` y `sitemap.xml` a `static/`; estructura `wordpress/wp-content/`
  creada; `deploy.yml` apunta a `static/`; nuevo `pages.yml` para el espejo
  beta (pendiente acción del propietario en Settings → Pages); scripts de
  stylelint actualizados; `wordpress/wp-content/uploads/` ignorado. QA: YAML
  parse OK, `git diff --check` OK, 12/12 checksums CSS/JS idénticos tras el
  movimiento, estructura de imagen plana preservada.
- **WU2 (2026-07-31):** scaffold de `revistalogos-core` (ADR 0005). Bootstrap
  con guard de acceso directo, constante de versión, hooks de
  activación/desactivación (flush de rewrite solo ahí) y rutina de upgrade
  idempotente; sin efectos secundarios al incluir, sin dependencia del theme.
  Commit `8e5ebfc`.
- **WU3 (2026-07-31, mismo commit que WU2):** modelo de contenido. CPTs
  `issue`/`article`/`author` con slugs `revista/*` (ADR 0008, docs/11) y
  capabilities por CPT; sin CPT `submission` (aplazado, ADR 0005 §4).
  Taxonomías `section` (jerárquica, términos iniciales aprobados),
  `article_type` (valores canónicos en inglés, etiquetas admin en español) y
  `keyword`. `register_post_meta` con sanitización, auth callbacks y esquemas
  REST; `issn`/`doi`/`orcid` almacenados solo como campos inertes (ADR 0013 —
  sin validación ni URLs derivadas, límite de Fase 4). Meta boxes nativas con
  nonce y chequeo de capacidad; relaciones artículo↔autores (many-to-many) y
  artículo↔número (many-to-one) normalizadas y limpiadas al borrar posts
  referenciados. Rol Managing Editor de mínimo privilegio, distinto del
  Editor nativo. Query del número actual derivada (más reciente por
  `date_published`, sin flag almacenado) y queries de dominio acotadas para
  el theme. Comentarios desactivados globalmente (invariante cero-cookies,
  ADR 0011) e integración de honeypot con CF7 guardada tras `class_exists`
  (ADR 0010). Commit `8e5ebfc`.
- **WU4 (2026-07-31):** scaffold del theme `revistalogos` + assets de
  presentación. `style.css` solo cabecera; `theme.json` restrictivo (paleta
  aprobada, colores/gradientes/duotono/tamaños de fuente/espaciado
  personalizados desactivados, ADR 0003 §1). `functions.php`: soporte de
  title-tag/thumbnail, ubicaciones de menú, `main.css` como único punto de
  entrada de hoja de estilos, `main.js` diferido en el footer, dequeue
  auditado de estilos de bloque nativos no usados (ADR 0003 §3), fallback de
  favicon y aviso admin + fallbacks seguros de front-end si
  `revistalogos-core` está inactivo. CSS/JS copiados byte a byte desde
  `static/` (SHA-256 agregado verificado igual; espacios en blanco finales
  preexistentes preservados deliberadamente). Shell de sitio con paridad
  estática: header con skip link y navegación primaria congelada (menu
  walker con clases `nav__*`, enlace dinámico al número actual, fallback
  hardcodeado), footer con listas de enlaces menú-o-fallback, breadcrumbs,
  paginación y estado vacío, `page.php` como renderizador institucional
  compartido vía `the_content()`, `index.php` de respaldo, `404.php`,
  `comments.php` desactivado. Commit `b46d313`.
- **WU5 (2026-07-31, mismo commit que WU4, mas `1cd5ab0`):** migración de
  plantillas por familias — todos los archivos/singles de
  `issue`/`article`/`author`/`post`, taxonomías `article_type`/`keyword`/
  `section`, todas las páginas institucionales (`page-acerca`, `page-etica`,
  `page-normas`, `page-politicas`, `page-enlaces`,
  `page-enviar-colaboracion`, `page-comite-editorial`, `page-contacto`),
  `privacy-policy.php`, `home.php`, `front-page.php`, `404.php`. Script de
  citación extraído de inline a `assets/js/citation.js` +
  `inc/citations.php`, con lógica de enqueue en el theme (commit `1cd5ab0`).
- **WU9:** ruta de búsqueda resuelta dentro de WU3/WU5 — `page-buscar.php`
  (`/buscar/?q=`) como plantilla principal y `search.php` como delegado fino
  para `/?s=` (ver «Decisions and assumptions», ya no es una WU separada).
- **WU10 (2026-07-31):** metadatos académicos — Highwire Press tags,
  JSON-LD Schema.org y Open Graph (`inc/metadata-output.php`, 250 líneas).
  Incluido en `b46d313` + `1cd5ab0`.

## Active work

- WU6: generador e importador de contenido institucional. Ver «Next exact
  action».

## QA status of completed work

Igual que WU1: sin runtime PHP/WP-CLI/WordPress local, WU2–WU5, WU9 y WU10
solo tienen QA de **nivel 1** (sintaxis PHP no verificable localmente —
ver «Decisions and assumptions» — checksums CSS/JS agregados verificados
iguales para WU4/WU5). Activación de plugin/theme, render real de
plantillas, y todo lo de niveles 2-4 permanece `Unverified` en
`docs/fase3-validation-matrix.md` hasta que exista un entorno de staging.
No se declara "hecho" en el sentido de "verificado en WordPress real" —
solo en el sentido de "código escrito según las fuentes vinculantes".

## Validation evidence

Ver `docs/fase3-validation-matrix.md`. Baseline de paridad CSS/JS del estático
(SHA-256, tomado en `5fedf8a`, 2026-07-31):

```text
0b3c6452f805be5925b11895cc79d03f17159a4bcced6d7d189f9f58abdd65ce  assets/css/base.css
6737e0335487bae090c86263257304e7669590fadbc3c203b1e9029d0a871125  assets/css/components.css
01bf4101d184bf8d15b980600c3c92703c0c626dbf99280270a1f6fdd7a0e4a8  assets/css/layout.css
d20aefbafb03b4c8bd36f82d4bfe583f65ffbcde4c4555d0c24666308f8d89ab  assets/css/main.css
30fe2260367a0585940c4bcf5da0abc98c78aa4144925e5f346108023ed29252  assets/css/tokens.css
fdffc0ebfebda05b806996f2a50186106406142c5c0c3fffd670466bd5996907  assets/css/utilities.css
52e5adb59a72a9a3f84f3149f5879910e33b1280dbb12b8d75a3ee5d8cf2d884  assets/css/pages/archive.css
7f0d80a7caf91d5e1e9be0501b4abd915cddd35a8cf9cc20253f79031afc32a0  assets/css/pages/article.css
48c58fab6ea848edc07cdc2227ff4f678199d56e378a2896a005ed9b6cfb70fd  assets/css/pages/home.css
7ae2ab58e6acdb14d27911e32b198f9b366a978aeef64bec09628baee3f3b807  assets/css/pages/issue.css
d75e7fbd757c5402c2d4a94e6836883819579ca1245daa142c0b235555e69b93  assets/css/pages/static-pages.css
2bd3e97fa31242fbc70536d1c602c8ff31f3a780f23957f72a7c5b5b118f7f79  assets/js/main.js
```

## Failures and root causes

- Ninguno todavía.

## Decisions and assumptions

- **Herramientas (2026-07-31):** PHP, WP-CLI, Composer y actionlint **no** están
  instalados en la máquina de desarrollo; Node 20.17 y npm 10.8 sí. No se
  instala toolchain global sin autorización. Consecuencia: `php -l`, activación
  de plugin/theme, migración, fixtures y comparación visual quedan `Unverified`
  hasta que exista un runtime (staging u otro entorno).
- El generador de payload de migración se implementa en **Node** (herramienta
  del repo, sin dependencias nuevas), porque es la única runtime disponible y el
  payload es un artefacto local versionado; el importador es PHP/WP-CLI dentro
  de `revistalogos-core` y no depende de rutas del repositorio.
- `docs/12-theme-file-structure` §8 sitúa los CPT en `inc/` del theme; ADR 0005
  §3 lo ajusta explícitamente: se sigue ADR 0005 (CPTs en el plugin).
- El prompt maestro pide `search.html → page-buscar.php` (ruta `/buscar/?q=`);
  `docs/17` §2.2 mapea `search.html → search.php`. Se sigue el prompt y
  `docs/11` (ruta preferida `/buscar/?q=`), manteniendo `search.php` como
  delegado fino para `/?s=` sin crear variantes indexables en competencia.

## Documentation discrepancies

Registradas para corrección en commit de documentación separado (WU12):

1. **GitHub Pages tras la reorg (material):** el espejo beta de GitHub Pages
   publica hoy desde la **raíz** de `main` (Pages «deploy from branch»;
   `_config.yml` con `baseurl: /demo-revistalogos`). Al mover el estático a
   `static/`, Pages dejará de servir el sitio (la raíz ya no tendrá HTML).
   Resolución aplicada: se añade un workflow de Pages (`.github/workflows/pages.yml`,
   push a `main`, publica `static/`) que preserva el comportamiento automático
   deliberado (backlog 2026-07-28). **Acción del propietario pendiente:**
   cambiar en Settings → Pages la fuente de «Deploy from a branch» a
   «GitHub Actions». Hasta entonces el espejo queda roto o sirviendo la raíz.
2. **`docs/13-static-file-structure`** describe el estático en la raíz; tras la
   reorg queda obsoleto (actualización separada).
3. **`docs/15-assets-strategy`/`docs/12`** documentan subcarpetas
   `assets/img/logos/`, `placeholders/`, etc.; la estructura real es **plana**
   (`assets/img/*.svg|jpg|png`). Se preserva la estructura plana durante la
   reorganización (regla del prompt §14 Fase 1); reorganizar imágenes requiere
   decisión explícita.
4. **`docs/03` §3** almacena `article.doi_url` como campo; ADR 0013/`docs/22`
   lo definen **computado, no almacenado**. Gana ADR 0013: en Fase 3 solo se
   registra almacenamiento inerte de `issue.issn`, `issue.doi`, `article.doi`,
   `author.orcid`; sin `doi_url`/`orcid_url` almacenados ni validación (Fase 4).
5. **`docs/03` §1/§3/§6/§8 y `docs/12` §2/§4.0** describen el CPT `submission`,
   rol Author y área privada: **aplazados** por ADR 0005 §4; no se implementan
   en Fase 3 (el texto de los docs ya lo señala vía ADR, no requiere edición).
6. **`docs/16` §1** lista `assets/img/` con nombres sin subcarpetas pero
   `docs/15` §1 con subcarpetas — misma discrepancia que (3).
7. **`content-source/` está en `.gitignore`** (línea `/content-source/`): el
   material canónico existe solo en el working tree local, no en Git. El
   generador de migración lo trata como entrada local y el payload generado
   (versionado) como artefacto reproducible con checksum de la fuente.

## Blockers

- Ninguno duro para el trabajo local. Potenciales (no bloquean todavía):
  - Hostname exacto del subdominio de staging: solo necesario al configurar
    secretos y ejecutar el primer despliegue (prohibido sin autorización).
  - Runtime WordPress: necesario para QA niveles 2-4; sin él todo queda
    `Unverified`.
  - Acción del propietario: cambiar la fuente de GitHub Pages a «GitHub
    Actions» (ver discrepancia 1).

## Repository state

- Rama: `main`; HEAD al iniciar: `5fedf8a`; tag existente: `v0.1.0`.
- Working tree al iniciar: limpio salvo `docs/FABLE5-Fase3-WordPress-Master-Prompt-v4.md`
  (sin trackear; es el prompt de esta fase, se versiona en WU0).
- Despliegues: Hostinger manual (`deploy.yml`, `workflow_dispatch`) y GitHub
  Pages automático desde raíz de `main`.

## Files changed

Se actualiza al cierre de cada WU. WU0: los cinco artefactos del harness + el
prompt maestro versionado.

## Next exact action

Ejecutar WU6 (generador e importador de contenido institucional,
docs/17 §Fase 3.1, prompt maestro):

1. Generador de payload en Node (herramienta del repo, sin dependencias
   nuevas): lee `content-source/` (gitignored, local) y produce un
   artefacto JSON versionado con el contenido institucional (enfoque,
   normas, ética, políticas, origen del nombre) más checksum de la fuente,
   determinista e idempotente.
2. Importador PHP/WP-CLI dentro de `revistalogos-core` (comando de
   administración o WP-CLI) que consume ese payload y crea/actualiza las
   páginas de WordPress correspondientes sin depender de rutas del
   repositorio.
3. Verificación de texto: el importador debe poder confirmar que el
   contenido publicado coincide con la fuente (no solo que se ejecutó sin
   error).
4. Respetar ADR 0004: nada del Vol. 12 Nº 2 ni de los datos dummy se migra
   aquí — solo contenido institucional real ya presente en
   `content-source/`.
5. QA nivel 1 (sintaxis del generador Node, ejecución local del generador
   contra `content-source/`); el importador PHP queda `Unverified` sin
   runtime WordPress, igual que el resto del código de Fase 3.
6. Commit `feat(plugin): content migration` y actualizar este archivo.

## Resume procedure

```bash
git status --short && git branch --show-current && git log -5 --oneline
```

1. Leer `.cursor/rules/` y este archivo.
2. Verificar que la rama/commit registrados coinciden con Git; si no, corregir
   este archivo antes de continuar.
3. Inspeccionar cambios sin commitear; no descartar nada ajeno.
4. Releer las fuentes vinculantes de la WU activa.
5. Re-ejecutar la última QA aplicable (`docs/fase3-validation-matrix.md`).
6. Continuar desde «Next exact action».
