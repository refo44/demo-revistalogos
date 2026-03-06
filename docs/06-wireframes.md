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
| Hero | 1 | Título (LOGO ET SPES), subtítulo, descripción breve, CTA principal (Ver número actual) |
| Carrusel de banner | 2 | Opcional. Eventos, CENFISS. Slides con imagen, título, CTA. |
| Grid de sidebar | 3 | Noticias (lista + enlace), Colaboración (info), CENFISS (enlace). Tarjetas. |

**Nota:** Las secciones por disciplina (Metafísica, Ética, etc.) son opcionales; pueden ocultarse o mostrarse.

---

### Número individual

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Archivos → [Título del número] |
| Header del número | 2 | Imagen de portada, título (Vol. X Nº Y), meta (ISSN, DOI, fecha), descripción, acciones (descarga PDF, Ver PDF) |
| Tabla de contenidos | 3 | Secciones (Metafísica, Ética, etc.). Por sección: enlaces a artículos con autores, páginas, DOI. |

---

### Artículo individual

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Archivos → [Número] → [Título del artículo] |
| Header del artículo | 2 | Título (ES/EN), autores, meta (volumen, páginas, DOI, fechas) |
| Caja de metadatos | 3 | Autores, afiliaciones, ORCID, DOI, páginas, sección, palabras clave, fechas, cita sugerida |
| Contenido del artículo | 4 | Resumen, Abstract, Palabras clave, cuerpo (secciones), referencias |
| Acciones del artículo | 5 | Descarga PDF, Leer más (si extracto) |

---

### Archivo de números

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Archivos |
| Header del archivo | 2 | Título, descripción |
| Grid de números | 3 | Tarjetas: portada, volumen, título, meta, descripción, estadísticas, PDF + Ver contenido |

---

### Archivo de artículos

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Artículos |
| Header del archivo | 2 | Título, descripción |
| Filtros | 3 | Búsqueda (título, autor, palabras clave), select sección, select año, Buscar, Limpiar |
| Grid de artículos | 4 | Tarjetas: título, subtítulo (EN), autores, meta (DOI, páginas, sección, año), resumen, palabras clave, PDF + Leer más |

---

### Enviar colaboración

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Enviar Colaboración |
| Header de página | 2 | Título, descripción |
| Contenido principal | 3 | Instrucciones numeradas (login/registro, normas, formulario, email). Caja de aviso. |
| Sidebar | 4 | Enlaces útiles: Normas, Políticas, Comité, Contacto |

---

### Normas, Políticas, Ética

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → [Título de la página] |
| Header de página | 2 | Título, descripción |
| Contenido | 3 | Secciones editoriales (H2, H3), botones de descarga PDF, enlaces a APA/Vancouver |
| Sidebar | 4 | Opcional. Enlaces rápidos, páginas relacionadas. |

---

### Acerca

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Acerca |
| Header de página | 2 | Título, descripción |
| Contenido principal | 3 | Reseña, Línea Editorial (Enfoque, Objetivos, Principios, Valores, Criterios), Fundamento Normativo |
| Sidebar | 4 | Información de la Revista (ISSN, etc.), enlaces |

---

### Contacto

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Contacto |
| Header de página | 2 | Título, descripción |
| Contenido | 3 | Info CENFISS, dirección, email, web. Opcional: formulario de contacto. |

---

### Comité Editorial

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Comité Editorial |
| Header de página | 2 | Título, descripción |
| Contenido | 3 | Consejo Editorial, Editor General, Editores adjuntos, Árbitros. Lista o tarjetas. |

---

### Enlaces

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Enlaces |
| Header de página | 2 | Título, descripción |
| Contenido | 3 | Lista de enlaces externos (CENFISS, partners, recursos). |

---

### Noticias (índice del blog)

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Noticias |
| Header del archivo | 2 | Título, descripción |
| Lista de entradas | 3 | Tarjetas: fecha, autor, título, extracto, Leer más |

---

### Entrada individual (noticia)

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Migas de pan | 1 | Inicio → Noticias |
| Header de la entrada | 2 | Fecha, autor, título |
| Contenido de la entrada | 3 | Cuerpo completo |
| Footer de la entrada | 4 | Opcional: entradas relacionadas, volver a Noticias |

---

### Resultados de búsqueda

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Header de búsqueda | 1 | Consulta, conteo de resultados |
| Lista de resultados | 2 | Artículos/números/entradas que coinciden con la consulta. Mismo patrón de tarjeta que archivos. |
| Estado vacío | 3 | Si no hay resultados: mensaje, enlace a Inicio, enlaces para explorar |

---

### 404

| Bloque | Orden | Contenido / función |
|--------|-------|---------------------|
| Mensaje | 1 | "Página no encontrada" o similar |
| Acciones | 2 | Enlace a Inicio, enlace a Número actual |

---

## Reglas responsive

- **Móvil:** Columna única; bloques apilados verticalmente; mismo orden que escritorio. Nav colapsa a toggle. Sidebar pasa debajo del contenido principal.
- **Tablet:** Igual que móvil; puede permitir grid de 2 columnas para tarjetas donde el espacio lo permita.
- **Escritorio:** Maquetación main + sidebar donde aplique (Acerca, Enviar colaboración, Normas). Grids de tarjetas: 2–3 columnas.

**Regla:** Lo responsive no rediseña; adapta proporciones. Mismo contenido, menos ancho.

---

## Inventario de componentes

| Componente | Uso |
|------------|-----|
| Hero | Home. Título, subtítulo, CTA. |
| Migas de pan | Todas las páginas de contenido. Inicio → ruta. |
| Header de archivo | Archivos. Título, descripción. |
| Tarjeta de número | Archivo de números, Home. Portada, título, meta, PDF, Ver contenido. |
| Tarjeta de artículo | Archivo de artículos, TOC del número individual. Título, autores, resumen, PDF, Leer más. |
| Caja de metadatos | Artículo individual. Lista estructurada clave-valor. |
| Tarjeta de sidebar | Acerca, Enviar colaboración, Normas. Enlaces relacionados, info. |
| Sección editorial | Páginas estáticas. H2/H3, párrafos, listas. |
| Paginación | Archivos, búsqueda. Anterior/siguiente, números de página. |

---

## Entregable

- Bocetos en papel, o
- Wireframes Figma / HTML
- Por pantallas de `04-screen-map`

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
