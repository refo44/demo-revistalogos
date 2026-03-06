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
| `/` | Home |
| `/acerca/` | Acerca (enfoque, alcance, objetivos) |
| `/contacto/` | Contacto |
| `/normas/` | Normas de Publicación |
| `/etica/` | Ética Editorial |
| `/politicas/` | Políticas |
| `/enviar-colaboracion/` | Enviar colaboración |
| `/comite/` | Comité Editorial |
| `/enlaces/` | Enlaces de interés |
| `/noticias/` | Noticias (índice del blog) |

### Contenido (CPT)

| Ruta | Contenido |
|------|-----------|
| `/numeros/` | Archivo de números |
| `/numeros/{slug}/` | Número individual (ej. `/numeros/vol-12-n2-2025/`) |
| `/articulos/` | Archivo de artículos |
| `/articulos/{slug}/` | Artículo individual (ej. `/articulos/la-naturaleza-del-ser/`) |
| `/autores/` | Archivo de autores |
| `/autores/{slug}/` | Autor individual |
| `/noticias/{slug}/` | Entrada individual (noticia) |

### Área privada (autenticados)

| Ruta | Contenido |
|------|-----------|
| `/login/` | Login (o `wp-login.php`) |
| `/registro/` | Registro (si está habilitado) |
| `/mi-cuenta/` | Panel de autor |
| `/mi-cuenta/nuevo/` | Nuevo envío |
| `/mi-cuenta/envios/` | Mis envíos |
| `/mi-cuenta/envios/{id}/` | Ver envío |

### Archivos filtrados (opcional)

| Ruta | Contenido |
|------|-----------|
| `/articulos/seccion/{section}/` | Artículos por sección (Metafísica, Ética, etc.) |
| `/articulos/tipo/{type}/` | Artículos por tipo (article, essay, review) |

### Búsqueda

| Ruta | Cuándo |
|------|--------|
| `/?s={query}` | Resultados de búsqueda (por defecto WordPress) |
| o `/buscar/?s={query}` | Si existe página de búsqueda |

### Estados

| Ruta | Cuándo |
|------|--------|
| 404 | Página no encontrada |

---

## 2. Reglas

- **URLs limpias:** Sin query strings para contenido principal. Usar solo para búsqueda (`?s=`).
- **Slugs consistentes:** Coincidir exactamente con los slugs de páginas WordPress. Minúsculas, guiones.
- **Barra final:** Usar barra final para consistencia (ej. `/acerca/`, `/numeros/`).
- **Jerarquía:** Máximo 2–3 niveles. `/numeros/{slug}/`, `/articulos/{slug}/`.
- **Slugs en español:** Slugs de páginas en español (acerca, contacto, normas, enviar-colaboracion).

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
| `/comite/` | `page-comite.php` |
| `/enlaces/` | `page-enlaces.php` |
| `/noticias/` | `home.php` (página de entradas) |
| `/numeros/` | `archive-issue.php` |
| `/numeros/{slug}/` | `single-issue.php` |
| `/articulos/` | `archive-article.php` |
| `/articulos/{slug}/` | `single-article.php` |
| `/noticias/{slug}/` | `single.php` |
| `/?s=` | `search.php` |
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
| `page-comite.html` | `/comite/` |
| `page-enlaces.html` | `/enlaces/` |
| `blog-index.html` | `/noticias/` |
| `archive-issue.html` | `/numeros/` |
| `single-issue.html` | `/numeros/{slug}/` |
| `archive-article.html` | `/articulos/` |
| `single-article.html` | `/articulos/{slug}/` |
| `single-post.html` | `/noticias/{slug}/` |
| `search.html` | `/?s=` o `/buscar/` |

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
