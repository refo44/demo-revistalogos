# Revista de Filosofía LOGO ET SPES — Orden de Implementación

**Secuencia acordada para llevar el sitio a la web.** **No saltar etapas.**  
**Versión 1.0**

**Depende de:** Todos los docs previos (02–16, 18–20)

---

## Fase 1: Documentación y diseño — HECHO

1. **Identidad completa:** Extraer paleta y tipografía del manual de marca → actualizar `02-corporate-identity`
2. **Wireframes:** Estructura de bloques por pantalla según `06-wireframes` (papel, Figma o HTML)
3. **Validar documentación:** Asegurar que todos los docs estén alineados
4. **Consultar tendencias UX/UI:** `18-ux-ui-trends` como filtro para decisiones de diseño

---

## Fase 2: Maqueta estática — HECHO

1. **Maqueta responsive** con:
   - HTML5 semántico
   - CSS3 (tokens de identidad, roles semánticos)
   - JS mínimo con `defer` (navegación, formularios, accesibilidad)
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
page-comite.html
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
| `page-comite.html` | `page-comite.php` | `/comite/` |
| `page-enlaces.html` | `page-enlaces.php` | `/enlaces/` |
| `blog-index.html` | `home.php` | `/noticias/` |
| `archive-issue.html` | `archive-issue.php` | `/numeros/` |
| `archive-article.html` | `archive-article.php` | `/articulos/` |
| `single-issue.html` | `single-issue.php` | `/numeros/{slug}/` |
| `single-article.html` | `single-article.php` | `/articulos/{slug}/` |
| `single-post.html` | `single.php` | `/noticias/{slug}/` |
| `search.html` | `search.php` | `/?s={query}` |
| `404.html` | `404.php` | 404 |

### 2.3 Invariantes de diseño

Durante la migración a WordPress, **no** cambiar:

- Estructura de bloques
- Jerarquía visual
- Copy editorial
- Tokens de identidad
- Arquitectura CSS

WordPress añade: motor de contenido, admin, contenido dinámico. No rediseña.

---

## Fase 3: WordPress — SIGUIENTE

1. **Convertir** maqueta a tema WordPress según `12-theme-file-structure`
2. Alinear con `03-wordpress-content-model` y `11-url-tree`; assets en el tema según `15-assets-strategy`
3. Implementar CPTs: `issue`, `article` (y `post` para noticias)
4. **Desplegar:** Staging (opcional) y producción; configurar contenido y hosting

---

## Prioridad de páginas

1. **Home** — Hero, mensaje principal, CTA principal
2. **Contacto** — Formulario, enlaces sociales
3. **Enviar colaboración** — CTA principal para autores
4. **Normas** — Normas de publicación, PDFs
5. **Acerca** — Enfoque, alcance, objetivos
6. **Comité Editorial** — Consejo editorial
7. **Ética** — Normas de ética
8. **Políticas** — Políticas editoriales
9. **Enlaces** — Enlaces externos
10. **Noticias** — Índice del blog

---

## Regla

La maqueta estática está validada. Proceder al desarrollo del tema WordPress y despliegue.

---

## Checklist pre-lanzamiento

- [x] Identidad (paleta, tipografía) definida
- [x] Todas las páginas maquetadas
- [ ] Formulario de contacto funcional (WordPress)
- [ ] Enlaces externos verificados
- [ ] Accesibilidad: estándares 19 aplicados (contraste, alt, teclado, foco, formularios)
- [ ] Tema WordPress desplegado
- [ ] Contenido migrado / configurado

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
