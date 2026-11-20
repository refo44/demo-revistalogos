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
[#84](https://github.com/refo44/demo-revistalogos/issues/84), y solo cuando el
subdominio exista en cPanel
([#74](https://github.com/refo44/demo-revistalogos/issues/74)). El
2026-11-18 el nombre aún no resolvía en DNS.

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
   `composer install --no-dev` y sube el plugin. Excluye `.qa-*.php`.
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
   noindex desde el primer momento.
3. **GitHub → Settings → Environments → `wordpress-fse-subdomain`.**
   Conviene limitar *Deployment branches* a `feat/fse` y, si se quiere,
   pedir revisión manual. Los secretos:

   | Secreto | Contenido |
   | --- | --- |
   | `FSE_FTP_SERVER` | Host FTPS del cPanel |
   | `FSE_FTP_USERNAME` | `usuario@logos-et-spes.cenfiss.net` |
   | `FSE_FTP_PASSWORD` | Contraseña de esa cuenta |
   | `FSE_THEME_REMOTE_DIR` | Ruta, vista desde la jaula, que acaba en `wp-content/themes/revistalogos/` |
   | `FSE_PLUGIN_REMOTE_DIR` | Ruta, vista desde la jaula, que acaba en `wp-content/plugins/revistalogos-core/` |

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
