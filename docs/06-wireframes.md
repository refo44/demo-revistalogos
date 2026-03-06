# Revista de Filosofía LOGO ET SPES — Wireframes

**Versión 1.0**

Estructura de pantalla: jerarquía, bloques y flujo de lectura por vista. No diseño visual; apoya mapa de pantallas y navegación.

**Depende de:** `04-screen-map`, `05-information-architecture-navigation`  
**Referencia:** `02-corporate-identity`, `20-layout-principles`

---

## Propósito

Los wireframes definen:

- Jerarquía de bloques por pantalla
- Flujo de lectura
- Ubicación de componentes
- Comportamiento responsive (móvil → escritorio)

**No** definen colores, tipografía ni diseño visual final. Eso viene de `02-corporate-identity`.

---

## Estructura global (todas las páginas)

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Enlace saltar | 0 | Saltar al contenido principal (accesibilidad) |
| Header | 1 | Logo, nav principal |
| Main | 2 | Contenido específico de la página |
| Footer | 3 | Info revista, enlaces rápidos, normas, contacto, CC |

---

## Estructura por pantalla

### Home

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Hero | 1 | Título revista, subtítulo, CTA Ver número actual |
| Número actual destacado | 2 | Portada del número, resumen breve, Ver número |
| Artículos recientes | 3 | 3–4 artículos del número actual |
| Carrusel | 4 | Opcional: eventos o anuncios |
| Sidebar / tarjetas | 5 | Noticias, colaboración, CENFISS |

---

### Número individual

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → [Título del número] |
| Encabezado del número | 2 | Imagen de portada, título (Vol. X Nº Y), meta (ISSN, DOI, fecha), descripción, acciones (Ver PDF, Descargar PDF) |
| Tabla de contenidos | 3 | Secciones (Metafísica, Ética, etc.). Por sección: título del artículo, Autor → enlace al perfil, páginas, DOI. |

---

### Artículo individual

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → [Número] → [Título del artículo] |
| Encabezado del artículo | 2 | Título (ES/EN), Autores → enlace a perfil, meta (volumen, páginas, DOI, fechas) |
| Resumen / Abstract | 3 | Resumen, Abstract, Palabras clave |
| Contenido | 4 | Cuerpo (secciones) |
| Referencias | 5 | Lista de referencias |
| Caja de metadatos | 6 | Autores, afiliaciones, ORCID, DOI, páginas, sección, palabras clave, fechas, cita sugerida |
| Acciones | 7 | Ver PDF, Descargar PDF |

---

### Archivo de números

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → Números publicados |
| Encabezado | 2 | Título, descripción |
| Grid de números | 3 | Tarjetas: portada, volumen, título, meta, descripción, estadísticas, Ver PDF + Descargar PDF |

---

### Archivo de autores

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → Autores |
| Encabezado | 2 | Título, descripción |
| Grid de autores | 3 | Tarjetas: nombre, afiliación, enlace a perfil |

---

### Archivo de artículos

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → Artículos |
| Encabezado | 2 | Título, descripción |
| Filtros | 3 | Búsqueda (título, autor, palabras clave), select sección, select año, ordenar por (año, título), Buscar, Limpiar |
| Grid de artículos | 4 | Tarjetas: título, subtítulo (EN), autores → enlace a perfil, meta (DOI, páginas, sección, año), resumen, palabras clave, Ver PDF + Descargar PDF |

---

### Enviar colaboración

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Enviar colaboración |
| Encabezado | 2 | Título, descripción |
| Contenido principal | 3 | Instrucciones numeradas (login/registro, normas, formulario, email). Caja de aviso. |
| Acciones | 4 | Iniciar sesión, Crear envío |
| Sidebar | 5 | Enlaces útiles: Normas, Políticas, Comité, Contacto |

---

### Normas, Políticas, Ética

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → [Título de la página] |
| Encabezado | 2 | Título, descripción |
| Contenido | 3 | Secciones editoriales (H2, H3), botones de descarga PDF, enlaces a APA/Vancouver |
| Sidebar | 4 | Opcional. Enlaces rápidos, páginas relacionadas. |

---

### Acerca

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Acerca |
| Encabezado | 2 | Título, descripción |
| Contenido principal | 3 | Reseña, Línea Editorial (Enfoque, Objetivos, Principios, Valores, Criterios), Fundamento Normativo |
| Bloque institucional | 4 | Descripción CENFISS + enlace externo |
| Sidebar | 5 | Información de la Revista (ISSN, etc.), enlaces |

---

### Contacto

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Contacto |
| Encabezado | 2 | Título, descripción |
| Contenido | 3 | Info CENFISS, dirección, email, web. Opcional: formulario de contacto. |

---

### Comité Editorial

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Comité Editorial |
| Encabezado | 2 | Título, descripción |
| Contenido | 3 | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. Lista o tarjetas. |

---

### Enlaces

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Enlaces |
| Encabezado | 2 | Título, descripción |
| Contenido | 3 | Lista de enlaces externos (CENFISS, partners, recursos). |

---

### Noticias (índice del blog)

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Noticias |
| Encabezado | 2 | Título, descripción |
| Lista de entradas | 3 | Tarjetas de noticia: fecha, autor, título, extracto, Leer más |

---

### Entrada individual (noticia)

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Noticias |
| Encabezado de la entrada | 2 | Fecha, autor, título |
| Contenido de la entrada | 3 | Cuerpo completo |
| Footer de la entrada | 4 | Opcional: entradas relacionadas, volver a Noticias |

---

### Autor individual

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Revista → Autores → [Nombre del autor] |
| Perfil de autor | 2 | Bio, afiliación, ORCID |
| Lista de artículos | 3 | Artículos publicados del autor |

---

### Resultados de búsqueda

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Encabezado de búsqueda | 1 | Consulta, conteo de resultados |
| Lista de resultados | 2 | Prioridad: 1) Artículos, 2) Números, 3) Autores, 4) Noticias. Mismo patrón de tarjeta que archivos. |
| Estado vacío | 3 | Si no hay resultados: mensaje, enlace a Inicio, enlaces para explorar |

---

### 404

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Mensaje | 1 | "Página no encontrada" o similar |
| Acciones | 2 | Enlace a Inicio, enlace a Número actual |

---

## Reglas responsive

- **Móvil:** Columna única; bloques apilados verticalmente; mismo orden que escritorio. Nav colapsa a toggle. Sidebar pasa debajo del contenido principal. Migas de pan pueden simplificarse a: Inicio → Sección (para evitar saturación).
- **Tablet:** Igual que móvil; puede permitir grid de 2 columnas para tarjetas donde el espacio lo permita.
- **Escritorio:** Maquetación main + sidebar donde aplique (Acerca, Enviar colaboración, Normas). Grids de tarjetas: 2–3 columnas.

**Regla:** Lo responsive no rediseña; adapta proporciones. Mismo contenido, menos ancho.

---

## Inventario de componentes

| Componente | Uso |
|------------|-----|
| Hero | Home. Título, subtítulo, CTA. |
| Migas de pan | Todas las páginas de contenido. Inicio → Revista → ruta. |
| Encabezado | Archivos, páginas estáticas. Título, descripción. |
| Tarjeta de número | Archivo de números, Home. Portada, título, meta, Ver PDF, Descargar PDF. |
| Tarjeta de artículo | Archivo de artículos, TOC del número individual. Título, autores → perfil, resumen, Ver PDF, Descargar PDF. |
| Tarjeta de noticia | Índice de noticias. Fecha, autor, título, extracto, Leer más. |
| Perfil de autor | Página con bio y lista de artículos. |
| Caja de metadatos | Artículo individual. Lista estructurada clave-valor. |
| Tarjeta de sidebar | Acerca, Enviar colaboración, Normas. Enlaces relacionados, info. |
| Sección editorial | Páginas estáticas. H2/H3, párrafos, listas. |
| Paginación | Archivos, búsqueda. Anterior/siguiente, números de página. |

---

## Flujo principal de lectura

Inicio → Número actual → Artículo → Autor / otros artículos

Este flujo refleja cómo se navega una revista académica.

---

## Entregable

- Bocetos en papel, o
- Wireframes Figma / HTML
- Por pantallas de `04-screen-map`

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
