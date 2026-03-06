# Revista de Filosofía LOGO ET SPES — Mapa de Pantallas

Lista de las pantallas que existen en el sitio. No describe diseño visual; solo define qué vistas deben construirse. Fuente única para determinar qué vistas existen en el sistema. El contenido de cada una está en 03; la estructura del tema en 12.

**Depende de / referencia:** `01-platform-plan`, `03-wordpress-content-model`, `05-information-architecture-navigation`  
**Estructura de bloques por pantalla:** `06-wireframes`

---

## Páginas fijas

| Página | Función |
|--------|---------|
| Inicio | Hero, número actual, CTA principal. Punto de entrada para leer y enviar. |
| Acerca | Enfoque, alcance, objetivos, origen del nombre Logo et Spes. |
| Contacto | Contacto institucional, consejo editorial, CENFISS, email. |
| Normas | Normas de publicación, descargas PDF, enlaces a APA/Vancouver. |
| Ética | Normas de ética editorial (alineación COPE). |
| Políticas | Políticas editoriales. |
| Enviar colaboración | Instrucciones para autores, formularios, CTA principal para envío. |
| Comité Editorial | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. |
| Enlaces | Enlaces de interés, CENFISS, partners. |

---

## Páginas condicionales

| Página | Condición |
|--------|------------|
| Resultados de búsqueda | El usuario envía una consulta de búsqueda. Prioridad: 1) Artículos, 2) Números, 3) Autores, 4) Noticias. |
| Archivo filtrado | El usuario filtra por sección/article_type (artículos, números). |

---

## Área privada (autores autenticados)

| Pantalla | Función |
|----------|---------|
| Login | Formulario de inicio de sesión |
| Registro | Creación controlada de cuentas (o solo por invitación) |
| Panel de autor | Resumen, enlaces a normas, nuevo envío |
| Nuevo envío | Crear envío, subir manuscrito + archivo anonimizado |
| Mis envíos | Lista de envíos del autor |
| Ver envío | Detalle de un envío, estado |
| Editar borrador | Editar borrador antes del envío |
| Confirmación de envío | Confirmación tras enviar |

---

## Vistas de contenido (individual)

| Vista | Uso |
|-------|-----|
| Número individual | Metadatos del número, editorial, lista de artículos del número, descarga PDF. |
| Artículo individual | Artículo, ensayo o reseña individual. Texto completo o PDF, metadatos, autores. |
| Entrada individual | Noticia individual. CENFISS presente, eventos. |
| Autor individual | Perfil del autor, afiliación, ORCID, artículos publicados. |

---

## Vistas de archivo

| Vista | Uso |
|-------|-----|
| Archivo de noticias | Listado de noticias, CENFISS presente, eventos. |
| Archivo de números | Lista de todos los números publicados. |
| Archivo de artículos | Lista de artículos. Filtrar por sección, tipo, número. |
| Archivo de autores | Lista de autores. |
| Archivo por sección | Artículos filtrados por sección filosófica (Metafísica, Ética, etc.). |
| Archivo por tipo | Artículos por tipo (article, essay, review, editorial). |
| Archivo por palabra clave | Artículos por palabra clave. |

---

## Estados

| Estado | Descripción |
|--------|-------------|
| Búsqueda vacía | Sin resultados para la consulta. Ofrecer enlace a inicio o explorar. |
| Archivo vacío | Aún no hay números/artículos. Mensaje informativo. |
| Archivo de autor vacío | Autor aún sin artículos publicados. |
| 404 | La página no existe. Enlace a inicio. |

---

## Elementos globales (componentes, no pantallas)

**Header:** Logo, nav principal (máximo dos niveles). Estructura: Inicio | Revista (Número actual, Números publicados, Autores) | Normas | Enviar colaboración | Acerca | Noticias | Contacto. Enlace CENFISS.

**Footer:** Info de la revista (ISSN, DOI), Enlaces rápidos, Normas editoriales, Contacto, licencia Creative Commons, política de privacidad, ética editorial.

Presentes en todas las páginas.

---

## Total de pantallas a construir

**Páginas públicas:** Inicio, Acerca, Contacto, Normas, Ética, Políticas, Enviar colaboración, Comité Editorial, Enlaces

**Archivos:** Archivo de noticias, Archivo de números, Archivo de artículos, Archivo de autores, Archivo por sección, Archivo por tipo, Archivo por palabra clave

**Vistas de contenido:** Número individual, Artículo individual, Entrada individual, Autor individual

**Área privada:** Login, Registro, Panel de autor, Nuevo envío, Mis envíos, Ver envío, Editar borrador, Confirmación de envío

**Estados del sistema:** 404, Búsqueda vacía, Archivo vacío, Archivo de autor vacío

**Componentes globales:** Header, Footer

---

## Mapeo de plantillas WordPress

| Vista | Plantilla |
|-------|-----------|
| Inicio | `front-page.php` |
| Acerca | `page-acerca.php` |
| Contacto | `page-contacto.php` |
| Normas | `page-normas.php` |
| Ética | `page-etica.php` |
| Políticas | `page-politicas.php` |
| Enviar colaboración | `page-enviar-colaboracion.php` |
| Comité Editorial | `page-comite.php` |
| Enlaces | `page-enlaces.php` |
| Archivo de noticias | `home.php` o `index.php` |
| Archivo de números | `archive-issue.php` |
| Archivo de artículos | `archive-article.php` |
| Archivo de autores | `archive-author.php` |
| Archivo por sección | `taxonomy-section.php` |
| Archivo por tipo | `taxonomy-article_type.php` |
| Archivo por palabra clave | `taxonomy-keyword.php` |
| Número individual | `single-issue.php` |
| Artículo individual | `single-article.php` |
| Entrada individual | `single.php` |
| Autor individual | `single-author.php` |
| Resultados de búsqueda | `search.php` |
| 404 | `404.php` |
| Header | `parts/header.php` |
| Footer | `parts/footer.php` |

---

## Qué NO tiene el sitio

- E-commerce o pagos
- Comentarios en artículos
- Feed social o login social
- Publicidad o contenido patrocinado

**Nota:** El panel de autor y la gestión de envíos son parte del sistema (Capa 6.5). La cuenta existe solo para facilitar el proceso editorial.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
