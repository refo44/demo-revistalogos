# Snapshot de producción WordPress (2026-08-19)

Estado factual del corte del sitio público de **estático dummy → WordPress clásico**.
No cambia decisiones de ADR: registra lo que hay en el servidor después del corte.
Detalle de topología: [ADR 0016](../adr/0016-topologia-hosting-cpanel.md).
Despliegue: [wordpress-manual-deployment.md](wordpress-manual-deployment.md),
[ADR 0009](../adr/0009-mecanismo-y-alcance-del-despliegue.md).
Reanudación: `docs/fase3-execution-state.md`.

## Sitio público

| Ítem | Valor |
| ---- | ----- |
| URL | `https://logo-et-spes.cenfiss.net` |
| wp-admin | `https://logo-et-spes.cenfiss.net/wp-admin/` |
| Document root | `/home/cenfiss2/public_html/logo-et-spes.cenfiss.net` |
| Corte | In situ sobre el mismo subdominio. Sin staging extra. |
| `cenfiss.net` | Sin cambios (WordPress institucional + Moodle). |
| `test.cenfiss.net` | Sin cambios (Laravel antiguo). |
| Theme / plugin en disco | Desplegados por FTPS el 2026-08-19 (run #1 Success) |
| Activación | **Manual en wp-admin.** El workflow no activa. Tras el corte, `revistalogos` (clásico) y `revistalogos-core` quedaron activos por acción administrativa. |
| Indexación | **Considerada cerrada.** Observada en el setup del corte: Ajustes → Lectura → «Pedir a los motores de búsqueda que no indexen este sitio». **No** asumir el checkbox actual ni el `robots.txt` vigente. El FTPS **no** la abre. Completar el 100 % del contenido editorial **no** es prerequisito. Abrirla es decisión explícita del propietario tras el launch gate de abajo. **No** abrir en esta documentación. |
| Permalinks | `/%postname%/` (Ajustes → Enlaces permanentes → Nombre de la entrada). Tras activar `revistalogos-core`, volver a guardar para regenerar rewrites de CPTs. Plugin `0.2.1`: el single `/revista/autores/{slug}/` exige un flush tras desplegar (upgrade `maybe_upgrade` o Guardar enlaces permanentes). El 404 no se corrige solo con un flush sobre `0.2.0`. |
| Fixtures | Dataset **demo** (`wp revistalogos fixtures seed`): **no** importado y **no** permitido en el live. **Excepción de propietario 2026-08-19 (actualizada):** bootstrap editorial Volume 1 (`wp revistalogos fixtures bootstrap`) — estructura Issue/Articles editable, reutiliza el autor canónico, `_les_bootstrap*`, sin DOI/ORCID/ISSN falsos. **No ejecutado** en esta tarea. |
| Contenido | WordPress clásico live en producción; carga de contenido editorial real iniciada y actualmente en proceso desde wp-admin. **No** completa. Fuente de verdad: BD + `uploads/` (ADR 0009). El FTPS de Git no despliega contenido. |
| Administración | Existe un usuario administrador asignado a esa gestión editorial. Identidad, correo y credenciales **no** se documentan aquí. |

FSE (ADR 0015) **sigue siendo la dirección futura** y **no bloqueó** este corte.
Orden operativo actual: WordPress clásico live; carga editorial real **en
proceso** desde wp-admin → QA del theme → limpieza del estático residual →
PHP 8.3 (revista, 2026-08-22) → plugins aprobados → indexación solo si el propietario lo
decide (launch gate; 100 % de contenido no es prerequisito) → FSE
incremental primero en Docker.

## Launch gate de indexación

**Estado actual:** indexación **considerada cerrada**. Verificar; no
asumir abierta. **No** abrir en esta tarea ni como efecto de
`deploy-wordpress.yml`.

El sitio ya está live. Completar el **100 %** del contenido editorial
**no** es prerequisito: el propietario puede abrir indexación antes si, tras
el gate, considera que lo ya público está listo.

Antes de abrir (este gate **no** está ejecutado; el sitemap de WordPress en
producción **no** se da por verificado aquí):

1. No hay contenido dummy/fixture **público**. Preferencia de lanzamiento:
   recuento de objetos `_les_fixture=1` = 0. Si queda alguno, debe no ser
   accesible públicamente y estar retenido a propósito (p. ej. borrador).
2. El contenido ahora publicado es real y apto para indexación pública.
3. Las páginas públicas importantes funcionan.
4. Canonical, meta, Schema.org y Highwire son coherentes.
5. `robots.txt` revisado.
6. Visibilidad para buscadores / noindex de WordPress revisada (Ajustes → Lectura).
7. Sitemap de WordPress verificado.
8. Comportamiento residual de `sitemap`/`robots` del estático reconciliado.
9. El propietario aprueba explícitamente abrir la indexación.

## Topología física

```text
/home/cenfiss2/public_html/
├── wp-admin/                         # WordPress principal de CENFISS
├── wp-content/                       # WordPress principal de CENFISS
├── wp-includes/                      # WordPress principal de CENFISS
│
├── logo-et-spes.cenfiss.net/         # instalación aislada de la revista
│   ├── wp-admin/
│   ├── wp-content/
│   │   ├── themes/revistalogos/
│   │   ├── plugins/revistalogos-core/
│   │   └── uploads/
│   ├── wp-includes/
│   ├── wp-config.php
│   ├── index.php
│   ├── .htaccess
│   └── restos del estático anterior
│
├── test.cenfiss.net/
├── moodle/
└── logo-et-spes-static-backup-2026-08-18.zip
```

La revista **no** comparte `wp-content`, core ni base de datos con el WordPress
de `cenfiss.net`.

## Runtime

Producción (observado en el corte). Son **observaciones de hosting**, no
requisitos de la aplicación:

```text
WordPress: 7.0.4
PHP efectivo en wp-admin: 8.0.30
MariaDB del servidor: 10.11.18-MariaDB-cll-lve
Apache: 2.4.68
cPanel: 136.0 (build 35)
Arquitectura: x86_64
OS: Linux
Nombre de servidor: caroni
IP compartida: 157.250.197.234
```

cPanel MultiPHP mostraba PHP 8.2 heredado; WordPress reportó **8.0.30**.
Discrepancia pendiente de revisar. **No** se cambió PHP durante el corte.
**No** aplicar MultiPHP global para «alinear» (ADR 0016 §2).

Docker local (sin cambio de política de hosting; alineado en WordPress 7.1):

```text
WordPress: 7.1 (imagen wordpress:7.1.0-php8.3-apache)
PHP: 8.3 (local/CI)
MariaDB: sin cambios (11)
URL: http://localhost:8080
```

Nota 2026-08-20: el core de producción autoactualizó a WordPress **7.1**.
El bloque de corte conserva el runtime **observado entonces** (7.0.4 /
PHP 8.0.30). PHP de producción **no** se tocó en esa fecha.

Nota 2026-08-21: Docker local y CI usan PHP **8.3**. En esa fecha
producción seguía en **8.0.30**. `config.platform.php` sigue **8.2.0**.
`Requires PHP: 7.4` sigue siendo el mínimo declarado, no el runtime.

### Runtime vigente (2026-08-22)

Migración de PHP de la revista **ya ejecutada a mano** en cPanel
(propietario). Esta unidad solo documenta. WordPress de producción
sigue **7.1**. PHP de `logo-et-spes.cenfiss.net`: **8.3** (sin patch
concreto documentado).

Ruta efectiva (no MultiPHP Manager):

```text
logo-et-spes.cenfiss.net
→ cPanel (cuenta cenfiss2)
→ CloudLinux PHP Selector
→ Site Isolation enabled
→ PHP per-domain
→ PHP 8.3
```

Site Isolation + PHP per-domain fue deliberado: no cambiar PHP de
`cenfiss.net` ni de `test.cenfiss.net`. El control «Use MultiPHP
Manager» **no** fue la ruta usada. No aplicar PHP a toda la cuenta.

`config.platform.php` permanece **8.2.0** (suelo de resolución Composer).
`Requires PHP` permanece **7.4** (mínimo declarado). Runtime 8.3 ≠
esos dos conceptos.

Validación manual del propietario (no automatizada): portada,
`/revista/numeros/`, `/revista/articulos/`, `/revista/autores/`;
wp-admin; editor Gutenberg de Article; picker de autores; Media
Library; PDF existente; Site Health «Bueno»; desapareció la
advertencia de PHP 8.0.30 obsoleto; sin errores/warnings visibles
atribuibles a la migración.

Recomendaciones restantes de Site Health (plugin inactivo, tema por
defecto, módulos PHP, `post_max_size` / `upload_max_filesize`,
motores de búsqueda, OPcache) **no** forman parte de esta migración.

#### Rollback de PHP (no ejecutado)

Si aparece una regresión atribuible a PHP 8.3: en la configuración
per-domain de `logo-et-spes.cenfiss.net`, volver temporalmente a
PHP 8.0; verificar recuperación; investigar antes de reintentar.
No cambiar `cenfiss.net` ni `test.cenfiss.net`. No requiere SSH.

## Instalación Softaculous

WordPress se instaló con Softaculous en la raíz del subdominio:

```text
Protocolo: https://
Dominio: logo-et-spes.cenfiss.net
Directorio: vacío
Versión: 7.0.4
```

Se descartó `/wp/` porque el subdominio ya tiene document root propio.
La instalación quedó registrada en Softaculous.

## Base de datos

Antes del corte existían: `cenfiss2_4bplx`, `cenfiss2_moodle`,
`cenfiss2_tRZQu`, `cenfiss2_wp200`.

La revista usa una **BD nueva**, distinta de esas cuatro. No se reutilizó la
del WordPress principal, Moodle ni test.

## Backups (mecanismos distintos)

No confundir. El workflow de GitHub **no** incluye rollback de BD.

| Mecanismo | Qué cubre | No es |
| --------- | --------- | ----- |
| **Git** | Código de theme/plugin (y el resto del repo) | BD, `uploads/`, core |
| **FTPS de este repo** | Solo theme + plugin | No hace backup; no restaura BD |
| **JetBackup 5** | Backups de cuenta cPanel. On Demand en el corte: contador 21 → 22, **después** de instalar WP y **antes** del deploy final de theme/plugin | No sustituye Git |
| **ZIP estático** | `/home/cenfiss2/public_html/logo-et-spes-static-backup-2026-08-18.zip` — dump del HTML pre-WP | No es backup de WordPress/BD |
| **Backuply** (plugin Softaculous en el WP) | Backups desde WordPress, si está configurado | No forma parte del deploy de Git; no evaluado como mecanismo oficial |
| **`.htaccess` estático** | `.htaccess.static-backup-2026-08-18` | WordPress tiene un `.htaccess` nuevo |

Rollback por escenario: [wordpress-manual-deployment.md](wordpress-manual-deployment.md) § ROLLBACK.

## Recuperación institucional (completada)

**Estado 2026-08-19 (declaración del propietario):** las Pages
institucionales se importaron en producción, Verify pasó y la navegación
pública funciona de nuevo. Ese contenido es **permanente**. No hay cleanup
que lo borre.

La vía temporal Tools → Institutional Content Import (`Content_Recovery_Admin`)
completó su propósito y está **retirada** en `revistalogos-core` 0.2.3. Se
conservan `Content_Migrator` y `wp revistalogos content
validate|plan|import|verify`. SSH no estaba disponible al diseñar esa
recuperación; la UI fue un puente, no un reemplazo de WP-CLI.

Histórico del procedimiento (ya ejecutado; no repetir el import):

1. commit/push after owner approval;
2. deploy plugin via existing manual production workflow;
3. open temporary wp-admin import tool;
4. run Validate and Plan;
5. STOP if collision/error;
6. create fresh JetBackup On Demand;
7. enter real backup evidence;
8. explicitly confirm;
9. run institutional import;
10. inspect Verify;
11. test public P0 routes;
12. remove temporary admin tool in a follow-up patch (hecho en 0.2.3, este
    árbol; pendiente de deploy).

## Bootstrap editorial Volume 1

No es el dataset demo (`fixtures seed`). Es un **bootstrap editorial de
producción**: crea la estructura inicial del Vol. 1 Nº 1 para que el editor
sustituya placeholders en wp-admin sobre los mismos objetos. Ciclo esperado:
`plan` → `bootstrap --apply` → edición en wp-admin → adopción. No hay ciclo
borrar/recrear.

Comandos (Docker / WP-CLI; dry-run por defecto):

```text
wp revistalogos fixtures plan
wp revistalogos fixtures bootstrap
wp revistalogos fixtures bootstrap --apply
wp revistalogos fixtures verify
wp revistalogos fixtures teardown --kind=bootstrap
```

En producción, el CLI `--apply` sigue exigiendo `--confirm-production` y
`--backup`. El hosting **no** ofrece una vía práctica de SSH/WP-CLI (sin
Terminal cPanel; SSH no alcanzable). Para **esta** ejecución de Volume 1
el propietario exceptúa la evidencia de backup fresco. La vía temporal
Tools → Volume 1 Editorial Bootstrap (`Bootstrap_Admin`, plugin 0.2.4)
reutiliza el mismo dominio `Fixtures`; no expone teardown ni force; exige
confirmación explícita y exactamente un Author canónico. No reescribe la
política general de backup del CLI ni de la migración institucional.
**Ejecutado en producción.** Plugin **0.2.6 retira esa UI**; el dominio y
el CLI `wp revistalogos fixtures bootstrap|plan|verify|teardown` permanecen.
No volver a abrir esa pantalla; no re-ejecutar bootstrap ni teardown en
producción en esta tarea.

Plugin `0.2.6` (wp-admin, sin cambiar el contrato de almacenamiento ni
el editor del CPT): los autores se asignan con un buscador (REST de
núcleo; sin precargar el catálogo). Publicar un artículo exige al menos
un Author CPT publicado; borrador/pendiente pueden no tener autores.
Los artículos bootstrap ya publicados sin autores **no** se despublican
al actualizar el plugin. El CPT `article` sigue en el editor de bloques;
guardar después de asignar autores y luego publicar. El PDF de
artículo/número se elige con el selector nativo de Media Library
(`application/pdf`); quitar desvincula y no borra el archivo.

Theme `revistalogos` 0.2.1: los CTAs `.btn` conservan color de primer
plano accesible en `:link` y `:visited`.

**No ejecutar en producción en esta tarea.**

Autor canónico: `rafael-eduardo-figueredo-oropeza` (Rafael Eduardo Figueredo
Oropeza). El bootstrap lo reutiliza si hay exactamente un Author CPT con ese
slug; no lo crea, no lo marca `_les_bootstrap` / `_les_fixture`, no lo borra.
0 o >1 coincidencias: aborta.

Adopción: hash `_les_bootstrap_source_hash` de campos editoriales. Si el
contenido diverge, el objeto queda `_les_bootstrap_adopted=1` (sticky). Un
re-run no lo pisa. `teardown --kind=bootstrap` no lo borra. `teardown` sin
`--kind` no toca objetos `_les_bootstrap`.

Fuente: **Option 2 (propietario, 2026-08-19).** La maqueta Vol. 12 Nº 2
sigue excluida como verdad editorial y como `fixtures seed`. El bootstrap
**adapta** títulos, abstracts, secciones, orden y media placeholder de esa
maqueta a Vol. 1 Nº 1, marcados `_les_bootstrap*`. Identificadores falsos
(DOI/ORCID/ISSN), paginación bibliográfica dummy y autores de la maqueta
**no** se importan. Cover/PDF placeholder solo desde `resources/fixtures/`,
marcados bootstrap, sustituibles en wp-admin.

## Restos del sitio estático (deuda operativa)

WordPress se instaló sobre la carpeta del estático. Siguen en el document
root de la revista (observados; **no** borrar desde este repo ni en esta
tarea de documentación):

```text
404.html
archive-article.html
archive-author.html
archive-issue.html
noticias.html
page-acerca.html
page-comite-editorial.html
page-contacto.html
page-enlaces.html
page-enviar-colaboracion.html
page-etica.html
page-normas.html
page-politicas.html
page-privacidad.html
search.html
single-article.html
single-author.html
single-issue.html
single-post.html
sitemap.xml
robots.txt
assets/
partials/
index.html_          # antiguo index.html; Apache sirve index.php
.htaccess.static-backup-2026-08-18
```

Limpieza = follow-up operativo cuando el rollback al dummy ya no haga falta.
No hay ADR que ordene borrarlas ahora. El `static/` **del repositorio** no
es esto: ver § Referencias estáticas abajo.

## FTP dedicado

```text
Cuenta:     deploy_revista@logo-et-spes.cenfiss.net
cPanel FTP: ftp.cenfiss.net  puerto 21 (FTP y FTPS explícito)
```

`ftp.cenfiss.net` presenta certificado TLS emitido para `caroni.tepuyserver.net`
(CN y SAN: `caroni.tepuyserver.net`). Ambos hosts responden en el puerto 21.
La cuenta se comprobó contra `caroni.tepuyserver.net`: la raíz FTP visible
es el document root de la revista (`wp-admin/`, `wp-content/`, `wp-includes/`,
`wp-config.php`, `wp-login.php`, `index.php`, `.htaccess`, HTML residual).
**Jaula = sitio de la revista**, no `/home/cenfiss2/public_html/`.

`PRODUCTION_THEME_REMOTE_DIR` y `PRODUCTION_PLUGIN_REMOTE_DIR` se interpretan
**relativos a esa raíz FTP**. No usar paths absolutos de cPanel
(`/home/cenfiss2/…`) como destinos del Action sin verificar la jaula.

El secreto `PRODUCTION_FTP_SERVER` debe ser el hostname que **coincide** con
el certificado TLS (no `ftp.cenfiss.net` si el CN/SAN es `caroni.tepuyserver.net`).
No desactivar verificación TLS. No documentar ni commitear la contraseña.

## GitHub Environment y workflow

Environment: **`wordpress-production`**. Nomenclatura migrada de `STAGING_*`
a `PRODUCTION_*`.

Secretos (nombres solamente; **no** se vuelcan valores):

```text
PRODUCTION_FTP_SERVER
PRODUCTION_FTP_USERNAME        (cuenta: deploy_revista@logo-et-spes.cenfiss.net)
PRODUCTION_FTP_PASSWORD
PRODUCTION_THEME_REMOTE_DIR    (relativo a la jaula; p. ej. wp-content/themes/revistalogos/)
PRODUCTION_PLUGIN_REMOTE_DIR   (relativo a la jaula; p. ej. wp-content/plugins/revistalogos-core/)
```

Workflow: `.github/workflows/deploy-wordpress.yml`

```text
name: Deploy WordPress theme+plugin to production
on: workflow_dispatch
concurrency.group: deploy-wordpress-production
environment: wordpress-production   (ambos jobs)
needs: deploy-theme                 (el plugin espera al theme)
protocol: ftps
dangerous-clean-slate: false
```

Origen → destino:

```text
./wordpress/wp-content/themes/revistalogos/        → wp-content/themes/revistalogos/
./wordpress/wp-content/plugins/revistalogos-core/  → wp-content/plugins/revistalogos-core/
```

Sigue siendo **solo manual**.

### Primer deploy real

```text
Deploy WordPress theme+plugin to production #1
Status: Success  (~27 s)
Upload theme via FTPS     success
Upload plugin via FTPS    success
```

Warnings de Actions en el run #1 (histórico): `actions/checkout@v4` y
`SamKirkland/FTP-Deploy-Action@v4.3.6` declaraban Node.js 20. El 2026-08-20
el workflow se actualizó a `checkout@v5` y `FTP-Deploy-Action@v4.4.0` (Node 24).
Confirmar la desaparición de anotaciones en el próximo `workflow_dispatch`;
este mantenimiento no dispara deploy.

## Barreras operativas del deploy

1. Environment `wordpress-production`
2. `workflow_dispatch` manual
3. Cuenta FTP dedicada
4. Cuenta FTP enjaulada al subdominio
5. Rutas remotas acotadas (solo theme y plugin)
6. Theme y plugin en jobs separados
7. `dangerous-clean-slate: false`
8. Sin deploy de core, `uploads/` ni `wp-config.php`
9. Sin migraciones ni fixtures desde CI
10. Sin activación de theme/plugin desde CI
11. Rutas remotas relativas a la jaula FTP, no a `/home/cenfiss2/public_html/`

Esto reduce el riesgo de afectar `cenfiss.net`.

## Workflows

Producción WordPress (único deploy de código a cPanel):

```text
.github/workflows/deploy-wordpress.yml
name: Deploy WordPress theme+plugin to production
on: workflow_dispatch
environment: wordpress-production
```

Espejo estático (no toca cPanel):

```text
.github/workflows/pages.yml
name: Deploy static mirror to GitHub Pages
```

Legacy estático a hosting («Deploy to Hostinger», `deploy.yml`): **retirado**
el 2026-08-19. No recrear. Volcaría HTML sobre WordPress. Follow-up: borrar
en GitHub los Repository secrets `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`,
`FTP_PORT`, `FTP_REMOTE_DIR` (ya no los usa ningún workflow). No tocar
`PRODUCTION_*` del Environment `wordpress-production`.

## Plugins de Softaculous (pendientes de evaluación)

Softaculous instaló un bundle. **No** se desinstalaron durante el corte.
No asumir que deban mantenerse. Revisar después según necesidad real
(seguridad, backups, SMTP, caché, SEO) y ADR 0006. Son runtime de hosting:
**no** viajan en el deploy de Git (solo `revistalogos` + `revistalogos-core`).
No rediseñar el workflow para desinstalarlos.

```text
Backuply / Backuply Pro
GoSMTP / GoSMTP Pro
Loginizer / Loginizer Pro
SiteSEO / SiteSEO Pro
SpeedyCache / SpeedyCache Pro
Akismet
Hello Dolly
```

Los plugins **aprobados** (Contact Form 7, WP Statistics) **aún no** están
instalados/configurados en producción. Ver
[third-party-plugins.md](third-party-plugins.md).

## Pendientes inmediatos (backlog operativo)

1. QA completo del theme clásico en producción.
2. Guardar permalinks tras activar `revistalogos-core`.
3. ~~Revisar discrepancia PHP 8.0.30 vs MultiPHP 8.2 inherited.~~
   **Cerrado 2026-08-22 para la revista:** PHP **8.3** vía CloudLinux
   PHP Selector + Site Isolation en `logo-et-spes.cenfiss.net` solo.
   `cenfiss.net` y `test.cenfiss.net` no se tocaron. MultiPHP Manager
   no fue el mecanismo. Site Health residual (OPcache, módulos,
   tamaños de upload, etc.) es otra unidad.
4. Evaluar plugins Softaculous instalados.
5. Instalar/configurar Contact Form 7 (ADR 0010).
6. Instalar/configurar WP Statistics (ADR 0011).
7. Verificar cero cookies para visitante anónimo.
8. Revisar página de privacidad.
9. Limpiar restos HTML del estático cuando el rollback ya no sea necesario.
10. Indexación: verificar (no asumir abierta). No la abre el deploy. El
    100 % del contenido editorial no es prerequisito. Launch gate arriba.
    **No** abrir en este backlog como acción automática.
11. No importar el dataset demo de fixtures. Bootstrap editorial
    restringido: solo tras aprobación explícita; no ejecutado aquí.
12. Revisar warnings Node.js 20→24 del workflow (no bloquean).
13. FSE después, primero en Docker (ADR 0015).
14. Post-deploy funcional: portada, nav, CSS/JS, plantillas, caché (SpeedyCache).
15. Borrar a mano en GitHub (Repository secrets) `FTP_HOST`, `FTP_USERNAME`,
    `FTP_PASSWORD`, `FTP_PORT`, `FTP_REMOTE_DIR` si ya no los usa ningún
    workflow. No tocar `PRODUCTION_*`.

## Referencias estáticas, fixtures y restos (clasificación)

El WordPress en producción **no** hace obsoleto el material de referencia del
repo. Categorías según evidencia del repositorio:

| Artefacto | Dónde | Categoría | Notas |
| --------- | ----- | --------- | ----- |
| Maqueta HTML/CSS/JS | `static/` | Referencia visual + prototipo Fase 2 congelado (ADR 0001) | Sigue siendo criterio de paridad. No borrar. |
| Espejo beta | `refo44.github.io/demo-revistalogos` vía `pages.yml` | Copia de revisión (automática en `main`) | Deliberado; no toca cPanel. |
| Fixtures WP (demo) | plugin `revistalogos-core` (`seed`, `_les_fixture = 1`, kind `demo`) | Test fixture (ADR 0004) | Solo Docker. **Prohibido** en el live. |
| Bootstrap editorial | `wp revistalogos fixtures bootstrap` (`_les_bootstrap*`, kind `volume-1`) | Excepción de propietario 2026-08-19 (ciclo seed→edit→adopt) | Vol. 1 Nº 1 + artículos de la maqueta, autor canónico reutilizado, sin identificadores falsos. **No ejecutado** en producción en esta tarea. Purga: `teardown --kind=bootstrap` solo de objetos no adoptados; nunca borra Rafael ni Pages. |
| HTML residual en el document root de producción | servidor, no Git | Deuda operativa / leftover del corte | Lista arriba. No borrar en esta tarea. |
| ZIP `logo-et-spes-static-backup-2026-08-18.zip` | cPanel `public_html/` | Backup histórico pre-WP | No es rollback de la app. |
| Theme PHP | `wordpress/wp-content/themes/revistalogos/` | Implementación | Lo que FTPS despliega. |
| Plugin | `wordpress/wp-content/plugins/revistalogos-core/` | Implementación | Lo que FTPS despliega. |

No hay carpeta `mocks/` en el repo. «Mock» en docs/ADR 0004 = dataset de
fixtures, no un directorio de mockups.

## Resumen corto

```text
Estado de producción, 2026-08-19

WordPress 7.0.4 instalado en:
https://logo-et-spes.cenfiss.net

Theme / plugin:
revistalogos + revistalogos-core (activación en wp-admin, no en CI)

Deployment:
GitHub Actions manual vía FTPS
Environment: wordpress-production
Upload success ≠ QA funcional

FTP:
cuenta dedicada deploy_revista@logo-et-spes.cenfiss.net
jaulada al document root de la revista
rutas remotas relativas a esa jaula

Backup:
Git ≠ JetBackup ≠ ZIP estático ≠ Backuply

Sitio:
WordPress clásico live en producción; carga de contenido editorial real
iniciada y actualmente en proceso desde wp-admin (no completa)

Administración:
existe un administrador asignado a la carga editorial
(identidad no documentada)

Indexación:
verificar (no asumir abierta)

Fixtures:
no importados (no usar seed de Docker en prod)

FSE:
pendiente para fase posterior

cenfiss.net:
sin cambios y operativo

test.cenfiss.net:
sin cambios
```
