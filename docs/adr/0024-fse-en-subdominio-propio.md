# ADR 0024: El FSE completo va a un subdominio propio, `logos-et-spes.cenfiss.net`

## Estado

Aceptada

## Fecha

2026-11-18

## Contexto

Hasta hoy el FSE ([ADR 0015](0015-block-theme-fse-site-editor.md)) llegaba al público en el mismo sitio que ya está en producción. El plan era este:

- §7.4 de ADR 0015 situaba el corte en el subdominio de la revista.
- Las notas del 2026-11-02 y el 2026-11-07 ordenaban la secuencia: sesión 9, después la comparación de [#103](https://github.com/refo44/demo-revistalogos/issues/103), después la fusión de `feat/fse` en `main` y por último el switch de [#84](https://github.com/refo44/demo-revistalogos/issues/84).
- [ADR 0016](0016-topologia-hosting-cpanel.md) prohibía crear dominios o subdominios nuevos.
- La beta en hosting iba a un subdominio desechable, que se borraría tras el corte ([#73](https://github.com/refo44/demo-revistalogos/issues/73), [#74](https://github.com/refo44/demo-revistalogos/issues/74), [#75](https://github.com/refo44/demo-revistalogos/issues/75) y [#85](https://github.com/refo44/demo-revistalogos/issues/85)).

El 2026-11-18 el propietario cambió el plan. La producción actual, `logo-et-spes.cenfiss.net`, no se sustituye. Se crea un subdominio nuevo, `https://logos-et-spes.cenfiss.net/`, y el FSE completo se despliega allí. El nombre del subdominio ya es el correcto de la revista ([ADR 0023](0023-nombre-de-la-revista-logos-et-spes.md)).

El mismo día, `logos-et-spes.cenfiss.net` aún no resuelve en DNS (NXDOMAIN). El subdominio está decidido, pero no existe todavía. Se creó el 2026-11-20 (punto e).

## Decisión

### 1. La producción clásica no se sustituye

`logo-et-spes.cenfiss.net` sigue en la línea live, el theme 0.2.x y el plugin 0.2.x, con sus minor y patch:

- Sale de `main` con tag anotado y FTPS ([ADR 0020](0020-despliegue-produccion-desde-etiqueta.md), `deploy-wordpress.yml`, Environment `wordpress-production`).
- Nunca recibe la major FSE.
- No hay switch en ese sitio.

### 2. El FSE completo vive en `logos-et-spes.cenfiss.net`

Es un WordPress propio en el cPanel `cenfiss2`, aislado como los demás sitios de ADR 0016 §2:

- un document root propio, que no está dentro de la carpeta de la revista ni en `/public_html` de `cenfiss.net` ni en `test.cenfiss.net`;
- una instalación nueva de WordPress con una base MySQL nueva;
- una cuenta FTP nueva enjaulada en esa raíz, que no es `deploy_revista@logo-et-spes.cenfiss.net`;
- PHP 8.3 solo para ese dominio (CloudLinux), sin tocar los demás;
- AutoSSL.

El sitio ejecuta la línea FSE: la major del mismo theme `revistalogos` y del mismo plugin `revistalogos-core`. Los directorios y los nombres siguen siendo los mismos (ADR 0015, nota 2026-11-02). Son dos instalaciones y cada una lleva su versión, así que no hay `revistalogos-classic` ni un segundo plugin.

### 3. Sustituye al subdominio desechable

El host temporal de [#73](https://github.com/refo44/demo-revistalogos/issues/73)–[#75](https://github.com/refo44/demo-revistalogos/issues/75) pasa a ser `logos-et-spes.cenfiss.net`, que no es desechable. La pasada de la sesión 9 ([#83](https://github.com/refo44/demo-revistalogos/issues/83)) en hosting se hace sobre él. [#85](https://github.com/refo44/demo-revistalogos/issues/85), que borraba el host tras el corte, se queda sin objeto.

### 4. Despliegue propio, nunca el de producción

- Workflow propio con `workflow_dispatch` manual (ADR 0009). No reutiliza `deploy-wordpress.yml`.
- Environment propio con sus secretos. No usa `wordpress-production` ni `PRODUCTION_*`.
- El FTPS se limita al theme y al plugin. No toca `uploads/`, `wp-config.php` ni el núcleo, y no activa nada.
- Mientras el sitio sea una beta sin indexar, se despacha desde `feat/fse` sin tag de producción, como preveía [#75](https://github.com/refo44/demo-revistalogos/issues/75). Antes de abrirlo al público hace falta una política de release propia (punto abierto c).
- Nada se despliega allí hasta que el propietario cree el subdominio y su cuenta FTP.

### 5. Indexación cerrada en el subdominio nuevo

La producción clásica está indexada y el subdominio nuevo tendrá su mismo contenido. Por eso:

- Ajustes → Lectura marca «Pedir a los motores de búsqueda que no indexen este sitio».
- `robots.txt` lleva `Disallow: /` y no lleva `Sitemap:`.
- Nada enlaza al subdominio, ni la producción ni GitHub Pages ni `/llms.txt`.

Abrir la indexación, las canónicas y cualquier redirección son decisiones explícitas del propietario. No las abre la documentación ni ningún despliegue.

### 6. El contenido viene de producción

La plantilla no crea contenido (contrato de conversión de ADR 0015). El sitio nuevo recibe una copia de la base de producción y de los `uploads/` que referencia:

- El host de producción se reescribe a `https://logos-et-spes.cenfiss.net` solo en esa copia.
- La producción no se modifica.
- El dump no se commitea.
- No se ejecuta `fixtures seed`.

Mientras el propietario no decida otra cosa, el contenido editorial se sigue cargando en la producción clásica. La copia del subdominio se puede rehacer (punto abierto b).

### 7. #84 y #103

[#84](https://github.com/refo44/demo-revistalogos/issues/84) deja de ser un switch en la producción. Pasa a ser el primer despliegue de la major FSE en `logos-et-spes.cenfiss.net`.

[#103](https://github.com/refo44/demo-revistalogos/issues/103) sigue como estaba: la producción clásica comparada con la major FSE sobre los mismos datos. Sigue bloqueando la fusión de `feat/fse` en `main` (ADR 0015, nota 2026-11-07). El primer despliegue en el subdominio no la espera, igual que el host desechable no la esperaba.

### 8. No ejecuta la migración del nombre

El subdominio lleva el nombre correcto, pero este ADR no ejecuta el plan de migración de ADR 0023 §6: ni redirecciones 301, ni `siteurl`/`home` de producción, ni reindexación.

Que `logos-et-spes.cenfiss.net` llegue a ser la URL canónica de la revista es una decisión posterior del propietario, dentro de ese plan (punto abierto a).

### Puntos abiertos (decisión del propietario)

a. **El sitio clásico.** Puede convivir sin fecha, redirigir con 301 al nuevo cuando el FSE sea el sitio público, o retirarse. Respondido en parte el 2026-11-19: no convive sin fecha (ver la nota «El clásico se retira o se redirige»). Falta elegir entre retirarlo y redirigirlo, y cuándo.

b. **Dónde se edita.** Una vez exista el sitio nuevo, falta decidir si se hace una copia única, copias repetidas o edición en un solo sitio, y cuál. Dos bases editadas en paralelo divergen.

c. **Release del subdominio cuando deje de ser beta.** Desde qué rama y con qué tag se despliega. Qué pasa con `main` y con la línea live al fusionar `feat/fse`: ADR 0020 §4 descarta ramas `release/` largas, y la producción clásica sigue necesitando minor y patch.

d. **Indexación y canónicas** del subdominio nuevo.

e. **Alta en cPanel.** DNS, document root, MySQL, cuenta FTP, PHP 8.3 y AutoSSL. Es una acción del propietario; el 2026-11-18 el nombre aún no resuelve. Hecha el 2026-11-20 ([#74](https://github.com/refo44/demo-revistalogos/issues/74)): WordPress vacío, cerrado a buscadores y con HTTPS forzado. El Environment de GitHub existe desde el 2026-11-21; falta la copia de contenido. Detalle en `docs/operations/fse-subdomain-deployment.md`, «Estado del alta».

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Switch en el mismo `logo-et-spes.cenfiss.net` (plan anterior) | Sustituye el sitio indexado que se edita a diario. El propietario prefiere no tocarlo. |
| Subdominio desechable y, después, el switch en producción | Son dos movimientos y un host que se tira. El subdominio nuevo es permanente y ya lleva el nombre correcto. |
| Instalar el FSE en una carpeta dentro de la revista o de `cenfiss.net` | Mezcla raíces y viola ADR 0016 §2. |
| Activar la major en el WordPress de producción | WordPress ejecuta el directorio activo, así que el FSE sustituiría al clásico (ADR 0015, nota 2026-11-02). |

## Consecuencias

**Beneficios:**

- El sitio público e indexado no corre riesgo con el FSE.
- El Site Editor se prueba con datos reales en hosting real, no solo en Docker.
- El subdominio nuevo ya usa el nombre correcto de la revista.

**Riesgos / costes:**

- Hay dos instalaciones de WordPress que mantener: núcleo, plugins de terceros, copias de seguridad, PHP y caché.
- Si se edita en las dos, las bases divergen (punto b).
- Un arreglo de dominio que necesiten los dos sitios sale primero en la línea live desde `main` y llega a `feat/fse` por la fusión de `main` de siempre.
- El contenido duplicado puede afectar al posicionamiento si el subdominio se indexa por error. De ahí el punto 5.
- `cenfiss2` aloja un sitio más.

**Trabajo futuro:**

- Alta del subdominio en cPanel (punto e).
- Workflow y Environment del subdominio ([#75](https://github.com/refo44/demo-revistalogos/issues/75)).
- Reescribir [#73](https://github.com/refo44/demo-revistalogos/issues/73), [#74](https://github.com/refo44/demo-revistalogos/issues/74), [#83](https://github.com/refo44/demo-revistalogos/issues/83) y [#84](https://github.com/refo44/demo-revistalogos/issues/84) según este ADR, y cerrar [#85](https://github.com/refo44/demo-revistalogos/issues/85).
- Resolver los puntos a–d antes de abrir el subdominio al público.

## Qué sustituye

| ADR | Efecto |
| --- | ------ |
| 0015 §7.4 | El corte ya no ocurre en `logo-et-spes.cenfiss.net`. El FSE llega al público en `logos-et-spes.cenfiss.net`. |
| 0015, notas 2026-11-02 y 2026-11-07 | Lo que dicen del switch de [#84](https://github.com/refo44/demo-revistalogos/issues/84) en producción queda sin efecto (punto 7). Se mantienen las dos líneas de versión y la comparación de [#103](https://github.com/refo44/demo-revistalogos/issues/103) antes de fusionar. |
| 0016 contexto y §3 | Contexto: «no hay dominios ni subdominios nuevos» admite esta excepción. §3: el corte de 2026-08-19 sigue siendo historia. |
| 0020 | Sin cambios en producción. No se aplica al subdominio nuevo mientras sea una beta (punto 4). |
| 0023 §6 | Sin cambios. El subdominio no es la migración del nombre (punto 8). |

## El clásico se retira o se redirige (2026-11-19)

Nota del propietario. Responde en parte el punto a. No cambia los puntos 1–8 ni los puntos abiertos b–e.

El sitio clásico no va a convivir sin fecha: se retirará o se redirigirá con 301 a `logos-et-spes.cenfiss.net` pronto. Siguen abiertos cuál de las dos cosas y cuándo.

Consecuencias:

- `feat/fse` sigue siendo una rama aparte hasta entonces. Las correcciones de dominio siguen llegando desde `main` por la fusión de `main` de siempre.
- Cuando el clásico se retire o se redirija, `feat/fse` se fusiona en `main` con un solo theme, en el directorio `revistalogos`. Antes sigue haciendo falta [#103](https://github.com/refo44/demo-revistalogos/issues/103) (ADR 0015, nota 2026-11-07).
- No se crea un segundo directorio de theme en `main`.
- El punto c sigue abierto: desde qué rama y con qué tag se despliega el subdominio después de la fusión.

Alternativa descartada: fusionar ya `feat/fse` en `main` con dos themes, el clásico en `revistalogos` y el FSE en un directorio nuevo (por ejemplo `revistalogos-fse`). El plugin no es problema, porque en `feat/fse` solo añade los bloques de dominio y sirve a los dos sitios. El theme sí lo es:

- Duplica el theme y sus pruebas.
- Obliga a cambiar la puerta del subdominio, que exige `themes/revistalogos/`, y `tools/check-release-pending.sh`, que tendría que vigilar dos `Version:`.
- Reabre la nota 2026-11-02 de ADR 0015 («Dos líneas de versión, sin renombrar») y la regla de CLAUDE.md que la recoge.
- Choca con [#103](https://github.com/refo44/demo-revistalogos/issues/103), que bloquea la fusión.

Ese coste solo compensa si los dos sitios conviven mucho tiempo, y no va a ser así.

## Referencias

- [ADR 0009](0009-mecanismo-y-alcance-del-despliegue.md), [0015](0015-block-theme-fse-site-editor.md), [0016](0016-topologia-hosting-cpanel.md), [0020](0020-despliegue-produccion-desde-etiqueta.md) y [0023](0023-nombre-de-la-revista-logos-et-spes.md)
- `docs/fse-implementation-prompt.md`
- `docs/operations/produccion-wordpress.md`
- Issue padre [#12](https://github.com/refo44/demo-revistalogos/issues/12)
