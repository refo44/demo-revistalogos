# Fase 3 — Matriz de validación

Evidencia durable de QA de la Fase 3. Estados permitidos: `Pass`, `Fail`,
`Unverified`. Nada se marca `Pass` sin evidencia de ejecución; lo que el entorno
local no puede ejecutar (sin PHP/WP-CLI/WordPress, ver
`docs/fase3-execution-state.md` §Decisions) queda `Unverified` hasta que exista
runtime.

Formato de cada fila: validación, método, resultado, estado, commit probado.

## Nivel 1 — Estático

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |
| Baseline checksums CSS/JS del estático | `shasum -a 256` | 12 hashes registrados en execution-state | Pass | 5fedf8a |

## Nivel 2 — Componente

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |

## Nivel 3 — Integración

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |

## Nivel 4 — Regresión de cara al usuario

| Validación | Método | Resultado | Estado | Commit |
| ---------- | ------ | --------- | ------ | ------ |

## Matriz de cobertura static → WordPress

| Static source | WordPress template | Shared part | Dynamic source | Status | Validation | Known differences |
| ------------- | ------------------ | ----------- | -------------- | ------ | ---------- | ----------------- |
| index.html | front-page.php | hero-current-issue, article-card, issue-card | current issue query, latest articles/news | Pendiente | — | — |
| noticias.html | home.php | pagination, content-none | posts page query | Pendiente | — | — |
| page-acerca.html | page-acerca.php | content-institutional-page | page content (migración) | Pendiente | — | — |
| page-contacto.html | page-contacto.php | content-institutional-page + región CF7 | page content + CF7 | Pendiente | — | — |
| page-enlaces.html | page-enlaces.php | content-institutional-page, external-link-note | page content | Pendiente | — | — |
| page-enviar-colaboracion.html | page-enviar-colaboracion.php | content-institutional-page | page content + adjuntos | Pendiente | — | — |
| page-etica.html | page-etica.php | content-institutional-page | page content | Pendiente | — | — |
| page-normas.html | page-normas.php | content-institutional-page | page content + adjuntos | Pendiente | — | — |
| page-politicas.html | page-politicas.php | content-institutional-page | page content | Pendiente | — | — |
| page-privacidad.html | privacy-policy.php | content-institutional-page | página de Ajustes → Privacidad | Pendiente | — | — |
| page-comite-editorial.html | page-comite-editorial.php | content-institutional-page | page content | Pendiente | — | — |
| archive-issue.html | archive-issue.php | issue-card, pagination, content-none | WP_Query issue | Pendiente | — | — |
| single-issue.html | single-issue.php | toc, metadata-box, breadcrumbs | issue + artículos vinculados | Pendiente | — | — |
| archive-article.html | archive-article.php | article-card, pagination, content-none | WP_Query article (+ taxonomías) | Pendiente | — | — |
| single-article.html | single-article.php | metadata-box, breadcrumbs, author-card | article + autores + issue | Pendiente | — | — |
| archive-author.html | archive-author.php | author-card, pagination | WP_Query author | Pendiente | — | — |
| single-author.html | single-author.php | article-card | author + artículos vinculados | Pendiente | — | — |
| single-post.html | single.php | breadcrumbs | post | Pendiente | — | — |
| search.html | page-buscar.php (+ search.php delegado) | article-card, pagination, content-none | WP_Query acotada por `q` | Pendiente | — | — |
| 404.html | 404.php | content-none | — | Pendiente | — | — |
