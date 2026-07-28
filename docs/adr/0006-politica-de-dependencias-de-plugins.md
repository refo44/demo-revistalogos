# ADR 0006: Política de dependencias de plugins de terceros

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

ADR 0005 resolvió el modelo de contenido con código propio (plugin `revistalogos-core`) en lugar de un plugin de campos de terceros. Esa decisión responde a un criterio más general que conviene fijar por separado, porque gobernará varias decisiones futuras del backlog (formulario de contacto D9, analítica/privacidad D10, seguridad/HSTS D12, y cualquier necesidad de caché, backups o SEO).

El riesgo a gestionar: los plugins de terceros poco usados o abandonados quedan **desactualizados**, dejan de ser compatibles con versiones nuevas de WordPress y **rompen el sitio** o abren huecos de seguridad. Para una revista académica que debe permanecer estable y citable durante años, y sin presupuesto para soporte de pago (ADR 0005), cada dependencia es un pasivo de mantenimiento.

## Decisión

**Minimizar las dependencias de plugins de terceros. Preferir WordPress nativo o código propio; cuando un plugin sea realmente necesario, adoptar solo los gratuitos, muy usados y activamente mantenidos.**

### Orden de preferencia

1. **Nativo de WordPress** — si el core lo resuelve, no se añade plugin.
2. **Código propio** en `revistalogos-core` (o un plugin propio adicional) — para lógica específica de la revista.
3. **Plugin de terceros** — solo si construirlo a mano es desproporcionado y se cumplen los criterios de abajo.

### Criterios para aceptar un plugin de terceros

Todos, no algunos:

- **Gratuito** — sin funciones esenciales tras muro de pago (ADR 0005).
- **Muy usado** — base de instalaciones activas grande (señal de soporte y escrutinio).
- **Activamente mantenido** — actualizaciones recientes y compatibilidad declarada con la versión actual de WordPress.
- **Necesario** — sin alternativa nativa o propia razonable.
- **Acotado** — hace una cosa; se evita el plugin «todo en uno» que arrastra funciones no usadas.

### Reglas

- Cada plugin de terceros que se adopte se **justifica en el ADR de la decisión** que lo introduce (p. ej. D9 para el formulario de contacto), citando esta política.
- Se evita el solapamiento: no dos plugins para lo mismo.
- Se prefiere **pocos plugins bien elegidos** a muchos plugins pequeños.
- Los plugins de pago quedan descartados por defecto; si una necesidad solo la cubre uno de pago, se **aplaza y se construye propio** en una fase posterior (coherente con ADR 0005).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Resolver todo con plugins (el enfoque WordPress «habitual») | Multiplica dependencias, superficie de actualización y riesgo de abandono; contradice el objetivo de estabilidad a largo plazo. |
| Prohibir todo plugin de terceros | Rígido en exceso; reconstruir a mano funciones estándar bien resueltas (p. ej. un formulario robusto) sería desproporcionado. |
| Permitir cualquier plugin gratuito | «Gratuito» no basta: los plugins poco usados o sin mantenimiento son el riesgo principal; por eso se exige además muy usado y mantenido. |

## Consecuencias

**Beneficios:**

- Menor superficie de mantenimiento, actualizaciones y vulnerabilidades.
- Estabilidad a largo plazo acorde con una publicación académica citable.
- Independencia del ciclo de vida de proyectos de terceros pequeños.

**Riesgos / costes:**

- Más código propio que mantener (aceptado en ADR 0005).
- Exige criterio caso a caso; la decisión se documenta en cada ADR que introduzca un plugin.

**Trabajo futuro:**

- Aplicar esta política en D9 (formulario de contacto), D10 (analítica/privacidad) y D12 (seguridad).
- Mantener en el repositorio una lista breve de los plugins de terceros adoptados y por qué (p. ej. en `CONTRIBUTING.md` o doc de operaciones), para el despliegue (ver D8, fuentes de verdad duales).

## Referencias

- ADR 0005 (código propio para el modelo de contenido; sin plugins de pago)
- Backlog D9, D10, D12 (decisiones que aplicarán esta política)
- `docs/12-theme-file-structure` §9 (sin dependencia de builders)
