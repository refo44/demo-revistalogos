# Revista de Filosofía LOGO ET SPES — Orden de Implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**  
**Versión 1.0**

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
blog-index.html
archive-issue.html
archive-article.html
single-issue.html
single-article.html
single-post.html
search.html          (por construir)
404.html             (por construir)
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
| `blog-index.html` | `home.php` | `/noticias/` |
| `archive-issue.html` | `archive-issue.php` | `/numeros/` |
| `archive-article.html` | `archive-article.php` | `/articulos/` |
| `single-issue.html` | `single-issue.php` | `/numeros/{slug}/` |
| `single-article.html` | `single-article.php` | `/articulos/{slug}/` |
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

---

## Prioridad de páginas

1. **Home** — Hero, mensaje principal, CTA principal
2. **Número actual** — Portada, tabla de contenidos, PDF
3. **Archivo de números** — Listado de números publicados
4. **Contacto** — Formulario, enlaces sociales
5. **Enviar colaboración** — CTA principal para autores
6. **Normas** — Normas de publicación, PDFs
7. **Acerca** — Enfoque, alcance, objetivos
8. **Comité Editorial** — Autoridades CENFISS
9. **Ética** — Normas de ética
10. **Políticas** — Políticas editoriales
11. **Enlaces** — Enlaces externos
12. **Noticias** — Índice del blog

---

## Regla

La maqueta estática está validada y se convierte en la base del tema WordPress. Proceder al desarrollo del tema y despliegue.

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
- [ ] Primer número cargado en el sistema
- [ ] Primer artículo cargado en el sistema

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
