# Runbook — Despliegue manual de WordPress (staging)

Procedimiento operativo del despliegue **manual** del theme `revistalogos` y el
plugin `revistalogos-core` por FTPS (ADR 0009). Crear o validar el workflow
**no autoriza ejecutarlo**: cada despliegue requiere decisión explícita del
propietario en la sesión en curso.

## Alcance

Se despliega **solo**:

```text
wordpress/wp-content/themes/revistalogos/   → STAGING_THEME_REMOTE_DIR
wordpress/wp-content/plugins/revistalogos-core/ → STAGING_PLUGIN_REMOTE_DIR
```

Nunca se despliega ni se toca: `wp-content/uploads/`, `wp-config.php`, el core
de WordPress, contenido de base de datos, plugins de terceros, el `.htaccess`
de la raíz del servidor, ni temas/plugins ajenos. Sin modo delete/mirror sobre
directorios compartidos. Sin activación automática de nada. Sin migraciones ni
fixtures durante el despliegue.

## Secretos requeridos (staging)

```text
STAGING_FTP_SERVER
STAGING_FTP_USERNAME
STAGING_FTP_PASSWORD
STAGING_THEME_REMOTE_DIR    (p. ej. .../wp-content/themes/revistalogos/)
STAGING_PLUGIN_REMOTE_DIR   (p. ej. .../wp-content/plugins/revistalogos-core/)
STAGING_SITE_URL
```

Los valores no se escriben nunca en el repositorio. El hostname de staging es
un dato pendiente del propietario (bloqueador solo cuando desplegar sea la
siguiente acción).

## Procedimiento

1. **Entorno objetivo:** confirmar que es el subdominio de staging (nunca
   `public_html` del dominio principal).
2. **Rama y commit:** anotar `git rev-parse HEAD`; desplegar solo desde `main`.
3. **Working tree limpio:** `git status --short` sin salida.
4. **Gate de QA:** nivel 1 completo en verde (ver
   `docs/fase3-validation-matrix.md`); niveles 2-3 en el propio staging tras el
   despliegue.
5. **Backup remoto del código:** copiar por FTPS los directorios remotos de
   theme y plugin a `*-backup-YYYYMMDD/` antes de sobrescribir.
6. **Rutas remotas acotadas:** verificar que los secretos apuntan exactamente a
   los dos directorios del alcance.
7. **Disparo manual:** Actions → «Deploy WordPress theme+plugin to staging» →
   Run workflow (rama `main`).
8. **Verificación de transferencia:** revisar el log del workflow; sin
   borrados fuera de alcance.
9. **Disponibilidad:** en wp-admin de staging, Apariencia → Temas y Plugins
   muestran las versiones nuevas (la activación es manual y deliberada).
10. **Intocados:** confirmar que core, `uploads/`, `wp-config.php` y plugins de
    terceros no cambiaron (fechas/tamaños por FTPS).
11. **Smoke tests:** portada, un archivo de CPT, una página institucional,
    búsqueda, 404; consola sin fatales; sin cookies para visitante anónimo.
12. **Cabeceras:** si ya se aplicó la sección de cabeceras (abajo), verificar
    con `curl -sI` que las cuatro cabeceras de ADR 0012 responden y que
    `http://` → 301 `https://`.
13. **Registro:** anotar commit desplegado, fecha y resultado en
    `docs/fase3-execution-state.md`.
14. **Rollback:** restaurar los directorios `*-backup-YYYYMMDD/` por FTPS (o
    redesplegar el commit anterior); WordPress vuelve al código previo sin
    tocar contenido.

## Cabeceras de seguridad en staging (operación manual de servidor)

ADR 0012 exige en el servidor WordPress: redirección 301 HTTP→HTTPS y las
cuatro cabeceras reversibles. **No** se despliegan desde este repositorio en la
fase WordPress (el workflow no toca el `.htaccess` raíz del servidor); se
aplican a mano en el `.htaccess` que WordPress gestiona, solo con acceso y
autorización, y solo en staging hasta el corte:

```apacheconf
# Security headers (ADR 0012). No HSTS until the professional audit.
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(), payment=()"
</IfModule>

RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteCond %{HTTP:X-Forwarded-Proto} !=https
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
# ... aquí sigue el bloque estándar # BEGIN WordPress ... # END WordPress
```

Reglas duras (ADR 0008 §5, ADR 0012):

- **No** conservar las reglas de archivos planos del estático (`.html` sin
  extensión) ni la regla que **elimina la barra final**: entrarían en conflicto
  con `redirect_canonical` de WordPress (bucles de redirección).
- **No** añadir HSTS ni CSP (posteriores a la auditoría profesional).
- Staging permanece `noindex` + `robots.txt` con `Disallow: /` (ADR 0009 §4);
  ese noindex **no** se traslada a producción en el corte.
- No declarar cabeceras aplicadas sin verificación `curl` real.

## Estado

| Ítem | Estado |
| ---- | ------ |
| Workflow implementado | Ver `.github/workflows/deploy-wordpress.yml` |
| Workflow validado | Parse YAML local; ejecución nunca disparada |
| Despliegue autorizado | No |
| Despliegue ejecutado | No |
| Verificación post-despliegue | No aplica |
