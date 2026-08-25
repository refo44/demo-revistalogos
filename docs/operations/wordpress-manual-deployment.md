# Runbook — Despliegue de producción (theme + plugin)

Procedimiento canónico del despliegue **manual** del theme `revistalogos` y el
plugin `revistalogos-core` por FTPS a WordPress en
`https://logo-et-spes.cenfiss.net` (ADR 0009, topología ADR 0016).

Crear o validar el workflow **no autoriza ejecutarlo**. Cada run exige una
decisión explícita del propietario. Un upload exitoso **no** prueba que el
sitio público funcione.

Snapshot factual del corte 2026-08-19:
[produccion-wordpress.md](produccion-wordpress.md).

## Entornos (no confundir)

| Entorno | Qué es | Cómo se actualiza |
| ------- | ------ | ----------------- |
| **Local** | Docker, `http://localhost:8080` (ADR 0014) | Volúmenes locales; no es un deploy |
| **Referencia estática** | `static/` en Git; espejo beta GitHub Pages | `pages.yml` (automático en `main`) |
| **Producción WordPress** | `logo-et-spes.cenfiss.net` | Este runbook (`deploy-wordpress.yml`) |
| **Staging WordPress** | **No existe.** No hay subdominio de staging extra (ADR 0016). | — |
| **Estático en cPanel** | Workflow `Deploy to Hostinger` (`deploy.yml`) | **Retirado** (2026-08-19). No recrear. No hay botón de Actions para volcar HTML sobre WP. |

No hay promoción automática staging → producción. No hay Environment de
staging WordPress. El Environment de este workflow es solo
`wordpress-production`.

## Invariantes (no negociables)

1. Despliegue de producción solo manual (`workflow_dispatch`).
2. Requiere iniciación humana explícita.
3. Merge a `main` **no** es un deploy (ADR 0020). HEAD debe llevar una
   etiqueta anotada `vMAJOR.MINOR.PATCH`. Run workflow **desde esa
   etiqueta**, no desde `main` suelto.
4. GitHub Actions **no** despliega el core de WordPress (`wp-admin`, `wp-includes`).
5. GitHub Actions **no** despliega la base de datos ni dumps.
6. GitHub Actions **no** despliega `uploads/` ni la biblioteca de medios.
7. GitHub Actions **no** modifica `wp-config.php`.
8. GitHub Actions **no** modifica el `.htaccess` de la raíz del document root.
9. GitHub Actions **no** despliega plugins de terceros.
10. GitHub Actions despliega **solo** `revistalogos` y `revistalogos-core`.
11. Theme y plugin tienen destinos remotos **separados y acotados**.
12. `dangerous-clean-slate` permanece `false`.
13. Sin delete/mirror sobre directorios compartidos de `wp-content`.
14. La cuenta FTP está enjaulada al sitio de la revista, no a
    `/home/cenfiss2/public_html/` (CENFISS + Moodle).
15. Credenciales en el GitHub Environment `wordpress-production`.
16. Los secretos **nunca** van al repositorio.
17. Activar theme/plugin es una acción **aparte** en wp-admin. El workflow no
    activa nada. No añadir WP-CLI de activación sin un ADR nuevo.
18. Migraciones, fixtures e importación de contenido quedan fuera de este FTPS.
19. Éxito del Action ≠ sitio correcto. Siempre hay verificación post-despliegue.

## Destino (qué es y qué no es)

Cuenta cPanel: `cenfiss2`, home `/home/cenfiss2`.

```text
/home/cenfiss2/public_html/                    ← WordPress institucional CENFISS + Moodle
                                               NUNCA destino de este workflow
/home/cenfiss2/public_html/logo-et-spes.cenfiss.net/
                                               ← document root de la revista
```

El workflow **no** sube archivos arbitrarios a `public_html` de la cuenta.
Tampoco sube al document root de la revista: solo a dos subdirectorios.

Artefactos locales → destinos remotos (relativos a la **raíz FTP enjaulada**,
que es el document root de la revista, no una ruta absoluta de cPanel):

```text
./wordpress/wp-content/themes/revistalogos/        → PRODUCTION_THEME_REMOTE_DIR
./wordpress/wp-content/plugins/revistalogos-core/  → PRODUCTION_PLUGIN_REMOTE_DIR
```

Valores conceptuales de esas rutas (no son paths de filesystem
`/home/cenfiss2/…`):

```text
wp-content/themes/revistalogos/
wp-content/plugins/revistalogos-core/
```

**No** sustituirlas por rutas absolutas de cPanel sin comprobar cómo está
enjaulada la cuenta FTP. El listado FTP de
`deploy_revista@logo-et-spes.cenfiss.net` muestra `wp-admin/`, `wp-content/`,
`wp-includes/`, `wp-config.php`, `index.php`, `.htaccess` en la raíz visible:
la cuenta aterriza en el sitio de la revista.

Nunca se despliega: core, `wp-admin`, `wp-includes`, `wp-config.php`,
`.htaccess` de la raíz, `uploads/`, BD, dumps, media, plugins de terceros,
HTML estático residual, `static/`, configuración de servidor.

## Secretos (Environment `wordpress-production`)

```text
PRODUCTION_FTP_SERVER
PRODUCTION_FTP_USERNAME
PRODUCTION_FTP_PASSWORD
PRODUCTION_THEME_REMOTE_DIR
PRODUCTION_PLUGIN_REMOTE_DIR
```

Nunca imprimir ni commitear sus valores. El usuario FTP (público, operativo):

```text
deploy_revista@logo-et-spes.cenfiss.net
```

cPanel muestra servidor `ftp.cenfiss.net` puerto 21 (FTP y FTPS explícito).
El certificado TLS de ese host tiene CN/SAN `caroni.tepuyserver.net` (Let’s
Encrypt). Conectar con verificación TLS a `ftp.cenfiss.net` produce mismatch
de hostname. Ambos hosts responden en el puerto 21.

**Lección operativa:** `PRODUCTION_FTP_SERVER` debe ser el hostname que
**coincide** con el certificado TLS del FTPS explícito. No desactivar la
verificación TLS. No usar FTP en claro.

El workflow estático `deploy.yml` («Deploy to Hostinger») **fue retirado**
tras el corte a WordPress. Ya no hay Action que use los secretos de
repositorio `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_PORT`,
`FTP_REMOTE_DIR`. No recrear ese workflow. Follow-up manual: borrar esos
cinco secretos en GitHub → Settings → Secrets and variables → Actions
(Repository secrets). **No** tocar los secretos del Environment
`wordpress-production` (`PRODUCTION_*`).

## Workflow

Archivo: `.github/workflows/deploy-wordpress.yml`

```text
name: Deploy WordPress theme+plugin to production
on: workflow_dispatch          # solo manual
environment: wordpress-production
concurrency.group: deploy-wordpress-production
cancel-in-progress: false
jobs: require-release-tag → deploy-theme → deploy-plugin
      (plugin also needs the tag job)
protocol: ftps
dangerous-clean-slate: false
plugin job: runner-only PHP 8.3 + ext-dom/ext-mbstring (does NOT
           change cPanel PHP); composer install --no-dev from plugin
           lock, then FTPS (generated vendor/ uploaded; never committed)
```

Primer run (2026-08-19): **Success** (~27 s), ambos jobs. Warnings Node.js
20→24 de ese run (`actions/checkout@v4`, `FTP-Deploy-Action@v4.3.6`) son
históricos. Desde 2026-08-20 el workflow usa `checkout@v5` y
`FTP-Deploy-Action@v4.4.0`. Confirmar anotaciones en el próximo
`workflow_dispatch`; no disparar deploy solo para comprobarlo.

---

## PRE-DESPLIEGUE

1. **Release etiquetado (ADR 0020):** `./tools/require-production-release-tag.sh`
   debe pasar. Si falla, no hay deploy: bump de `package.json` /
   `VERSION.md` / `CHANGELOG.md`, PR `chore(release): vX.Y.Z`, etiqueta
   anotada, y Run workflow **desde esa tag**. Merge a `main` no basta.
   No despachar desde `v0.2.0` (producción ya sirve plugin 0.2.8).
2. **Commit y rama:** `git rev-parse HEAD` y la etiqueta (`git describe --exact-match --tags`).
   Working tree limpio (`git status --short`).
3. **Cambios incluidos:** solo theme y/o plugin first-party. Sin fixtures,
   sin dumps, sin `static/`.
4. **Destino:** `https://logo-et-spes.cenfiss.net` — WordPress de la revista.
   No `cenfiss.net`, no `test.cenfiss.net`, no `/home/cenfiss2/public_html/`.
5. **Environment:** `wordpress-production`. Confirmar en el run de Actions.
6. **Límites remotos:** `PRODUCTION_THEME_REMOTE_DIR` y
   `PRODUCTION_PLUGIN_REMOTE_DIR` son rutas **relativas a la jaula FTP**,
   cada una el directorio del artefacto. No el document root.
7. **Backup disponible (no lo hace el workflow):**
   - JetBackup 5 (cuenta cPanel) — rollback de hosting/archivos.
   - Backuply (plugin en el WP; no forma parte del deploy de Git).
   - ZIP `logo-et-spes-static-backup-2026-08-18.zip` — artefacto
     **pre-migración** del estático; no es rollback normal de la app.
   - Git — código; no restaura BD.
8. **No exponer secretos** en issues, logs pegados ni commits.
9. **PHP de producción no se toca.** Hosting ya corre PHP **8.3**.
   `setup-php` en `deploy-wordpress.yml` es solo el runner de Actions
   para Composer. Antes del primer deploy de WU4 (plugin con `vendor/`
   Dompdf): **verificar** `ext-dom` y `ext-mbstring` en ese PHP 8.3.
   No cambiar CloudLinux PHP Selector, MultiPHP ni la versión PHP.

---

## DESPLIEGUE

1. GitHub → Actions → **Deploy WordPress theme+plugin to production**.
2. **Run workflow**. En *Use workflow from* elegir la **etiqueta**
   `vX.Y.Z`, no `main` (salvo que `main` coincida exactamente con esa
   etiqueta). El job *Require annotated release tag* corre primero y
   aborta si HEAD no está etiquetado.
3. Disparo **manual**. Esperar: *Require annotated release tag*, luego
   *Upload theme via FTPS*, luego *Upload plugin via FTPS*.
4. Si el chequeo de etiqueta o el theme fallan, el plugin **no** corre.

El workflow **no** activa el theme ni el plugin.

---

## POST-DESPLIEGUE

Transferencia ≠ aplicación. Completar:

1. Workflow en verde. Revisar warnings (Node 20→24 conocidos) y errores.
2. wp-admin: Apariencia → Temas y Plugins. El código nuevo está en disco;
   **activar** (o reactivar) es acción administrativa aparte si hace falta.
3. Portada pública, navegación, CSS/JS/assets, plantillas representativas
   (archivo de número, página institucional, 404).
4. Sin fatales PHP en pantalla ni en `debug.log` si está accesible.
5. Formularios / analítica **si** ya están instalados (CF7 y WP Statistics
   eran pendientes en el corte; no inventar que ya funcionan).
6. Caché: Softaculous dejó SpeedyCache. Si está activo, comprobar que el
   front no sirve HTML/CSS viejo; purgar en wp-admin si hace falta. El
   workflow **no** purga caché.
7. Visibilidad para buscadores: **verificar** Ajustes → Lectura (no asumir
   el valor). El deploy **no** abre indexación. Completar el 100 % del
   contenido editorial **no** es prerequisito. Abrirla es decisión explícita
   del propietario tras el launch gate de
   `docs/operations/produccion-wordpress.md`. **No** abrirla como paso de
   este POST.
8. Registrar commit, fecha y resultado en `docs/fase3-execution-state.md`.

---

## ROLLBACK

No hay rollback automático en GitHub Actions. Distinguir el fallo:

| | Mecanismo | Cuándo |
| - | --------- | ------ |
| **A. Código** | Volver a disparar este workflow sobre un commit conocido bueno de theme/plugin. No toca BD ni `uploads/`. | Regresión de PHP/CSS/plantillas |
| **B. Hosting / archivos** | Restauración JetBackup 5 / cPanel. Alcance de cuenta; usar con cuidado (hay otros sitios en `cenfiss2`). | Daño de filesystem más allá de theme/plugin |
| **C. Base de datos** | Backup/restore de BD aparte (JetBackup y/o Backuply). **Este workflow no despliega ni restaura BD.** | Contenido, ajustes, activaciones |
| **D. Estático pre-WP** | ZIP `logo-et-spes-static-backup-2026-08-18.zip`. Histórico. **No** es el rollback normal de WordPress. | Solo si se acepta volver al dummy HTML |

No copiar el ZIP estático sobre `index.php`. No recrear `deploy.yml` como
«rollback».

---

## Cabeceras de seguridad (fuera de este workflow)

ADR 0012: 301 HTTP→HTTPS y cuatro cabeceras reversibles se aplican **a mano**
en el `.htaccess` que WordPress gestiona. El workflow no toca ese archivo.
Sin HSTS ni CSP hasta la auditoría profesional. No declarar cabeceras sin
`curl -sI` real.

## Estado

| Ítem | Estado |
| ---- | ------ |
| Workflow | `.github/workflows/deploy-wordpress.yml` |
| Environment | `wordpress-production` |
| Primer run | **Success** 2026-08-19 (~27 s), theme + plugin |
| Activación en CI | No (ni entonces ni ahora) |
| Verificación funcional pública | Pendiente (matriz: transfer Pass; paridad/cookies/CF7/cabeceras Unverified) |
