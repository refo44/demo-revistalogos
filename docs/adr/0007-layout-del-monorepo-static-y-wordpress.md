# ADR 0007: Layout del monorepo — static/ y wordpress/

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

El sitio estático y WordPress conviven en el **mismo repositorio** (decisión del propietario, registrada en el backlog). Hoy (Fase 2) el sitio estático vive en la **raíz** del repo (`index.html`, `*.html`, `assets/`, `.htaccess`, `robots.txt`, `sitemap.xml`) junto a `docs/` y `.github/`, y es lo que se despliega a producción.

Al entrar en la Fase 3 hay que decidir cómo se organizan las dos implementaciones dentro del monorepo. La maqueta original (README) preveía un `theme-revistalogos/` en la raíz, junto a los `*.html`. El proyecto hermano *Camino del Dharma* probó ese layout mezclado y lo **retiró** (su ADR 0011 → 0014) porque confunde implementación y tooling, y mezcla `index.html` con `front-page.php`.

## Decisión

Adoptar un monorepo con **dos implementaciones delimitadas** en carpetas separadas:

```text
revistalogos/
├── static/                              # Referencia estática congelada (ex raíz)
│   ├── index.html, *.html
│   ├── assets/
│   ├── .htaccess, robots.txt, sitemap.xml
│   └── partials/ …
├── wordpress/
│   └── wp-content/
│       ├── themes/revistalogos/         # Presentación (ADR 0002, 0003)
│       └── plugins/revistalogos-core/   # CPTs, taxonomías, campos, roles (ADR 0005)
├── docs/                                # Compartido; no se despliega
├── .github/
└── README.md
```

### Reglas

| Carpeta | Rol |
| ------- | --- |
| **`static/`** | Producción pública **durante** la Fase 3; recibe mantenimiento. Referencia de paridad plantilla a plantilla para el theme. |
| **`wordpress/`** | Theme y plugin propio; staging hasta el corte. **No** incluye el core de WordPress, `wp-config.php`, `wp-content/uploads/` ni plugins de terceros. |
| **`docs/`, `.github/`** | Compartidos; no se despliegan a producción. |

- **Prohibido** mezclar `index.html` y `front-page.php` en el mismo directorio.
- Solo se versionan el **theme** y el **plugin propio**; el detalle de qué se despliega y qué no se fija en el ADR de despliegue (backlog **D8**, fuentes de verdad duales).

### Momento de ejecución

- La reorganización se hace al **inicio de la Fase 3**, como primer paso del scaffolding del theme/plugin, **no ahora** mientras el estático en raíz sigue siendo la producción de Fase 2.
- El movimiento de archivos a `static/` se coordina con el **cambio de rutas del workflow de despliegue** (D8): hoy el workflow copia `./*.html` desde la raíz; tras el movimiento copiará desde `static/`. Se cambia una sola vez.
- Se crea una **etiqueta Git previa a la reorg** como punto de rollback.

### Tras la migración validada

- `static/` **deja de mantenerse** como sitio activo (se archiva en tag o rama; no se despliega).
- El theme + `revistalogos-core` pasan a ser la **única implementación activa**.

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Estático en la raíz + `theme-revistalogos/` al lado (plan original del README) | Mezcla `*.html`, `docs/` y código WP; el proyecto hermano lo retiró por confuso (0011 → 0014). |
| Dos repositorios (estático + WordPress) | Fragmenta el historial y los ADR; contradice el repo compartido acordado. |
| Eliminar `static/` al iniciar Fase 3 | Se pierde la referencia de paridad visual plantilla a plantilla. |
| Reorganizar ahora (Fase 2) | Innecesario: rompería la producción actual sin beneficio hasta que empiece el theme. |

## Consecuencias

**Beneficios:**

- Límites claros: implementación vs tooling; estático vs dinámico.
- `static/` como contrato de aceptación plantilla a plantilla.
- `wordpress/` contiene solo lo versionado del CMS propio (theme + plugin).
- Coherencia con el proyecto hermano del que se adaptan aprendizajes.

**Riesgos / costes:**

- Reorganización única al iniciar Fase 3: actualizar el workflow de despliegue (rutas), README, y referencias de `.htaccess`/`sitemap.xml`; actualizar `docs/13-static-file-structure`.
- Duplicación temporal de assets entre `static/` y el theme durante el desarrollo.

**Trabajo futuro:**

- Ejecutar la reorg como primer paso del scaffolding (coordinada con D8).
- Tag Git pre-reorg (rollback).
- Actualizar `docs/13-static-file-structure` y el árbol del README.
- Sincronizar assets de `static/assets/` al theme durante el desarrollo.

## Referencias

- Análogo: *Camino del Dharma* ADR 0014 (monorepo con `static/`)
- ADR 0002, 0003 (theme = presentación), ADR 0005 (plugin `revistalogos-core`)
- Backlog **D8** (despliegue acotado, fuentes de verdad duales)
- `docs/12-theme-file-structure`, `docs/13-static-file-structure`
