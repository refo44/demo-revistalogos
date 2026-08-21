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
   WordPress (hoy: harnesses Docker aislados; PHPUnit+WP aplazado); aceptación
   (`tools/qa-*.sh`).
4. **Gherkin** vive en `tests/Features/`. Especifica comportamiento de
   negocio. **Behat no se instala** hasta que haya escenarios suficientes.
5. **TDD** es obligatorio para comportamiento de dominio nuevo (ADR 0017 y
   siguientes). Las reglas operativas están en `docs/23-testing-foundation.md`.
6. **Fuente durable de testing:** este ADR, `docs/23-testing-foundation.md` y
   el resumen en `CLAUDE.md`. `.cursor/` está gitignored a propósito; reglas
   Cursor locales, si existen, no son autoridad ni requisito.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| PHPUnit 10/11 (solo PHP 8.1+/8.2+) | Impide ejecutar el runner en el mínimo declarado 7.4; 9.6 cubre 7.4 y 8.2. |
| WordPress Core test suite / wp-env / wp-browser en esta unidad | Duplica el Docker aislado que ya prueba WP 7.1; coste desproporcionado para el primer incremento. |
| Behat ahora | Ceremonial: pocos escenarios; YAGNI. |
| Brain Monkey / WP_Mock / Pest / Mockery | No hacen falta para el seam unitario actual; mocks masivos de WP se evitan. |
| Solo harnesses shell | Insuficiente para TDD de políticas puras (ADR 0017). |
| Composer dentro del plugin | Choca con ADR 0006 y con el empaquetado FTPS. |

## Consecuencias

**Beneficios:** ADR 0017 queda desbloqueado para TDD; CI puede correr units
sin secretos ni producción; el plugin desplegado no cambia.

**Riesgos / costes:** Composer en raíz hay que no copiarlo al plugin;
integración PHPUnit/WP queda como incremento; D12b (auditoría de
automatización de seguridad) **no** se cierra con este ADR.

**Trabajo futuro:** suite PHPUnit de integración WordPress 7.1 en Docker
aislado cuando un contrato WP no quepa en un harness; runner Gherkin si el
volumen de `.feature` lo justifica; matriz PHP 7.4/8.0 solo si aporta.

## Estado de implementación (2026-08-20)

Nota factual; no cambia §1–§6. El PHP de primer partido se valida con
`php -l` nativo (`tools/php-lint.sh`, `composer lint:php`). El lockfile de
Composer de la raíz se audita con `composer audit --locked` y debe
instalarse respetando `config.platform.php` 8.2.0 (Docker/CI), sin
`--ignore-platform-reqs`. Dependabot abre PRs semanales de Composer y
GitHub Actions, sin auto-merge. No se añadió PHPStan, Psalm, PHPCS, ni otro
escáner de seguridad. No cierra D12b.

## Referencias

- `docs/23-testing-foundation.md` (reglas operativas)
- ADR 0006, 0009, 0014, 0016, 0017
