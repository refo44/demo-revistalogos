# Aprendizajes — Dockerización de un entorno local WordPress

**Playbook portable.** Destila lo aprendido al dockerizar el runtime local de
este proyecto (ADR 0014, `docker-compose.yml`, 2026-07-31) en forma aplicable a
**cualquier otro proyecto WordPress** con la misma forma: código propio (theme
y/o plugin) versionado en Git, hosting compartido sin contenedores en
producción, y necesidad de QA real sin instalar toolchain global en la máquina.

Este documento es genérico a propósito: donde dice `mi-plugin`/`mi-theme`,
sustituir por los nombres del proyecto.

---

## 1. El problema que resuelve

Sin PHP/WP-CLI/WordPress locales, toda la QA de runtime (activación, comandos
CLI, render de plantillas, cookies) queda `Unverified` y rehén del staging.
Instalar el stack nativo (Homebrew/MAMP/Local) contamina la máquina, no es
reproducible ni versionable, y suele requerir autorización.

**Aprendizaje central:** un `docker-compose.yml` de tres servicios convierte
«sin runtime» en «runtime completo, reproducible y desechable» en minutos, sin
tocar la máquina, y el archivo queda versionado junto al código que valida.

## 2. Arquitectura mínima que funcionó (3 servicios)

```text
db         → mariadb:11            (volumen db_data; healthcheck)
wordpress  → wordpress:X.Y-phpZ    (Apache; volumen wp_data; puerto local)
wpcli      → wordpress:cli-phpZ    (mismos mounts y DB; para wp <cmd>)
```

Reglas que demostraron su valor:

1. **Bind-mount solo del código propio.** El core de WordPress y la base de
   datos viven en volúmenes Docker; del repositorio se montan únicamente
   `mi-plugin/` y `mi-theme/`:

   ```yaml
   volumes:
     - wp_data:/var/www/html
     - ./wordpress/wp-content/plugins/mi-plugin:/var/www/html/wp-content/plugins/mi-plugin
     - ./wordpress/wp-content/themes/mi-theme:/var/www/html/wp-content/themes/mi-theme
   ```

   Beneficio doble: lo que se edita en Git es exactamente lo que corre (sin
   copias), y el alcance de los mounts **replica el alcance del despliegue**
   (si en producción solo se despliegan theme y plugin, en local solo se
   montan theme y plugin). El contenido (BD, `uploads/`) queda fuera de Git en
   ambos mundos — misma separación código/contenido que en producción.

2. **Servicio `wpcli` separado**, con la imagen oficial `wordpress:cli`, los
   mismos mounts y la misma configuración de entorno que el servicio web.
   Uso: `docker compose run --rm wpcli wp <comando>`. Es la puerta de entrada
   de toda la QA scriptable.

3. **Healthcheck en la base de datos + `depends_on: condition:
   service_healthy`** en los otros dos servicios. Sin esto, el primer
   arranque (y cada CI hipotético) sufre condiciones de carrera contra la
   inicialización de MariaDB/InnoDB:

   ```yaml
   healthcheck:
     test: ["CMD", "healthcheck.sh", "--connect", "--innodb_initialized"]
     interval: 5s
     timeout: 5s
     retries: 12
   ```

4. **`name:` explícito** en el compose (nombre de proyecto estable, volúmenes
   predecibles entre máquinas y sesiones).

## 3. Gotchas descubiertas (los que cuestan horas)

1. **`WORDPRESS_CONFIG_EXTRA` se evalúa en tiempo de ejecución y por
   contenedor.** La imagen oficial genera un `wp-config.php` que lee esa
   variable del entorno del proceso. Consecuencia no obvia: si defines
   `WP_ENVIRONMENT_TYPE=local` solo en el servicio `wordpress`, el servicio
   `wpcli` seguirá reportando `production` en `wp_get_environment_type()`.
   En este proyecto eso hizo que los guards de producción del plugin se
   negaran a escribir — comportamiento correcto que, de paso, **validó los
   guards gratis**. Aprendizaje doble:
   - Duplicar `WORDPRESS_CONFIG_EXTRA` (o al menos `WP_ENVIRONMENT_TYPE`) en
     **todos** los servicios que ejecutan PHP.
   - Si tu código tiene guards por entorno, la primera vez que el CLI los
     rechace es una **prueba de nivel 2 gratuita**: documenta ese rechazo como
     evidencia antes de arreglar la variable.

2. **UID/GID del CLI.** Ejecutar `wpcli` como `user: "33:33"` (www-data de la
   imagen Apache) evita el clásico desorden de permisos entre archivos creados
   por el contenedor web (uploads, `.htaccess`) y los creados por el CLI.

3. **Credenciales fuera del compose.** `.env` (gitignored) + `.env.example`
   versionado como plantilla, y sintaxis `${VAR:?mensaje}` para que el compose
   **falle rápido y con mensaje claro** si el `.env` no existe:

   ```yaml
   MARIADB_PASSWORD: ${MARIADB_PASSWORD:?define MARIADB_PASSWORD en .env}
   ```

   El usuario admin de WordPress vive solo en el volumen local, nunca en Git;
   el servicio escucha solo en `localhost`.

4. **Declarar el entorno y activar el log de debug desde el día uno:**

   ```yaml
   WORDPRESS_CONFIG_EXTRA: |
     define( 'WP_ENVIRONMENT_TYPE', 'local' );
     define( 'WP_DEBUG', true );
     define( 'WP_DEBUG_LOG', true );
     define( 'WP_DEBUG_DISPLAY', false );
   ```

   Un `debug.log` vacío tras activar plugin+theme y navegar las pantallas es
   evidencia barata y objetiva de «sin warnings ni fatales».

5. **Puerto parametrizado con default** (`"${WORDPRESS_PORT:-8080}:80"`) para
   convivir con otros proyectos dockerizados en la misma máquina.

6. **Fijar versiones de imagen que imiten al hosting**
   (`wordpress:7.1.0-php8.2-apache` y no `latest` ni tags flotantes `7`/`7.1`;
   el ejemplo 7.0.4 del 2026-08-18 queda como evidencia histórica):
   la paridad de versión PHP con el hosting real es lo que da valor
   probatorio a `php -l` y a la QA local.

7. **Cambiar el tag de la imagen no actualiza el core en `wp_data`.** El
   servicio monta `wp_data:/var/www/html`, así que los archivos de WordPress
   ya instalados sobreviven al recreate del contenedor. Tras un upgrade de
   tag: `docker compose pull wordpress`, `up -d --force-recreate wordpress`
   **sin** `-v`, luego `wp core update --version=X.Y.Z` y `wp core update-db`.
   Un `.maintenance` residual deja el sitio en 503 hasta borrarlo. Nunca
   `docker compose down -v` para subir de versión.

## 4. Método: qué QA ejecutar en cuanto el entorno levanta

Secuencia que funcionó aquí y es repetible en cualquier proyecto (cada paso
deja evidencia registrable):

1. **Sintaxis:** `php -l` sobre todos los `.php` propios vía `wpcli`
   (aquí: 59 archivos, 0 errores — antes solo había un heurístico).
2. **Activación:** `wp plugin activate` / `wp theme activate` + `debug.log`
   vacío.
3. **Permalinks + jerarquía:** `wp rewrite structure '/%postname%/'` y `curl`
   de las URLs clave esperando 200 (aquí 15/15).
4. **Comandos propios, en escalera:** dry-run → `--apply` → re-ejecución
   (probar **idempotencia**: la segunda pasada debe ser todo `skip/unchanged`).
5. **Ciclo de vida de datos demo:** teardown → seed → verify → reseed (sin
   duplicados) → teardown (sin huérfanos) → teardown de nuevo (no-op seguro).
6. **Invariantes de front:** `curl -I` buscando ausencia de `Set-Cookie`;
   grep del HTML renderizado buscando recursos de hosts externos.
7. **Plugins de terceros aprobados:** instalarlos con `wp plugin install` y
   verificar su comportamiento observable (no el declarado).

**Regla de honestidad que conviene copiar:** introducir un estado intermedio
en la matriz de validación — aquí `Pass (local)` — distinto de `Pass`.
La evidencia local **no sustituye** al staging para lo que depende del hosting
real (versión PHP del hosting, `.htaccess`/Apache real, FTPS, HTTPS,
cabeceras, correo). Distinguir los dos niveles evita declarar de más.

## 5. Límites deliberados (qué NO hace este entorno)

- **No participa en ningún despliegue.** Si producción es hosting compartido
  por FTPS, Docker es solo banco de pruebas; no aparece en workflows.
- **No sustituye el gate de staging** (ver regla `Pass (local)`).
- **No versiona estado:** `docker compose down -v` lo destruye todo; cada
  máquina re-ejecuta importación/seed. Eso es una ventaja (reproducibilidad),
  no un defecto — pero exige que la carga de datos sea scriptable e
  idempotente, lo cual a su vez mejora el proyecto.
- **Kubernetes sobra** para un stack de 3 servicios, aunque el motor local lo
  ofrezca (Rancher Desktop): Compose es suficiente. KISS.

## 6. Checklist portable (copiar a cualquier proyecto)

- [ ] `docker-compose.yml` en la raíz con `name:`, 3 servicios (db con
      healthcheck, wordpress, wpcli) y versiones de imagen fijadas a la
      paridad del hosting.
- [ ] Bind-mounts SOLO del código propio; core y BD en volúmenes.
- [ ] `WORDPRESS_CONFIG_EXTRA` con `WP_ENVIRONMENT_TYPE` + debug log,
      **duplicado en `wpcli`**.
- [ ] `wpcli` con `user: "33:33"`.
- [ ] `.env` gitignored + `.env.example` versionado + `${VAR:?…}`.
- [ ] Puerto parametrizado con default.
- [ ] Comentario de uso en cabecera del compose (up, run wpcli, URL, admin).
- [ ] ADR (o registro equivalente) que fije el alcance: solo desarrollo/QA,
      sin papel en despliegues, y la regla `Pass (local)` vs `Pass`.
- [ ] Primera sesión de QA en escalera (§4) con resultados registrados en la
      matriz de validación del proyecto.
- [ ] Guards por entorno en el código propio (`wp_get_environment_type()`)
      verificados aprovechando el gotcha §3.1.

## 7. Plantilla de referencia

La instancia concreta y comentada vive en [`docker-compose.yml`](../../docker-compose.yml)
y [`.env.example`](../../.env.example) de este repositorio; la decisión y sus
alternativas en [ADR 0014](../adr/0014-entorno-local-de-desarrollo-docker.md).
Para reutilizarla: copiar ambos archivos, sustituir los dos bind-mounts por el
plugin/theme del nuevo proyecto, ajustar la versión de imagen a la paridad del
hosting de destino y renombrar `name:`.

---

**Proyecto de origen:** Revista de Filosofía LOGO ET SPES (Fase 3, WordPress)
**Fecha:** 2026-07-31 (gotcha §3.7 y tag 7.0.4: 2026-08-18)
