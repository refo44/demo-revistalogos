# ADR 0015: Block theme, Gutenberg y Site Editor

## Estado

Aceptada

## Fecha

2026-08-16

## Contexto

Fase 3 se implementó como theme clásico. Convertirlo a block theme exigía una decisión nueva. Esa decisión es esta.

El theme `revistalogos` ya es un classic theme completo (WU4/WU5). Tres hechos posteriores cambian el alcance de presentación, no el de contenido (ADR 0005):

1. Los hex digitales de `tokens.css` / `docs/02` son **provisionales**. La edición **impresa** ya definió otros colores. El desarrollador **no** tendrá acceso continuo al repo: el admin tiene que cambiar la paleta desde **wp-admin**, no editando CSS.
2. El propietario elige **Full Site Editing** (Gutenberg a escala de sitio): plantillas, partes y estilos en **Apariencia → Editor**. No Personalizar / Customizer.
3. Un frontend **Next.js** (SSR/headless) se consideró y **se descarta**: WordPress ya hace SSR; el admin opera en wp-admin; el hosting es cPanel/PHP (ADR 0016); el Site Editor no pintaría un front Next.

ADR 0003 §1 y §3 (theme.json mínimo; dequeue de `global-styles` / `wp-block-library`) impiden hoy el Site Editor y los colores en Estilos. Hay que sustituir esos dos apartados sin tirar `main.css` ni rediseñar (0001/0002).

## Decisión

### 1. El theme público pasa a block theme (FSE)

Las plantillas viven en `templates/*.html` y `parts/*.html` (markup de Gutenberg). Existe `templates/index.html` (sin eso WordPress no abre el Site Editor).

La conversión es **incremental**: un block theme sigue sirviendo los `.php` mientras no exista el `.html` equivalente. No se borra un PHP el mismo día que el HTML está a medias.

### 2. El Site Editor es superficie oficial

El Administrador usa **Apariencia → Editor** para plantillas, partes y **Estilos → Colores**. Gutenberg es el editor de esas plantillas y sigue siéndolo en el cuerpo de las páginas institucionales (`the_content()`).

El rol **Managing Editor** no recibe `edit_theme_options`: edita CPTs, no el sitio. En WordPress no hay permiso nativo de «solo colores»; el resguardo es operativo (un Administrador; no editar plantillas salvo copia de seguridad).

### 3. Colores de marca en Estilos, no en Git

`theme.json` declara la paleta semántica (primary, text, link, fondos, estados de UI). `tokens.css` alias a `--wp--preset--color--*`. Los defaults son los de la identidad / maqueta; el admin los pisa con la paleta impresa.

Color **libre por bloque** sigue desactivado (`settings.color.custom = false`). Tipografía y espaciado **no** se liberan en Estilos.

Se deja de hacer dequeue de `global-styles` (y de `wp-block-library` cuando el front emita markup de bloques).

### 4. BEM y `main.css` se conservan

Los bloques llevan `className` de la maqueta. `main.css` sigue siendo la autoridad de layout y componentes. El bloque Navigation de núcleo **no** sustituye el header mientras no reproduzca `nav__*`.

La paridad deja de ser checksum byte a byte de una sola hoja (0003 «CSS inmutable como asset»). Pasa a ser visual + clases BEM conservadas. La arquitectura `tokens → base → layout → components` no se reescribe.

### 5. Bloques de dominio en el plugin

Número actual, tarjetas, autores N:N, TOC, metadatos y citas se registran en `revistalogos-core` (0005). El theme solo ensambla.

### 6. Next.js / headless: rechazado

WordPress sigue siendo CMS **y** servidor del sitio público. No hay `frontend/`, no hay export estático Next, no se diseña la API «por si acaso». Reabrir headless exige un ADR nuevo, hosting Node y alguien que lo mantenga.

### 7. Orden de implementación

1. Este ADR (papel).
2. Bootstrap FSE en **Docker** (ADR 0014): `theme.json` + aliases + `templates/index.html` + quitar dequeue. Gate: Site Editor abre; cambiar primary se ve en `localhost:8080`. Las demás plantillas pueden seguir en PHP.
3. Header/footer en `parts/`; bloques de dominio; `front-page`; páginas; archivos; `single-issue`; `single-article` al final.
4. **Después** del gate local: corte en el subdominio de la revista (ADR 0016). No antes.

### 8. Qué sustituye y qué no

| ADR | Efecto |
| --- | ------ |
| 0003 §1 y §3 | **Sustituidos** (theme.json de paleta + Global Styles en público). El resto de 0003 (tokens como arquitectura, `custom: false`, carve-out AA) sigue. |
| 0001 | La maqueta es el **default de fábrica** y el criterio de conversión, no un veto eterno al Site Editor. |
| 0002 | «No rediseñar» vale para la conversión y para no inventar UI. Tras el corte, el admin **puede** cambiar estructura en el Editor. |
| 0005 | Sin cambios: dominio en el plugin. |

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Seguir en classic theme + Customizer para la paleta | Cumple colores con 1–2 sesiones, pero el propietario elige Site Editor / Gutenberg. |
| Block theme «de libro» (tirar BEM, restatar layout en theme.json) | Rediseño; choca 0001/0002. |
| Next.js + WP como CMS | SSR duplicado; el admin pierde el Editor; hosting cPanel/PHP (0016); dos frontends. |
| Híbrido permanente (unas pantallas HTML, otras PHP sin plan de cierre) | Dos jerarquías sin beneficio cuando el destino es FSE completo. |

## Consecuencias

**Beneficios:**

- El admin cambia la paleta impresa sin el desarrollador ni Git.
- Plantillas editables en el Site Editor cuando existan los `.html` y los bloques de dominio.
- Inversión alineada con wp-admin, no con un segundo runtime.

**Riesgos / costes:**

- Quien entra al Site Editor puede romper header y home. Mitigado: un Administrador; nota operativa.
- 15–25 sesiones de implementación; las difíciles son header, API de bloques y `single-article`.
- `deploy.yml` del estático **no** debe lanzarse contra la carpeta de la revista una vez instalado WP (0016).
- Markup `wp-block-*` convivirá con BEM; hay que vigilar especificidad.

**Trabajo futuro:**

- Implementar el bootstrap FSE en Docker (sesión 2) antes de cualquier WP en el subdominio.
- Registrar bloques de dominio en `revistalogos-core`.
- Actualizar `docs/12-theme-file-structure` cuando existan `templates/` y `parts/`.
- Nota operativa para el admin: Estilos → Colores; no editar plantillas sin respaldo.

## Referencias

- Prompt maestro §9.1 (classic theme hasta decisión nueva)
- ADR 0001, 0002, 0003 (§1/§3 sustituidos), 0005, 0014, 0016
- `wordpress/wp-content/themes/revistalogos/functions.php` (dequeue a retirar)
- `docs/02-corporate-identity` (paleta provisional; impresa es la marca real)

## Estado de implementación (2026-08-19)

Nota factual; **no** sustituye las decisiones §1–§6 (block theme, Site Editor, paleta en Estilos, BEM, bloques en el plugin, Next.js rechazado).

- El theme desplegado y activo en producción es el **clásico** `revistalogos`. FSE **pendiente**.
- El corte in situ (ADR 0016) se ejecutó **sin** esperar al gate local de §7.2/§7.4. FSE **no bloqueó** el corte. Eso es un cambio de **orden operativo**, no de destino arquitectónico.
- Orden operativo actual: WordPress clásico live en producción; carga de contenido editorial real iniciada y actualmente en proceso desde wp-admin → QA → limpieza del estático residual → revisar PHP → plugins aprobados → **luego** FSE incremental, **primero en Docker**.
- Snapshot: `docs/operations/produccion-wordpress.md`.
- El riesgo de volcar HTML estático sobre WP (`deploy.yml`, consecuencia arriba) queda cerrado: ese workflow se **retiró** el 2026-08-19. `pages.yml` no despliega a cPanel.
- Nota factual 2026-08-20 (no cambia §1–§6): la generación automática de PDF de artículo, cuando se implemente, es dominio del plugin ([ADR 0017](0017-generacion-automatica-pdf-articulo.md)). FSE y el theme clásico solo consumen `pdf_file`. La conversión a block theme **no** regenera PDFs ni migra adjuntos.

## Contrato de conversión (2026-10-19)

Nota de implementación. No sustituye §1–§6. El prompt que ejecuta cada sesión es `docs/fse-implementation-prompt.md`. Issues: [#12](https://github.com/refo44/demo-revistalogos/issues/12) y [#73](https://github.com/refo44/demo-revistalogos/issues/73)–[#85](https://github.com/refo44/demo-revistalogos/issues/85).

1. **La plantilla no crea contenido.** `templates/front-page.html` no crea la portada ni una Page. La conversión lee números, artículos, autores y Pages ya publicados. No se relanza el importador institucional ni `fixtures seed`.
2. **La ruta se prueba entrando.** Una vista convertida se comprueba con una petición HTTP a la URL canónica, con barra final ([ADR 0008](0008-politica-de-urls.md)). `get_permalink()` no basta.
3. **Una transferencia no es paridad.** Un workflow o un FTPS en verde solo prueba la copia de archivos. La paridad de una pantalla es visual: captura de `static/` frente a la URL de WordPress, escritorio y 320px, con las clases BEM de la maqueta todavía presentes (`nav__*`, `header__*`, `issue-card`, `single-article__*`). Un envoltorio `wp-block-*` es aceptable si el layout BEM se sostiene.
4. **Bloques.** Un archivo por familia en `revistalogos-core`, `block.json` más `render_callback`, cargado desde `Plugin::load_modules`. El theme coloca el bloque y no consulta CPTs. Sin datos, o si el helper de dominio no existe, el callback devuelve cadena vacía: sin etiquetas rotas y sin error fatal. Los assets de esos bloques no se encolan en el front clásico de `main`.
5. **Header y footer.** `parts/header.html` y `parts/footer.html` pueden ser un cascarón que invoca el bloque. El marcado `header__*` / `nav__*` / skip-link lo sigue emitiendo `Revistalogos_Nav_Walker`. El bloque Navigation de núcleo no sustituye esa cabecera. El footer conserva las ubicaciones `footer-quick` y `footer-norms`.
6. **Estilos.** `theme.json` conserva la paleta ya declarada. `tokens.css` aliasa los `--color-*` existentes a `var(--wp--preset--color--*)`. Tipografía, espaciado y color libre por bloque siguen cerrados. `main.css` sigue siendo la autoridad de layout. El dequeue de `global-styles` se retira en `feat/fse`, no en `main`, hasta el corte.
7. **PHP.** El `.php` de una vista se borra solo cuando el `.html` equivalente cumple el gate de su sesión. Una sesión, un PR, contra `feat/fse`.
8. **Qué cierra esta conversión.** Presentación y el comportamiento que esa plantilla sustituye (menú, citación en pantalla, formulario ya existente). El contenido institucional y el árbol de URLs no se reabren. El CSS adicional del Personalizador en producción bloquea solo el corte ([#38](https://github.com/refo44/demo-revistalogos/issues/38) → [#84](https://github.com/refo44/demo-revistalogos/issues/84)); no bloquea el bootstrap en Docker.

## Theme clásico de reserva tras el corte (2026-10-29)

Enmienda del propietario. Cambia cómo se ejecuta el corte de §7.4 y deja sin efecto, **desde el corte**, la regla del prompt «no inventar un segundo directorio de theme». No cambia §1–§6, el contrato de conversión ni las sesiones 5–9.

**Motivo.** El propietario quiere elegir en wp-admin entre el block theme y el clásico estable, y volver atrás sin un redeploy.

1. **Dos themes en producción tras el corte.** `revistalogos` es el block theme, destino de §1. `revistalogos-classic` es una copia congelada del clásico. El Administrador elige uno en **Apariencia → Temas**.
2. **Hasta el corte no cambia nada.** Las sesiones 5–9 siguen convirtiendo `revistalogos` in situ en `feat/fse`. `revistalogos-classic` no existe en el repo antes de la release A del punto 5.
3. **Un solo plugin.** `revistalogos-core` no se duplica ni se elige por theme. Dos copias activas chocan (clases, CPT, roles), y alternar versiones obliga a desactivar el dominio. La versión vigente sirve a los dos themes. Los bloques de dominio son aditivos: el clásico no los coloca, y sin el partial del theme devuelven cadena vacía (contrato §4). Mientras `revistalogos-classic` siga instalado, un cambio del plugin no se publica si rompe el clásico.
4. **La copia clásica.** Vive en `wordpress/wp-content/themes/revistalogos-classic/`. Se toma de `main` después de [#38](https://github.com/refo44/demo-revistalogos/issues/38), para que lleve el CSS adicional ya migrado. `Theme Name: Revista LOGO ET SPES (clásico)`. Conserva `Text Domain: revistalogos`, las constantes y las funciones con el mismo nombre (WordPress solo carga el theme activo), el dequeue de `global-styles` y las tres ubicaciones de menú. Tiene su propio `Version:`. Solo recibe arreglos que impidan usarlo como reserva; el trabajo nuevo va al block theme.
5. **El corte son dos releases** (ADR 0020, cada una con su tag):
   - **A, desde `main`, antes de fusionar `feat/fse`:** añade `revistalogos-classic` y su paso FTPS. El Administrador lo activa. Gate: portada y un artículo idénticos a los de `revistalogos`, y las tres ubicaciones de menú asignadas.
   - **B, el corte de [#84](https://github.com/refo44/demo-revistalogos/issues/84):** fusionar `feat/fse` → `main`, tag, FTPS. No se lanza mientras el theme activo en producción siga siendo `revistalogos`, porque el FTPS lo convertiría en block theme sin pasar por wp-admin. El block theme llega inactivo, y el Administrador lo activa cuando el propietario decide.
6. **Volver atrás.** Activar `revistalogos-classic` en Apariencia → Temas. Las plantillas y los Estilos guardados en el Site Editor quedan asociados a `revistalogos` y vuelven al reactivarlo. Tras cada cambio de theme se comprueban las asignaciones de menú: WordPress traslada las ubicaciones con el mismo nombre, pero no se da por hecho.
7. **Despliegue y comprobaciones.** En la release A, `deploy-wordpress.yml` gana un paso FTPS para `revistalogos-classic` con su propio secreto de ruta remota, y `tools/check-release-pending.sh` vigila el `Version:` de los dos themes. Nunca se despliega un theme sin el otro mientras los dos estén instalados.
8. **Retirada.** Por decisión del propietario, no antes de publicar un número completo con el block theme activo. Retirar es una release: desinstalar en wp-admin, borrar el directorio y el paso FTPS.
