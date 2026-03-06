# Revista de Filosofía LOGO ET SPES — Modelo de Contenido WordPress

**Versión 1.0**

Modelo de contenido oficial para la implementación WordPress. Basado en el plan de plataforma y las fuentes de contenido.

**Depende de:** `01-platform-plan`, `02-corporate-identity`, `04-screen-map`  
**Referencia:** `09-ui-copy-sheet`, `11-url-tree`, `12-theme-file-structure`

---

## 1. Esquema general

### Tipos de entrada

| Clave | Etiqueta | Tipo | Slug | Uso principal |
|-------|----------|------|------|----------------|
| page | Páginas | Nativo | por página | Páginas institucionales y editoriales estáticas |
| post | Entradas | Nativo | noticias | Noticias, CENFISS presente, misceláneas |
| issue | Números | Custom | numeros | Volúmenes publicados de la revista |
| article | Artículos | Custom | articulos | Contenido académico publicado |
| author | Autores | Custom | autores | Perfiles de autor reutilizables (públicos) |
| submission | Envíos | Custom (privado) | envios | Manuscritos enviados por usuarios autenticados |

**Regla:** El contenido publicado y los manuscritos enviados son dos sistemas distintos. Un envío no es un artículo. Cuando se acepta, el editor crea o publica el artículo vinculado.

### Contenido fijo vs. dinámico

| Tipo | Gestión |
|------|---------|
| Páginas estáticas | page |
| Números (issues) | issue (CPT) |
| Artículos, ensayos, reseñas, editoriales | article (CPT) |
| Autores | author (CPT) |
| Envíos (manuscritos) | submission (CPT, privado) |
| Noticias, CENFISS presente | post |

---

## 2. Campos por página

### Home (front-page)

- Hero: título, subtítulo, descripción breve, CTA principal (enlace al número actual)
- Bloque: Tarjeta del número actual (título, portada, enlace, PDF)
- Bloque: Carrusel de banner opcional (eventos, CENFISS)
- Bloque: Enlaces rápidos (Normas, Enviar colaboración)

### acerca (página)

- Título, contenido (enfoque, alcance, objetivos, origen del nombre Logo et Spes)
- Opcional: Resumen del consejo editorial

### contacto (página)

- Título, contenido
- Info de contacto: email (revista.cenfiss@gmail.com), web CENFISS, dirección
- Opcional: Formulario de contacto (o enlace mailto)

### normas (página)

- Título, contenido (normas de publicación)
- Descarga PDF: Solicitud de Publicación y Declaración de Ética
- Descarga PDF: Instrumento de Arbitraje
- Enlaces a guías APA, Vancouver

### politicas (página)

- Título, contenido (políticas editoriales)

### enviar-colaboracion (página)

- Título, contenido (instrucciones para autores)
- Descarga PDF: Solicitud de Publicación
- Enlaces a normas, politicas

### comite (página)

- Título, contenido (Consejo Editorial, Editor General, Editores adjuntos, Árbitros)

### enlaces (página)

- Título, contenido (enlaces de interés, CENFISS, partners)

---

## 3. Custom Post Types

### issue

| Campo | Tipo | Uso |
|-------|------|-----|
| Title | Nativo | Título del volumen (ej. "Filosofía Contemporánea: Nuevas Perspectivas") |
| Content | Nativo | Descripción del número. El editorial es un artículo con article_type = editorial. |
| Featured image | Nativo | Imagen de portada |
| volume_number | number | Vol. 12 |
| issue_number | number | Nº 2 |
| year | number | 2025 |
| date_published | date | Fecha de publicación |
| issn | text | ISSN (cuando esté disponible) |
| doi | text | Prefijo/sufijo DOI |
| pdf_file | file | PDF completo del número (Media Library) |

**Relaciones:** Artículos vinculados al número vía post meta. El conteo de artículos es derivado (no almacenar).

### article

| Campo | Tipo | Uso |
|-------|------|-----|
| Title | Nativo | Título en español |
| Content | Nativo | Cuerpo completo (o extracto + enlace PDF) |
| title_en | text | Título en inglés (requerido por normas) |
| abstract | textarea | Resumen en español (máx 250 palabras) |
| abstract_en | textarea | Resumen en inglés |
| article_type | taxonomy | article \| essay \| review \| editorial |
| authors | relation | Enlaces al CPT author (muchos a muchos) |
| doi | text | DOI del artículo |
| doi_url | url | URL completo DOI (https://doi.org/xxxxx) |
| pages | text | Paginación (ej. "15-32") |
| pdf_file | file | PDF del artículo (Media Library) |
| issue | relation | Número padre |
| section | taxonomy | Metafísica, Ética, Epistemología, etc. |
| keyword | taxonomy | 3–5 palabras clave (búsqueda, filtro, SEO) |
| language | text | Idioma principal (es, en) |
| publication_date | date | Fecha de publicación |
| received_date | date | Fecha de envío |
| accepted_date | date | Fecha de aceptación |
| citation_format | text | Cita preformateada (ej. "Pérez, Juan. La naturaleza del ser. Revista de Filosofía LOGO ET SPES. Vol. 12, Nº 2 (2025): 15–32.") |

**Prioridad a campos nativos:** Usar Title, Content, Featured image cuando sea posible. Los campos custom complementan.

### author

| Campo | Tipo | Uso |
|-------|------|-----|
| Title | Nativo | Nombre completo (usar solo post_title) |
| afiliacion | text | Institución, afiliación |
| orcid | text | ORCID iD |
| bio | textarea | Biografía breve |
| email | text | Opcional; para contacto |

**Slug:** WordPress genera automáticamente (ej. `/autores/jose-morales/`). Usar post_name.

**Relaciones:** Los artículos enlazan a autores (muchos a muchos). Reutilizable entre artículos. Habilita páginas de archivo de autor.

### submission (CPT privado)

Objeto editorial interno. No visible al público. Los autores envían; los editores gestionan.

| Campo | Tipo | Uso |
|-------|------|-----|
| submission_title | text | Título del manuscrito |
| submission_type | select | article \| essay \| review |
| manuscript_file | file | Manuscrito completo con identificación del autor |
| anonymized_file | file | Versión anonimizada para arbitraje (requerida por normas editoriales) |
| cover_letter | file | Opcional |
| signed_form | file | Solicitud de Publicación, Declaración de Ética (opcional) |
| author_user | relation | Usuario WordPress (remitente) |
| coauthors | text | Nombres de coautores (o relación con CPT author) |
| status | select | Ver flujo de estados abajo |
| submitted_at | datetime | Cuándo se envió |
| updated_at | datetime | Última actualización |
| decision_date | datetime | Cuándo se tomó la decisión del editor |
| assigned_editor | relation | Editor que gestiona este envío |
| notes_for_editor | textarea | Notas del autor |
| linked_article | relation | Cuando se acepta: enlace al artículo publicado |

**Archivos requeridos por normas editoriales:** Manuscrito con identificación + versión anonimizada.

**Flujo de estados:** draft → submitted → under_internal_review → under_peer_review → revisions_required → accepted → rejected | published

**Regla:** Solo los envíos aceptados pueden generar artículos.

**Flujo:** Autor crea envío → Editor gestor revisa → Si se acepta → Editor crea/publica artículo vinculado al número.

**Visibilidad:** Los envíos son visibles solo para: el autor que envía, editores, administradores.

---

## 4. Metadatos de artículos (indexación académica)

Para Google Scholar, Crossref, catálogos de bibliotecas. Añadir como meta tags o datos estructurados:

| Campo | Uso |
|-------|-----|
| citation_title | Título del artículo |
| citation_author | Autor(es) |
| citation_publication_date | Fecha de publicación |
| citation_journal_title | Revista de Filosofía LOGO ET SPES |
| citation_volume | Número de volumen |
| citation_issue | Número de ejemplar |
| citation_firstpage | Primera página |
| citation_lastpage | Última página |
| citation_pdf_url | URL del PDF (para Google Scholar) |
| citation_language | Idioma principal (es, en) |

---

## 5. Taxonomías

| Clave | Etiqueta | Tipo | Aplica a |
|-------|----------|------|----------|
| section | Sección | Jerárquica | article |
| article_type | Tipo | No jerárquica | article |
| keyword | Palabras clave | No jerárquica | article |
| philosopher | Filósofo / tema | No jerárquica | article (opcional) |

**Valores article_type:** article, essay, review, editorial.

**Valores section (ejemplos):** Metafísica, Ética, Epistemología, Filosofía de la Religión, Filosofía Política, Lógica, Historia de la Filosofía, Otros.

**keyword:** 3–5 requeridas por artículo (por normas editoriales). Habilita búsqueda, filtrado, SEO académico.

**philosopher:** Taxonomía opcional para navegación temática. kant, aristotle, ethics, phenomenology. Omitir si no se necesita.

---

## 6. Usuarios y roles

| Rol | Capacidades |
|-----|-------------|
| **Author** | Login, crear envíos, subir archivos, editar borradores antes de enviar, ver estado del envío |
| **Managing Editor** | Revisar envíos, cambiar estado, descargar archivos, gestionar decisiones (rol custom; evitar conflicto con "Editor" de WordPress) |
| **Administrator** | Configuración completa del sistema |

**Reviewer:** Omitir en primera versión si el arbitraje es externo. Añadir después si el arbitraje se gestiona dentro del sitio.

---

## 7. Permalinks

| Tipo | Estructura | Ejemplo |
|------|------------|---------|
| Issue | `/numeros/{slug}/` | `/numeros/2025-vol-12-n2/` |
| Article | `/articulos/{slug}/` | `/articulos/la-naturaleza-del-ser/` (opcional: `/articulos/2025/la-naturaleza-del-ser/`) |
| Author | `/autores/{slug}/` | `/autores/juan-perez/` |
| Login | `/login/` o `wp-login.php` | — |
| Panel de autor | `/autores/panel/` o `/mi-cuenta/` | — |

**Prefijo opcional** (si el sitio vive en subruta): `revista/numeros/`, `revista/articulos/`, `revista/autores/`. Mejora SEO, citabilidad, estabilidad. Requiere configuración de permalinks.

---

## 8. Área privada (envío de autores)

| Pantalla | Función |
|----------|---------|
| Login | Formulario de inicio de sesión |
| Registro | Creación controlada de cuentas (o solo por invitación) |
| Panel de autor | Resumen, enlaces a normas, nuevo envío |
| Nuevo envío | Crear envío, subir archivos |
| Mis envíos | Lista de envíos del autor |
| Ver envío | Detalle de un envío, estado |
| Editar borrador | Editar borrador antes del envío |

**Pantallas Managing Editor (admin):** Lista de envíos, detalle de envío, cambiar estado, descargar archivos.

---

## 9. Plantillas mínimas

**Públicas:**

- `front-page.php`: Home
- `page-acerca.php`, `page-contacto.php`, `page-normas.php`, `page-politicas.php`, `page-enviar-colaboracion.php`, `page-comite.php`, `page-enlaces.php`
- `archive-issue.php`: Lista de números
- `single-issue.php`: Número individual
- `archive-article.php`: Lista de artículos (filtrable por sección, tipo)
- `single-article.php`: Artículo individual
- `archive-author.php`: Lista de autores
- `single-author.php`: Autor individual
- `taxonomy-section.php`: Artículos por sección
- `taxonomy-article_type.php`: Artículos por tipo
- `taxonomy-keyword.php`: Artículos por palabra clave
- `taxonomy-philosopher.php`: Artículos por filósofo (si se usa la taxonomía)
- `home.php` o `index.php`: Noticias (índice del blog)
- `single.php`: Entrada individual (noticia)
- `page.php`: Fallback para otras páginas
- `404.php`: No encontrado

**Privadas (envío de autores):**

- `page-login.php` o redirección a `wp-login.php`
- `page-registro.php` (si el registro está habilitado)
- `page-mi-cuenta.php` o `author-dashboard.php`: Panel de autor
- Plantillas de envío (o páginas admin custom / shortcodes)

---

## 10. Integraciones externas

| Recurso | Implementación |
|---------|----------------|
| Email (revista.cenfiss@gmail.com) | Enlaces mailto o plugin de formulario de contacto |
| Formularios PDF | Media library; enlaces desde normas, enviar-colaboracion |
| Sitio CENFISS (cenfiss.net) | Enlaces externos en footer, enlaces |
| Resolvedor DOI | Opcional: enlace a doi.org para DOIs de artículos |

---

## 11. Requisitos de accesibilidad del contenido

Sin reemplazar `19-accessibility-standards`, este modelo requiere:

- **Imágenes:** Siempre rellenar texto alt. Decorativas: `alt=""`.
- **Video informativo:** Subtítulos o transcripción cuando el contenido es informativo.
- **Estructura editorial:** Cada página tiene un H1 único y jerarquía clara H2/H3.
- **PDFs:** Los PDFs deben estar etiquetados y ser accesibles cuando sea posible.

---

## 12. Principio rector

El sistema prioriza la lectura, la citación y la divulgación académica sobre el comportamiento promocional. Todo en este modelo existe para: **difundir y divulgar el pensamiento filosófico a través de una publicación académica de acceso abierto**. Sin capas de marketing ni embudos. Solo claridad y propósito.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
