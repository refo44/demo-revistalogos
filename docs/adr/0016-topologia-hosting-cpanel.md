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
| `cenfiss.net` | `/public_html` | WordPress institucional (Softaculous: «Centro de Filosofía…») + Moodle (`cenfiss2_moodle`, `moodledata`). PHP efectivo visto por HTTP: 8.0.30 (el panel lista Inherited 8.2). |
| `logo-et-spes.cenfiss.net` | `/public_html/logo-et-spes.cenfiss.net` | Maqueta estática dummy. Sin `wp-config.php`. AutoSSL válido. |
| `test.cenfiss.net` | `/public_html/test.cenfiss.net` | Laravel 10 skeleton (Softaculous), roto desde 2024 (pide PHP ≥ 8.1; el vhost responde 8.0.30). No es staging de la revista. |

Otras cuentas cPanel vistas en la misma sesión (`confiadi` / `cenfissu`) **no** son el inventario de la revista. El File Manager vivo de la maqueta es `/home/cenfiss2/…`. No se usa `cenfissu` ni Confiadi para deploys de LOGO ET SPES.

ADR 0009 preveía un **subdominio de staging extra**. El propietario decide: **no hay dominios ni subdominios nuevos**. `logo-et-spes.cenfiss.net` se queda. El dummy (`robots.txt` `Disallow: /`, ADR 0004) se puede sustituir **in situ** cuando el FSE bootstrap funcione en Docker (0015 §7).

`deploy.yml` solo publica `static/` a la carpeta de la revista (FTPS). `deploy-wordpress.yml` existe y apunta a secretos `STAGING_*` que no están ligados a un hostname real. D12b (checks automáticos) sigue pendiente tras la auditoría (0012 §6).

## Decisión

### 1. Etiqueta de proveedor

«Hostinger» en ADR anteriores se lee como **este cPanel (`cenfiss2`)**. No se reescriben esos ADR (inmutabilidad). Este documento es la topología vigente.

### 2. Tres sitios, tres raíces; no se tocan los ajenos

- Nunca un deploy a `/public_html` (CENFISS + Moodle).
- Nunca WP, theme ni plugin de la revista en `test.cenfiss.net` (Laravel).
- Nunca HSTS `includeSubDomains` desde `cenfiss.net` (0012).
- Nunca cambiar MultiPHP de `cenfiss.net` o `test` «para alinear» la revista.

### 3. Corte WP en el mismo subdominio

Cuando ADR 0015 §7.2 esté visto en `localhost:8080`:

1. Backup de `/home/cenfiss2/public_html/logo-et-spes.cenfiss.net`.
2. MultiPHP: la revista ya figura **Inherited 8.2**; no Apply global. El desplegable no debe aplicarse en 5.6.
3. WordPress **nuevo** (Softaculous) **solo** en `logo-et-spes.cenfiss.net`, URL `https://`.
4. Base MySQL **nueva** (`cenfiss2_*` distinta de `moodle`, `tR2qU`, `4bplx`, `wp200`). Hay cupo (formulario Create Database presente; 4 bases actuales).
5. Theme + plugin vía FTPS acotado (mismo esquema que `deploy-wordpress.yml`).
6. **Dejar de lanzar** `deploy.yml` (estático) contra esa carpeta: volvería a volcar HTML sobre WP.

Hasta ese corte, `deploy.yml` sigue siendo el deploy de producción de la revista.

### 4. FTP

La cuenta **`deploy_revista@logo-et-spes.cenfiss.net`** está enjaulada a la carpeta de la revista. Es la que debe usar GitHub Actions (`FTP_*`). El usuario especial `cenfiss2` (`/home/cenfiss2`) **no** va en secretos. No se crean cuentas FTP nuevas con jaula en `/home/cenfiss2`.

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

- Tras sesión 2 de FSE en Docker: corte §3.
- Reapuntar `deploy-wordpress.yml` a las rutas reales de `logo-et-spes` (secretos `FTP_*` de `deploy_revista` o equivalentes `STAGING_*` documentados).
- Bloquear la ruta anidada bajo `cenfiss.net`.
- Actualizar menciones operativas «Hostinger» en runbooks cuando se toquen por otra razón.

## Referencias

- Inventario cPanel 2026-08-16 (Dominios, File Manager, MySQL, FTP, Softaculous, MultiPHP, SSL)
- ADR 0004, 0009, 0012, 0014, 0015
- `.github/workflows/deploy.yml`, `deploy-wordpress.yml`
- `docs/operations/wordpress-manual-deployment.md`
