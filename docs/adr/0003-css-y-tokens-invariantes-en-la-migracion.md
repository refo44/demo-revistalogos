# ADR 0003: CSS y tokens invariantes en la migración

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

ADR 0002 fija el principio «WordPress adapta, no rediseña». La capa donde ese principio se viola con más facilidad —y de forma silenciosa— es el CSS: al portar a WordPress aparecen tentaciones concretas de reescribir estilos que rompen la paridad con la maqueta.

La maqueta ya trae la arquitectura CSS que `docs/12-theme-file-structure` §7 prescribe para el theme: módulos `tokens → base → layout → components → pages/ → utilities` con un único punto de entrada `main.css`, y tokens de diseño en `assets/css/tokens.css`. El proyecto no tiene paso de build (solo stylelint).

WordPress introduce tres puntos de fricción que exigen decisión:

1. **`theme.json`** — el sistema de diseño del editor de bloques. Si restata los tokens, aparece una **segunda fuente de verdad** que puede divergir de `tokens.css`.
2. **Valores de token** — ¿congelados en absoluto, o se admite corregir un valor que incumple contraste AA?
3. **CSS de WordPress y plugins** — Gutenberg, formularios de comentarios y plugins inyectan hojas propias que pueden pisar los tokens.

## Decisión

**El CSS y los tokens se trasladan a WordPress sin reescribirse.** Son un asset que se **encola**, no código que se porta. Misma estructura de archivos, mismos nombres de clase, mismos valores de token. `assets/css/tokens.css` es la **única fuente de verdad** del diseño.

### 1. `theme.json` mínimo y restrictivo (Opción A)

- `theme.json` contiene **solo** la paleta de marca como conjunto de opciones permitidas, y **desactiva** la personalización libre: `settings.color.custom = false`, `custom-gradient`/`duotone` desactivados, `settings.typography.customFontSize = false`, `settings.spacing.custom = false`.
- Todo lo visual sigue viniendo del `main.css` encolado; `theme.json` **no** restata tipografía, espaciado ni componentes.
- Beneficio doble: cero divergencia con `tokens.css` y aplicación efectiva de ADR 0002 — el editor no puede elegir colores fuera de marca.
- Lo único duplicado es la lista de colores de la paleta (pequeña y estable).

### 2. Valores de token: congelados con carve-out de accesibilidad

- Los valores de token están **congelados** durante la migración.
- **Excepción (igual que ADR 0002):** se permite corregir un valor por **accesibilidad** (p. ej. contraste que incumple WCAG AA) siempre que se **porte también a la maqueta** y se registre en `docs/migracion-static-wordpress.md`. No se congelan defectos conocidos.

### 3. Dequeue selectivo del CSS ajeno

- Se **retiran (dequeue)** las hojas de estilo de WordPress y plugins que **no** se usen, para que `main.css` siga siendo autoritativo y no haya guerras de especificidad ni bloat.
- **Requisito previo:** auditar qué estilos de bloques del núcleo usa realmente el theme antes de retirar `wp-block-library` u otros; no se retira a ciegas.
- El CSS de plugins necesarios (p. ej. formulario de contacto) se re-estiliza con los tokens en lugar de heredar su estética por defecto.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| `theme.json` completo que refleje todos los tokens (Opción B) | Crea dos fuentes de verdad (tokens.css + theme.json) que divergen; el beneficio WYSIWYG es bajo porque los editores trabajan en campos de CPT, no componiendo en Gutenberg. |
| `theme.json` generado desde `tokens.css` por build (Opción C) | Añade tooling de build que el proyecto no tiene (hoy solo stylelint); complejidad no justificada para el alcance actual. |
| Congelación absoluta de valores de token | Obligaría a portar defectos de contraste conocidos; el carve-out los corrige sin abrir el rediseño. |
| Conservar todo el CSS de WordPress/plugins | Hereda overrides, especificidad y estilos fuera de token — justo lo que este ADR evita. |
| Renombrar clases a convención `.wp-block-*` | Forzaría cambios de markup y rompería la paridad con la maqueta. |

## Consecuencias

**Beneficios:**

- Paridad visual exacta con la maqueta; el CSS es autoritativo.
- Una sola fuente de verdad de diseño (`tokens.css`); sin divergencia con `theme.json`.
- El editor de bloques no permite salidas de marca (refuerza ADR 0002).
- Menos bloat y sin sorpresas de especificidad al retirar CSS ajeno innecesario.

**Riesgos / costes:**

- El editor de bloques se ve más plano (sin sistema de diseño completo en `theme.json`). Aceptable: el contenido es estructurado (CPTs), no maquetación libre.
- El dequeue exige una auditoría previa de qué estilos de bloques del núcleo se usan.
- El carve-out de accesibilidad exige disciplina de back-port y registro en el ledger.

**Trabajo futuro:**

- Al scaffolding del theme: encolar `main.css` vía `wp_enqueue_style`; crear `theme.json` mínimo restrictivo; auditar y hacer dequeue selectivo.
- Registrar toda corrección de token (carve-out) en `docs/migracion-static-wordpress.md`.

## Referencias

- ADR 0002 (WordPress como adaptación sin rediseño) — principio del que deriva
- `docs/12-theme-file-structure` §7 (estrategia CSS, un solo `main.css`)
- `docs/14-css-architecture`
- `assets/css/` (tokens, base, layout, components, pages/, utilities, main)
- `docs/migracion-static-wordpress.md` (ledger; registra correcciones del carve-out)
