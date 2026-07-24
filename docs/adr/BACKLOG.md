# Backlog de decisiones (ADR pendientes)

Lista de decisiones a convertir en ADR, **una por una**. Al resolver cada una: se escribe el archivo `NNNN-*.md`, se añade su fila al índice de `README.md` y se marca aquí como resuelta.

Origen: análisis comparativo con el proyecto hermano *Camino del Dharma* (mismo patrón «maqueta estática → WordPress, sin rediseño») y con los invariantes ya afirmados en `docs/17-implementation-order` y `docs/12-theme-file-structure`.

---

## Grupo A — registrar decisiones ya tomadas

Invariantes que los documentos numerados ya afirman; el ADR los hace vinculantes y trazables. Baja deliberación.

| ID | ADR propuesto | Qué fija | Estado |
| -- | ------------- | -------- | ------ |
| D1 | [0001](0001-maqueta-estatica-como-base-definitiva.md) — Maqueta estática como base definitiva | La maqueta es el contrato visual congelado | ✅ Resuelta |
| D2 | [0002](0002-wordpress-como-adaptacion-sin-rediseno.md) — WordPress como adaptación sin rediseño | Invariante «no rediseñar» (doc 17 §2.3) | ✅ Resuelta |
| D3 | 0003 — CSS y tokens invariantes en la migración | Arquitectura CSS y tokens no cambian | ⏳ Pendiente |
| D4 | 0004 — Datos dummy excluidos de producción | Lista «prohibido migrar» (doc 17 §3.1) | ⏳ Pendiente |
| D5 | 0005 — Modelo de contenido: CPTs y taxonomías | issue/article/author/submission + section/article_type/keyword | ⏳ Pendiente |

## Grupo B — decisiones genuinamente abiertas

Requieren elegir entre alternativas antes o durante la construcción del theme.

| ID | ADR propuesto | Decisión a tomar | Desbloquea | Estado |
| -- | ------------- | ---------------- | ---------- | ------ |
| D6 | 0006 — Layout del monorepo | `static/` + `wordpress/` **vs** `theme-revistalogos/` en raíz | Scaffold del theme | ⏳ Pendiente |
| D7 | 0007 — Política de URLs | Barra final sí/no + enlaces internos absolutos de raíz | Conversión de enlaces | ⏳ Pendiente |
| D8 | 0008 — Mecanismo y alcance del despliegue | FTPS vs rsync; fuentes de verdad duales; deploy acotado | Arreglo de despliegue | ⏳ Pendiente |
| D9 | 0009 — Formulario de contacto en WordPress | Plugin vs endpoint propio; tratamiento de datos | — | ⏳ Pendiente |
| D10 | 0010 — Analítica y privacidad | GA4 / cookies sí o no; página de privacidad | — | ⏳ Pendiente |
| D11 | 0011 — Alojamiento de PDFs | Theme `assets/pdf/` **vs** Media Library | — | ⏳ Pendiente |
| D12 | 0012 — HSTS / cabeceras y CI/CD | Cabeceras de seguridad + momento de automatización | — | ⏳ Pendiente |

---

## Restricciones ya fijadas por el propietario (2026-07-23)

Se registran aquí hasta convertirlas en su ADR correspondiente:

- **Repositorio compartido (monorepo):** el sitio estático y WordPress conviven en el **mismo repositorio**. → condiciona **D6**.
- **Despliegue solo manual:** el workflow de GitHub Actions se conserva para el sitio estático pero se ejecuta **únicamente por disparo manual** (`workflow_dispatch`); nada se despliega en automático. → condiciona **D8**.
- **CI/CD automático aplazado:** sin triggers de `push`/`schedule`/`pull_request` por ahora. → condiciona **D12**.

Estado en el repo: `.github/workflows/deploy.yml` ya es `workflow_dispatch`-only (cumple). `deploy.sh` **eliminado** el 2026-07-23 (hacía `git push` y mencionaba GitHub Pages y una ruta `prototype/` inexistente, incoherente con el despliegue manual por FTPS a Hostinger). El despliegue al host se dispara solo desde Actions → «Deploy to Hostinger» → Run workflow.

---

## Orden sugerido

1. Grupo A (D1–D5) — registrar en una sesión; son actas, no debates.
2. Grupo B por dependencia: **D6 → D7 → D8** primero (desbloquean tareas de enlaces, despliegue y scaffold).
3. D9–D12 después; no bloquean la construcción.

Regla: se resuelve **una decisión a la vez**, con sus alternativas y consecuencias, para conservar el razonamiento.
