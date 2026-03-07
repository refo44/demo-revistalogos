# Revista de Filosofía LOGO ET SPES — Árbol de URLs

**Estructura oficial de URLs**

Define las rutas canónicas del sitio. Fuente única para "qué URL lleva a dónde".

**Depende de:** `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`  
**Referencia:** `03-wordpress-content-model`, `12-theme-file-structure`

---

## 1. Estructura

### Páginas principales

| Ruta | Contenido |
|------|-----------|
| `/` | Inicio |
| `/acerca/` | Acerca (enfoque, alcance, objetivos) |
| `/contacto/` | Contacto |
| `/normas/` | Normas de Publicación |
| `/etica/` | Ética Editorial |
| `/politicas/` | Políticas |
| `/enviar-colaboracion/` | Enviar colaboración |
| `/comite-editorial/` | Comité Editorial |
| `/enlaces/` | Enlaces de interés |
| `/noticias/` | Noticias (índice del blog) |

### Contenido (CPT) — prefijo /revista/

| Ruta | Contenido |
|------|-----------|
| `/revista/` | Hub Revista (opcional) |
| `/revista/numeros/` | Archivo de números |
| `/revista/numeros/{slug}/` | Número individual (ej. `/revista/numeros/vol-12-n2-2025/`) |
| `/revista/articulos/` | Archivo de artículos |
| `/revista/articulos/{slug}/` | Artículo individual (ej. `/revista/articulos/la-naturaleza-del-ser/`) |
| `/revista/articulos/{año}/{slug}/` | Artículo con año (opcional, para citabilidad: `/revista/articulos/2025/la-naturaleza-del-ser/`) |
| `/revista/autores/` | Archivo de autores |
| `/revista/autores/{slug}/` | Autor individual |
| `/noticias/{slug}/` | Entrada individual (noticia) |

### Archivos filtrados (taxonomías)

| Ruta | Contenido |
|------|-----------|
| `/revista/seccion/{slug}/` | Artículos por sección (ej. `/revista/seccion/metafisica/`) |
| `/revista/tipo/{slug}/` | Artículos por tipo (ej. `/revista/tipo/ensayo/`) |

### Área privada (autenticados)

| Ruta | Contenido |
|------|-----------|
| `/login/` | Login (o `wp-login.php`) |
| `/registro/` | Registro (si está habilitado) |
| `/mi-cuenta/` | Panel de autor |
| `/mi-cuenta/envios/` | Mis envíos |
| `/mi-cuenta/envio/{id}/` | Ver envío |
| `/mi-cuenta/nuevo-envio/` | Nuevo envío |

### Búsqueda

| Ruta | Cuándo |
|------|--------|
| `/buscar/?q={query}` | Resultados de búsqueda (recomendado para UX) |
| `/?s={query}` | Alternativa (por defecto WordPress) |

### DOI (opcional, para Google Scholar)

| Ruta | Contenido |
|------|-----------|
| `/doi/{doi}` | Redirección al artículo (ej. `/doi/10.1234/rles.2025.001`) |

### Estados

| Ruta | Cuándo |
|------|--------|
| `/404/` | Documentación; WordPress usa `404.php` internamente |
| 404 | Página no encontrada |

---

## 2. Reglas

- **URLs limpias:** Sin query strings para contenido principal. Usar solo para búsqueda (`?q=`).
- **Slugs consistentes:** Coincidir exactamente con los slugs de páginas WordPress. Minúsculas, guiones. Sin acentos en slugs (`/etica/`, no `/ética/`).
- **Barra final:** Usar barra final para consistencia (ej. `/acerca/`, `/revista/numeros/`).
- **Prefijo /revista/:** Contenido editorial bajo `/revista/` para SEO académico, citabilidad y evitar conflicto con secciones institucionales.
- **Jerarquía:** Máximo 3–4 niveles. `/revista/numeros/{slug}/`, `/revista/articulos/{slug}/`.
- **Slugs en español:** Slugs de páginas en español (acerca, contacto, normas, enviar-colaboracion, comite-editorial).
- **URLs canónicas:** Definir mediante `rel="canonical"` para evitar duplicación entre variantes de URL.

**Nota WordPress:** El prefijo `/revista/` requiere configuración de rewrite rules o CPT con `has_archive` y `rewrite['slug']` (ej. `issue` → `revista/numeros`).

---

## 3. Mapeo WordPress

| Ruta | Plantilla |
|------|-----------|
| `/` | `front-page.php` |
| `/acerca/` | `page-acerca.php` |
| `/contacto/` | `page-contacto.php` |
| `/normas/` | `page-normas.php` |
| `/etica/` | `page-etica.php` |
| `/politicas/` | `page-politicas.php` |
| `/enviar-colaboracion/` | `page-enviar-colaboracion.php` |
| `/comite-editorial/` | `page-comite-editorial.php` |
| `/enlaces/` | `page-enlaces.php` |
| `/noticias/` | `home.php` (página de entradas) |
| `/revista/numeros/` | `archive-issue.php` |
| `/revista/numeros/{slug}/` | `single-issue.php` |
| `/revista/articulos/` | `archive-article.php` |
| `/revista/articulos/{slug}/` | `single-article.php` |
| `/revista/autores/` | `archive-author.php` |
| `/revista/autores/{slug}/` | `single-author.php` |
| `/revista/seccion/{slug}/` | `taxonomy-section.php` |
| `/revista/tipo/{slug}/` | `taxonomy-article_type.php` |
| `/noticias/{slug}/` | `single.php` |
| `/buscar/` | `page-buscar.php` (recibe `?q=`) o `search.php` |
| `/login/` | `page-login.php` o redirección |
| `/mi-cuenta/` | `page-mi-cuenta.php` |
| 404 | `404.php` |

---

## 4. Mapeo HTML estático a URL (maqueta)

| Archivo estático | URL WordPress |
|------------------|---------------|
| `index.html` | `/` |
| `page-acerca.html` | `/acerca/` |
| `page-contacto.html` | `/contacto/` |
| `page-normas.html` | `/normas/` |
| `page-etica.html` | `/etica/` |
| `page-politicas.html` | `/politicas/` |
| `page-enviar-colaboracion.html` | `/enviar-colaboracion/` |
| `page-comite.html` | `/comite-editorial/` |
| `page-enlaces.html` | `/enlaces/` |
| `noticias.html` | `/noticias/` |
| `archive-issue.html` | `/revista/numeros/` |
| `single-issue.html` | `/revista/numeros/{slug}/` |
| `archive-article.html` | `/revista/articulos/` |
| `single-article.html` | `/revista/articulos/{slug}/` |
| `single-post.html` | `/noticias/{slug}/` |
| `search.html` | `/buscar/?q=` |

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
