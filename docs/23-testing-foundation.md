# Testing Foundation y estrategia de pruebas

Estrategia canónica de pruebas del monorepo. Las decisiones vinculantes
están en [ADR 0018](adr/0018-testing-foundation.md). Este documento no
reescribe ADR 0006, 0009, 0014 ni 0017.

## Propósito

Proteger comportamiento de dominio, contratos e integraciones reales —
sobre todo contenido editorial, relaciones, URLs, importers y guards —
con la pila más pequeña que lo permita. No maximizar recuento ni
cobertura. ADR 0017 (PDF automático) se implementa **después**, con TDD,
usando esta base.

## Autoridad

Fuente durable (Git), en este orden cuando hablen de pruebas:

1. `content-source/` (texto editorial; no política de tests).
2. `docs/` — este documento es la guía operativa de testing.
3. `docs/adr/` — ADR 0018 (decisiones); ADR 0017 (PDF, no implementado).
4. `CLAUDE.md` — resumen para agentes; apunta aquí, no duplica el manual.

**`.cursor/` está gitignored a propósito.** Si un desarrollador mantiene
reglas Cursor locales, son espejos opcionales de conveniencia: no son
fuente de verdad, no se versionan, no se exigen en otros clones ni en CI.

## Taxonomía

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

Composer y PHPUnit viven en la **raíz** del repo (`composer.json`,
`phpunit.xml.dist`). `vendor/` no se versiona ni se despliega.

## PHPUnit y Composer

- Runner: **PHPUnit 9.6** (`phpunit/phpunit`, `require-dev`).
- Composer **solo** para tooling de test. El plugin y el theme siguen con
  `require_once` (ADR 0006). El workflow FTPS no sube `vendor/` ni `tests/`.

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
- Ejemplo futuro (no crear ahora): `tests/Features/article-pdf-generation.feature`.

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
- Suite por defecto **offline** tras tener dependencias e imágenes. No
  producción, GitHub, Crossref, ORCID, CDN ni APIs remotas. Eso, si acaso,
  es smoke manual aparte.

## Comandos canónicos

```bash
# Nivel 1 (CI y local con PHP+Composer)
composer install --no-interaction
composer test
composer test:unit    # alias

# Nivel 1 en el portátil sin PHP (ADR 0014)
./tools/run-phpunit.sh

# Nivel 2/3 — Docker aislado (nunca producción)
./tools/qa-editorial-bootstrap.sh
./tools/qa-article-editorial-ux.sh
./tools/qa-volume1-bootstrap-admin.sh   # ausencia de UI temporal
./tools/qa-author-permalinks.sh         # excepción: volúmenes primarios
```

`composer test:integration` **no existe aún**. Cuando exista una suite
PHPUnit/WP, se añadirá ese script y nada más.

## CI

Workflow `.github/workflows/test.yml`: `pull_request` y `push` a `main`.
PHP 8.2, `composer test:unit`. Sin environment de producción, sin secretos
FTPS, sin deploy. No cierra D12b. Sin matriz PHP/WP. La integración en CI
es el siguiente incremento (harness o PHPUnit/WP), no este.

## Accesibilidad

Checks automáticos (axe, Playwright) son útiles y **insuficientes**. No
están instalados. Siguen valiendo pruebas manuales de teclado, foco, 320px
y 200% zoom. Inspección estática de CSS no es verificación en navegador.

## Seguridad (alcance)

Caps, nonces de **nuestro** contrato, permisos REST, meta inválida,
invariantes de publicación y guards destructivos merecen tests cuando
toquen ese código. Esta foundation no es un escáner de seguridad.

## Compatibilidad

| Superficie | PHP | WordPress |
| ---------- | --- | --------- |
| Declarado (plugin/theme) | `>= 7.4` | Requires 6.4; cabecera Tested up to histórica 7.0 |
| Producción (cPanel) | 8.0.30 | 7.1 (live) |
| Docker / CI tests | **8.2** | **7.1** (integración) |

No se redefine el mínimo 7.4. PHPUnit 9.6 puede correr en 7.4; CI no lo
hace todavía (una sola línea canónica).

## Expansión futura

Añadir una herramienta (Behat, Brain Monkey, Playwright, suite WP oficial,
matriz de versiones) solo con necesidad arquitectónica demostrada. ADR 0017
introduce sus propias costuras por TDD; no adelantar `PdfGenerator` ni
librerías PDF aquí.

**Versión:** 1.0
**Proyecto:** Revista de Filosofía LOGO ET SPES 0.2.0
