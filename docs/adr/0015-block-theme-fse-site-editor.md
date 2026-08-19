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
- Orden operativo actual: producción clásica estable → QA → limpieza del estático residual → revisar PHP → plugins aprobados → contenido real → **luego** FSE incremental, **primero en Docker**.
- Snapshot: `docs/operations/produccion-wordpress.md`.
