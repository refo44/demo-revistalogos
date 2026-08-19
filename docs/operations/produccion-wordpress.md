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
| Indexación | **Observada en el setup del corte:** Ajustes → Lectura → «Pedir a los motores de búsqueda que no indexen este sitio». **Política (ADR 0004):** no abrir hasta contenido editorial real. **No** asumir el valor actual: verificar en cada post-deploy. Abrir indexación es decisión del propietario, no un efecto del FTPS. |
| Permalinks | `/%postname%/` (Ajustes → Enlaces permanentes → Nombre de la entrada). Tras activar `revistalogos-core`, volver a guardar para regenerar rewrites de CPTs. |
| Fixtures | **No importados** desde el repo. El contenido que se publica se carga en wp-admin, no con `wp revistalogos fixtures`. |
| Contenido | WordPress clásico live en producción; carga de contenido editorial real iniciada y actualmente en proceso desde wp-admin. Fuente de verdad: BD + `uploads/` (ADR 0009). El FTPS de Git no despliega contenido. |

FSE (ADR 0015) **sigue siendo la dirección futura** y **no bloqueó** este corte.
Orden operativo actual: WordPress clásico live; carga editorial real **en
proceso** desde wp-admin → QA del theme → limpieza del estático residual →
revisar PHP → plugins aprobados → abrir indexación solo con decisión
explícita → FSE incremental primero en Docker.

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

Docker local (sin cambio de política; alineado en versión de WordPress):

```text
WordPress: 7.0.4
PHP: 8.2
MariaDB: sin cambios (11)
URL: http://localhost:8080
```

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

Warnings de Actions: `actions/checkout@v4` y
`SamKirkland/FTP-Deploy-Action@v4.3.6` declaran Node.js 20; GitHub los fuerza
a Node.js 24. No rompió el deploy; queda como mantenimiento del workflow.

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
3. Revisar discrepancia PHP 8.0.30 vs MultiPHP 8.2 inherited.
4. Evaluar plugins Softaculous instalados.
5. Instalar/configurar Contact Form 7 (ADR 0010).
6. Instalar/configurar WP Statistics (ADR 0011).
7. Verificar cero cookies para visitante anónimo.
8. Revisar página de privacidad.
9. Limpiar restos HTML del estático cuando el rollback ya no sea necesario.
10. Verificar visibilidad para buscadores (política ADR 0004: no abrir hasta
    contenido editorial real; no asumir el checkbox).
11. No importar fixtures.
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
| Fixtures WP | plugin `revistalogos-core` (`_les_fixture = 1`) | Test fixture (ADR 0004) | Solo Docker. **Prohibidas** en producción. |
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
iniciada y actualmente en proceso desde wp-admin

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
