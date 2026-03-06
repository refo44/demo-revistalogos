# Revista de Filosofía LOGO ET SPES — Mapa de Pantallas

Lista de qué pantallas existen. No describe el diseño; solo qué vistas construir. Fuente única para "qué vistas construir". El contenido de cada una está en 03; la estructura del tema en 12.

**Depende de / referencia:** `01-platform-plan`, `03-wordpress-content-model`, `05-information-architecture-navigation`  
**Estructura de bloques por pantalla:** `06-wireframes`

---

## Páginas fijas

| Página | Función |
|--------|---------|
| Home | Hero, número actual, CTA principal. Punto de entrada para leer y enviar. |
| Acerca | Enfoque, alcance, objetivos, origen del nombre Logo et Spes. |
| Contacto | Contacto institucional, consejo editorial, CENFISS, email. |
| Normas | Normas de publicación, descargas PDF, enlaces a APA/Vancouver. |
| Ética | Normas de ética editorial (alineación COPE). |
| Políticas | Políticas editoriales. |
| Enviar colaboración | Instrucciones para autores, formularios, CTA principal para envío. |
| Comité Editorial | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. |
| Enlaces | Enlaces de interés, CENFISS, partners. |
| Noticias | Índice del blog. CENFISS presente, eventos, misceláneas. |

---

## Páginas condicionales

| Página | Condición |
|--------|------------|
| Resultados de búsqueda | El usuario envía una consulta de búsqueda. |
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
| Número individual | Número individual. Editorial, lista de artículos, descarga PDF. |
| Artículo individual | Artículo, ensayo o reseña individual. Texto completo o PDF, metadatos, autores. |
| Entrada individual | Noticia individual. CENFISS presente, eventos. |

---

## Vistas de archivo

| Vista | Uso |
|-------|-----|
| Archivo de números | Lista de todos los números publicados. |
| Archivo de artículos | Lista de artículos. Filtrar por sección, tipo, número. |
| Archivo de autores | Lista de autores (opcional; puede enlazar desde metadatos del artículo). |

---

## Estados

| Estado | Descripción |
|--------|-------------|
| Búsqueda vacía | Sin resultados para la consulta. Ofrecer enlace a inicio o explorar. |
| Archivo vacío | Aún no hay números/artículos. Mensaje informativo. |
| 404 | La página no existe. Enlace a inicio. |

---

## Elementos globales (componentes, no pantallas)

**Header:** Logo, nav principal (Inicio, Número Actual, Números Publicados, Enviar colaboración, Acerca, Noticias, Contacto, Enlaces), enlace CENFISS.

**Footer:** Info de la revista (ISSN, DOI), Enlaces rápidos, Normas editoriales, Contacto, licencia CC.

Presentes en todas las páginas.

---

## Total de pantallas a construir

**Páginas principales (estáticas):** Home, Acerca, Contacto, Normas, Ética, Políticas, Enviar colaboración, Comité Editorial, Enlaces, Noticias

**Archivos:** Archivo de números, Archivo de artículos

**Vistas de contenido:** Número individual, Artículo individual, Entrada individual

**Condicionales:** Resultados de búsqueda, Archivos filtrados

**Privadas (autores):** Login, Registro, Panel de autor, Nuevo envío, Mis envíos, Ver envío, Editar borrador, Confirmación de envío

**Estados:** 404, Búsqueda vacía, Archivo vacío

**Componentes globales:** Header, Footer

---

## Mapeo de plantillas WordPress

| Vista | Plantilla |
|-------|-----------|
| Home | `front-page.php` |
| Acerca | `page-acerca.php` |
| Contacto | `page-contacto.php` |
| Normas | `page-normas.php` |
| Ética | `page-etica.php` |
| Políticas | `page-politicas.php` |
| Enviar colaboración | `page-enviar-colaboracion.php` |
| Comité Editorial | `page-comite.php` |
| Enlaces | `page-enlaces.php` |
| Noticias | `home.php` o `index.php` |
| Archivo de números | `archive-issue.php` |
| Archivo de artículos | `archive-article.php` |
| Número individual | `single-issue.php` |
| Artículo individual | `single-article.php` |
| Entrada individual | `single.php` |
| Búsqueda | `search.php` |
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
