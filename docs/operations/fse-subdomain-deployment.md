# Runbook — Despliegue del subdominio FSE (theme + plugin)

Despliegue **manual** de la major FSE del theme `revistalogos` y del plugin
`revistalogos-core` por FTPS a `https://logos-et-spes.cenfiss.net`, el
WordPress propio del FSE ([ADR 0024](../adr/0024-fse-en-subdominio-propio.md)
§4, [#75](https://github.com/refo44/demo-revistalogos/issues/75)). Fíjate en
la S: **no** es la producción `logo-et-spes.cenfiss.net`, que tiene su propio
runbook ([wordpress-manual-deployment.md](wordpress-manual-deployment.md)) y
nunca recibe la major FSE.

Que el workflow exista, o que esté fusionado, **no autoriza ejecutarlo**. Cada
run exige una decisión explícita del propietario. El primer run es
[#84](https://github.com/refo44/demo-revistalogos/issues/84). El subdominio
existe en cPanel desde el 2026-11-20
([#74](https://github.com/refo44/demo-revistalogos/issues/74)), con un
WordPress cerrado a buscadores. El Environment y sus secretos existen
desde el 2026-11-21, y desde el 2026-11-22 lleva una copia del contenido
de producción (ver «Copia de contenido»). El primer run se hizo el
2026-11-22 (ver «Primer despliegue»).

## Qué hace y qué no

| | |
| --- | --- |
| Workflow | `.github/workflows/deploy-fse-subdomain.yml`, solo `workflow_dispatch` |
| Rama | Solo `feat/fse`. Rechaza `main`, cualquier otra rama y toda etiqueta `vX.Y.Z` |
| Etiqueta | Ninguna mientras el subdominio sea una beta sin indexar. ADR 0020 rige solo producción. Antes de abrirlo hace falta una política de release propia (ADR 0024, punto c) |
| Environment | `wordpress-fse-subdomain`, propio. Nunca `wordpress-production` ni `PRODUCTION_*` |
| Alcance | `wp-content/themes/revistalogos/` y `wp-content/plugins/revistalogos-core/` |
| No toca | `uploads/`, `wp-config.php`, núcleo, base de datos, plugins de terceros, `.htaccess`, la jaula de producción |
| No hace | Activar el theme o el plugin, migraciones, `fixtures seed`, cambiar Lectura o `robots.txt` |
| PHP | `setup-php` configura solo el runner de Actions que construye `vendor/`. No cambia el PHP del subdominio en cPanel |

Los trabajos, en orden:

1. **`require-feat-fse`.** Si el run no salió de `feat/fse`, falla antes de
   pedir la aprobación del Environment.
2. **`deploy-theme`.** Primero corre `tools/require-fse-subdomain-target.sh`
   con los secretos; si pasa, sube el theme con `dangerous-clean-slate: false`.
3. **`deploy-plugin`.** Repite la puerta, construye `vendor/` con
   `composer install --no-dev` y sube el plugin. Excluye `.qa-*.php` y
   `.gitkeep`: el FTPS del subdominio corta la conexión al subir un archivo
   vacío, y el único que hay es un `.gitkeep` de `vendor/` que nada carga.
4. **`verify-noindex`.** Solo lectura: pide `robots.txt` y la portada, y
   falla si `robots.txt` ya no dice `Disallow: /` o la portada no lleva
   `<meta name="robots" … noindex>`. No arregla nada: avisa. Si falla, el
   propietario vuelve a marcar Ajustes → Lectura → «Pedir a los motores de
   búsqueda que no indexen este sitio» en el subdominio.

## La puerta del destino

`tools/require-fse-subdomain-target.sh` corre antes de cada subida y no
imprime ningún secreto. Rechaza:

- un `GITHUB_REF` distinto de `refs/heads/feat/fse`;
- un secreto vacío;
- una cuenta FTP que no sea `usuario@logos-et-spes.cenfiss.net`. Es una
  lista de permitidos, no de prohibidos. La cuenta de producción
  (`…@logo-et-spes.cenfiss.net`) tiene además su propio mensaje;
- un directorio remoto que no termine exactamente en
  `wp-content/themes/revistalogos/` (theme) o
  `wp-content/plugins/revistalogos-core/` (plugin), con la barra final. Puede
  llevar delante el prefijo del docroot. Tampoco acepta `..` ni una ruta que
  contenga `logo-et-spes.cenfiss.net`.

El contrato es `./tools/qa-require-fse-subdomain-target.sh`: 26 casos, sin
red ni Docker. Corre en CI, en el job `deploy-gates` de `Tests`, junto al de
producción.

## Alta (propietario, una vez)

Nada de esto lo puede hacer un agente.

1. **cPanel** (`cenfiss2`, #74). El subdominio `logos-et-spes.cenfiss.net`
   lleva:
   - document root propio y base MySQL propia;
   - PHP 8.3 por dominio y AutoSSL;
   - una cuenta FTP **enjaulada en ese docroot**, con nombre
     `usuario@logos-et-spes.cenfiss.net`. No reutilices
     `deploy_revista@logo-et-spes.cenfiss.net`.
2. **WordPress en el subdominio.** Va con el contenido copiado de producción
   (ADR 0024 §3), con el host reescrito solo en la copia. Lectura queda en
   noindex desde el primer momento. Tras cargar la copia, vuelve a marcar
   Lectura: ver «Estado del alta».
3. **GitHub → Settings → Environments → `wordpress-fse-subdomain`.**
   Conviene limitar *Deployment branches* a `feat/fse` y, si se quiere,
   pedir revisión manual. Los secretos:

   | Secreto | Contenido |
   | --- | --- |
   | `FSE_FTP_SERVER` | Host FTPS cuyo certificado coincide con el nombre: `caroni.tepuyserver.net`, no el `ftp.cenfiss.net` que muestra cPanel (ver «FTP dedicado» en `produccion-wordpress.md`) |
   | `FSE_FTP_USERNAME` | `usuario@logos-et-spes.cenfiss.net` |
   | `FSE_FTP_PASSWORD` | Contraseña de esa cuenta |
   | `FSE_THEME_REMOTE_DIR` | Ruta, vista desde la jaula, que acaba en `wp-content/themes/revistalogos/` |
   | `FSE_PLUGIN_REMOTE_DIR` | Ruta, vista desde la jaula, que acaba en `wp-content/plugins/revistalogos-core/` |

### Estado del alta (2026-11-20)

Hecho por el propietario y comprobado desde fuera el 2026-11-20 y el
2026-11-21:

- Docroot `public_html/logos-et-spes.cenfiss.net`, hermano de la carpeta de
  la revista. DNS resuelve, AutoSSL emite el certificado y Force HTTPS está
  activo: `http://` y `www.` redirigen con 301 a
  `https://logos-et-spes.cenfiss.net/`.
- PHP 8.3 solo para este dominio (`x-powered-by: PHP/8.3.37`).
  `cenfiss.net` y `test.cenfiss.net` siguen en 8.0 y no se tocaron. El
  aviso de PHP obsoleto que muestra el wp-admin de `cenfiss.net` no es de
  este sitio.
- Cuenta FTP `deploy_fse@logos-et-spes.cenfiss.net`, enjaulada en ese
  docroot.
- WordPress instalado limpio con Softaculous, sin contenido hasta la
  copia del 2026-11-22. No se usó «Clonar» desde producción: copia también los ajustes indexables.
- Cerrado a buscadores: la portada lleva `noindex, nofollow` y
  `robots.txt` responde `User-agent: *` / `Disallow: /`.

Lo que costó y conviene saber la próxima vez:

- **La carpeta de la cuenta FTP.** Al escribir el nombre de usuario, cPanel
  lo añade al campo «Directorio» (`…/logos-et-spes.cenfiss.net/deploy_fse`).
  Hay que rellenar el directorio al final y comprobar la ruta antes de
  crear la cuenta; después no se puede editar.
- **Los plugins de Softaculous.** La instalación trae unos 25 plugins
  propios, con varios activos. SiteSEO impone `index, follow`, un
  `sitemaps.xml` y su propio `robots.txt`, por encima de Lectura. Los que
  tienen su versión Pro instalada no se desactivan en lote (la casilla sale
  gris): hay que pulsar «Desactivar» en cada fila. Quedaron desactivados,
  no borrados, por decisión del propietario.
- **`robots.txt` es un archivo físico.** Desde WordPress 5.3, Lectura solo
  pone la meta `noindex` y ya no escribe `Disallow: /`. Por eso hay un
  `robots.txt` en el docroot. Está fuera de `wp-content`, así que el
  workflow no lo toca, y `verify-noindex` lo exige.
- **La copia de producción reabre la indexación.** Producción es
  indexable, así que su base trae Lectura desmarcada y su propia lista de
  plugins activos. Justo después de cargar la copia hay que volver a marcar
  Lectura y comprobar que la portada lleva `noindex`. En la copia del
  2026-11-22 se puso `blog_public` a `0` por SQL antes de abrir el sitio. El `robots.txt`
  físico no cambia con la copia.
- **El servidor FTP no es el que muestra cPanel.** `ftp.cenfiss.net` lleva
  a la misma máquina, pero su certificado FTPS es de
  `caroni.tepuyserver.net`. `ftp.logos-et-spes.cenfiss.net` no existe en
  DNS. `FSE_FTP_SERVER` usa el nombre del certificado, igual que
  producción.

El 2026-11-21 el propietario creó el Environment `wordpress-fse-subdomain`
en GitHub. Comprobado con `gh api`: una sola regla de *Deployment
branches*, la rama `feat/fse`, y los cinco secretos `FSE_*`, sin variables.
GitHub no deja leer los valores; la puerta del workflow los valida en el
primer run.

La contraseña HTTP opcional de #74 no se puso.

### Copia de contenido (2026-11-22)

El propietario copió la base y los `uploads/` de producción al subdominio
(ADR 0024 §6), a mano desde cPanel y phpMyAdmin, porque el hosting no
tiene SSH ni WP-CLI. Es una foto del 2026-11-22: lo que se cargue después
en producción no llega solo (ADR 0024, punto abierto b). No se usó
`fixtures seed`.

Pasos, en este orden:

1. **Exportar producción.** phpMyAdmin → base de producción → Exportar,
   método Personalizado, compresión gzip, con `DROP TABLE` y sin
   `CREATE DATABASE`/`USE`. Producción solo se lee. El volcado se guardó
   fuera del repositorio y se borra después; nunca se sube a Git.
2. **Importar** en la base del subdominio, que ya tenía las tablas de
   Softaculous con otro prefijo. Ninguna choca: conviven.
3. **Cambiar `$table_prefix`** en el `wp-config.php` del subdominio al
   prefijo de producción. Solo esa línea, y después de importar: si va
   antes, WordPress muestra la pantalla de instalación.
4. **SQL antes de abrir el sitio**, en la base del subdominio: `siteurl` y
   `home` a `https://logos-et-spes.cenfiss.net` y `blog_public` a `0`
   (3 filas). Así la copia nunca sirve una página indexable, y Lectura
   aparece ya marcada.
5. **`uploads/`.** Administrador de archivos → comprimir
   `wp-content/uploads` de producción en un zip fuera de `public_html`,
   extraerlo en `wp-content/` del subdominio y borrar el zip.
6. **Primer acceso a wp-admin** del subdominio, con la misma cuenta que en
   producción (la base es una copia). Comprobar Lectura y los plugins.
7. **Better Search Replace**, instalado solo en el subdominio: buscar
   `//logo-et-spes.cenfiss.net`, reemplazar por
   `//logos-et-spes.cenfiss.net`, todas las tablas, GUID sin marcar.
   Primero en prueba (20 celdas: 11 en `posts`, 7 en `postmeta`, 1 en
   `options` y 1 en `users`), luego en real (20 cambios), y se borra el
   plugin. Un `REPLACE` en SQL rompería los datos serializados. Repetirlo
   es inocuo: `logos-et-spes` no contiene `logo-et-spes`.

Lo que hay que saber:

- **Los plugins de producción llegan con sus ajustes.** Al primer acceso,
  WordPress desactivó los que no existen en el subdominio:
  `revistalogos-core`, Contact Form 7 y WP Statistics. Seguían activos
  GoSMTP (con la cuenta SMTP de producción), Backuply (con su destino de
  copias) y SpeedyCache. El propietario desactivó los 22 plugins; ninguno
  se borró.
- **La portada salía en blanco hasta #84.** La base dice que el theme
  activo es `revistalogos`, que todavía no está en el subdominio, y
  WordPress no tiene plantilla que servir. Por lo mismo, la API no muestra
  los PDF que cuelgan de artículos: sus tipos los registra el plugin.
- **Quedan las tablas de Softaculous** con su prefijo en la misma base. No
  las usa nada; se pueden borrar más adelante.

Comprobado desde fuera el 2026-11-22:

- `wp-json` responde «LOGOS ET SPES» con `url` y `home` en el subdominio.
- `robots.txt` sigue en `Disallow: /`.
- Tres archivos de `uploads/` (una imagen y dos PDF) se sirven con los
  mismos bytes que en producción.
- En páginas y medios de la API, las únicas referencias a
  `logo-et-spes` que quedan son los GUID, que se dejaron a propósito.
- Producción no cambió: sus páginas solo enlazan a su propio dominio.

### Primer despliegue (2026-11-22, #84)

Antes del run se probó en Docker el estado en que queda el subdominio justo
después de subir: theme de `feat/fse` activo, plugin desactivado y
`blog_public` a `0`. La portada respondió 200 con
`<meta name='robots' content='noindex, nofollow'>` y sin errores de PHP, así
que `verify-noindex` podía pasar antes de activar el plugin. Docker volvió
después a su estado.

1. **Run [45082919307](https://github.com/refo44/demo-revistalogos/actions/runs/45082919307)**
   desde `feat/fse` (a923eff). Subió el theme. `deploy-plugin` falló dos
   veces (el run y un reintento de los trabajos fallidos) con
   `Server sent FIN packet unexpectedly`, siempre en
   `vendor/thecodingmachine/safe/generated/Exceptions/.gitkeep`, el único
   archivo vacío del build. El plugin quedó subido a medias pero inactivo,
   así que el sitio siguió sirviendo el theme con noindex.
2. **[#141](https://github.com/refo44/demo-revistalogos/pull/141)** excluye
   `**/.gitkeep` de la subida del plugin (ver `deploy-plugin` arriba).
3. **Run [45100104127](https://github.com/refo44/demo-revistalogos/actions/runs/45100104127)**
   desde `feat/fse` (715bc45): los cuatro trabajos en verde. El plugin
   completo, `vendor/` incluido, quedó en el subdominio.
4. **Activación a mano.** El theme `revistalogos` ya estaba activo porque
   lo dice la base copiada. El propietario activó `revistalogos-core` en el
   wp-admin del subdominio. El workflow no activa nada.

Comprobado desde fuera el 2026-11-22, con el plugin activo:

- La API lista los mismos tipos que producción y las mismas cantidades:
  11 artículos, 1 número y 10 autores.
- 41 URLs públicas: la portada, los archivos de artículos, números y
  autores, el número, los 11 artículos, los 10 autores, 13 páginas, una URL
  inexistente y la búsqueda. Responden 200, salvo el 404 esperado y la
  búsqueda, que redirige a `/buscar/?q=…` igual que producción. Todas
  llevan `noindex, nofollow`, ninguna muestra errores de PHP y los enlaces
  apuntan al subdominio. La única mención a `logo-et-spes` está en el
  texto del aviso de privacidad, que nombra la dirección principal.
- Las 11 hojas de estilo, scripts e imágenes que piden la portada, un
  artículo y el número responden 200.
- `robots.txt` sigue en `Disallow: /`.
- Versiones servidas: el subdominio tiene theme 0.3.8 y plugin 0.2.27;
  producción sigue en theme 0.2.17 y plugin 0.2.26, sin cambios.

No comprobado: que `dom` y `mbstring` estén activas en el PHP del
subdominio. Hace falta antes de generar allí un PDF de artículo.

## Ejecutar (cada vez, decisión del propietario)

1. Comprobar que `feat/fse` está en verde en `Tests` y que su commit es el
   que se quiere ver en el subdominio.
2. Actions → **Deploy FSE subdomain (feat/fse → logos-et-spes)** → Run
   workflow → *Use workflow from*: **Branch `feat/fse`**.
3. Si es el primer despliegue (#84), activar el theme y el plugin a mano en
   el wp-admin del subdominio. El workflow no activa nada.
4. Revisar el run. Si `verify-noindex` falla, el sitio está indexable: hay que
   restaurar Lectura antes de cualquier otra cosa.

GitHub solo lista un workflow `workflow_dispatch` si el archivo está en la
rama por defecto, así que `deploy-fse-subdomain.yml` vive también en `main`.
Lanzado desde `main` falla en el primer trabajo, como debe.

## Cuando `feat/fse` se fusione en `main`

`feat/fse` es temporal. Se fusiona en `main` cuando el clásico se retire o se
redirija ([ADR 0024](../adr/0024-fse-en-subdominio-propio.md), nota
2026-11-19), después de
[#103](https://github.com/refo44/demo-revistalogos/issues/103). Tres sitios
están atados a `feat/fse` y tienen que cambiar juntos, en el mismo PR o justo
después:

1. el `if` del job `require-feat-fse` en
   `.github/workflows/deploy-fse-subdomain.yml`;
2. `ALLOWED_REF` en `tools/require-fse-subdomain-target.sh` y los casos de
   `tools/qa-require-fse-subdomain-target.sh` que lo usan;
3. la regla *Deployment branches* del Environment `wordpress-fse-subdomain`
   en GitHub. Está fuera del repo y la cambia el propietario.

Si se cambia solo uno, los otros siguen exigiendo `feat/fse`: o el workflow
rechaza todos los runs, o queda una protección menos. Qué los sustituye (rama, tag y
política de release del subdominio) es el punto abierto c de ADR 0024.

## Rollback

No hay rollback automático. Para volver atrás, se relanza el workflow desde
un commit anterior de `feat/fse`: se revierte en una rama, PR a `feat/fse` y
se relanza. No borres directorios remotos a mano: el upload no es un espejo,
y un archivo que sobra en el theme no rompe un tema de bloques.
