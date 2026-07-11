# Revista de Filosofía LOGO ET SPES — Inventario de Fuentes de Contenido

**Lista canónica de fuentes de contenido y estado editorial**

Fuente única para saber qué contenido existe, dónde vive, cómo se mapea al sitio y si es institucional, dinámico o demostrativo.

**Depende de:** `03-wordpress-content-model`, `04-screen-map`  
**Referencia:** `09-ui-copy-sheet`, `15-assets-strategy`

---

## 1. Estructura de directorios

```
content-source/
└── PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md

assets/
├── img/
│   ├── logo-revista.svg
│   ├── logo-revista.png
│   ├── logo-cenfiss.svg
│   ├── favicon.svg
│   ├── favicon.png
│   ├── portada-ejemplo.jpg
│   ├── article-placeholder.svg
│   ├── avatar-default.svg
│   ├── placeholder-banner.jpg
│   └── banner-main.jpg
└── pdf/
    ├── normas-publicacion.pdf
    ├── solicitud-publicacion-declaracion-etica.pdf
    ├── articulo-01.pdf
    └── numero-v12n2-2025.pdf
```

**Regla:** `content-source/` es canónico. No modificar; usar tal cual está escrito. El documento fuente define el proyecto, la estructura y la normativa editorial, pero **no contiene la primera edición publicada**. WordPress es el sistema de publicación. Los assets en `assets/` son copias para implementación o recursos demostrativos. Ver `.cursor/rules/content-source-priority.mdc`.

---

## 2. Fuentes documentales

| Archivo | Contenido | Mapea a |
|---------|-----------|---------|
| `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` | Documento del proyecto: estructura, políticas, normas, formularios, modelo de portada | Todas las páginas |
| — Sección Primera (Estructura Básica) | Portada, Sumario, Editorial, Artículos, Autores, Árbitros, Misceláneas | Home, Número individual, Artículo individual, Archivo |
| — Sección Segunda (Descripción de Partes) | Enfoque, Alcance, Objetivos, Políticas, Normas de Publicación, Normas de Ética, Formularios | Acerca, Normas, Ética, Políticas, Enviar colaboración |
| — Sección Tercera | Simulación teórica del sitio | Wireframes, estructura |
| — Formularios (Solicitud, Arbitraje) | Plantillas de formularios | Enviar colaboración, Normas |
| — Origen del nombre Logo et Spes | Prof. Luis Felipe Ramírez | Acerca |
| `assets/pdf/normas-publicacion.pdf` | Normas de publicación (extracto/adaptación) | Normas |
| (Por crear) `assets/pdf/politicas-editoriales.pdf` | Políticas editoriales | Políticas |

### 2.1 Estado editorial de las fuentes

| Clasificación | Qué incluye | Tratamiento |
|---------------|-------------|-------------|
| Institucional canónico | Enfoque y alcance, objetivos, políticas, normas, ética, origen del nombre y formularios de `content-source/` | Puede cargarse en páginas WordPress respetando literalmente la fuente |
| Estructura del sitio | Plantillas HTML, partials, navegación, componentes, CSS y JavaScript | Se convierte en tema WordPress; no es contenido editorial |
| Contenido dinámico | Números, artículos, editoriales, autores, noticias y sus metadatos | Se crea como contenido administrable en WordPress |
| Demostrativo / dummy | Vol. 12 Nº 2, números históricos, seis artículos, autores, noticias, ISSN, DOI, ORCID, paginación y portadas de ejemplo | Sirve solo para validar la maqueta; no se migra como contenido real |
| Primera edición real | Portada, sumario, editorial, artículos, autores, páginas, PDF y metadatos oficiales | Se obtiene del PDF final y de los datos entregados por el equipo editorial |

---

## 3. Fuentes de imágenes

| Archivo / carpeta | Contenido | Mapea a |
|-------------------|-----------|---------|
| `assets/img/logo-revista.svg` | Logo de la revista | Header, todas las páginas |
| `assets/img/logo-cenfiss.svg` | Logo CENFISS | Footer, Enlaces, referencias institucionales |
| `assets/img/favicon.svg` | Favicon | Todas las páginas |
| `assets/img/article-placeholder.svg` | Fallback de artículo | Archivo de artículos, tarjetas de artículo |
| `assets/img/avatar-default.svg` | Fallback de avatar de autor | Artículo individual, tarjetas de autor |
| `assets/img/placeholder-banner.jpg` | Placeholder de banner | Carrusel del Home |

**Ya implementados:** `portada-ejemplo.jpg` (portada demostrativa), `banner-main.jpg` y `placeholder-banner.jpg`.
**Fuente para contenido real:** Las portadas oficiales deben provenir del equipo editorial o del diseño institucional de la revista. Fotos de autores de los propios autores; imágenes de eventos de CENFISS.

---

## 4. Video / audio

| Fuente | Contenido | Implementación |
|--------|-----------|----------------|
| (Ninguno actualmente) | — | — |

**Regla:** Si se incorpora contenido audiovisual, debe ser complementario al contenido editorial y no sustituir información textual esencial. Proporcionar subtítulos o transcripción para contenido informativo. Ver `19-accessibility-standards`.

---

## 5. Assets de marca

| Archivo | Uso |
|---------|-----|
| `assets/img/logo-revista.svg` | Header, favicon, materiales digitales |
| `assets/img/logo-cenfiss.svg` | Footer, Enlaces, institucional |
| `assets/img/favicon.svg` | Favicon |
| `content-source/PROYECTO...md` | Referencia de identidad (02), estructura |

---

## 6. Fuentes PDF

| Archivo | Contenido | Mapea a |
|---------|-----------|---------|
| `assets/pdf/normas-publicacion.pdf` | Normas de publicación | Página Normas |
| `assets/pdf/articulo-01.pdf` | Artículo demostrativo; no pertenece a la primera edición real | Artículo individual de la maqueta |
| `assets/pdf/numero-v12n2-2025.pdf` | Número demostrativo; no pertenece a la primera edición real | Número individual de la maqueta |
| `assets/pdf/solicitud-publicacion-declaracion-etica.pdf` | Archivo provisional pendiente de validación editorial | Enviar colaboración |
| (Por crear) `politicas-editoriales.pdf` | Políticas editoriales | Página Políticas |
| (Por crear) `instrumento-arbitraje.pdf` | Formulario según documento del proyecto | Normas |

**Regla para la primera edición:** El PDF integral, los PDFs individuales y la portada final deben subirse a la Media Library de WordPress. Los archivos demostrativos actuales no se renombran ni se publican como si fueran contenido oficial.

---

## 7. Tamaños de imagen recomendados

| Uso | Ancho máx | Formato | Contexto |
|-----|-----------|---------|----------|
| Portada de número | 1000–1200 px | JPG/WebP | Archivo de números, número individual |
| Hero / banner | 1200–1600 px | JPG/WebP | Home |
| Miniatura tarjeta de artículo | 400–600 px | JPG/WebP | Tarjetas |
| Avatar de autor | 200–300 px | JPG/WebP | Artículo individual, tarjetas de autor |
| Logos | SVG | SVG | Header, footer |

---

## 8. Matriz de trazabilidad

| Página / Sección | Fuente(s) |
|------------------|-----------|
| Home | Texto institucional de content-source + consultas dinámicas para número actual, artículos y noticias; la portada actual es demostrativa |
| Acerca | Documento del proyecto (Enfoque, Alcance, Objetivos, Origen del nombre) |
| Normas | Documento del proyecto (Normas de Publicación, Ética), assets/pdf/normas-publicacion.pdf |
| Ética | Documento del proyecto (Normas de Ética) |
| Políticas | Documento del proyecto (Políticas Editorial), assets/pdf/politicas-editorial.pdf |
| Enviar colaboración | Documento del proyecto (Formularios, instrucciones), assets/pdf/solicitud-publicacion-declaracion-etica.pdf (placeholder) |
| Consejo Editorial | Documento del proyecto (estructura y datos disponibles) + datos oficiales de la primera edición |
| Contacto | Documento del proyecto (ubicación, email), CENFISS |
| Enlaces | CENFISS, partners (externos) |
| Número individual | Datos de la primera edición, portada oficial y PDF integral; la maqueta actual es dummy |
| Artículo individual | Datos y PDF de cada artículo de la primera edición; `articulo-01.pdf` es dummy |
| Archivo de números | Consulta dinámica de números publicados; inicialmente debe mostrar solo ediciones reales |
| Archivo de artículos | Consulta dinámica de artículos publicados |
| Noticias | Entradas reales de CENFISS; las noticias actuales de la maqueta son dummy |
| Header / Footer | assets/img/logo-revista.svg, assets/img/logo-cenfiss.svg y datos institucionales; ISSN y depósito legal pendientes |

---

## 9. Acciones pendientes

| Acción | Responsable | Estado |
|--------|-------------|--------|
| Crear `politicas-editoriales.pdf` | Editorial | Pendiente |
| Reemplazar `solicitud-publicacion-declaracion-etica.pdf` (hoy es un placeholder sin contenido real) | Editorial | Pendiente |
| Crear `instrumento-arbitraje.pdf` | Editorial | Pendiente |
| Recibir y registrar metadatos oficiales de la primera edición | Editorial | Pendiente |
| Añadir portada real del primer número publicado (hoy `portada-ejemplo.jpg` es una portada de ejemplo, no la oficial) | Editorial | Pendiente |
| Recibir PDF integral y PDFs individuales de la primera edición | Editorial | Pendiente |
| Sustituir el dataset dummy de números, artículos, autores y noticias | Dev / Editorial | Pendiente |
| Añadir `portada-ejemplo.jpg` para validar la maqueta | Diseño | Implementado |
| Añadir `banner-main.jpg` y `placeholder-banner.jpg` | Diseño | Implementado |
| Construir `404.html` | Dev | Implementado |
| Construir `search.html` | Dev | Implementado |

---

## 10. Reglas de trazabilidad

- Cada página/sección en `04-screen-map` debe trazarse a al menos una fuente de contenido. Si se crea contenido en WordPress sin fuente, documentar la decisión en este archivo.
- Todo asset visual o PDF publicado debe estar registrado en este inventario.
- Ningún dato de demostración puede convertirse en contenido publicado sin confirmación documental del equipo editorial.
- Los conteos, archivos, filtros, tabla de contenidos, paginación, breadcrumbs, SEO y metadatos de citación se generan desde WordPress; no se copian como contenido estático.

---

**Versión:** 1.1
**Proyecto:** Revista de Filosofía LOGO ET SPES
