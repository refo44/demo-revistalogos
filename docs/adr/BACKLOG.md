# Backlog de decisiones (ADR pendientes)

Lista de decisiones a convertir en ADR, **una por una**. Al resolver cada una: se escribe el archivo `NNNN-*.md`, se añade su fila al índice de `README.md` y se marca aquí como resuelta.

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

---

## Restricciones ya fijadas por el propietario (2026-07-23)

Se registran aquí hasta convertirlas en su ADR correspondiente:

- **Repositorio compartido (monorepo):** el sitio estático y WordPress conviven en el **mismo repositorio**. → condiciona **D6**.
- **Despliegue solo manual:** el workflow de GitHub Actions se conserva para el sitio estático pero se ejecuta **únicamente por disparo manual** (`workflow_dispatch`); nada se despliega en automático. → condiciona **D8**.
- **CI/CD automático aplazado:** sin triggers de `push`/`schedule`/`pull_request` por ahora. → condiciona **D12**.
- **Dominio de producción:** `https://logo-et-spes.cenfiss.net/` (Hostinger). ✅ **Resuelto el 2026-07-28:** las 38 referencias absolutas del HTML (16 `canonical`, `og:url`/`twitter:url`, imágenes sociales y bloques JSON-LD) apuntan ya al dominio principal, en forma **sin extensión y sin barra final**, la convención que documenta `sitemap.xml` y aplica el `.htaccess`. *Nota: la caracterización previa de «obsoletos» era imprecisa —la dirección de GitHub Pages sigue viva—; el defecto era que declaraban canónica la copia en lugar de la principal.*
- **Indexación bloqueada hasta el lanzamiento:** `robots.txt` permanece en `Disallow: /` hasta que la migración a WordPress esté terminada y validada y esté cargado el contenido editorial real. Solo entonces se abre la indexación y se añade la línea `Sitemap:`. Es el brazo de aplicación de **D4** (datos dummy excluidos de producción); se documentará en el ADR 0004 y en el checklist de lanzamiento (doc 17).

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

Estado en el repo: `.github/workflows/deploy.yml` ya es `workflow_dispatch`-only (cumple). `deploy.sh` **eliminado** el 2026-07-23 (hacía `git push` y mencionaba GitHub Pages y una ruta `prototype/` inexistente, incoherente con el despliegue manual por FTPS a Hostinger). El despliegue al host se dispara solo desde Actions → «Deploy to Hostinger» → Run workflow. **El de GitHub Pages, en cambio, se publica solo en cada push a `main`** (verificado el 2026-07-28).

---

## Orden sugerido

1. Grupo A (D1–D5) — registrar en una sesión; son actas, no debates.
2. Grupo B por dependencia: **D6 → D7 → D8** primero (desbloquean tareas de enlaces, despliegue y scaffold).
3. D9–D12 después; no bloquean la construcción.

Regla: se resuelve **una decisión a la vez**, con sus alternativas y consecuencias, para conservar el razonamiento.
