# Testing Foundation y estrategia de pruebas

Estrategia canónica de pruebas del monorepo. Las decisiones vinculantes
están en [ADR 0018](adr/0018-testing-foundation.md). Este documento no
reescribe ADR 0006, 0009, 0014 ni 0017.

## Propósito

Proteger comportamiento de dominio, contratos e integraciones reales —
sobre todo contenido editorial, relaciones, URLs, importers y guards —
con la pila más pequeña que lo permita. No maximizar recuento ni
cobertura. ADR 0017 WU1–WU4 usan esta base en TDD (política, adaptador,
orquestación, renderer Dompdf). Media Library y el cableado WordPress
siguen pendientes.

## Autoridad

Fuente durable (Git), en este orden cuando hablen de pruebas:

1. `content-source/` (texto editorial; no política de tests).
2. `docs/` — este documento es la guía operativa de testing.
3. `docs/adr/` — ADR 0018 (decisiones); ADR 0017 (PDF: renderer Dompdf
   real; persistencia y publicación WordPress no cableadas).
4. `CLAUDE.md` — resumen para agentes; apunta aquí, no duplica el manual.

**`.cursor/` está gitignored a propósito.** Si un desarrollador mantiene
reglas Cursor locales, son espejos opcionales de conveniencia: no son
fuente de verdad, no se versionan, no se exigen en otros clones ni en CI.

## Taxonomía

### PHP syntax (`php -l`)

Sintaxis nativa únicamente. No es análisis estático, estilo ni tipos.

- Canónico local: `./tools/php-lint.sh`
- Composer: `composer lint:php`
- Ámbito: `wordpress/wp-content/plugins/revistalogos-core/**/*.php`,
  `wordpress/wp-content/themes/revistalogos/**/*.php`, `tests/**/*.php`.
  Excluye `vendor/`, WordPress Core, plugins/themes de terceros, caché y
  temporales (no se recorre la instalación montada completa).

### Composer dependency audit (`composer audit --locked`)

Advisories conocidas sobre lockfiles Composer. La raíz cubre tooling de
test (PHPUnit). El plugin cubre runtime Dompdf
(`wordpress/wp-content/plugins/revistalogos-core/composer.lock`).
`composer audit:deps` audita la raíz. CI también audita el lock del
plugin. Falla con el exit status nativo de Composer. Sin reglas de
ignore. **No está garantizado offline.** No requiere `vendor/` para
auditar.

No sustituye revisión de WordPress Core, plugins, themes, dependencias npm
ni el hosting de producción. No cierra D12b.

### Nivel 1 — Unitario (PHPUnit)

Comportamiento de dominio puro: milisegundos, determinista.

- Sin base de datos, HTTP, orquestación Docker **dentro** del test, ni
  arranque de WordPress.
- Sin red ni filesystem salvo que el contrato lo exija (entonces rutas
  temporales aisladas).

Ejemplos futuros: política «¿generar PDF?», validación ORCID, transformaciones
deterministas.

### Nivel 2 — Integración WordPress

Contratos que **son** WordPress: meta, hooks, CPT, REST, adjuntos, caps,
sanitizers que llaman APIs de WP.

Hoy: harnesses Docker aislados (`tools/qa-*.sh`) contra WordPress **7.1**.
No se instala la test suite oficial de WordPress en este incremento (duplicaría
Compose). Un `composer test:integration` PHPUnit queda como trabajo futuro.

No fingir APIs de WordPress cuando el objeto de la prueba es el comportamiento
de WordPress.

### Nivel 3 — Aceptación / QA

Flujos completos: bootstrap, teardown, permalinks, UX editorial, rutas HTTP.
Siguen siendo los scripts `tools/qa-*.sh`. No reescribirlos a PHPUnit por
uniformidad.

## Directorios

```text
tests/
  Unit/         PHPUnit nivel 1
  Support/      bootstrap unitario (sin WordPress)
  Features/     Gherkin (.feature); hoy solo README de convención
```

No hay `tests/Integration/` hasta que exista un bootstrap PHPUnit/WP.

Composer de **test** vive en la **raíz** (`composer.json`,
`phpunit.xml.dist`). Composer de **runtime** vive en
`wordpress/wp-content/plugins/revistalogos-core/` (Dompdf). Ningún
`vendor/` se versiona. El deploy FTPS genera el `vendor/` del plugin
con `--no-dev` y lo sube.

## PHPUnit y Composer

- Runner: **PHPUnit 9.6** (`phpunit/phpunit`, `require-dev`).
- Sintaxis: nativo `php -l` vía `tools/php-lint.sh` / `composer lint:php`. Sin PHPStan, Psalm ni PHPCS.
- `composer test` = lint PHP + `composer audit --locked` + suite unitaria.
  No ejecuta `tools/qa-*.sh`.
- Composer de raíz: **solo** tooling de test. El plugin carga su
  `vendor/autoload.php` si existe y sigue usando `require_once` para
  código propio (ADR 0006). El workflow FTPS sube el `vendor/` generado
  del plugin; no sube `tests/` ni el `vendor/` de la raíz.
- `config.platform.php` es **8.2.0**: baseline de resolución del tooling
  de test, no el `Requires PHP: 7.4` del plugin ni el runtime PHP **8.3**
  de Docker/CI/producción. Son cuatro conceptos distintos. `composer.lock`
  debe instalarse **sin** `--ignore-platform-reqs`. Un clone limpio con
  el lockfile tiene que resolver contra esa plataforma.

## Integración WordPress

Reproducible en Docker, BD desechable, sin producción y sin la BD Docker
primaria del desarrollador cuando el harness muta datos.

Patrón ya usado: `docker compose -p <proyecto-efímero>` + `down -v`.

`tools/qa-author-permalinks.sh` aún usa los volúmenes primarios (`8080`):
excepción conocida; no tomarlo como modelo para tests nuevos.

## Shell QA vigente

| Script | Rol | Notas |
| ------ | --- | ----- |
| `tools/qa-editorial-bootstrap.sh` | Aceptación: bootstrap Vol. 1, adopción, teardown, guards, HTTP | Aislado, puerto 8082 |
| `tools/qa-fixtures-bootstrap.sh` | Alias del anterior | No duplicar |
| `tools/qa-article-editorial-ux.sh` | Aceptación: picker de autores, publicar-con-autor, PDF picker, CTA | Aislado, puerto 8084 |
| `tools/qa-article-pdf-adapter.sh` | Integración: adaptador PDF de solo lectura (ADR 0017 WU2) | Aislado, puerto 8085 |
| `tools/qa-article-pdf-renderer.sh` | Integración: renderer Dompdf en memoria (ADR 0017 WU4), PHP Apache + WP-CLI | Aislado, puerto 8086 |
| `tools/qa-author-permalinks.sh` | Integración: `journal_author`, permalinks | Volúmenes **primarios** |
| `tools/qa-volume1-bootstrap-admin.sh` | Regresión de **ausencia**: UI `Bootstrap_Admin` retirada en 0.2.6 | No es pilar permanente; no ampliar |

`tools/qa-content-recovery-admin.sh` ya no existe (UI de recovery retirada).
PHPUnit no replica estos flujos enteros.

## Gherkin

- Ubicación canónica: `tests/Features/`.
- Idioma: **español** (como `docs/`). Un feature, un idioma.
- Behat **no** está instalado. Los `.feature` son especificación de negocio
  versionada; PHPUnit y los harnesses ejecutan.
- Prohibido: selectores CSS, nombres de clase/método, IDs de BD, `sleep`,
  hooks internos salvo que el hook sea el contrato público.
- ADR 0017 work unit 1: `tests/Features/article-pdf-generation.feature`
  (especificación de negocio; sin Behat). La política pura se verifica
  en PHPUnit. El cableado WordPress y el renderer siguen pendientes.

Gherkin describe comportamiento observable. PHPUnit verifica PHP. Los
harnesses verifican el flujo integrado. Pueden complementarse; no hace falta
una traza 1:1.

## TDD (dominio nuevo)

1. **RED** — el test más pequeño que expresa el comportamiento; debe fallar
   por la razón esperada.
2. **GREEN** — el cambio de producción más pequeño que lo cumple.
3. **REFACTOR** — diseño, sin cambiar comportamiento, tests en verde.

Reglas: un incremento de comportamiento a la vez; no una batería especulativa
de arquitectura no escrita; un bug fix empieza por un test de regresión
cuando sea práctico; no cambiar tests para ocultar una regresión salvo que
el requisito haya cambiado.

## Calidad de tests

Un test debe proteger al menos uno de: invariante de dominio; comportamiento
observable; contrato de integración; permiso/seguridad; integridad de datos;
fallo importante; regresión ocurrida o realista.

Evitar: constantes vs sí mismas; getters triviales; «WordPress hace lo que
dice el handbook»; whitespace de markup; orden incidental de arrays; métodos
privados; estructura interna de clases; tests solo para subir cobertura.

Nombres: el comportamiento que falló (`test_last_token_is_the_surname_used_in_citation_formats`),
no el nombre del método de producción. Sin numerar tests.

Asserciones centradas. Sin snapshots enormes de HTML. En HTML, semántica,
no layout.

## Anti-flakiness e independencia

Nunca: red externa, producción, reloj de pared, `sleep` arbitrario, filas de
BD sin `ORDER BY`, timestamps del mismo segundo como orden implícito, azar
sin semilla, estado global de otro test, orden de ejecución, contenido
editorial preexistente.

Lección ya vista: un teardown que reclasificaba artículos tras borrar el
número (meta `issue` + hash de adopción) y orden por timestamps del mismo
segundo. Los tests que necesiten orden o tiempo deben **definirlo**.

Cada test de integración crea su estado y lo aísla/limpia. Ejecutable
solo, repetido y en cualquier orden. Sin fixtures persistentes ocultos.

## Mocks

Objetos reales simples en unitarios. Mockear límites significativos
(renderer PDF, persistencia de adjuntos, servicio externo). No mockear la
clase bajo prueba, value objects, ni cada función de WordPress. Si el
comportamiento es de WordPress, integración real.

## Base de datos, archivos, red

- Unitarios: sin DB.
- Integración: DB WordPress desechable. Nunca producción, nunca la BD
  primaria si el test la muta de forma no aislada, nunca contenido real
  como prerrequisito.
- Archivos generados: directorio temporal y limpieza. Los tests futuros de
  PDF no acumulan PDFs en Git.
- Suite por defecto **offline** tras tener dependencias e imágenes, salvo
  `composer audit --locked`, que **no está garantizado offline** (puede
  requerir metadatos/red de advisories de Composer/Packagist; no requiere
  `vendor/`). No producción, GitHub, Crossref, ORCID, CDN ni APIs remotas
  de producto. Eso, si acaso, es smoke manual aparte.

## Comandos canónicos

```bash
# Gate rápido — CI y local con PHP+Composer
composer install --no-interaction
composer lint:php
composer audit:deps    # composer audit --locked
composer test:unit
composer test          # lint → audit --locked → units; not qa-*.sh

# Portátil sin PHP nativo (ADR 0014)
./tools/php-lint.sh
./tools/run-phpunit.sh
# audit: docker run --rm -v "$PWD":/app -w /app composer:2 composer audit --locked

# Nivel 2/3 — Docker aislado (nunca producción)
./tools/qa-editorial-bootstrap.sh
./tools/qa-article-editorial-ux.sh
./tools/qa-article-pdf-adapter.sh   # ADR 0017 WU2, aislado
./tools/qa-article-pdf-renderer.sh  # ADR 0017 WU4, aislado
./tools/qa-volume1-bootstrap-admin.sh   # ausencia de UI temporal
./tools/qa-author-permalinks.sh         # excepción: volúmenes primarios
```

`php -l` comprueba **sintaxis**. `composer audit --locked` comprueba
**advisories del lockfile de Composer**. PHPUnit comprueba
**comportamiento**. Los `qa-*.sh` comprueban **WordPress integrado**. Los
harnesses que ya llaman `php -l` sobre archivos sueltos se conservan; el
gate global los complementa.

`composer test:integration` **no existe aún**. Cuando exista una suite
PHPUnit/WP, se añadirá ese script y nada más.

## CI

Workflow `.github/workflows/test.yml`: `pull_request` y `push` a `main`.
PHP 8.3, `composer install` (respeta `config.platform.php` 8.2.0; sin
`--ignore-platform-reqs`), `composer lint:php`, `composer audit --locked`,
después `composer test:unit`. `actions/cache@v5` (Node 24). Dependabot
abre PRs semanales de Composer y GitHub Actions; **sin auto-merge**. Cada
PR corre este workflow y necesita revisión del propietario. `composer
audit --locked` detecta advisories; Dependabot propone actualizaciones.
Alerts y Security Updates de GitHub: verificar en la UI del repositorio.
Sin environment de producción, sin secretos FTPS, sin deploy. No cierra
D12b. Sin matriz PHP/WP. La integración en CI es el siguiente incremento
(harness o PHPUnit/WP), no este.

## Accesibilidad

Checks automáticos (axe, Playwright) son útiles y **insuficientes**. No
están instalados. Siguen valiendo pruebas manuales de teclado, foco, 320px
y 200% zoom. Inspección estática de CSS no es verificación en navegador.

## Seguridad (alcance)

Caps, nonces de **nuestro** contrato, permisos REST, meta inválida,
invariantes de publicación y guards destructivos merecen tests cuando
toquen ese código.

`composer audit --locked` cubre dependencias Composer de la raíz
(dev/test). CI también audita el lock runtime del plugin (Dompdf).
No escanea WordPress Core, plugins/themes de terceros, npm ni el hosting.
No es un sustituto de revisión de seguridad del producto, no cierra D12b
y no aplica parches. Dependabot (Composer + GitHub Actions, semanal)
abre PRs; no sustituye el audit ni se auto-mergea. Un major de PHPUnit
que suba el baseline de PHP no se acepta por inercia.

## Compatibilidad

| Superficie | PHP | WordPress |
| ---------- | --- | --------- |
| Declarado (plugin/theme) | `>= 7.4` | Requires 6.4; cabecera Tested up to histórica 7.0 |
| Producción (cPanel) | **8.3** | 7.1 (live) |
| Docker / CI tests | **8.3** | **7.1** (integración) |

No se redefine el mínimo 7.4 (runtime canónico ≠ compatibilidad
declarada). PHPUnit 9.6 puede correr en 7.4; CI no lo hace todavía (una
sola línea canónica). `config.platform.php` 8.2.0 obliga a que las
transitivas del lockfile (p. ej. `doctrine/instantiator`) sean
instalables en PHP 8.2; no se genera ni se instala el lock con
`--ignore-platform-reqs`. Producción, Docker y CI corren **8.3**; el
suelo de resolución Composer de raíz sigue **8.2.0**. El `setup-php`
de CI/deploy es **solo** el runner de GitHub Actions; **no** cambia el
PHP 8.3 de cPanel. El `platform: 7.4.0` del plugin solo acota
dependencias runtime. Antes de un deploy WU4: verificar `ext-dom` y
`ext-mbstring` en ese PHP 8.3 existente; no cambiar la versión PHP.

## Expansión futura

Añadir una herramienta (Behat, Brain Monkey, Playwright, suite WP oficial,
matriz de versiones) solo con necesidad arquitectónica demostrada. ADR 0017
work unit 1 ya tiene la política pura; no adelantar `PdfGenerator` ni
librerías PDF hasta la unidad de renderer.

**Versión:** 1.0
**Proyecto:** Revista de Filosofía LOGO ET SPES 0.2.0
