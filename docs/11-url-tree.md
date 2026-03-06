# Revista de Filosofía LOGO ET SPES — URL Tree

**Official URL structure**

Defines the canonical routes of the site. Single source for "what URL leads where".

**Depends on:** `04-screen-map`, `05-information-architecture-navigation`, `09-ui-copy-sheet`  
**Reference:** `03-wordpress-content-model`, `12-theme-file-structure`

---

## 1. Structure

### Main pages

| Route | Content |
|-------|---------|
| `/` | Home |
| `/acerca/` | Acerca (enfoque, alcance, objetivos) |
| `/contacto/` | Contacto |
| `/normas/` | Normas de Publicación |
| `/etica/` | Ética Editorial |
| `/politicas/` | Políticas |
| `/enviar-colaboracion/` | Enviar colaboración |
| `/comite/` | Comité Editorial |
| `/enlaces/` | Enlaces de interés |
| `/noticias/` | Noticias (blog index) |

### Content (CPT)

| Route | Content |
|-------|---------|
| `/numeros/` | Archive issues |
| `/numeros/{slug}/` | Single issue (e.g. `/numeros/vol-12-n2-2025/`) |
| `/articulos/` | Archive articles |
| `/articulos/{slug}/` | Single article (e.g. `/articulos/la-naturaleza-del-ser/`) |
| `/noticias/{slug}/` | Single post (noticia) |

### Filtered archives (optional)

| Route | Content |
|-------|---------|
| `/articulos/seccion/{section}/` | Articles by section (Metafísica, Ética, etc.) |
| `/articulos/tipo/{type}/` | Articles by type (article, essay, review) |

### Search

| Route | When |
|-------|------|
| `/?s={query}` | Search results (WordPress default) |
| or `/buscar/?s={query}` | If search page exists |

### States

| Route | When |
|-------|------|
| 404 | Page not found |

---

## 2. Rules

- **Clean URLs:** No query strings for main content. Use for search only (`?s=`).
- **Consistent slugs:** Match WordPress page slugs exactly. Use lowercase, hyphens.
- **Trailing slash:** Use trailing slash for consistency (e.g. `/acerca/`, `/numeros/`).
- **Hierarchy:** Max 2–3 levels. `/numeros/{slug}/`, `/articulos/{slug}/`.
- **Spanish slugs:** Page slugs in Spanish (acerca, contacto, normas, enviar-colaboracion).

---

## 3. WordPress mapping

| Route | Template |
|-------|----------|
| `/` | `front-page.php` |
| `/acerca/` | `page-acerca.php` |
| `/contacto/` | `page-contacto.php` |
| `/normas/` | `page-normas.php` |
| `/etica/` | `page-etica.php` |
| `/politicas/` | `page-politicas.php` |
| `/enviar-colaboracion/` | `page-enviar-colaboracion.php` |
| `/comite/` | `page-comite.php` |
| `/enlaces/` | `page-enlaces.php` |
| `/noticias/` | `home.php` (posts page) |
| `/numeros/` | `archive-issue.php` |
| `/numeros/{slug}/` | `single-issue.php` |
| `/articulos/` | `archive-article.php` |
| `/articulos/{slug}/` | `single-article.php` |
| `/noticias/{slug}/` | `single.php` |
| `/?s=` | `search.php` |
| 404 | `404.php` |

---

## 4. Static HTML to URL mapping (maquette)

| Static file | WordPress URL |
|-------------|---------------|
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
| `search.html` | `/?s=` or `/buscar/` |

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
