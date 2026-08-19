# ADR 0016: Topología de hosting (cPanel cenfiss2)

## Estado

Aceptada

## Fecha

2026-08-16

## Contexto

Los ADR 0009, 0012 y 0014 y el execution state de Fase 3 llaman **Hostinger** al alojamiento de `logo-et-spes.cenfiss.net`. Inventario en cPanel (2026-08-16): no es el panel de Hostinger. Es **cPanel** (Jupiter, LiteSpeed), cuenta **`cenfiss2`**, home `/home/cenfiss2`. En la misma cuenta conviven otros proyectos. El desarrollador no tendrá acceso continuo; el admin operará wp-admin (ADR 0015).

Hechos verificados (panel + File Manager + Softaculous + MultiPHP + SSL + DNS):

| Host | Document root | Qué hay |
| ---- | ------------- | ------- |
| `cenfiss.net` | `/public_html` | WordPress institucional (Softaculous: «Centro de Filosofía…») + Moodle (`cenfiss2_moodle`, `moodledata`). PHP efectivo visto por HTTP: 8.0.30 (el panel lista Inherited 8.2). **Sin cambios en el corte 2026-08-19.** |
| `logo-et-spes.cenfiss.net` | `/public_html/logo-et-spes.cenfiss.net` | **WordPress 7.0.4 propio** (Softaculous, 2026-08-19). Theme clásico `revistalogos` y plugin `revistalogos-core` activos. WordPress clásico live en producción; carga de contenido editorial real iniciada y actualmente en proceso desde wp-admin. BD nueva, aislada. Restos del estático aún en la raíz (temporales). AutoSSL válido. Inventario 2026-08-16: era maqueta dummy sin `wp-config.php`. |
| `test.cenfiss.net` | `/public_html/test.cenfiss.net` | Laravel 10 skeleton (Softaculous), roto desde 2024 (pide PHP ≥ 8.1; el vhost responde 8.0.30). No es staging de la revista. **Sin cambios en el corte.** |

Otras cuentas cPanel vistas en la misma sesión (`confiadi` / `cenfissu`) **no** son el inventario de la revista. El File Manager vivo de la maqueta es `/home/cenfiss2/…`. No se usa `cenfissu` ni Confiadi para deploys de LOGO ET SPES.

ADR 0009 preveía un **subdominio de staging extra**. El propietario decide: **no hay dominios ni subdominios nuevos**. `logo-et-spes.cenfiss.net` se queda. El dummy se sustituyó **in situ** el 2026-08-19 con WordPress clásico (el gate FSE de 0015 §7 no bloqueó el corte).

`deploy.yml` («Deploy to Hostinger») publicaba `static/` por FTPS; **retirado** el 2026-08-19. No recrearlo. `deploy-wordpress.yml` es el único deploy de código al cPanel de la revista (Environment `wordpress-production`, secretos `PRODUCTION_*`). GitHub Pages (`pages.yml`) sigue como espejo de `static/` y **no** toca el servidor. D12b (checks automáticos) sigue pendiente tras la auditoría (0012 §6).

## Decisión

### 1. Etiqueta de proveedor

«Hostinger» en ADR anteriores se lee como **este cPanel (`cenfiss2`)**. No se reescriben esos ADR (inmutabilidad). Este documento es la topología vigente.

### 2. Tres sitios, tres raíces; no se tocan los ajenos

- Nunca un deploy a `/public_html` (CENFISS + Moodle).
- Nunca WP, theme ni plugin de la revista en `test.cenfiss.net` (Laravel).
- Nunca HSTS `includeSubDomains` desde `cenfiss.net` (0012).
- Nunca cambiar MultiPHP de `cenfiss.net` o `test` «para alinear» la revista.

### 3. Corte WP en el mismo subdominio

**Ejecutado 2026-08-19** (theme clásico; el gate FSE de 0015 §7.2 no se exigió). Pasos que sí se hicieron:

1. Backup ZIP del estático + JetBackup 5 On Demand de la cuenta.
2. MultiPHP: no se Apply global; no se cambió PHP. WordPress reportó PHP **8.0.30** pese a Inherited 8.2 (discrepancia abierta).
3. WordPress **nuevo** (Softaculous) **solo** en `logo-et-spes.cenfiss.net`, URL `https://`, directorio vacío, 7.0.4.
4. Base MySQL **nueva** (distinta de `moodle`, `tRZQu`, `4bplx`, `wp200`).
5. Theme + plugin vía FTPS acotado (`deploy-wordpress.yml`, run #1 Success).
6. Dejar de lanzar `deploy.yml` (estático) contra esa carpeta. **Hecho:** el archivo se retiró del repo (2026-08-19); no recrearlo.

El deploy de código de la revista es `deploy-wordpress.yml`. Snapshot: `docs/operations/produccion-wordpress.md`.

### 4. FTP

La cuenta **`deploy_revista@logo-et-spes.cenfiss.net`** está enjaulada a la carpeta de la revista. Es la que debe usar GitHub Actions (`PRODUCTION_FTP_*`). El usuario especial `cenfiss2` (`/home/cenfiss2`) **no** va en secretos. No se crean cuentas FTP nuevas con jaula en `/home/cenfiss2`.

cPanel lista `ftp.cenfiss.net:21`; el certificado TLS de ese host está emitido para `caroni.tepuyserver.net`. El secreto `PRODUCTION_FTP_SERVER` debe ser el hostname que coincide con ese certificado (FTPS explícito con verificación TLS). Ambos hosts responden en el puerto 21; la cuenta está jaulada al document root de la revista. `PRODUCTION_*_REMOTE_DIR` son rutas **relativas a esa jaula**, no paths `/home/cenfiss2/…`.

Tras el WP, la misma cuenta cubre `wp-content/themes/revistalogos` y `plugins/revistalogos-core` (siguen dentro de la jaula).

### 5. Docker no se despliega

ADR 0014: Compose es solo local. Este hosting no ejecuta contenedores. *Setup Node.js App* en cPanel no reabre Next.js (0015 §6).

### 6. CI/CD

No se inventa un pipeline nuevo para FSE. Theme y plugin viajan por FTPS acotado. Producción Hostinger/cPanel **sigue manual** (`workflow_dispatch`, 0009). D12b (stylelint / `php -l` en PR, sin deploy) se decide **después** de la auditoría.

### 7. SSL

`logo-et-spes.cenfiss.net` y `www.` tienen AutoSSL (caducidad ~2026-10-04). No generar certificados a mano ni borrar restos de `test` / nombres viejos.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Subdominio `staging-logo` / reutilizar `test` | El propietario no quiere dominios nuevos; `test` es Laravel. El dummy de la revista no está indexado. |
| Instalar el theme en el WP de `cenfiss.net` | Mezcla institucional + revista; Moodle al lado; viola 0005/0009. |
| Apuntar FTPS a `/public_html` | Pisa CENFISS y Moodle. |
| Subir Docker al cPanel | El plan no corre contenedores. |
| Corregir Laravel de `test` subiendo PHP de toda la cuenta | Rompe el 8.0 efectivo de CENFISS/Moodle. |

## Consecuencias

**Beneficios:**

- Inventario único para quien retome el corte sin el historial de chat.
- El FTP ya está acotado; el riesgo de pisar Moodle es de configuración, no de falta de cuenta.
- El admin usará el WP del subdominio de la revista, no el de `cenfiss.net`.

**Riesgos / costes:**

- El document root de la revista es **hijo** de `/public_html`. `https://cenfiss.net/logo-et-spes.cenfiss.net/` puede servir los mismos archivos. Tras el WP, denegar esa ruta desde el dominio principal.
- Cabeceras HTTP de `cenfiss.net`/`test` aún pueden decir PHP 8.0 pese a Inherited 8.2 (handlers viejos). No se «arregla» desde la revista.
- LiteSpeed Cache, si se activa en el WP nuevo, hay que purgar al desplegar.
- Una cuenta cPanel equivocada (`cenfissu`, Confiadi) puede recibir un Softaculous en el sitio incorrecto.

**Trabajo futuro:**

- QA de producción clásica; permalinks; revisar PHP 8.0.30 vs Inherited 8.2; evaluar plugins Softaculous; CF7 y WP Statistics; cero cookies; limpiar restos HTML cuando el rollback no haga falta. Lista: `docs/operations/produccion-wordpress.md`.
- FSE incremental **después**, primero en Docker (ADR 0015). El corte §3 ya está hecho.
- Bloquear la ruta anidada bajo `cenfiss.net`.
- Actualizar menciones operativas «Hostinger» en runbooks cuando se toquen por otra razón.
- Warnings Node.js 20→24 del workflow (`checkout@v4`, FTP-Deploy-Action@v4.3.6): mantenimiento futuro, no bloquearon el deploy.

## Referencias

- Inventario cPanel 2026-08-16 (Dominios, File Manager, MySQL, FTP, Softaculous, MultiPHP, SSL)
- Corte in situ 2026-08-19 — `docs/operations/produccion-wordpress.md`
- ADR 0004, 0009, 0012, 0014, 0015
- `.github/workflows/deploy-wordpress.yml` (producción); `deploy.yml` retirado 2026-08-19
- `docs/operations/wordpress-manual-deployment.md`
