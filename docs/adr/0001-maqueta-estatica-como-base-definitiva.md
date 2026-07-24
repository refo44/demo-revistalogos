# ADR 0001: Maqueta estática como base definitiva

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

El proyecto se construyó en dos fases previas a WordPress: documentación/diseño (Fase 1) y una **maqueta estática HTML/CSS/JS** (Fase 2), ambas marcadas como HECHO en `docs/17-implementation-order`. La maqueta no es un borrador desechable: se diseñó desde el inicio para mapear 1:1 a la *template hierarchy* y a los Custom Post Types de WordPress (nombres de archivo `archive-issue.html`, `single-article.html`, `page-acerca.html`, etc.), con arquitectura CSS modular y marcadores `<!-- WP:… -->` en las zonas que luego serán loops y template tags.

Al entrar en la Fase 3 (WordPress) existe el riesgo de tratar la maqueta como una referencia informal y rehacer estructura, jerarquía visual o copy «de paso». Eso invalidaría el trabajo validado de las Fases 1–2 y abriría la puerta a divergencias entre lo aprobado y lo implementado.

Se necesita fijar el rol de la maqueta antes de empezar a portar plantillas a PHP.

## Decisión

**La maqueta estática es la base visual y estructural definitiva del sitio.** El tema de WordPress es una adaptación de esa maqueta, no un rediseño (ver ADR 0002).

- La maqueta es el **contrato de aceptación**: cada plantilla PHP debe reproducir su equivalente estático plantilla a plantilla (estructura de bloques, jerarquía, semántica HTML, copy editorial permanente).
- Su estructura semántica (`header`, `nav`, `main`, `article`, `footer`), su arquitectura CSS y sus tokens son la referencia de paridad. Los invariantes concretos se fijan en ADR 0002 (sin rediseño) y ADR 0003 (CSS y tokens).
- El **contenido demostrativo** de la maqueta (Vol. 12 Nº 2, números históricos, artículos, autores, noticias e identificadores de ejemplo) es material de andamiaje: valida la estructura visual, **no** está aprobado como contenido editorial y no se migra a producción (ver ADR 0004).
- Alcance de «definitiva»: se refiere a **estructura, diseño y copy permanente**, no al dataset de ejemplo.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Tratar la maqueta como referencia informal y rediseñar en WordPress | Descarta el trabajo validado de Fases 1–2; reintroduce decisiones de diseño ya cerradas; sin contrato de paridad no hay criterio de aceptación. |
| Rehacer la maqueta con un page builder (Elementor u otro) | Contradice `docs/12-theme-file-structure` §9 (sin dependencia de builders); rompe la arquitectura CSS y la semántica acordadas. |
| Congelar también el dataset de ejemplo como definitivo | El contenido de ejemplo no está validado editorialmente; publicarlo sería fabricar contenido académico (ver ADR 0004). |

## Consecuencias

**Beneficios:**

- Criterio de aceptación claro para la migración: paridad plantilla a plantilla contra la maqueta.
- El trabajo de Fases 1–2 se preserva como activo, no se repite.
- Base común para los ADR 0002 y 0003, que derivan de este.

**Riesgos:**

- Rigidez ante mejoras de diseño surgidas durante la Fase 3: deben canalizarse actualizando primero la maqueta (y esta decisión), no improvisando en el theme.

**Trabajo futuro:**

- Al reorganizar el repo para la Fase 3, la maqueta pasa a ser la referencia congelada de paridad (layout a definir en ADR 0006).
- Mantener la maqueta y el theme sincronizados durante la transición mediante `docs/migracion-static-wordpress.md`.

## Referencias

- `docs/17-implementation-order` §2, §2.3 (invariantes de diseño)
- `docs/README` (objetivo: mapeo 1:1 a template hierarchy y CPTs)
- ADR 0002 (WordPress como adaptación sin rediseño)
- ADR 0003 (CSS y tokens invariantes)
- ADR 0004 (datos dummy excluidos de producción)
