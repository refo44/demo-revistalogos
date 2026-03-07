# Logos et Spes - Prototipo HTML/CSS/JS "WP-ready"

Prototipo estático multi-página para la revista académica «Logos et Spes» (CENFISS), compatible con una futura migración a un theme de WordPress.

Este repositorio contiene:

- **Documentación del proyecto** (`docs/`)
- **Maqueta HTML** del sitio
- **Contenido fuente editorial** (`content-source/`)
- **Tema WordPress** (cuando se implemente, en `theme-revistalogos/`)

## 🎯 Objetivo

Este prototipo mapea 1:1 a la template hierarchy y Custom Post Types (CPT) de WordPress para issues (números) y articles (artículos), y páginas informativas.

## 📁 Estructura del Proyecto

```
/prototype/
├── front-page.html                 → Home (mapea a front-page.php)
├── page-acerca.html               → Acerca (page-acerca.php)
├── archive-issue.html             → Archivo de números (archive-les_issue.php)
├── single-issue.html             → Detalle de número (single-les_issue.php)
├── archive-article.html           → Listado de artículos (archive-les_article.php)
├── single-article.html            → Ficha de artículo (single-les_article.php)
├── author.html                    → Ficha de autor (author.php)
├── taxonomy-section.html          → Taxonomía "section" (taxonomy-section.php)
├── search.html                    → Resultados de búsqueda (search.php)
├── 404.html                       → Error 404 (404.php)
├── noticias.html                → Noticias/Noticias (home.php)
├── single-post.html               → Entrada de blog (single.php)
├── /partials/                     → Simulan template parts de WP
│   ├── header.html                → <header> + nav principal
│   ├── footer.html                → <footer>
│   ├── breadcrumbs.html           → Migas accesibles
│   ├── article-card.html          → Tarjeta de artículo
│   ├── issue-card.html            → Tarjeta de número
│   ├── metadata-box.html          → Caja de metadatos académicos
│   ├── toc.html                   → Tabla de contenidos de un número
│   ├── pagination.html            → Paginación accesible
│   └── hero-issue.html            → Hero del número vigente
└── /assets/
    ├── /css/
    │   ├── tokens.css             → Design tokens (colores/espacios/tipo)
    │   ├── base.css               → Reset ligero y tipografía
    │   ├── layout.css             → Layout, grid, header/nav/footer
    │   ├── components.css         → Cards, hero, toc, breadcrumbs, buttons, metadata
    │   ├── pages.css              → Ajustes por plantilla
    │   ├── utilities.css          → Helpers (visually-hidden, flows, gaps)
    │   └── main.css               → @import del resto (para futura enqueue en WP)
    ├── /js/
    │   └── main.js                → Solo: menú móvil, skip-link fix, acordeones opc.
    ├── /img/
    │   ├── logo-cenfiss.svg
    │   ├── logo-revista.svg
    │   └── portada-ejemplo.jpg
    └── /pdf/
        ├── numero-v12n2-2025.pdf
        └── articulo-01.pdf
```

## 🚀 Cómo usar

1. **Abrir en navegador**: Simplemente abre `front-page.html` en cualquier navegador moderno
2. **Navegación**: Todas las páginas están interconectadas con enlaces relativos
3. **Sin dependencias**: No requiere servidor web ni herramientas de build

## 🎨 Características de Diseño

### Estilo Visual
- **Paleta sobria**: Aguamarina institucional derivada del documento fuente y grises neutros
- **Tipografía**: Sistema de fuentes (Georgia para títulos, Arial para texto)
- **Layout**: Grid responsivo sin frameworks
- **Accesibilidad**: Contraste AA, foco visible, skip links

### Componentes UI
- ✅ Hero del número vigente con CTA de descarga PDF
- ✅ Tabla de contenidos agrupada por sección con anclas locales
- ✅ Tarjetas de artículos/números con metadatos visibles (DOI, autores)
- ✅ Caja de metadatos académicos con definición clara (DL/DT/DD)
- ✅ Paginación accesible con roles y etiquetas
- ✅ Buscador con campo y resultados simulados
- ✅ Estado vacío y página 404

## 🔧 Marcadores WP-placeholders

El HTML incluye comentarios que marcan las zonas que luego serán loops/funciones de WP:

```html
<!-- WP:LOOP_ISSUES_START --> … <!-- WP:LOOP_ISSUES_END -->
<!-- WP:LOOP_ARTICLES_START --> … <!-- WP:LOOP_ARTICLES_END -->
<!-- WP:THE_TITLE -->, <!-- WP:THE_CONTENT -->, <!-- WP:THE_EXCERPT -->
<!-- WP:THE_POST_THUMBNAIL -->, <!-- WP:THE_DATE -->, <!-- WP:THE_AUTHOR -->
<!-- WP:ARCHIVE_LINK -->, <!-- WP:SINGLE_PDF_URL -->
<!-- WP:NAV_MENU(primary) --> (indicando ubicación de menú)
<!-- WP:BREADCRUMBS --> (luego reemplazable por plugin o función)
```

## 📊 Modelos de Datos (pensados para WP)

### CPT "les_issue" (Números)
- volumen, número, año, ISSN, Depósito Legal
- portada (img), editorial breve, PDF completo
- DOI del número (opcional)

### CPT "les_article" (Artículos)
- título ES/EN, autores (Person array), afiliación, ORCID, DOI
- páginas, sección (taxonomy section), palabras clave (taxonomy keyword)
- resúmenes ES/EN, PDF

### Taxonomías
- **section**: Metafísica, Ética, Epistemología, Filosofía de la Religión
- **keyword**: palabras clave

## 🔍 SEO & Datos Estructurados

### Meta Tags
- Cada HTML con `<title>` único y `<meta name="description">`
- Open Graph y Twitter Cards
- Enlaces `rel="canonical"`

### JSON-LD
- **front-page.html**: Periodical + Organization (CENFISS)
- **single-issue.html**: PublicationIssue con volumen/número/año
- **single-article.html**: ScholarlyArticle con DOI, autores con ORCID, afiliación

## ♿ Accesibilidad

- **WCAG AA**: Contraste suficiente, foco visible, skip links
- **Semántica**: HTML5 semántico con roles ARIA apropiados
- **Navegación**: Menú accesible con aria-expanded, breadcrumbs
- **Formularios**: Labels asociados, estados de error claros
- **Modo oscuro**: Soporte con `@media (prefers-color-scheme: dark)`

## 📱 Responsive Design

- **Mobile-first**: Diseño adaptativo sin frameworks
- **Breakpoints**: 640px, 768px, 1024px, 1280px
- **Grid**: CSS Grid con fallbacks para navegadores antiguos
- **Tipografía**: Escala fluida con clamp()

## 🎯 Criterios de Aceptación Cumplidos

- ✅ Todas las páginas existen y son navegables desde el menú y migas
- ✅ single-issue.html y single-article.html incluyen botones de descarga PDF con aria-label
- ✅ Tabla de contenidos del número agrupada por sección y con anclas
- ✅ Ficha de artículo con títulos ES/EN, autores/afiliación/ORCID, DOI, palabras clave, páginas, cita sugerida y JSON-LD
- ✅ HTML5 válido, contraste AA, foco visible
- ✅ CSS/JS listos para futura enqueue en WP (main.css, main.js)
- ✅ Comentarios WP-placeholders en todas las zonas que luego serán loop/template tags

## 🔄 Migración a WordPress

Para migrar este prototipo a WordPress:

1. **Templates**: Renombrar archivos HTML a .php según template hierarchy
2. **Loops**: Reemplazar comentarios WP-placeholders con funciones de WP
3. **Partials**: Convertir partials/ a template parts con `get_template_part()`
4. **Assets**: Enqueue CSS/JS con `wp_enqueue_style()` y `wp_enqueue_script()`
5. **CPTs**: Crear Custom Post Types para issues y articles
6. **Campos**: Implementar campos personalizados con ACF o campos nativos
7. **Taxonomías**: Registrar taxonomías para sections y keywords

## 📄 Licencia

Licenciamiento del repositorio:

- **Código** (HTML, CSS, JS, scripts y configuración): **MIT**. Ver `LICENSE`.
- **Contenido editorial y de publicación**: **Creative Commons Atribución 4.0 Internacional (CC BY 4.0)**. Ver `LICENSE-CONTENT`.

Los materiales de terceros conservan su propia licencia o atribución cuando corresponda.

---

**Desarrollado para CENFISS - Centro de Estudios Filosóficos y Sociales**
