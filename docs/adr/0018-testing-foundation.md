# ADR 0018: Testing Foundation

## Estado

Aceptada

## Fecha

2026-08-20

## Contexto

ADR 0017 acepta la generación automática del PDF de artículo al publicar y
exige una Testing Foundation **antes** de escribir ese código (TDD). El
repositorio ya tenía harnesses Docker + WP-CLI (`tools/qa-*.sh`) y lint
estático; no tenía PHPUnit, Composer, directorio `tests/`, Gherkin ni CI de
pruebas.

Restricciones vigentes: ADR 0006 (minimizar dependencias de **plugins** de
producción), ADR 0009/0016 (FTPS acotado theme+plugin; sin SSH/WP-CLI en
hosting), ADR 0014 (Docker local; el portátil no tiene PHP/Composer nativos),
plugin `Requires PHP: 7.4`, Docker PHP 8.2, producción PHP 8.0.30, WordPress
local 7.1.

## Decisión

1. **PHPUnit 9.6** es el runner de tests PHP. Es la última línea 9.x que
   puede ejecutarse en PHP 7.4–8.2; CI y el portátil (vía Docker) usan **PHP
   8.2**, el runtime canónico de desarrollo.
2. **Composer solo de desarrollo** en la raíz del monorepo (`require-dev`).
   No entra en el plugin ni en el theme. El FTPS (ADR 0009) no sube `vendor/`
   ni `tests/`. El plugin sigue sin autoload Composer (ADR 0006 / 0005).
3. **Tres niveles:** unitario (PHPUnit, sin WordPress ni DB); integración
   WordPress in-process (suite PHPUnit/WordPress con `wp-phpunit` 7.1 en
   Docker aislado: `tests/WordPress/`, `composer test:wp`) más harnesses
   `tools/qa-*.sh` para HTTP / wp-admin / CLI; aceptación (`tools/qa-*.sh`
   para flujos completos). No fingir APIs de WordPress (Brain Monkey /
   WP_Mock). La suite WP no entra en `composer test` ni en CI: requiere
   Docker, igual que los harnesses.
4. **Gherkin** vive en `tests/Features/`. Especifica comportamiento de
   negocio. **Behat no se instala** hasta que haya escenarios suficientes.
5. **TDD** es obligatorio para comportamiento de dominio nuevo (ADR 0017 y
   siguientes). Las reglas operativas están en `docs/23-testing-foundation.md`.
6. **Fuente durable de testing:** este ADR, `docs/23-testing-foundation.md`,
   `docs/24-project-testing-standard.md` y el resumen en `CLAUDE.md`.
   `.cursor/` está gitignored a propósito; reglas Cursor locales, si
   existen, no son autoridad ni requisito.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| PHPUnit 10/11 (solo PHP 8.1+/8.2+) | Impide ejecutar el runner en el mínimo declarado 7.4; 9.6 cubre 7.4 y 8.2. |
| WordPress Core test suite / wp-env / wp-browser **en el primer incremento** (2026-08-20) | Duplicaba el Docker aislado que ya probaba WP 7.1; coste desproporcionado entonces. El incremento 2026-08-28 instaló `wp-phpunit` (no wp-env ni wp-browser) como runner de nivel 2 in-process. |
| Behat ahora | Ceremonial: pocos escenarios; YAGNI. |
| Brain Monkey / WP_Mock / Pest / Mockery | No hacen falta para el seam unitario actual; mocks masivos de WP se evitan. |
| Solo harnesses shell | Insuficiente para TDD de políticas puras (ADR 0017). |
| Composer dentro del plugin | Choca con ADR 0006 y con el empaquetado FTPS. |

## Consecuencias

**Beneficios:** ADR 0017 queda desbloqueado para TDD; CI puede correr units
sin secretos ni producción; el plugin desplegado no cambia.

**Riesgos / costes:** Composer en raíz hay que no copiarlo al plugin;
la suite PHPUnit/WP y los harnesses requieren Docker local y no corren
en el workflow `test.yml`; D12b (auditoría de automatización de
seguridad) **no** se cierra con este ADR.

**Trabajo futuro:** runner Gherkin si el volumen de `.feature` lo
justifica; matriz PHP 7.4/8.x o WordPress en CI solo si aporta; no
añadir Behat, Brain Monkey, wp-env ni Playwright sin necesidad
arquitectónica.

## Estado de implementación (2026-08-20)

Nota factual; no cambia §1–§6. El PHP de primer partido se valida con
`php -l` nativo (`tools/php-lint.sh`, `composer lint:php`). El lockfile de
Composer de la raíz se audita con `composer audit --locked` y debe
instalarse respetando `config.platform.php` 8.2.0 (Docker/CI), sin
`--ignore-platform-reqs`. Dependabot abre PRs semanales de Composer y
GitHub Actions, sin auto-merge. No se añadió PHPStan, Psalm, PHPCS, ni otro
escáner de seguridad. No cierra D12b.

Nota factual 2026-08-21 (no cambia §1–§6): el runtime canónico de
Docker/CI es **PHP 8.3**. `config.platform.php` permanece **8.2.0**.
Producción sigue en **8.0.30**. `Requires PHP: 7.4` no se redefinió.

Nota factual 2026-08-22 (no cambia §1–§6): producción
(`logo-et-spes.cenfiss.net`) también corre **PHP 8.3**. El suelo de
resolución Composer sigue **8.2.0**. `Requires PHP: 7.4` no se
redefinió. PHPUnit 9.6 sin cambio de major.

Nota factual 2026-08-24 (no cambia §1–§6): el oficio de cómo escribir un
test vive en `docs/24-project-testing-standard.md`. Complementa `docs/23`
(taxonomía, CI, comandos). No añade runners ni cambia PHPUnit 9.6, Gherkin
sin Behat, ni la exclusión de Brain Monkey / Mockery / Pest.

Nota factual 2026-08-28 (no cambia §1–§2 ni §4–§5; **§3 queda alineada
con esta nota**): el propietario autorizó instalar la suite
PHPUnit/WordPress para el diseño editorial del PDF (issue #10).
Implementación: `wp-phpunit/wp-phpunit` 7.1 + `yoast/phpunit-polyfills`
en el Composer de raíz, `phpunit-wp.xml.dist`, `tests/WordPress/`,
`tools/run-phpunit-wp.sh` / `composer test:wp`, Compose efímero puerto
8090, tablas `wptests_`, `down -v` al salir. Primer contrato:
`ArticlePdfEditorialSourceBuilderTest`. No entra en `composer test` ni
en CI. No se instaló Behat, Brain Monkey, WP_Mock, Mockery, Pest,
wp-env ni wp-browser. El texto original de §3 («PHPUnit+WP aplazado»)
deja de ser el estado vigente.

Nota factual 2026-08-28 (no cambia §1–§6): SonarQube Cloud está
conectado por GitHub App (**Automatic Analysis**, proyecto
`refo44_demo-revistalogos`). El alcance vive en `.sonarcloud.properties`
(plugin + theme; `tests/` como tests). `sonar-project.properties` se ignora
mientras Automatic Analysis esté ON. No se importa cobertura PHPUnit; el
0.0 % del Quality Gate no es un gate de cobertura. No se añadió
PHPStan, Psalm ni PHPCS. No cierra D12b.

## Referencias

- `docs/23-testing-foundation.md` (reglas operativas)
- `docs/24-project-testing-standard.md` (oficio: sociable-first, BDD, AAA, dobles)
- `.sonarcloud.properties` (alcance Automatic Analysis; no es un gate de cobertura)
- ADR 0006, 0009, 0014, 0016, 0017
