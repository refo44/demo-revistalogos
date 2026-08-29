# Backlog de decisiones (ADR pendientes)

Lista de decisiones a convertir en ADR, **una por una**. Al resolver cada una: se escribe el archivo `NNNN-*.md`, se añade su fila al índice de `README.md` y se marca aquí como resuelta.

El trabajo de **implementación o proceso ya aceptado y aún no hecho** vive en [Trabajo pendiente aceptado](#trabajo-pendiente-aceptado). No es un segundo ADR: enlaza las decisiones ya tomadas. No implementar desde este archivo.

Origen: análisis comparativo con el proyecto hermano *Camino del Dharma* (mismo patrón «maqueta estática → WordPress, sin rediseño») y con los invariantes ya afirmados en `docs/17-implementation-order` y `docs/12-theme-file-structure`.

> **Numeración:** el número de ADR se asigna **secuencialmente al redactar**. Los números de la columna «ADR propuesto» de abajo son **indicativos** y se desplazan si se intercalan decisiones nuevas (p. ej. la política de plugins tomó el 0006).

---

## Grupo A — registrar decisiones ya tomadas

Invariantes que los documentos numerados ya afirman; el ADR los hace vinculantes y trazables. Baja deliberación.

| ID | ADR propuesto | Qué fija | Estado |
| -- | ------------- | -------- | ------ |
| D1 | [0001](0001-maqueta-estatica-como-base-definitiva.md) — Maqueta estática como base definitiva | La maqueta es el contrato visual congelado | ✅ Resuelta |
| D2 | [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) — WordPress como adaptación sin rediseño | Invariante «no rediseñar» (doc 17 §2.3) | ✅ Resuelta |
| D3 | [0003](0003-css-y-tokens-invariantes-en-la-migracion.md) — CSS y tokens invariantes en la migración | Arquitectura CSS y tokens no cambian | ✅ Resuelta |
| D4 | [0004](0004-datos-dummy-excluidos-de-produccion.md) — Datos dummy excluidos de producción | Lista «prohibido migrar» (doc 17 §3.1) | ✅ Resuelta |
| D5 | [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) — Modelo de contenido: CPTs, taxonomías y plugin propio | issue/article/author/submission + section/article_type/keyword | ✅ Resuelta |

## Decisiones transversales resueltas

Principios que gobiernan varias decisiones de abajo.

| ID | ADR | Qué fija | Estado |
| -- | --- | -------- | ------ |
| DT1 | [0006](0006-politica-de-dependencias-de-plugins.md) — Política de dependencias de plugins | Minimizar terceros; nativo/propio primero; solo plugins gratis, muy usados y mantenidos. Gobierna D9, D10, D12. | ✅ Resuelta |

## Grupo B — decisiones genuinamente abiertas

Requieren elegir entre alternativas antes o durante la construcción del theme. (Números de ADR indicativos; se asignan al redactar.)

| ID | Decisión a tomar | Desbloquea | Estado |
| -- | ---------------- | ---------- | ------ |
| D6 | Layout del monorepo → **`static/` + `wordpress/`** (ejecutar al iniciar Fase 3) | Scaffold del theme | ✅ Resuelta ([0007](0007-layout-del-monorepo-static-y-wordpress.md)) |
| D7 | Política de URLs → **con barra final** (default WP, KISS/YAGNI); enlaces del theme vía `get_permalink()` | ~~Conversión de enlaces~~ (ya no necesaria) | ✅ Resuelta ([0008](0008-politica-de-urls.md)) |
| D8 | Despliegue → **FTPS** + fuentes de verdad duales + deploy acotado (theme/plugin) + staging en subdominio noindex; un workflow tras el corte | Arreglo de despliegue | ✅ Resuelta ([0009](0009-mecanismo-y-alcance-del-despliegue.md)) |
| D9 | Formulario de contacto → **Contact Form 7** + honeypot (no reCAPTCHA) + solo correo (sin BD) | — | ✅ Resuelta ([0010](0010-formulario-de-contacto.md)) |
| D10 | Analítica y privacidad → **analítica propia sin cookies desde la v1** (WP Statistics); GA4 aplazado a fase posterior con asesoría legal; cero cookies como invariante duro (los terceros, preferencia blanda); **sin banner**; página de privacidad propia y provisional | Aviso de privacidad de D9; CSP de D12a | ✅ Resuelta ([0011](0011-analitica-y-privacidad.md)) |
| D11 | Alojamiento de PDFs → **Media Library** | — | ✅ Resuelta (en [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) §5) |
| D12a | Cabeceras de seguridad → **redirección HTTPS + 4 cabeceras reversibles ahora**; **HSTS y CSP tras la auditoría profesional**; nunca `preload` | — | ✅ Resuelta ([0012](0012-cabeceras-de-seguridad.md)) |
| D12b | Momento de automatización CI/CD | — | ⏳ Pendiente — se decide **tras la auditoría profesional** (ADR 0012 §6) |
| D13 | Identificadores académicos → **DOI (Crossref) y ORCID**: arquitectura resuelta (API pública ORCID gratuita, código propio); implementación técnica en nueva **Fase 4** de `docs/17` (posterior a WordPress); DOI tratado como coste editorial/legal (igual que ISSN/Depósito Legal), no como gasto de software — el trámite con Crossref (Sponsor, alta de cuenta) procede ya, en paralelo, sin esperar a esa fase | Aviso de privacidad (`page-politicas` §6); backfill del checklist de `docs/17` | ✅ Resuelta ([0013](0013-identificadores-academicos-doi-orcid.md)) |
| D14 | Clasificación de la sección de noticias/blog (CPT nativo `post`) → ¿se le añade una taxonomía (propia, tipo `keyword`, o nativa `category`/`post_tag` reactivada) para agrupar noticias, o se mantiene sin clasificar? Detectado 2026-07-31: `post_tag`/`category` existen en WordPress por defecto pero ni el plugin ni el theme las registran, renderizan ni enlazan — hoy son datos muertos si se usan desde el panel. **No se implementa todavía**; se abre solo para no perder el hallazgo. | Alcance de una futura plantilla `category.php`/`tag.php` si se resuelve por «sí» | ⏳ Pendiente |
| D15 | Tipo de theme → **block theme (FSE)** + Site Editor + paleta en Estilos; Next.js / headless **rechazado** | FSE incremental en Docker **después** de estabilizar producción clásica (el corte 2026-08-19 no esperó al gate FSE) | ✅ Resuelta ([0015](0015-block-theme-fse-site-editor.md)); implementación pendiente |
| D16 | Topología de hosting → **cPanel `cenfiss2`**, no panel Hostinger; corte WP **in situ** en `logo-et-spes.cenfiss.net`; sin subdominios nuevos | Secretos FTPS y Softaculous | ✅ Resuelta ([0016](0016-topologia-hosting-cpanel.md)); **corte ejecutado 2026-08-19** |
| D17 | Generación automática del PDF de artículo al publicar → **arquitectura aceptada** ([0017](0017-generacion-automatica-pdf-articulo.md)): `pdf_file` sigue siendo un ID de Media Library; ajuste wp-admin **OFF por defecto**; ON: PDF válido se conserva; si falta, generar; si falla, bloquear; no pisar un PDF válido; no regenerar al guardar; no backfill en upgrade. **Testing Foundation hecha** ([0018](0018-testing-foundation.md), `docs/23`). **WU1–WU6B** política, adaptador, orquestación, renderer, persistencia, source HTML, composición explícita y enforcement classic/REST. PDF de número: fuera de v1. Producción permanece OFF hasta decisión del propietario. | Activación en producción: decisión aparte del propietario (no auto-enable). WU7: [trabajo pendiente](#trabajo-pendiente-aceptado) | ✅ Resuelta ([0017](0017-generacion-automatica-pdf-articulo.md)); implementación local WU1–WU6B; hotfix 0.2.8 desplegado ([issue #9](https://github.com/refo44/demo-revistalogos/issues/9) cerrado); WU7 no iniciado |

---

## Restricciones ya fijadas por el propietario (2026-07-23)

Se registran aquí hasta convertirlas en su ADR correspondiente:

- **Repositorio compartido (monorepo):** el sitio estático y WordPress conviven en el **mismo repositorio**. → condiciona **D6**.
- **Despliegue solo manual:** el workflow de GitHub Actions se conserva para el sitio estático pero se ejecuta **únicamente por disparo manual** (`workflow_dispatch`); nada se despliega en automático. → condiciona **D8**.
- **CI/CD automático aplazado:** sin triggers de `push`/`schedule`/`pull_request` por ahora. → condiciona **D12**.
- **Dominio de producción:** `https://logo-et-spes.cenfiss.net/` (Hostinger). ✅ **Resuelto el 2026-07-28:** las 38 referencias absolutas del HTML (16 `canonical`, `og:url`/`twitter:url`, imágenes sociales y bloques JSON-LD) apuntan ya al dominio principal, en forma **sin extensión y sin barra final**, la convención que documenta `sitemap.xml` y aplica el `.htaccess`. *Nota: la caracterización previa de «obsoletos» era imprecisa —la dirección de GitHub Pages sigue viva—; el defecto era que declaraban canónica la copia en lugar de la principal.*
- **Indexación bloqueada hasta el lanzamiento:** `robots.txt` permanece en `Disallow: /` hasta que la migración a WordPress esté terminada y validada y esté cargado el contenido editorial real. Solo entonces se abre la indexación y se añade la línea `Sitemap:`. Es el brazo de aplicación de **D4** (datos dummy excluidos de producción); se documentará en el ADR 0004 y en el checklist de lanzamiento (doc 17). *Nota de estado 2026-08-19 (no reescribe la restricción original):* WordPress clásico ya está live; contenido editorial real en proceso de carga desde wp-admin, **no** completa. Indexación **considerada cerrada** (verificar; no asumir abierta). Abrirla es decisión explícita del propietario; **no** la abre el deploy. Completar el 100 % del contenido editorial **no** es prerequisito. **Excepción de propietario 2026-08-19:** un bootstrap editorial temporal (`_les_fixture=1`, kind `bootstrap`, sin identificadores falsos) puede existir durante la carga; **no** abrir indexación mientras queden fixtures temporales públicos (preferencia: recuento 0). El dataset demo de fixtures sigue prohibido en producción. Launch gate: `docs/operations/produccion-wordpress.md`.

### Añadidas el 2026-07-28

- **Público objetivo:** la revista es venezolana y está registrada en Venezuela, pero se dirige al público hispanohablante de **España y Latinoamérica**. → condicionó **D10** (activa el RGPD por la vía del art. 3(2)(a)).
- **Analítica en dos pasos:** se implementa **ya** una analítica propia, autoalojada y sin cookies (**WP Statistics**), muy antes que GA4; **GA4 queda para una fase posterior y con asesoría legal previa**. → recogido en ADR 0011 §2.
- **Cumplimiento por jurisdicción:** el RGPD es la **línea base** de diseño, no el punto final; en fases posteriores se harán **auditorías incrementales país por país**. → recogido en ADR 0011 §1.
- **Buzón en el dominio:** está **por verificar** si el plan de hosting contratado incluye correo en `cenfiss.net`. De ello depende poder sacar a Google de la cadena de destinatarios del formulario. → recogido en ADR 0011 §7.
- **«Cero terceros» no es un invariante.** Se usarán recursos de terceros cuando convengan (p. ej. emojis del núcleo de WordPress o Gravatar). Lo que se quiere evitar es el **infierno de mantenimiento de plugins**, que ya gobierna ADR 0006. Si hay que actualizar el aviso de privacidad, se actualiza. → recogido en ADR 0011 §3 y ADR 0012 §5.
- **Auditoría profesional de SEO y seguridad:** se encargará **después** de que la migración esté terminada y WordPress sirva en la URL principal. **HSTS no se activa hasta que esa auditoría concluya**, y la automatización CI/CD se decide con su información. → recogido en ADR 0012 §3 y §6.
- **Dos despliegues públicos, ambos se mantienen por ahora:** `logo-et-spes.cenfiss.net` (Hostinger, principal, despliegue manual) y `refo44.github.io/demo-revistalogos` (GitHub Pages, copia, **publica en automático en cada push a `main`**). Verificado el 2026-07-28: GitHub Pages **no devuelve ni admite cabeceras de seguridad**, así que la protección de ADR 0012 solo alcanza a la principal; ambos sirven `robots.txt` con `Disallow: /`. → recogido en ADR 0012 (Contexto) y en el aviso de privacidad (GitHub declarado como proveedor).
  - **Ojo con D12b:** ya existe un modelo de despliegue en dos niveles, y **es deliberado**. La publicación automática en cada push a GitHub Pages **es el comportamiento que se quiere**: los usuarios beta esperan ver el último estado sin esperar a nadie (propietario, 2026-07-28). La restricción «nada se despliega en automático» del 2026-07-23 aplica a **producción**, no a la URL de revisión.

    | Nivel | Destino | Disparo | Intención |
    | ----- | ------- | ------- | --------- |
    | Revisión | GitHub Pages | Automático en cada push a `main` | Querido: los beta testers ven el último estado al momento |
    | Producción | Hostinger | Manual (`workflow_dispatch`) | Querido: nada llega al dominio principal sin decisión explícita |

    D12b no consiste, por tanto, en decidir «si automatizar»: eso ya está resuelto y funciona. Consiste en decidir si se añaden **comprobaciones** automáticas (`stylelint`, validación de HTML, enlaces rotos) a ese flujo, que es una tercera cosa distinta de ambos despliegues.
  - **GitHub Pages es el prototipo de pruebas y de revisión por usuarios beta**, y se retirará en una fase posterior cuando esa revisión concluya (propietario, 2026-07-28). De ahí que la asimetría de cabeceras de ADR 0012 sea temporal y aceptable, y que la dirección canónica sea la de Hostinger.
  - **Diferencia conocida y asumida:** GitHub Pages no aplica el `.htaccess`, así que no tiene cabeceras de seguridad y sus URL no redirigen como producción (`page-acerca.html` responde 200 en vez de redirigir 301 a `/page-acerca`). Los usuarios beta ya lo saben; se anota como contexto, no como pendiente.
  - **D7 (canónicas): resuelto** — ver arriba, los `canonical` apuntan ya a la principal.
  - **Comprobado el 2026-07-28** que **nada en el pipeline reescribía las canónicas**: el workflow es un `cp` sin `sed`, no hay más workflows ni scripts, y ambos despliegues servían la etiqueta apuntando a GitHub Pages. El `.htaccess` sí canonicaliza **URL** (redirige `.html` a la forma sin extensión y quita barras finales), que es cosa distinta de la etiqueta `<link rel="canonical">`.

### Añadidas el 2026-07-29

- **La revista se publica en papel y en digital, con los mismos artículos.** CENFISS ya paga y gestiona ISSN y Depósito Legal de la versión **impresa** ante la Biblioteca Nacional de Venezuela; el trámite de la versión **digital** (su propio e-ISSN, distinto del impreso) sigue pendiente. → recogido en ADR 0013 §Contexto («Precisión 2») y en `docs/22-identificadores-academicos-doi-orcid` §2.2.
- **El registro DOI (Crossref) se paga con presupuesto editorial/legal de la revista, no con el presupuesto de software del sitio.** El propietario encuadra el DOI en la misma categoría que ISSN/Depósito Legal: un coste de la publicación como tal, no un «plugin o servicio de pago» en el sentido de ADR 0005. → recogido en ADR 0013 §Contexto («Precisión 1») y §2.1; **matiza el alcance de ADR 0005**, que sigue gobernando el software del sitio sin cambios.
- **No hacen falta cuentas de usuario para publicar la primera edición.** El equipo editorial crea la ficha pública de autor directamente; el sistema de cuentas/login para autores sigue siendo el subsistema de envíos ya aplazado por ADR 0005 §4. → confirmado (no es una decisión nueva) en ADR 0013 §7.
- **WordPress (Fase 3) todavía no está implementado** — el repositorio sigue en la Fase 2 (maqueta estática); solo existe la maqueta HTML. La implementación de ORCID es, por decisión del propietario, una **fase posterior a WordPress**, no parte de la Fase 3 ni anterior a que exista. Por dependencia técnica (vive en `revistalogos-core`), lo mismo aplica al código de depósito DOI. → recogido en ADR 0013 §Contexto («Precisión 4») como nueva **Fase 4: Identificadores académicos** en `docs/17-implementation-order`; no alcanza a los trámites administrativos con Crossref, que sí avanzan ya (punto anterior). *Nota de estado 2026-08-19 (histórica la frase «todavía no está implementado»):* WordPress clásico live en producción; carga editorial iniciada y actualmente en curso. Fase 4 ORCID/DOI sigue posterior.

### Añadidas el 2026-08-16

- **Block theme / Site Editor:** el theme público pasa a FSE (Gutenberg). Colores de marca (paleta impresa vs hex provisionales) se editan en Estilos, no en `tokens.css`. Next.js como front público **descartado**. → [ADR 0015](0015-block-theme-fse-site-editor.md).
- **Hosting:** el panel no es Hostinger; es **cPanel cuenta `cenfiss2`**. En el mismo disco: WP+Moodle en `cenfiss.net`, **WordPress clásico de la revista** en `logo-et-spes.cenfiss.net` (corte 2026-08-19), Laravel muerto en `test.cenfiss.net`. **No se crean subdominios.** FTP `deploy_revista@…` enjaulado a la revista. → [ADR 0016](0016-topologia-hosting-cpanel.md). Snapshot: `docs/operations/produccion-wordpress.md`. Matiza el «subdominio de staging» de D8/0009 sin anular FTPS manual.
- **CI/CD:** D12b sigue pendiente (auditoría). El workflow estático `deploy.yml` («Deploy to Hostinger») está **retirado**. Production deploy: `deploy-wordpress.yml`, Environment `wordpress-production`. Pages: `pages.yml`.

Estado en el repo: `.github/workflows/deploy.yml` («Deploy to Hostinger») **eliminado** el 2026-08-19 tras el corte WP. `deploy.sh` **eliminado** el 2026-07-23. El código de WordPress se dispara desde Actions → «Deploy WordPress theme+plugin to production» → Run workflow. El espejo de GitHub Pages se publica en cada push a `main` (deliberado; 2026-07-28).

### Añadidas el 2026-08-19

- **Bootstrap editorial temporal en producción:** el administrador de wp-admin puede recibir **una** estructura temporal (un número, un artículo, un autor) para sustituirla por el primer volumen real. No es el dataset demo. Sin DOI/ORCID/ISSN falsos. Marcados `_les_fixture=1`. Indexación cerrada mientras queden fixtures temporales públicos (preferencia: recuento 0). Comando: `wp revistalogos fixtures bootstrap`. **No ejecutado** en esta fecha. Matiza D4/ADR 0004 en implementación, no reescribe la decisión original.
  *Nota de implementación 2026-08-19:* el código vigente (plugin 0.2.3)
  aplica **Option 2**: Volume 1 editorial bootstrap (`_les_bootstrap*`,
  adopción por hash) adapta títulos/abstracts/secciones/orden de la maqueta
  Vol. 12 Nº 2 a Vol. 1 Nº 1; reutiliza `rafael-eduardo-figueredo-oropeza`;
  no crea autores dummy ni escribe DOI/ORCID/ISSN/paginación falsos. Sigue
  sin ejecutarse en producción.

### Añadidas el 2026-08-20

- **PDF de artículo al publicar:** el contenido WordPress del artículo es la fuente del PDF **generado**; `pdf_file` sigue siendo un ID de adjunto. Ajuste wp-admin `revistalogos_article_pdf_publication_enforcement`, **OFF por defecto** (ausente = OFF). Testing Foundation **hecha** ([0018](0018-testing-foundation.md)). ADR 0017 WU1–WU6B implementados. OFF: PDF opcional y manual; publicar sin PDF permitido. ON: PDF válido se conserva; si falta, se genera; si falla, no se publica; guardar un publicado no regenera; el upgrade no rellena ni despublica ni activa la opción. FSE solo lee `pdf_file`. PDF de número fuera de v1. Activación en producción: decisión del propietario. → [ADR 0017](0017-generacion-automatica-pdf-articulo.md).

### Añadidas el 2026-08-24

- **Producción WordPress solo desde release etiquetado:** merge a `main` no es un FTPS. GitHub Pages sigue automático. Antes de `deploy-wordpress.yml`: versionar (`package.json` / `VERSION.md` / `CHANGELOG.md`), etiqueta anotada `vX.Y.Z`, Run workflow **desde esa tag**. El workflow aborta sin ella. No disparo al pushear el tag (ADR 0009 §5 intacto). Plugin 0.2.8 live no se retiqueta; el próximo envío es un tag **nuevo**. → [ADR 0020](0020-despliegue-produccion-desde-etiqueta.md).
- **Borrar rama al mergear:** Settings → General → Pull Requests → *Automatically delete head branches* (`delete_branch_on_merge`) **activado**. No es una regla del ruleset. `main` y las etiquetas no se tocan. Ramas remotas ya mergeadas que quedaban: eliminadas. → [ADR 0019](0019-proteger-main-trunk-based.md) § Estado de implementación.

---

## Orden sugerido

1. Grupo A (D1–D5) — registrar en una sesión; son actas, no debates.
2. Grupo B por dependencia: **D6 → D7 → D8** primero (desbloquean tareas de enlaces, despliegue y scaffold).
3. D9–D12 después; no bloquean la construcción.
4. D16 corte **hecho** (2026-08-19, theme clásico). D15 → FSE incremental en Docker **después** de QA de producción (sin esperar D12b ni D14).
5. D17 arquitectura **hecha** (2026-08-20). Testing Foundation **hecha** (ADR 0018, 2026-08-20). Implementación de 0017 **WU1–WU6B completa en local** (enforcement default OFF). Hotfix 0.2.8 desplegado ([issue #9](https://github.com/refo44/demo-revistalogos/issues/9) cerrado). FSE, PDF de número y WU7: ver [Trabajo pendiente aceptado](#trabajo-pendiente-aceptado). Activación PDF en producción sigue siendo decisión aparte. FTPS de producción: [ADR 0020](0020-despliegue-produccion-desde-etiqueta.md).

---

## Trabajo pendiente aceptado

Trabajo **ya aceptado** y **no iniciado** (o no cerrado en producción). No es un ADR nuevo: las decisiones siguen en los ADR enlazados. Estados: **NEXT**, **PLANNED**, **DEFERRED**. Sin fechas ni puntos. Cursor no implementa, no commitea, no despliega desde esta lista.

**Completado — no es backlog:** ADR 0017 WU1–WU6B (generación al publicar, enforcement configurable, default OFF). Hotfix plugin **0.2.8** en producción ([issue #9](https://github.com/refo44/demo-revistalogos/issues/9) cerrado 2026-08-25; transfer Pass). ADR [0019](0019-proteger-main-trunk-based.md) (ruleset de `main` activo; docs por PR). ADR [0020](0020-despliegue-produccion-desde-etiqueta.md) (FTPS de producción solo desde etiqueta `vX.Y.Z`; merge ≠ deploy). Tag Git `v0.2.0` **existe** (anotada, 2026-08-18).

**No forma parte de ADR 0017 WU7:** backfill automático; regeneración al guardar un artículo; PDF de número; FSE; borrado permanente de PDFs históricos; regeneración masiva (salvo aprobación posterior aparte).

> **Homónimo:** el WU7 de la tabla Fase 3 en `docs/fase3-execution-state.md` es **fixtures** (hecho). **ADR 0017 WU7** es generación/regeneración/historial manual de PDF de artículo (no iniciado).

### NEXT / estabilización

#### 1. Checkpoint de producción — plugin 0.2.8

**Estado:** HECHO ([issue #9](https://github.com/refo44/demo-revistalogos/issues/9) cerrado 2026-08-25 por el propietario).

Plugin `revistalogos-core` **0.2.8** y theme **0.2.1** en producción. Transfer: Actions run `32698488419` (`d9bf6d2`); live `readme.txt` `Stable tag: 0.2.8`. Default OFF. Sin backfill. Smoke Gutenberg wp-admin no anotado como Pass aparte. Próximo FTPS: etiqueta nueva (ADR 0020), no `v0.2.0`. WU7 no iniciado.

#### 2. Proteger `main` + trunk-based ligero

**Estado:** HECHO (ruleset GitHub 2026-08-24; ADR [0019](0019-proteger-main-trunk-based.md)). Documentación de esta decisión entra por PR en `chore/protect-main-ruleset`.

Ruleset `Protect main (trunk-based)` (`21337399`), activo, sin bypass. `main` exige PR; 0 approvals base (ver ADR 0019 sobre la regla de +1 approval para PRs de Copilot sin atribución); check `PHP lint, Composer audit, and unit (PHP 8.3)`; sin force-push ni borrado. Trunk-Based Development: ramas cortas, sin `develop`. GitHub **borra la rama head al mergear** (`delete_branch_on_merge`; no es el ruleset). Nombres: [Conventional Branch 1.1.0](https://conventionalbranch.org/). Mensajes: [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/). Ambos por convención, no en el ruleset. Preferir squash. Cursor **sigue sin** commit, push, merge ni deploy.

### PLANNED

No hay dependencia ADR que fuerce el orden entre diseño editorial y WU7. Se planifica **diseño antes de WU7** para que Generate/Regenerate consuman la misma plantilla (evitar dos sistemas de presentación). El spike de la sección «Cómo Citar» (ítem 9) es **independiente** de 3 y 4: presentación del theme clásico, no PDF.

#### 3. Diseño editorial profesional del PDF de artículo

**Estado:** IMPLEMENTADO en rama `feat/article-pdf-editorial-design`
(2026-08-28, [issue #10](https://github.com/refo44/demo-revistalogos/issues/10));
pendiente de PR/merge del propietario. Mockup aprobado: **opción 1 «Clásico
filológico»** (separata centrada, DejaVu Serif, **escala de grises, sin
color** — decisión del propietario). Plantilla:
`Article_Pdf_Editorial_Template` (pura, PHPUnit) + Source Builder como
recolector (issue publicado, resúmenes, palabras clave, afiliación/ORCID/DOI
inertes, fechas en español; campos vacíos omitidos). TDD: unit +
suite PHPUnit/WordPress (`tools/run-phpunit-wp.sh`, autorizada por el
propietario) + re-run composición. WU7 (ítem 4) debe consumir esta plantilla.

El PDF individual debe parecer un artículo de revista académica arbitrada: sobrio, imprimible, citable, autónomo fuera del sitio, reconocible como LOGO ET SPES. Coherente con la web, **no** un printout de la página.

Antes de implementar: mockup de referencia aprobado (primera página, interior, referencias). Ese mockup es la aceptación visual.

Explorar/implementar en esa WU (ADR 0017 §5/§7 y diferidos de WU6A: número, DOI, páginas, taxonomías, resúmenes, imágenes remotas — no reabrir WU6A):

- portada: identidad LOGO ET SPES, volumen, número, año, ISSN si hay, título, autores, afiliación/ORCID/DOI/fechas editoriales si hay, resumen/abstract, palabras clave;
- cuerpo: tipografía académica, A4, márgenes, jerarquía, citas, imágenes, tablas, pies, bibliografía, saltos de página razonables;
- marco: números de página; encabezado/pie si Dompdf lo sostiene; DOI/ORCID/URL clicables si es práctico.

Arquitectura: Article WP → Source Builder → HTML semántico → plantilla/CSS PDF → **Dompdf** → PDF. Presentación separada de enforcement y persistencia. Dominio en `revistalogos-core` (ADR 0005). FSE/theme no genera PDFs. No cambiar de Dompdf salvo prueba concreta de insuficiencia.

WU7 debe usar esta plantilla canónica.

#### 4. ADR 0017 WU7 — Generar / Regenerar / historial recuperable / Restaurar

**Estado:** PLANNED. **No iniciado.** Independiente de exigencia ON/OFF.

Problema: hay artículos ya publicados sin PDF; ADR 0017 **no** hace backfill.

1. Publicado sin `pdf_file` válido: acción wp-admin «Generar PDF» desde el contenido actual; adjunto normal de Media Library; asignar `pdf_file`; el artículo sigue publicado.
2. Con PDF válido: «Regenerar PDF» con confirmación explícita.
3. Regeneración segura: **nunca** borrar ni desvincular el PDF actual antes de generar el nuevo con éxito; si falla, `pdf_file` no se toca; si acierta, el nuevo pasa a activo.
4. Historial recuperable: el PDF activo anterior entra en historial del artículo; el adjunto **no** se borra solo; el historial se ve en wp-admin.
5. Restaurar: PDF histórico → activo con confirmación; el que estaba activo pasa a historial; reversible.
6. Regresión: A activo y B histórico; restaurar B; A sigue recuperable y se puede restaurar otra vez.
7. Sin acción de borrado permanente en esta WU. Sin borrado automático de adjuntos.
8. Un save ordinario **no** genera, **no** regenera, **no** cambia el historial.
9. Sin backfill masivo en esta WU.
10. Reutilizar meta/persistencia actuales. Sin tabla SQL ni CPT nuevos salvo requisito demostrado.

Pruebas (cuando se implemente): Gherkin de negocio; QA WordPress aislada de IDs/`pdf_file`/historial; fallo de regeneración conserva el PDF viejo; restore reversible; adjuntos no borrados. TDD. No implementar ahora.

#### 9. Spike — sección «Cómo Citar» (`.citation-section`) como collapsible vertical

**Estado:** PLANNED. Spike de investigación; **no** implementar el colapso hasta go del spike. Issue: [#15](https://github.com/refo44/demo-revistalogos/issues/15).

El bloque de formatos de cita en `single-article` está siempre abierto (grid de 6 tarjetas). No hay disclosure en el theme. Investigar convertirlo en desplegable vertical con HTML/CSS nativos (BEM + tokens; sin Tailwind ni librerías; sin JS de abrir/cerrar).

**As-Is (ya confirmado en código):** `section.citation-section` → `h2` + `.citation-formats` (grid `auto-fit minmax(280px, 1fr)`) → `.citation-format` × N → `#citation-copy-status` + `.citation-actions` + `.citation-info`. Sin `height` / `overflow` / `transition` en `.citation-*`. `citation.js` exige `.citation-copy` + `previousElementSibling` = `.citation-text`, e IDs `#export-all-citations` y `#download-ris`. `#ris-data` existe **solo en WordPress** (payload de Descargar RIS); el prototipo estático no lo tiene. Superficies: `single-article.php` + `static/single-article.html` + `pages/article.css` (×2).

**To-Be (propuesta a validar):** envolver el interior en `<details class="citation-section__disclosure">` / `<summary>` (el `h2` «Cómo Citar» es el disparador) + `.citation-section__panel` (`grid-template-rows: 0fr → 1fr`) + `.citation-section__panel-inner` (`overflow: hidden`; truco Powell/Tsonev). No tocar el grid de tarjetas, `inc/citations.php`, ni el copy. `hidden="until-found"` **no** se aplica a `<details>`.

Preguntas del spike: qué se pliega; estado inicial (`open` vs cerrado); si el snap al cerrar (limitación nativa de `<details>`) es aceptable; paridad estático ↔ WP; Find in page de «Vancouver» (Chromium sí, resto no portable); `h2` en `<summary>` frente a AT (`docs/19`).

Cierre: As-Is/To-Be rellenados; go / no-go / go con snap al cerrar. Si go, WU aparte `feat/collapse-citation-section`. Sin commit ni deploy desde este ítem.

### DEFERRED

#### 5. PDF de número / número completo

**Estado:** DEFERRED. [ADR 0017](0017-generacion-automatica-pdf-articulo.md) §7 (fuera de v1) y D17. Ítem de backlog o ADR **aparte**. No inferir comportamiento. **No** mezclar con WU7.

#### 6. Procedencia / linaje de versión del PDF

**Estado:** DEFERRED. ADR 0017 §4/§7 (`_les_pdf_origin`, hash) aplazado en v1. El historial de WU7 puede informar un diseño futuro. **No** diseñar campos ahora.

#### 7. Migración FSE

**Estado:** DEFERRED. Decisión **aceptada** ([ADR 0015](0015-block-theme-fse-site-editor.md), D15); implementación pendiente. Incremental, **primero en Docker**, después de estabilizar el clásico. Theme/FSE **consume** datos de dominio; **no** genera ni sustituye PDFs. `revistalogos-core` sigue dueño de Article/Issue/Author (ADR 0005).

#### 8. Otros ítems ya documentados (sin reinventar)

Solo trabajo o proceso **ya decidido** y aún no cerrado. Las decisiones **abiertas** (D12b, D14) quedan únicamente en la tabla de Grupo B.

| Ítem | Estado | Autoridad |
| ---- | ------ | --------- |
| Fase 4 — validación/URLs DOI–ORCID, depósito Crossref (trámites admin pueden ir en paralelo) | DEFERRED | [ADR 0013](0013-identificadores-academicos-doi-orcid.md); `docs/17` Fase 4; `docs/22` |
| Subsistema de envíos / portal de autor / CPT `submission` | DEFERRED | [ADR 0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) §4 |
| Taxonomía `philosopher` | DEFERRED (aplazada en ADR 0005; no es una decisión abierta) | ADR 0005 |
| HSTS y CSP tras auditoría profesional; GA4 en fase posterior con asesoría legal | DEFERRED | ADR 0012 §3/§6 (D12a, no D12b); ADR 0011 §2 |
| Activar exigencia PDF en producción | LATER (decisión del propietario; default OFF) | ADR 0017; no es auto-enable |
| Indexación pública | LATER (launch gate; no la abre el deploy) | ADR 0004; `docs/operations/produccion-wordpress.md` |
| e-ISSN digital / ISSN «en trámite» | LATER (trámite editorial, no software) | ADR 0013; ADR 0004 |
| Backlog **operativo** de producción (CF7/WP Statistics en el live, Softaculous, restos HTML, permalinks, SpeedyCache, secreto FTP legado, fuente de GitHub Pages) | no duplicar aquí | `docs/operations/produccion-wordpress.md` § Pendientes inmediatos; `docs/fase3-execution-state.md` |

Regla: se resuelve **una decisión a la vez**, con sus alternativas y consecuencias, para conservar el razonamiento.
