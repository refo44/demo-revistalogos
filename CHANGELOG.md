# Changelog

Todos los cambios notables de este proyecto se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto se adhiere a [Versionado Semántico](https://semver.org/lang/es/).
La versión vigente vive en `package.json` (fuente de verdad); ver `VERSION.md`.

## [Sin publicar]

### Por hacer
- Resolver el backlog de decisiones en ADR (ver `docs/adr/BACKLOG.md`): queda **D12b** (momento de automatización CI/CD), a decidir tras la auditoría profesional.
- Verificar tras el próximo despliegue que `http://` devuelve 301 y que las cuatro cabeceras nuevas llegan al navegador.
- Confirmar con el proveedor el plazo de conservación de los registros de acceso y completar el marcador `[Por confirmar]` del aviso de privacidad.
- Someter el aviso de privacidad a asesoría legal antes de abrir la indexación.
- Investigar el Programa de Sponsors de Crossref para Venezuela/Latinoamérica y confirmar el coste real de membresía DOI con el volumen del primer número (ADR 0013 §2.1 — puede avanzar ya, sin esperar a la Fase 4).
- Tramitar el ISSN electrónico (e-ISSN) ante la Biblioteca Nacional, en paralelo al DOI y sin depender de él (ADR 0013, ADR 0004).
- Designar quién en CENFISS gestiona las solicitudes de acceso/corrección/baja de datos de autor frente a Crossref, y revisar `page-politicas` §6 y la Solicitud de Publicación/Declaración de Ética con asesoría legal (ADR 0013 §6).
- Bootstrap FSE en Docker (ADR 0015) y corte WordPress en `logo-et-spes.cenfiss.net` (ADR 0016).

## [0.2.0] — 2026-08-18

Fase 3 WordPress en el monorepo, runtime Docker local en WordPress 7.0.4
y alineación de metadatos del theme/plugin.

### Añadido
- Monorepo `static/` + `wordpress/` (ADR 0007).
- Plugin first-party `revistalogos-core` (CPTs, taxonomías, meta, rol, migración, fixtures) y theme clásico `revistalogos`.
- Entorno local Docker (ADR 0014): `wordpress:7.0.4-php8.2-apache`, PHP 8.2, MariaDB 11, WP-CLI.
- Workflow manual FTPS de theme+plugin y espejo GitHub Pages desde `static/`.
- ADR 0010–0016 (contacto, analítica, cabeceras, DOI/ORCID, Docker, FSE, topología cPanel `cenfiss2`).
- `page-privacidad.html` — aviso de privacidad del sitio, **provisional**.
- `.htaccess` — redirección HTTPS y cabeceras reversibles (ADR 0012).
- `docs/22-identificadores-academicos-doi-orcid.md`.
- `screenshot.png` (1200×900) para Apariencia → Temas.

### Corregido
- URLs canónicas del HTML estático a `logo-et-spes.cenfiss.net`.
- Aviso de privacidad: declara GitHub Pages como segundo alojamiento.
- `.htaccess` versionado (el workflow de despliegue ya no era un no-op).
- `placeholder-banner.jpg` era un data URI de SVG con extensión `.jpg`; ahora es un JPEG real (fallback de números sin portada).

### Cambiado
- Core WordPress local 6.8.3 → **7.0.4**; theme y plugin `Tested up to: 7.0`.
- Enlace «Privacidad» del footer al aviso dedicado; `sitemap.xml` incluye `/page-privacidad`.
- Eliminado el prompt maestro de agente `docs/FABLE5-Fase3-WordPress-Master-Prompt-v4.md` (alcance ya cubierto por ADR y `docs/17`).
- README y `docs/13`/`docs/15` alineados al layout `static/` + `wordpress/` (ADR 0007); `docs/17` deja de marcar la Fase 3 como «SIGUIENTE».

### Notas
- El contenido dummy (Vol. 12 Nº 2, ISSN/DOI/ORCID ficticios) no se publica en producción (ADR 0004).
- Single CPT `author` (`/revista/autores/{slug}/`) sigue en 404 por colisión de query var; archivo y REST sí resuelven.

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

[Sin publicar]: https://github.com/refo44/demo-revistalogos/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/refo44/demo-revistalogos/releases/tag/v0.2.0
[0.1.0]: https://github.com/refo44/demo-revistalogos/releases/tag/v0.1.0
