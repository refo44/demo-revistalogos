# ADR 0002: WordPress como adaptación sin rediseño

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

ADR 0001 fija la maqueta estática como base estructural y visual definitiva. Queda por delimitar qué puede y qué no puede cambiar WordPress al portar cada plantilla a PHP durante la Fase 3.

El riesgo concreto es el *scope creep*: al convertir `single-article.html` en `single-article.php`, la tentación de «mejorar de paso» el layout, cambiar un componente o reordenar el header convierte una migración mecánica en un rediseño. Eso invalida el contrato de paridad de ADR 0001, alarga la Fase 3 de forma indefinida y hace divergir la maqueta del theme.

`docs/17-implementation-order` §2.3 ya afirma los invariantes de diseño; este ADR los hace vinculantes y resuelve la pregunta abierta: ¿la regla «sin rediseño» es absoluta, o admite correcciones surgidas durante el porte?

## Decisión

**La migración a WordPress es una adaptación, no un rediseño.** WordPress añade motor de contenido, panel editorial, roles de usuario y contenido dinámico; **no** modifica el diseño.

### 1. Prohibido rediseñar durante la migración

Durante la Fase 3 **no** se permite rediseñar. Concretamente, no se cambian:

- Estructura de bloques y jerarquía visual.
- Copy editorial permanente.
- Tokens de identidad y arquitectura CSS (detalle en ADR 0003).
- Estructura semántica HTML (`header`, `nav`, `main`, `article`, `footer`).
- Estructura de navegación.

### 2. La maqueta estática es el origen de todo cambio

Ningún cambio de diseño, estructura o copy se introduce directamente en el theme. **Si algo debe cambiar, se modifica primero en el sitio estático** y luego se porta al theme. El theme refleja la maqueta; nunca la adelanta. La maqueta es la fuente canónica durante toda la migración.

### 3. Las mejoras van después, no antes

Las mejoras de diseño o funcionalidad quedan **aplazadas hasta que la migración a WordPress esté terminada**. Una vez completada y validada, el equipo puede proponer mejoras por el cauce normal (actualizando la maqueta o, ya en régimen WordPress, según se decida). Antes o durante la migración: no.

### 4. Carve-out: correcciones semánticas / a11y / SEO

Se permiten correcciones de **semántica, accesibilidad y SEO técnico** (p. ej. un `aria-label` ausente, un orden de encabezados incorrecto, un `alt` faltante, metadatos), **siempre que:**

- **no** alteren el diseño visual ni la estructura de bloques; y
- se **porten también a la maqueta estática**, para que maqueta y theme no diverjan.

Toda corrección de este tipo se registra en `docs/migracion-static-wordpress.md` (ledger de migración). Esto mantiene la guardia estricta sin congelar defectos conocidos.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Congelación absoluta (cero cambios, ni siquiera correcciones) | Obligaría a portar a WordPress bugs conocidos de a11y/SEO; el carve-out §4 los corrige sin abrir la puerta al rediseño. |
| Permitir mejoras de diseño «pequeñas» durante el porte | Es exactamente el *scope creep* que este ADR previene; «pequeño» no es un criterio verificable. |
| Introducir cambios directamente en el theme y sincronizar la maqueta después | Invierte la fuente de verdad; la maqueta dejaría de ser el contrato de aceptación de ADR 0001 y las dos implementaciones divergirían. |

## Consecuencias

**Beneficios:**

- Migración mecánica y acotada: criterio de aceptación = paridad con la maqueta.
- Fuente de verdad única durante la transición (la maqueta), sin divergencia maqueta/theme.
- Los defectos de a11y/SEO se corrigen en ambos lados en lugar de perpetuarse.

**Riesgos:**

- Disciplina requerida: cualquier idea de mejora surgida en Fase 3 debe anotarse y aplazarse, no implementarse.
- El carve-out exige criterio para distinguir «corrección» (permitida) de «rediseño» (prohibida); la prueba es si toca diseño visual o estructura de bloques.

**Trabajo futuro:**

- Registrar cada corrección §4 en el ledger de migración (tarea #5 del backlog: `docs/migracion-static-wordpress.md`).
- Al cerrar la migración, abrir un cauce explícito para las mejoras aplazadas.

## Referencias

- ADR 0001 (maqueta estática como base definitiva)
- ADR 0003 (CSS y tokens invariantes) — detalle técnico del punto §1
- `docs/17-implementation-order` §2.3 (invariantes de diseño)
- `docs/migracion-static-wordpress.md` (ledger; registra las correcciones del carve-out)
