# Revista de Filosofía LOGO ET SPES — Orden de Implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**  
**Versión 1.1**

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

**Alcance de “HECHO”:** La estructura visual, responsive y accesible está maquetada. El Vol. 12 Nº 2, los números históricos, artículos, autores, noticias, identificadores y paginación son datos demostrativos. No están validados como contenido editorial y no deben migrarse a producción.

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

## Fase 3: WordPress — SIGUIENTE

1. **Convertir** maqueta a tema WordPress según `12-theme-file-structure`
2. Alinear con `03-wordpress-content-model` y `11-url-tree`; assets en el tema según `15-assets-strategy`
3. Implementar CPTs: `issue`, `article`, `author` (submission como CPT privado); `post` para noticias
4. **Desplegar:** Staging para validación editorial antes de producción; configurar contenido y hosting

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

### 3.2 Carga de la primera edición

Cuando el equipo editorial entregue el PDF final:

1. Crear el `issue` con portada, número, fecha, descripción, PDF integral e identificadores oficiales.
2. Extraer el sumario y crear un `article` por editorial, artículo, ensayo o reseña.
3. Crear y vincular los `author` con afiliación, ORCID y biografía confirmados.
4. Asignar sección, tipo, palabras clave, páginas, fechas y PDF individual a cada artículo.
5. Validar que títulos, orden, paginación y autoría coincidan con el PDF.
6. Revisar descargas, citaciones, metadatos académicos, Schema.org, canonical y accesibilidad.
7. Publicar primero en staging y obtener aprobación editorial antes de producción.

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

La maqueta estática está validada como base visual del tema WordPress. Su contenido demostrativo no está aprobado para publicación. Proceder al desarrollo del tema; la carga editorial real queda condicionada a la recepción y validación de la primera edición.

---

## Checklist pre-lanzamiento

- [x] Identidad (paleta, tipografía) definida
- [x] Todas las páginas maquetadas
- [ ] Formulario de contacto funcional (WordPress)
- [ ] Enlaces externos verificados
- [ ] Accesibilidad: estándares 19 aplicados (contraste, alt, teclado, foco, formularios)
- [ ] Navegación y breadcrumbs verificados
- [ ] PDFs descargables funcionando
- [ ] URLs canónicas verificadas
- [ ] SEO básico (title, meta description)
- [ ] Favicon cargado
- [ ] Sitemap generado
- [ ] Tema WordPress desplegado
- [ ] Contenido migrado / configurado
- [ ] Dataset dummy excluido de producción
- [ ] Primera edición recibida y validada contra el PDF final
- [ ] Primer número real cargado en el sistema
- [ ] Todos los artículos y autores de la primera edición cargados y vinculados
- [ ] ISSN, depósito legal, DOI y ORCID confirmados o marcados honestamente como pendientes
- [ ] Aprobación editorial completada en staging

---

**Versión:** 1.1
**Proyecto:** Revista de Filosofía LOGO ET SPES
