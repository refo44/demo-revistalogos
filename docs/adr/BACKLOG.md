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
| D9 | Formulario de contacto: nativo/propio vs plugin (según 0006); tratamiento de datos | — | ⏳ Pendiente |
| D10 | Analítica y privacidad: GA4 / cookies sí o no; página de privacidad | — | ⏳ Pendiente |
| D11 | Alojamiento de PDFs → **Media Library** | — | ✅ Resuelta (en [0005](0005-modelo-de-contenido-cpts-y-taxonomias.md) §5) |
| D12 | HSTS / cabeceras de seguridad + momento de automatización CI/CD | — | ⏳ Pendiente |

---

## Restricciones ya fijadas por el propietario (2026-07-23)

Se registran aquí hasta convertirlas en su ADR correspondiente:

- **Repositorio compartido (monorepo):** el sitio estático y WordPress conviven en el **mismo repositorio**. → condiciona **D6**.
- **Despliegue solo manual:** el workflow de GitHub Actions se conserva para el sitio estático pero se ejecuta **únicamente por disparo manual** (`workflow_dispatch`); nada se despliega en automático. → condiciona **D8**.
- **CI/CD automático aplazado:** sin triggers de `push`/`schedule`/`pull_request` por ahora. → condiciona **D12**.
- **Dominio de producción:** `https://logo-et-spes.cenfiss.net/` (Hostinger). **Pendiente**: los `<link rel="canonical">` y `og:url` del HTML aún apuntan a `refo44.github.io/demo-revistalogos` (obsoletos); corregir al resolver **D7** (política de URLs).
- **Indexación bloqueada hasta el lanzamiento:** `robots.txt` permanece en `Disallow: /` hasta que la migración a WordPress esté terminada y validada y esté cargado el contenido editorial real. Solo entonces se abre la indexación y se añade la línea `Sitemap:`. Es el brazo de aplicación de **D4** (datos dummy excluidos de producción); se documentará en el ADR 0004 y en el checklist de lanzamiento (doc 17).

Estado en el repo: `.github/workflows/deploy.yml` ya es `workflow_dispatch`-only (cumple). `deploy.sh` **eliminado** el 2026-07-23 (hacía `git push` y mencionaba GitHub Pages y una ruta `prototype/` inexistente, incoherente con el despliegue manual por FTPS a Hostinger). El despliegue al host se dispara solo desde Actions → «Deploy to Hostinger» → Run workflow.

---

## Orden sugerido

1. Grupo A (D1–D5) — registrar en una sesión; son actas, no debates.
2. Grupo B por dependencia: **D6 → D7 → D8** primero (desbloquean tareas de enlaces, despliegue y scaffold).
3. D9–D12 después; no bloquean la construcción.

Regla: se resuelve **una decisión a la vez**, con sus alternativas y consecuencias, para conservar el razonamiento.
