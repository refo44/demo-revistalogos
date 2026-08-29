# Estándar de pruebas del proyecto

Oficio para escribir tests en Revista de Filosofía LOGO ET SPES (PHP /
WordPress clásico). Complementa [23-testing-foundation](23-testing-foundation.md)
(taxonomía, CI, comandos) y no reescribe [ADR 0018](adr/0018-testing-foundation.md).

**Aplica a:** `tests/Unit/**/*Test.php`, `tests/Support/**/*.php`,
`tests/WordPress/**/*Test.php`, `phpunit-wp.xml.dist`,
`tests/Features/**/*.feature`, `tools/qa-*.sh`, `tools/run-phpunit-wp.sh`

**`.cursor/` está gitignored a propósito.** Si un desarrollador mantiene
reglas Cursor locales, son espejos opcionales: no son fuente de verdad, no
se versionan, no se exigen en otros clones ni en CI.

---

## Referencia rápida

| Pregunta | Respuesta por defecto |
|----------|------------------------|
| ¿Qué tipo de test primero? | **Sociable** — colaboradores internos reales; doblar solo sistemas externos |
| ¿Qué asertar? | **Resultados observables** por métodos públicos, HTTP o comportamiento wp-admin |
| ¿Qué evitar? | `expects()` / `method()` / orden de llamadas sobre colaboradores **internos** |
| ¿Cómo estructurar? | Plano, autocontenido, nombre de comportamiento + cuerpo AAA, sin estado mutable compartido |
| ¿Cuándo arrancar WordPress? | Contrato WP in-process → `tests/WordPress/` (`composer test:wp`). HTTP / wp-admin / CLI → `tools/qa-*.sh` aislado. Nunca en `tests/Unit/` |
| ¿Cuándo vale un test solitario? | Lógica pura con ramificación compleja donde el aislamiento aclara |
| ¿Puerta de calidad? | **Conductual** + **insensible a la estructura** |
| ¿Cómo correr la suite? | `composer test` (lint + audit + units); `composer test:wp` si cambió un contrato WP in-process; **un** `tools/qa-*.sh` si cambió HTTP/admin/CLI |

---

## 0. Filosofía

**Principio:** los tests deben **acoplarse al comportamiento** y
**desacoplarse de la estructura**.

Este proyecto exige:

- Pruebas sociables por defecto (colaboradores internos reales)
- Semántica BDD (Gherkin en español; nombres PHPUnit que enuncian el escenario)
- Diseño error-first en dominio nuevo (TDD: RED → GREEN → REFACTOR)
- Conciencia de frontera (WordPress, filesystem, HTTP, renderer, reloj)

Los tests solitarios solo cuando estén justificados.

### Desiderata

Ningún test necesita todas las propiedades. **No ceder una propiedad sin
recibir otra de mayor valor.**

**Par primario (no negociable en sociables y solitarios):**

- **Conductual** — si cambia el comportamiento, debe cambiar el resultado del test
- **Insensible a la estructura** — si solo cambia la estructura, el resultado no debe cambiar

**Propiedades de apoyo:**

- **Aislado** / **componible** — mismo resultado con cualquier orden o subconjunto
- **Determinista** — mismas entradas, mismos resultados
- **Rápido** — milisegundos en nivel 1; no arrancar WordPress/Docker salvo que el objetivo lo exija
- **Legible** — nombres y variables de negocio explican por qué existe el test
- **Específico** — al fallar, el escenario y la causa deben ser obvios
- **Automatizado** — nivel 1 en CI; `composer test:wp` y harnesses de nivel 2/3 deterministas y seguros en local (Docker; no en el gate por defecto)
- **Escribible** — el coste debe ser proporcionado; preferir cableado sociable a coreografía de mocks

**Diferidas a tipos concretos:**

- **Inspirador** — pasar debe dar confianza; los sociables lo logran con colaboradores internos reales
- **Predictivo** — todo verde debe aproximar aptitud de producción; QA Docker aislado, `composer test:wp` y contratos en fronteras WordPress

Validar comportamiento observable: reglas de negocio, errores, transiciones
de estado, corrección de frontera, guards de publicación/capacidad/nonce
cuando ese código cambia.

No fragmentar el comportamiento de negocio en micro-unitarios granulares.

Un test debe proteger al menos uno de: invariante de dominio; comportamiento
observable; contrato de integración; permiso/seguridad; integridad de datos;
fallo realista; regresión ocurrida o plausible.

---

## 1. Modelo de frontera del SUT (obligatorio)

Definir el sujeto bajo prueba (SUT) y clasificar colaboradores:

- Internos → reales por defecto (política de dominio, orquestador, mappers,
  helpers del theme sin WordPress)
- Externos → dobles (motor PDF, persistencia de adjuntos, HTTP, filesystem,
  cola, reloj, azar, entorno)

La frontera del SUT decide el tipo de test.

No colapsar capas arquitectónicas en el test por conveniencia.

El plugin posee el dominio; el theme, solo la presentación (ADR 0005). Un
unitario puede incluir un helper del theme libre de WordPress
(`revistalogos_split_name`). No registrar CPT/taxonomías/roles desde el
theme, ni generar PDF de artículo en el theme (ADR 0017).

No fingir APIs de WordPress cuando el objeto de la prueba **es** el
comportamiento de WordPress. Eso es nivel 2: suite PHPUnit/WordPress
(`tests/WordPress/`, `WP_UnitTestCase`) o un harness `tools/qa-*.sh`,
ambos en Docker aislado contra WordPress 7.1 real. No Brain Monkey /
WP_Mock.

### 1.1 Observación de frontera cambiada (upgrades de dependencia o adaptador)

Si el cambio actualiza una librería o un adaptador (Dompdf, API de
adjuntos, cliente HTTP), las pruebas de aceptación y regresión deben
**observar esa frontera**.

Exigido:

- Conservar colaboradores internos reales que poseen el comportamiento
  (política de publicación, orquestador, constructor de HTML fuente)
- Doblar solo el sistema externo, o ejecutar la librería real in-process
  cuando ese es el punto (`ArticlePdfDompdfRendererTest`)
- Asertar resultados observables: decisión de publicación, bytes PDF
  (`%PDF-`), asociación adjunto/`pdf_file`, HTTP, aviso wp-admin
- Hacer deterministas los fallos con dobles controlados — nunca un servicio
  externo realmente caído
- Asertar el mapeo de fallo (bloquear publicación, seguir sin publicar,
  conservar el contenido), no afirmaciones vagas de resiliencia

Prohibido como evidencia de aceptación del upgrade:

- Suites que mockean la librería actualizada o al colaborador que la usa
- Tests de negocio adyacentes que nunca ejercitan la frontera cambiada
- «Lint/suite en verde» como sustituto de observar la frontera
- Aserciones de orden interno de llamadas

Ejemplo de falsa confianza: un unitario que sustituye
`Dompdf_Article_Pdf_Renderer` por un doble de grabación no acepta un
upgrade de Dompdf, porque Dompdf no se ejecuta.
`ArticlePdfDompdfRendererTest` más `tools/qa-article-pdf-renderer.sh` sí.

---

## 2. Selección de tipo de test (orden estricto)

Correspondencia con los tres niveles de `docs/23-testing-foundation.md`:

| Este estándar | Nivel | Dónde vive |
|---------------|-------|------------|
| Programador sociable / solitario | Nivel 1 | `tests/Unit/`, PHPUnit, sin WordPress |
| Integración estrecha | Nivel 2 | `tests/WordPress/` (wp-phpunit, `composer test:wp`) |
| Contrato / aceptación | Nivel 3 | Gherkin en `tests/Features/` + `tools/qa-*.sh` |
| Integración amplia | Nivel 2/3 | Harnesses Docker (`tools/qa-*.sh`) para HTTP / wp-admin / CLI; nunca producción |

`tests/WordPress/` y `composer test:wp` **existen** desde 2026-08-28
(autorización del propietario; ADR 0018 nota factual). Es el runner de
nivel 2 in-process: framework `wp-phpunit/wp-phpunit` 7.1,
`WP_UnitTestCase`, factories de posts, meta y taxonomías, tablas
`wptests_`, Compose efímero (`tools/run-phpunit-wp.sh`). No existe
`tests/Integration/` ni `composer test:integration` — no inventar un
segundo bootstrap en paralelo.

**Cuándo wp-phpunit y cuándo un harness**

- **`tests/WordPress/` (por defecto para contratos WP in-process):**
  mapeo post/meta/taxonomía → HTML o DTO; registro de CPT/taxonomía/rol;
  adaptador o builder que llama APIs de WP; `render_callback` de un
  bloque de dominio con posts de factory; un guard de publicación
  invocable como PHP.
- **`tools/qa-*.sh`:** petición HTTP, pantalla wp-admin, Media Library
  picker, ajuste de opciones en el admin, comando WP-CLI del plugin tal
  como lo corre un editor, flujo de varios pasos (bootstrap, teardown,
  permalinks).
- **No** reescribir un harness que ya cubre HTTP/admin a PHPUnit por
  uniformidad. **No** usar un harness para evitar wp-phpunit cuando el
  contrato es mapeo in-process.

No instalar Behat, Brain Monkey, WP_Mock, Mockery, Pest ni Playwright
sin necesidad arquitectónica (ADR 0018). `wp-phpunit` no es una de esas
exclusiones: es el mecanismo de nivel 2.

### Sociable vs solitario

- **Sociable:** el SUT con **colaboradores internos reales** cableados.
- **Solitario:** el SUT aislado sustituyendo colaboradores por dobles.

Por defecto, sociable. El solitario cambia resiliencia al refactor por
aislamiento: usarlo solo si la frontera o la ramificación lo exigen.

Doblar solo lo **lento, frágil o no determinista** (DB, FS, HTTP, colas,
caché, entorno, reloj, azar, motor PDF cuando el SUT es orquestación y no
render). No doblar colaboradores internos de dominio para «unit test» de
delegación u orden de llamadas.

### Orden de prioridad

1. **Pruebas sociables** (por defecto, nivel 1)  
   Colaboradores internos reales. Doblar solo sistemas externos.

   Construcción:
   - Preferir **construcción manual** del grafo del SUT en el test (o un helper puro)
   - Cablear clases internas reales; no sustituirlas por mocks
   - Invocar **solo métodos públicos**
   - Asertar **resultados**, no cómo se llamó a colaboradores internos
   - No definir `ABSPATH` e incluir WordPress Core; el bootstrap unitario
     puede definir un `ABSPATH` dummy solo para cargar archivos que lo
     comprueban

   Anti-patrones (sensibles a la estructura, bajo valor):
   - `$this->expects($this->once())->method('…')` sobre colaboradores internos
   - Tests que se rompen si se renombra un método interno equivalente y el
     comportamiento no cambia

2. **Integración estrecha** (nivel 2)  
   Cableado, mapping, adaptadores, configuración y transformación de
   entrada **en WordPress real**, in-process.  
   Vive en `tests/WordPress/` y se corre con `composer test:wp` /
   `./tools/run-phpunit-wp.sh`. Ejemplo vigente:
   `ArticlePdfEditorialSourceBuilderTest`.  
   No llamar sistemas fuera de WordPress (host de producción, Crossref,
   ORCID, CDN).  
   `docker compose -p <proyecto-efímero>` pertenece aquí (el runner WP
   ya lo hace), no como arranque de cada unitario sociable.  
   Los flujos HTTP/admin/CLI no son este tipo: van al harness
   (`tools/qa-*.sh`). Excepción conocida: `tools/qa-author-permalinks.sh`
   usa volúmenes primarios; no copiar ese patrón.

3. **Pruebas solitarias** (restringidas, nivel 1)  
   Solo lógica pura con ramificación compleja donde el aislamiento aclara.  
   Restricciones:
   - No arrancar WordPress ni Docker
   - No llamar `$wpdb`, `WP_Query`, REST ni APIs de media
   - Solo clases o funciones planas
   - Sustituir colaboradores externos por dobles
   - Doblar solo contratos públicos; dobles mínimos y estables
   - Seguir asertando resultados observables; no coreografía interna

   Ejemplos justificados hoy: `Article_Pdf_Publication_Policy`,
   `revistalogos_split_name`.

4. **Tests de contrato**  
   Forma y semántica de la frontera.  
   Gherkin enuncia el contrato de negocio en español. PHPUnit nivel 1,
   `composer test:wp` o un harness lo ejecuta.  
   No asertar detalles internos. No poner selectores CSS, nombres de clase,
   IDs de BD ni `sleep` en `.feature`.

5. **Integración amplia** (nivel 3)  
   Flujo editorial completo o infraestructura WordPress real.  
   Determinista, aislada, segura en local. Nunca producción. Nunca importar
   `fixtures seed` como verdad editorial (ADR 0004).

### Intercambios esperados

Elegir el tipo a propósito:

- **Sociable / solitario (programador)** — escribible, rápido, específico,
  conductual, insensible a la estructura. Puede ceder predictivo e
  inspirador pleno; el sociable recupera inspiración con cableado real.
- **Contrato (aceptación)** — semántica de frontera y corrección
  conductual. Puede ceder velocidad y especificidad puntual.
- **Integración amplia** — confianza predictiva e inspiradora en WordPress
  real. Puede ceder velocidad y especificidad del fallo.

Al revisar un test: ¿qué propiedades tiene? ¿cuáles le faltan? ¿es ese el
intercambio buscado?

### Ejemplo sociable

Cablear colaboradores internos reales y asertar resultados. Doblar solo la
frontera externa (renderer, persistencia, HTTP).

```php
public function test_missing_pdf_invokes_renderer_and_returns_artifact() {
	// Arrange
	$renderer         = new Recording_Article_Pdf_Renderer();
	$renderer->output = "%PDF-generated\n";
	$orchestrator     = new Article_Pdf_Generation_Orchestrator( $renderer );

	// Act
	$publication_outcome = $orchestrator->orchestrate(
		Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
		'article-source-42'
	);

	// Assert
	$this->assertSame( "%PDF-generated\n", $publication_outcome['artifact'] );
	$this->assertSame(
		Article_Pdf_Publication_Policy::PUBLICATION_ALLOWED,
		$publication_outcome['publication_decision']
	);
}
```

No doblar `Article_Pdf_Publication_Policy` aquí: es colaborador interno. El
test sigue válido si el orquestador delega en otro helper interno con el
mismo keep/generate/block.

Observar un **doble de grabación en la costura externa** (si el renderer se
invocó) está permitido cuando *esa* es la regla de dominio («no debe
empezar la generación»). No añadir `expects()` sobre el orquestador o la
política.

---

## 3. Estructura BDD (obligatoria)

Cada test sigue semántica BDD:

- **Dado**: contexto del sistema
- **Cuando**: disparador o acción
- **Entonces**: resultado observable, incluidos fallos

**Gherkin** (`tests/Features/`, español): el título más `Dado` / `Cuando` /
`Entonces` debe permitir reconstruir el escenario. Un feature, un idioma.
Behat no está instalado; ejecutan PHPUnit y los harnesses.

**PHPUnit:** el nombre del método debe enunciar el comportamiento
observable. Convención vigente:

```text
test_<observable_behavior>
```

Ejemplo: `test_last_token_is_the_surname_used_in_citation_formats`

No nombrar tests por el método de producción (`test_decide_pdf_action`).
No numerar (`test_1`). No exigir las palabras Given/When/Then en el nombre
PHP.

El docblock del método puede llevar el relato Dado-Cuando-Entonces si el
nombre quedaría demasiado largo.

Al introducir dominio nuevo, los escenarios de fallo deben existir antes o
junto a los caminos felices (TDD). Tests que describen implementación en
vez de comportamiento no son válidos.

---

## 4. Patrón AAA (obligatorio en el cuerpo)

Arrange, Act, Assert estructura el código del test.

BDD narra el escenario. AAA estructura el cuerpo.

- Dado, Cuando, Entonces pueden aparecer en Gherkin, el nombre o el docblock.
- Arrange, Act, Assert deben estructurar el cuerpo.
- Los nombres describen comportamiento observable, no implementación.

Comentarios Arrange / Act / Assert están permitidos y se animan cuando el
cuerpo supera unas pocas líneas. Tests cortos pueden omitir las etiquetas
si las tres fases siguen siendo evidentes.

### Comentarios e higiene

- Nada trivial, redundante o irrelevante.
- No reiterar comportamiento obvio, sintaxis PHP ni el handbook de WordPress.
- Comentarios solo para intención, bordes no obvios o significado de dominio.
- AAA está permitido y se anima.
- Cada método de test conserva un docblock de *qué comportamiento protege*,
  no una paráfrasis del nombre.

---

## 5. Estructura (sin estado mutable compartido)

No usar `setUp()` ni `setUpBeforeClass()` para asignar el SUT u otras
propiedades mutables.  
Cada método declara sus colaboradores.

`setUp()` / `tearDown()` solo para limpieza, reset de dobles o (en
harnesses) ciclo de vida del servidor.  
`setUpBeforeClass()` / `tearDownAfterClass()` solo para configuración
inmutable.

Los unitarios actuales construyen `$policy`, `$orchestrator` y `$renderer`
dentro de cada método. Conservarlo.

### Estructura plana (obligatoria)

- Una subclase de `PHPUnit\Framework\TestCase` por archivo, nombrada por el
  clúster de comportamiento (`ArticlePdfPublicationPolicyTest`).
- No anidar clases TestCase ni inventar suites describe/it.
- Cada método autocontenido e independiente.
- Sin `protected $orchestrator` mutado entre métodos.
- Sin setup de suite más allá de `setUpBeforeClass()` inmutable.
- `@dataProvider` está permitido si es puro, devuelve arrays frescos y cada
  caso es un escenario nombrado completo — no un escondite de estado
  mutable.

**Prohibido:**

- Propiedades mutables asignadas en `setUp()` para el SUT
- Dependencia del orden de ejecución
- Fixtures ocultos en la BD o `/tmp`
- Depender de contenido editorial ya presente en el volumen WordPress
  primario del desarrollador

Beneficios: independencia real, sin estado compartido, lectura más simple,
sin anidación, sin orden implícito.

---

## 6. Arranque WordPress / Docker (estricto)

Arrancar WordPress solo cuando el objetivo exige cableado del framework
(integración, REST, meta, caps, Media Library, wp-admin).

Los sociables de negocio deben preferir **construcción manual** del SUT y
colaboradores internos reales. Evitar Docker y WordPress si el escenario
cabe en clases planas.

No arrancar WordPress dentro de `tests/Unit/`.

Nivel 2 in-process: `tests/WordPress/` **solo** vía
`./tools/run-phpunit-wp.sh` / `composer test:wp`. Ese runner levanta un
Compose efímero (`revistalogos-wp-phpunit`, puerto 8090), instala tablas
`wptests_` y hace `down -v` al salir. No apuntar `phpunit-wp.xml.dist`
al volumen primario (`localhost:8080`) ni a producción.

- Proyecto compose aislado + `down -v` si el harness o la suite WP muta
  datos.
- No reutilizar los volúmenes primarios (`localhost:8080`) en tests nuevos
  que mutan.
- No apuntar harnesses a producción (`logo-et-spes.cenfiss.net`).
- No ejecutar `wp revistalogos fixtures seed` en producción.
- Identificadores dummy (`1234-5678`, `10.1234/les.*`, `0000-0000-*`) no
  son verdad editorial (ADR 0004).

Los solitarios no arrancan WordPress.

---

## 7. HTTP y wp-admin

Las comprobaciones HTTP y de UX editorial viven en `tools/qa-*.sh` (curl,
WP-CLI `eval`, sitio aislado).

- Cada harness construye su propia URL (`http://localhost:<puerto-aislado>`).
- Payloads en línea o vía WP-CLI; no depender de contenido residual.
- No mezclar una llamada PHPUnit in-process de nivel 1 y HTTP en vivo en el
  mismo test — no hay WordPress en nivel 1. Los tests de
  `tests/WordPress/` son in-process contra wp-phpunit; no hagan `curl`
  al sitio primario.

No instalar Supertest, Pest ni un cliente HTTP PHP para imitar NestJS.

---

## 8. Datos y helpers

Preferir duplicación pequeña a abstracciones que ocultan comportamiento.

Los helpers deben ser puros, sin estado y devolver objetos frescos.

Evitar factories globales, fixtures mutables compartidos y estado oculto.

Factories y helpers compartidos al inicio del archivo (o en
`tests/Support/`) solo si son puros, sin estado y devuelven valores
frescos. En `tests/WordPress/`, `self::factory()->post->create()` y
helpers de instancia que crean posts/meta **por test** están permitidos;
no reutilizar IDs entre métodos.

No deben:

- Arrancar WordPress ni capturar un compose en marcha
- Guardar estado mutable
- Reutilizar o mutar objetos compartidos

Un doble de grabación que implementa una interfaz pública (véase §12)
puede tener contadores por instancia. Construir una instancia **nueva** en
cada test.

Toda la preparación permanece en el test o en hooks inmutables permitidos.

---

## 9. Calidad y determinismo (estricto)

Cada test aserta comportamiento observable significativo y falla solo por
defectos reales.

El par **conductual / insensible a la estructura** es la puerta de
calidad: el test demuestra que el comportamiento cambió (o no) — no que el
código se reorganizó.

No:

- Reiterar PHP o el handbook de WordPress Core
- Probar internos del framework
- Duplicar casos idénticos
- Probar funciones triviales sin ramificación
- Asertar métodos privados
- Asertar orden o recuento de llamadas en colaboradores internos
- Asertar caminos internos de cómputo
- Espiar o mockear métodos internos o colaboradores internos del SUT
- Escribir tests que fallan en refactors que preservan comportamiento
- Asertar constantes contra sí mismas, getters triviales, whitespace de
  markup, orden incidental de arrays o porcentaje de cobertura
- Tomar snapshots enormes de HTML; en HTML, semántica, no layout

Los tests deben ser:

- Seguros en paralelo (aislados) — harnesses con nombre de proyecto y
  puerto distintos
- Agnósticos al orden (componibles)
- Deterministas

Evitar:

- `sleep` arbitrario (sondeos acotados de «listo» en harnesses son
  excepción operativa, no patrón PHPUnit)
- Orden por reloj de pared
- Condiciones de carrera
- Azar sin semilla
- Dependencia de la hora actual
- Red real en la suite por defecto (`composer audit` es la excepción
  documentada; no está garantizado offline)

Si el tiempo afecta el comportamiento, pasar un timestamp explícito o un
doble de reloj. No usar «dos posts creados en el mismo segundo» como orden.

### 9.1 Ejecución de la suite (obligatorio)

Esto no es un monorepo Jest. No importar una regla de heap de 8 GB. No
agotar la máquina apilando QA Docker.

| Regla | Requisito |
|-------|-----------|
| **Nivel 1 (gate por defecto)** | `composer test` o `./tools/run-phpunit.sh` — lint, audit del lockfile, suite unitaria completa. La suite es pequeña; correrla entera. |
| **Nivel 2 in-process** | `composer test:wp` / `./tools/run-phpunit-wp.sh` cuando cambió un contrato WordPress que se ejercita en PHP (meta, CPT, builder, adaptador, `render_callback`). Requiere Docker. No entra en `composer test` ni en CI. |
| **Nivel 2/3 HTTP** | El `tools/qa-*.sh` **relevante** cuando cambió HTTP, wp-admin o CLI. Un harness a la vez. Cada uno debe hacer `down -v` y salir. |
| **Prohibido** | BD de producción; volúmenes primarios en tests nuevos que mutan; todos los harnesses «por si acaso» en un cambio solo unitario; usar un harness para evitar wp-phpunit en un mapeo in-process; instalar runners extra por teatro de cobertura |
| **CI** | `.github/workflows/test.yml` es lint + audit Composer (raíz y plugin) + `composer test:unit`. No corre `composer test:wp` ni `qa-*.sh`. No exigir Docker QA en un PR solo de docs o solo unitario. SonarQube Cloud es Automatic Analysis (`.sonarcloud.properties`); no es un gate de cobertura ni un paso de `test.yml`. |

Portátil sin PHP nativo: `./tools/php-lint.sh` y `./tools/run-phpunit.sh`
(ADR 0014). `./tools/run-phpunit-wp.sh` requiere Docker (el portátil no
necesita PHP nativo; sí el daemon).

---

## 10. Nombres (contexto de negocio obligatorio)

Seguir el nombrado WordPress PHP de `includes/`, `tests/Unit/` y
`tests/WordPress/`:

- Clases de test: `PascalCase` + sufijo `Test` (`ArticlePdfPublicationPolicyTest`)
- Métodos: `test_` + `snake_case` de comportamiento
- Clases de producción: `Class_Name`; funciones/variables: `snake_case`

Nombres descriptivos, reveladores de intención, legibles sin comentarios.

Prohibidos identificadores crípticos: `$sut`, `$srv`, `$cfg`, `$resp`, `$usr`.

Nombres de una letra solo en contadores de bucle muy pequeños.

- Variables del SUT: nombres de dominio (`$publication_policy`,
  `$generation_orchestrator`, `$dompdf_renderer`)
- Nombres cortos inequívocos en un test diminuto son aceptables (`$policy`
  dentro de `ArticlePdfPublicationPolicyTest`)
- Dobles: terminan en rol (`$recording_renderer`, `$failing_renderer`,
  `$clock_stub`)
- Clientes HTTP/harness: descriptivos (`$base_url`, `$admin_user`)
- Payloads: significado de dominio (`$spanish_article_html`,
  `$article_without_pdf`)
- Valores de retorno: rol (`$publication_outcome`, `$pdf_artifact`,
  `$name_parts`)
- Booleanos: empiezan por `is_`, `has_`, `should_` (`$has_valid_pdf`)
- Helpers: orientados a acción (`make_recording_renderer`)
- Factories: `make_x` (`make_spanish_article_html`)

Evitar `$data`, `$input`, `$result`, `$response`, `$mock_data` si existe un
nombre de dominio. Preferir `$publication_outcome` a `$result`.

Nombres consistentes para el mismo concepto de dominio; no filtrar detalles
de implementación.

---

## 11. Invocación de métodos de dominio

Cuando el objeto bajo prueba es una **política, orquestador o acción de
aplicación**, evitar patrones genéricos que ocultan la intención de
negocio.

Este proyecto no usa use cases NestJS con `execute()`. Los métodos públicos
ya nombran la acción (`decide_pdf_action`, `orchestrate`, `render`,
`revistalogos_split_name`). Conservarlo.

**Permitido:** método público específico sobre una variable que nombra el
objeto de dominio:

```php
$publication_outcome = $generation_orchestrator->orchestrate(
	Article_Pdf_Publication_Policy::GENERATE_REQUIRED,
	$article_source
);
```

**Prohibido** cuando variable y método son ambos genéricos o engañosos:

```php
$outcome = $sut->execute( $input );
$outcome = $use_case->run( $input );
$outcome = $handler->handle( $input );
```

Reglas:

- No introducir un wrapper genérico `execute()` en tests ni en producción
  para imitar NestJS.
- La intención de negocio debe inferirse del nombre de la variable **y**
  del método público.
- El nombre del test debe reflejar esa misma intención.

---

## 12. Dobles de prueba (contratos públicos manuscritos)

PHPUnit es el único runner. **No añadir** `jest-mock-extended`, Mockery,
Prophecy, Brain Monkey ni WP_Mock.

Preferir **objetos reales**. Cuando haya que doblar un colaborador externo,
preferir una **clase pequeña que implementa la interfaz pública** a
`$this->createMock()`.

Reglas:

- Doblar **solo colaboradores externos** (renderer, persistencia, HTTP,
  filesystem, reloj, env)
- **No** doblar colaboradores internos en tests sociables (políticas,
  orquestadores, mappers, validadores cableados dentro del SUT)
- No mockear la clase bajo prueba
- No mockear value objects
- No mockear cada función de WordPress; si el comportamiento es WordPress,
  usar `tests/WordPress/` (wp-phpunit) o un harness `tools/qa-*.sh`
- No extender una clase de producción solo para anular un método, salvo
  que esa clase sea la costura documentada y aún no exista interfaz

### Preferido: doble manuscrito

```php
class Recording_Article_Pdf_Renderer implements Article_Pdf_Renderer {
	public $invocations = 0;
	public $last_source;
	public $output = "%PDF-fake\n";

	public function render( $source ) {
		$this->invocations++;
		$this->last_source = $source;
		return $this->output;
	}
}
```

Patrón ya usado en `ArticlePdfGenerationOrchestratorTest`. Tipado al
contrato público, obvio, e insensible a refactors internos del orquestador.

### Mocks nativos PHPUnit (restringidos)

`$this->createMock()` / `getMockBuilder()` solo cuando un doble manuscrito
sería más grande que el test **y** el tipo doblado es frontera externa.
Aun así, devolver valores; no escribir `expects($this->once())->method(…)`
salvo que la llamada misma sea la regla de dominio observable en esa
costura.

### Evitar

- Mockear `Article_Pdf_Publication_Policy` en un test del orquestador
- Literales de objeto que no implementan la interfaz
- Mocks parciales del SUT
- Mocks de funciones WordPress (`\Brain\Monkey\Functions\when('get_post_meta')`)

**Prohibido:**

- Doblar colaboradores internos cuando el sociable debe cablear la instancia real
- Mocks sobrespecificados de coreografía interna
- Instalar una librería de mocking para «facilitar» los dobles

Los dobles deben ser mínimos, limitados a sistemas externos y construidos
de nuevo en cada test.

### Razón

- Encaja con ADR 0018 (sin Brain Monkey / Mockery / Pest)
- Evita que el mock se convierta en una segunda implementación
- Mantiene tests insensibles a la estructura
- Una clase de grabación de diez líneas es más barata y clara que un DSL
  de mock para un método

---

## 13. Qué no testear

No testear:

- Métodos privados
- Orden interno de llamadas
- Comportamiento de WordPress Core / handbook de plugins
- Internos de ORM / `$wpdb`
- One-liners triviales
- Comportamiento puro del lenguaje PHP
- Layout CSS y whitespace
- Porcentaje de cobertura (el 0.0 % del Quality Gate de SonarQube Cloud
  bajo Automatic Analysis no es un fallo de la suite; `docs/23`)
- Validación de identificadores de Fase 4 (DOI/ORCID/ISSN) — campos inertes
  (ADR 0013)
- Activar la exigencia de PDF al publicar como efecto de un deploy
  (ADR 0017; el default sigue OFF)

Solo comportamiento observable.

---

## Comandos canónicos

Los comandos, el alcance de CI y la lista de harnesses están en
[23-testing-foundation](23-testing-foundation.md). Resumen:

```bash
composer test              # lint → audit --locked → units; no test:wp ni qa-*.sh
composer test:unit
composer test:wp           # PHPUnit/WordPress aislado (Docker); no CI
./tools/php-lint.sh
./tools/run-phpunit.sh
./tools/run-phpunit-wp.sh
```

`php -l` comprueba **sintaxis**. `composer audit --locked` comprueba
**advisories del lockfile**. PHPUnit nivel 1 comprueba **comportamiento
puro**. `composer test:wp` comprueba **contratos WordPress in-process**.
`qa-*.sh` comprueba **flujos HTTP / wp-admin / CLI**.

---

**Versión:** 1.2
**Proyecto:** Revista de Filosofía LOGO ET SPES
