# Changelog

Todos los cambios notables de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto se adhiere a [Versionado Semántico](https://semver.org/lang/es/).
La versión vigente vive en `package.json` (fuente de verdad); ver `VERSION.md`.

## [Sin publicar]

### Añadido
- ADR 0011 — Analítica y privacidad (Aceptada): sin analítica en la v1, GA4 aplazado a fase posterior con asesoría legal, cero cookies y cero terceros, sin banner de consentimiento.
- `page-privacidad.html` — Aviso de privacidad del sitio, **provisional** y pendiente de validación legal.

### Cambiado
- El enlace «Privacidad» del footer apunta ahora al nuevo aviso (antes iba a `page-politicas.html#politica-privacidad`, que trata la confidencialidad editorial).
- `page-politicas.html` §6 remite al nuevo aviso para el tratamiento de datos de visitantes.
- `sitemap.xml` incluye `/page-privacidad`.

### Por hacer
- Resolver el backlog de decisiones en ADR (ver `docs/adr/BACKLOG.md`): queda **D12** (cabeceras de seguridad y momento de automatización CI/CD).
- Confirmar con el proveedor el plazo de conservación de los registros de acceso y completar el marcador `[Por confirmar]` del aviso de privacidad.
- Someter el aviso de privacidad a asesoría legal antes de abrir la indexación.

## [0.1.0] — 2026-07-23

Primera versión etiquetada. Línea base del prototipo estático «WP-ready»,
con la infraestructura de gobierno del proyecto en su sitio.

### Añadido
- Maqueta estática multipágina (HTML/CSS/JS) mapeada 1:1 a la *template hierarchy* de WordPress.
- Marco de ADR en `docs/adr/` (README, plantilla, backlog de decisiones D1–D12).
- ADR 0001 — Maqueta estática como base definitiva (Aceptada).
- ADR 0002 — WordPress como adaptación sin rediseño (Aceptada).
- `sitemap.xml` para el dominio `logo-et-spes.cenfiss.net`.
- `CHANGELOG.md` y `VERSION.md` (higiene de versiones).

### Eliminado
- `deploy.sh` obsoleto (hacía `git push` y referenciaba GitHub Pages y una ruta `prototype/` inexistente). El despliegue al host se dispara manualmente desde GitHub Actions.

### Notas
- El contenido editorial de la maqueta es demostrativo y **no** se publica en producción (ver `docs/17-implementation-order` §3.1).
- `robots.txt` permanece en `Disallow: /` mientras el sitio es prototipo.

[Sin publicar]: https://github.com/refo44/demo-revistalogos/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/refo44/demo-revistalogos/releases/tag/v0.1.0
