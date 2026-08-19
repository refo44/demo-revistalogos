# ADR 0014: Entorno local de desarrollo WordPress con Docker

## Estado

Aceptada

## Fecha

2026-07-31

## Contexto

Al cierre de la implementación local de Fase 3 (`ready_for_review`), la máquina
de desarrollo no tenía PHP, WP-CLI, Composer ni WordPress (ver
`docs/fase3-execution-state.md` §Decisions). Consecuencia: toda la QA de
niveles 2-4 (activación, migración, fixtures, render de plantillas) quedaba
`Unverified` a la espera de un staging en Hostinger, y ni siquiera `php -l`
era ejecutable.

La máquina sí dispone de Docker (motor de Rancher Desktop by SUSE, con
Kubernetes disponible pero no requerido). Eso permite un runtime WordPress
completo sin instalar toolchain global, algo que ya estaba descartado sin
autorización explícita.

## Decisión

### 1. Runtime local con Docker Compose

Se añade `docker-compose.yml` en la raíz del monorepo con tres servicios:

- **db**: MariaDB 11 (volumen `db_data`).
- **wordpress**: imagen oficial `wordpress:7.0.4-php8.2-apache` (PHP 8.2 +
  Apache; tag exacto, no `latest` ni `7`/`7.0`), puerto 8080, core en
  volumen `wp_data`. El volumen persiste los archivos del core: cambiar el
  tag de la imagen **no** actualiza WordPress dentro de `wp_data`. Tras
  recrear el contenedor hay que `wp core update --version=7.0.4` y
  `wp core update-db`. Nunca `docker compose down -v` para un upgrade.
- **wpcli**: imagen oficial `wordpress:cli-php8.2` para ejecutar los comandos del
  plugin (`wp revistalogos content …`, `wp revistalogos fixtures …`).

El plugin `revistalogos-core` y el theme `revistalogos` se montan por bind
desde el repo: el código editado en Git es el que corre, sin copias.

### 2. Entorno declarado `local`

`WP_ENVIRONMENT_TYPE=local` se inyecta vía `WORDPRESS_CONFIG_EXTRA`.
Detalle no obvio: la imagen oficial genera un `wp-config.php` que **evalúa esa
variable de entorno en tiempo de ejecución**, así que debe estar definida
también en el servicio `wpcli`; si falta, `wp_get_environment_type()` devuelve
`production` y los guards del plugin (import y fixtures) se niegan a escribir
— comportamiento correcto que confirma ADR 0004.

### 3. Alcance: solo desarrollo y QA local

- Este entorno **no participa en ningún despliegue**. Producción y staging en
  Hostinger siguen siendo hosting compartido PHP/MySQL desplegado por FTPS
  manual (ADR 0009); Hostinger no ejecuta contenedores, por lo que ni Docker
  ni el Kubernetes de Rancher Desktop tienen papel alguno hacia el hosting.
- Las fixtures (ADR 0004) están permitidas aquí: es exactamente el entorno
  demostrativo para el que existen. El guard de producción sigue activo.
- La base de datos y `uploads/` viven en volúmenes Docker locales, fuera de
  Git, coherente con las dos fuentes de verdad de ADR 0009 §2.
- Las credenciales de la base de datos **no viven en el compose** sino en un
  `.env` local (ya cubierto por `.gitignore`); el repo versiona solo
  `.env.example` como plantilla y el compose falla con mensaje claro si el
  `.env` falta (`${VAR:?…}`). El usuario admin de WordPress (`admin`, clave
  local trivial) existe solo en la base de datos del volumen, nunca en Git;
  el servicio solo escucha en `localhost`.

### 4. Relación con la QA de Fase 3

La matriz (`docs/fase3-validation-matrix.md`) admite desde ahora el estado
**`Pass (local)`**: evidencia ejecutada en este entorno. El gate formal de
lanzamiento sigue exigiendo la validación en staging Hostinger (paridad de
hosting real: versión PHP del hosting, `.htaccess` real, FTPS, HTTPS), pero
todo lo que no depende del hosting queda pre-validado localmente.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Instalar PHP/WP-CLI/MySQL nativos (Homebrew) | Toolchain global sin autorización; contamina la máquina; menos reproducible. |
| Kubernetes (Rancher Desktop) | Innecesario para un stack de 3 servicios; Compose es suficiente. KISS. |
| Herramientas empaquetadas (Local, MAMP, DevKinsta) | Dependencia GUI adicional no versionable en el repo; Compose es declarativo y ya soportado por el motor instalado. |
| Esperar al staging de Hostinger para toda la QA 2-4 | Bloqueaba la validación en decisiones del propietario (subdominio, secretos); el runtime local elimina la espera sin sustituir el gate de staging. |

## Consecuencias

**Beneficios:**

- QA de niveles 2-4 ejecutable localmente: `php -l`, activación, migración
  completa (`validate|plan|import|verify`), ciclo de fixtures, render real de
  plantillas, verificación de cookies/red.
- Entorno reproducible y desechable (`docker compose down -v` lo reinicia).
- El supuesto «sin runtime PHP/WordPress local» de
  `docs/fase3-execution-state.md` queda obsoleto y corregido allí.

**Riesgos / costes:**

- Divergencia posible con el hosting real (versión PHP, extensiones,
  `.htaccess`/Apache de Hostinger): por eso `Pass (local)` no sustituye la
  validación de staging para el gate de lanzamiento.
- Estado local no versionado (volúmenes): cada máquina debe re-ejecutar la
  importación y las fixtures; el procedimiento está en los comentarios del
  propio `docker-compose.yml`.
- **Upgrade de core (2026-08-18):** el entorno local pasó de
  `wordpress:6.8-php8.2-apache` (core 6.8.3 en `wp_data`) a
  `wordpress:7.0.4-php8.2-apache` (core 7.0.4) sin recrear `db_data` ni
  `wp_data`. PHP se mantuvo en 8.2; MariaDB en `mariadb:11`. Theme y plugin
  `Tested up to: 7.0`.

## Referencias

- ADR 0004 (fixtures nunca a producción; guard verificado aquí),
  ADR 0009 (despliegue FTPS; dos fuentes de verdad — sin cambios).
- [ADR 0016](0016-topologia-hosting-cpanel.md) — el hosting público es cPanel
  `cenfiss2`, no el panel Hostinger; Docker sigue siendo solo local.
- `docker-compose.yml`, `docs/fase3-validation-matrix.md`,
  `docs/fase3-execution-state.md`.

## Estado de implementación (2026-08-19)

Nota factual; no cambia §1–§4 (Compose solo local; `Pass (local)` no sustituye
el hosting).

- El gate de hosting ya no es un «staging Hostinger»: es
  `https://logo-et-spes.cenfiss.net` (WordPress 7.0.4, PHP efectivo **8.0.30**).
  Docker local sigue en PHP 8.2. La discrepancia está abierta; no se cambió
  PHP en el corte (ADR 0016).
- Fixtures se siembran solo aquí. Producción no las importó.
