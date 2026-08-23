# Revista de Filosofía LOGO ET SPES — Orden de Implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**  
**Versión 1.3**

**Depende de:** 01–16, 18–20

---

## Fase 1: Documentación, arquitectura y diseño — HECHO

1. **Identidad completa:** Extraer paleta y tipografía del manual de marca → actualizar `02-corporate-identity`
2. **Wireframes:** Estructura de bloques por pantalla según `06-wireframes` (papel, Figma o HTML)
3. **Validar documentación:** Asegurar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** Doc 18 como referencia para decisiones de diseño

---

## Fase 2: Maqueta estática — HECHO

1. **Maqueta responsive** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad; el sitio debe funcionar sin JS para contenido principal)
2. Contenido según: `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`, `02-corporate-identity`
3. Assets de `content-source/` copiados a `assets/`; imágenes optimizadas
4. **Validar** contra checklist de `18-ux-ui-trends` antes de cerrar fase
5. **Validar responsive:** Móvil, tablet, escritorio antes de WordPress

**Alcance de “HECHO”:** La estructura visual, responsive y accesible está maquetada. El Vol. 12 Nº 2, los números históricos, artículos, autores, noticias, identificadores y paginación son datos demostrativos. No están validados como contenido editorial y no deben migrarse a producción como verdad bibliográfica. (Bootstrap Volume 1 Option 2: ver §3.1 nota de implementación.)

### 2.1 Estructura HTML (base para WordPress)

La maqueta usa HTML plano en raíz. Fase WordPress = adaptación directa HTML-a-PHP, sin rediseño. URLs finales según `11-url-tree`.

```
index.html
page-acerca.html
page-contacto.html
page-normas.html
page-etica.html
page-politicas.html
page-enviar-colaboracion.html
page-comite-editorial.html
page-enlaces.html
noticias.html
archive-issue.html
archive-article.html
single-issue.html
single-article.html
single-post.html
search.html
404.html
partials/
assets/
```

### 2.2 Correspondencia WordPress

| HTML estático | WordPress | URL |
|---------------|-----------|-----|
| `index.html` | `front-page.php` | `/` |
| `page-acerca.html` | `page-acerca.php` | `/acerca/` |
| `page-contacto.html` | `page-contacto.php` | `/contacto/` |
| `page-normas.html` | `page-normas.php` | `/normas/` |
| `page-etica.html` | `page-etica.php` | `/etica/` |
| `page-politicas.html` | `page-politicas.php` | `/politicas/` |
| `page-enviar-colaboracion.html` | `page-enviar-colaboracion.php` | `/enviar-colaboracion/` |
| `page-comite-editorial.html` | `page-comite-editorial.php` | `/comite-editorial/` |
| `page-enlaces.html` | `page-enlaces.php` | `/enlaces/` |
| `noticias.html` | `home.php` | `/noticias/` |
| `archive-issue.html` | `archive-issue.php` | `/revista/numeros/` |
| `archive-article.html` | `archive-article.php` | `/revista/articulos/` |
| `single-issue.html` | `single-issue.php` | `/revista/numeros/{slug}/` |
| `single-article.html` | `single-article.php` | `/revista/articulos/{slug}/` |
| `single-post.html` | `single.php` | `/noticias/{slug}/` |
| `search.html` | `search.php` | `/?s={query}` o `/buscar/?q={query}` |
| `404.html` | `404.php` | 404 |

### 2.3 Invariantes de diseño

Durante la migración a WordPress, **no** cambiar:

- Estructura de bloques
- Jerarquía visual
- Copy editorial
- Tokens de identidad
- Arquitectura CSS
- Estructura semántica HTML (header, main, nav, footer, article)

WordPress añade: motor de contenido, panel editorial, roles de usuario y contenido dinámico. No rediseña.

---

## Fase 3: WordPress — EN CURSO (clásico en producción)

Implementación clásica (WU0–WU12) en el repositorio desde **0.2.0**. Corte
in situ **2026-08-19:** WordPress 7.0.4 en `https://logo-et-spes.cenfiss.net`,
theme clásico `revistalogos` y plugin `revistalogos-core` activos. WordPress
clásico live en producción; carga de contenido editorial real iniciada y
actualmente en proceso desde wp-admin (**no** completa). Pendiente: QA de
producción, no importar el dataset demo de fixtures, bootstrap FSE
(ADR 0015; primero en Docker). El bootstrap editorial restringido está
en código y **no** se ha ejecutado en producción. Estado operativo:
`docs/fase3-execution-state.md`. Snapshot:
`docs/operations/produccion-wordpress.md`.

Plan original de esta fase (histórico; no describe el estado actual):

1. **Convertir** maqueta a tema WordPress según `12-theme-file-structure` — hecho en el repo
2. Alinear con `03-wordpress-content-model` y `11-url-tree`; assets en el tema según `15-assets-strategy` — hecho en el repo
3. Implementar CPTs: `issue`, `article`, `author` (submission como CPT privado); `post` para noticias — hecho en el repo
4. **Desplegar:** Staging para validación editorial antes de producción — **no** hubo staging extra (ADR 0016). Corte in situ 2026-08-19. Contenido editorial real en proceso de carga desde wp-admin; **no** completa.

### 3.1 Separación obligatoria durante la migración

| Origen en la maqueta | Destino WordPress | Acción |
|----------------------|-------------------|--------|
| Header, footer, componentes, CSS y JavaScript | Tema | Convertir a plantillas y assets |
| Enfoque, normas, ética, políticas y origen del nombre | Páginas | Cargar desde `content-source/` sin alterar el texto |
| Número, editorial, artículos, autores y noticias | CPTs / posts | Crear únicamente con información editorial real |
| Portada, PDF integral y PDFs de artículos | Media Library | Subir los archivos finales de la primera edición |
| ISSN, depósito legal, DOI, ORCID y fechas | Campos / opciones | Registrar solo valores oficiales |
| Conteos, tabla de contenidos, filtros, archivos y paginación | Lógica WordPress | Generar desde consultas; no copiar valores dummy |

**Prohibido en producción:** Migrar el Vol. 12 Nº 2, los números Vol. 11–12, los seis artículos de ejemplo, las noticias ficticias, `1234-5678`, `10.1234/les.*`, `0000-0000-*`, las paginaciones demostrativas o los canonicals del sitio demo.

*Nota de implementación 2026-08-19 (Option 2; no reescribe la tabla de arriba):* esa prohibición sigue vigente para el dataset demo y para tratar la maqueta como verdad bibliográfica. El bootstrap editorial Volume 1 (`wp revistalogos fixtures bootstrap`) es una excepción de propietario distinta: puede **adaptar** títulos, abstracts, secciones y orden de la maqueta Vol. 12 Nº 2 como placeholders `_les_bootstrap*` de Vol. 1 Nº 1. No importa identificadores falsos, paginación bibliográfica dummy ni autores de la maqueta. El puente temporal Tools → Volume 1 Editorial Bootstrap se usó en producción y **se retiró en plugin 0.2.6**. El dominio y el CLI permanecen.

### 3.2 Carga de la primera edición

Carga editorial iniciada y actualmente en curso desde wp-admin de producción.
**No** está completa. No hay subdominio de staging extra (ADR 0016). Fixtures
dummy **no** importar. Existe un usuario administrador asignado a esta
gestión; su identidad **no** se documenta aquí.

Pasos restantes (la entrada ya empezó; no darlos por cerrados):

1. Crear el `issue` con portada, número, fecha, descripción, PDF integral e identificadores oficiales.
2. Extraer el sumario y crear un `article` por editorial, artículo, ensayo o reseña.
3. Crear y vincular los `author` con afiliación, ORCID y biografía confirmados.
4. Asignar sección, tipo, palabras clave, páginas, fechas y PDF individual a cada artículo.
5. Validar que títulos, orden, paginación y autoría coincidan con el PDF.
6. Revisar descargas, citaciones, metadatos académicos, Schema.org, canonical y accesibilidad.
7. Validar en el sitio live (no hay staging extra). No pisar la carga en curso con fixtures ni con el importador institucional.

---

## Prioridad de páginas

1. **Home** — Hero, mensaje principal, CTA principal
2. **Número actual** — Portada, tabla de contenidos, PDF
3. **Archivo de números** — Listado de números publicados
4. **Contacto** — Formulario, enlaces sociales
5. **Enviar colaboración** — CTA principal para autores
6. **Normas** — Normas de publicación, PDFs
7. **Acerca** — Enfoque, alcance, objetivos
8. **Consejo Editorial** — Autoridades y cargos confirmados
9. **Ética** — Normas de ética
10. **Políticas** — Políticas editoriales
11. **Enlaces** — Enlaces externos
12. **Noticias** — Índice del blog

---

## Regla

La maqueta estática está validada como base visual del tema WordPress. Su contenido demostrativo no está aprobado para publicación y no se migra como verdad editorial. El bootstrap Volume 1 (Option 2, ADR 0004 nota de implementación) solo adapta campos de presentación como placeholders `_les_bootstrap*`. WordPress clásico está live en producción; contenido editorial real en proceso de carga desde wp-admin (**no** completa).

---

## Checklist pre-lanzamiento

- [x] Identidad (paleta, tipografía) definida
- [x] Todas las páginas maquetadas
- [ ] Formulario de contacto funcional (WordPress) — CF7 aún no en producción
- [ ] Enlaces externos verificados
- [ ] Accesibilidad: estándares 19 aplicados (contraste, alt, teclado, foco, formularios)
- [ ] Navegación y breadcrumbs verificados
- [ ] PDFs descargables funcionando
- [ ] URLs canónicas verificadas
- [ ] SEO básico (title, meta description)
- [ ] Favicon cargado
- [ ] Sitemap generado
- [x] Tema WordPress desplegado (clásico `revistalogos` + `revistalogos-core`, 2026-08-19)
- [ ] Contenido editorial real: carga iniciada y actualmente en curso desde wp-admin (no completa)
- [x] Dataset dummy (seed demo) excluido de producción; mantener. Bootstrap editorial restringido: excepción 2026-08-19, **no ejecutado**.
- [ ] Primera edición recibida y validada contra el PDF final
- [ ] Primer número real cargado en el sistema
- [ ] Todos los artículos y autores de la primera edición cargados y vinculados
- [ ] ISSN (impreso ya obtenido; electrónico pendiente), depósito legal, DOI y ORCID confirmados o marcados honestamente como pendientes — mecanismo, costes y flujo en `22-identificadores-academicos-doi-orcid` y ADR 0013
- [ ] Aprobación editorial de la primera edición (no hay staging extra; validar en producción). Abrir indexación es decisión aparte del propietario (launch gate en `docs/operations/produccion-wordpress.md`); el 100 % del contenido no es prerequisito.

---

## Fase 4: Identificadores académicos (DOI y ORCID) — POSTERIOR

Fase separada, **deliberadamente posterior al lanzamiento de WordPress** (decisión del propietario; ver ADR 0013 §Contexto, «Precisión 4»). No bloquea el cierre de la Fase 3 ni el checklist de lanzamiento: el sitio puede salir a producción con los identificadores en «en trámite» (régimen de ADR 0004), y esta fase los completa después. Especificación completa en `docs/22-identificadores-academicos-doi-orcid`.

1. **Implementar en `revistalogos-core`:** validación de formato/checksum de `author.orcid`, campo derivado `orcid_url`, enlace visible y `sameAs` en JSON-LD, marcadores «en trámite» para `doi`/`issn` — `docs/22` §3-§6.
2. **Generador de depósito Crossref:** comando WP-CLI o acción de administración que produce el XML de un número a partir de los CPT `issue`/`article`/`author`, con revisión manual antes de cualquier envío — `docs/22` §7.
3. **Primer depósito real** (con cuenta Crossref activa y sitio en producción con URLs estables): depositar, confirmar, rellenar `doi`/`issn` reales, retirar el marcador «en trámite» de ese registro.
4. **«Sign in with ORCID»** queda fuera de esta fase — se plantea para cuando exista portal de autor (subsistema de envíos, ADR 0005 §4, fase posterior incluso a esta).

### Lo que puede avanzar antes, en paralelo a la Fase 3

El trámite administrativo con Crossref **no depende de que exista el theme** (ADR 0013 §2.1): investigar el Programa de Sponsors, confirmar el coste real, dar de alta la cuenta, designar quién gestiona solicitudes de datos personales frente a Crossref, tramitar el ISSN electrónico ante la Biblioteca Nacional, y revisar `page-politicas` §6 con asesoría legal. Checklist completo en `docs/22` §9.

---

## Posterior: Testing Foundation y PDF automático de artículo

No forma parte del cierre de Fase 3 ni de Fase 4. **Hoy** el PDF de artículo se sube a mano y publicar **no** lo exige.

**Testing Foundation: IMPLEMENTADA** (ADR 0018, `docs/23-testing-foundation.md`, PHPUnit 9.6, `composer test:unit`). ADR 0017 permanece **aceptado**. WU1–WU4 están en `revistalogos-core` (política, adaptador de solo lectura, orquestación, renderer Dompdf). **No** hay creación de adjuntos, **no** hay orquestación de publicación en WordPress. Publicar **sigue** sin exigir PDF. El PDF integral del número sigue siendo carga editorial manual. Ver ADR 0017.

---

**Versión:** 1.3
**Proyecto:** Revista de Filosofía LOGO ET SPES 0.2.0
