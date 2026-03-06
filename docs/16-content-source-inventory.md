# Revista de Filosofía LOGO ET SPES — Inventario de Fuentes de Contenido

**Lista canónica de fuentes de contenido**

Fuente única para qué contenido existe, dónde vive y cómo se mapea al sitio.

**Depende de:** `03-wordpress-content-model`, `04-screen-map`  
**Referencia:** `09-ui-copy-sheet`, `15-assets-strategy`

---

## 1. Estructura de directorios

```
content-source/
└── PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md

assets/
├── img/          (logos, placeholders, portadas)
└── pdf/          (normas, políticas, artículo/número de ejemplo)
```

**Regla:** `content-source/` es canónico. No modificar; usar tal cual está escrito. Los assets en `assets/` son copias para implementación. Ver `.cursor/rules/content-source-priority.mdc`.

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
| `assets/pdf/politicas-editorial.pdf` | Políticas editoriales | Políticas |

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

**Por añadir:** `portada-ejemplo.png` (portada de número), `flyer.jpg` (eventos CENFISS), `banner-main.png` (hero).  
**Fuente para contenido real:** Portadas de números del equipo editorial; fotos de autores de los autores; imágenes de eventos de CENFISS.

---

## 4. Video / audio

| Fuente | Contenido | Implementación |
|--------|-----------|----------------|
| (Ninguno actualmente) | — | — |

**Regla:** Si se añade, proporcionar subtítulos o transcripción para contenido informativo. Ver `19-accessibility-standards`.

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
| `assets/pdf/articulo-01.pdf` | Artículo de ejemplo | Artículo individual |
| `assets/pdf/numero-v12n2-2025.pdf` | Número de ejemplo | Número individual |
| (Por crear) `politicas-editorial.pdf` | Políticas editoriales | Página Políticas |
| (Por crear) `solicitud-publicacion-declaracion-etica.pdf` | Formulario según doc del proyecto | Enviar colaboración |
| (Por crear) `instrumento-arbitraje.pdf` | Formulario según doc del proyecto | Normas |

---

## 7. Tamaños de imagen recomendados

| Uso | Ancho máx | Formato |
|-----|-----------|---------|
| Portada de número | 800–1000 px | JPG/WebP |
| Hero / banner | 1200–1600 px | JPG/WebP |
| Miniatura tarjeta de artículo | 400–600 px | JPG/WebP |
| Avatar de autor | 200–300 px | JPG/WebP |
| Logos | SVG | SVG |

---

## 8. Matriz de trazabilidad

| Página / Sección | Fuente(s) |
|------------------|-----------|
| Home | Doc del proyecto (Presentación, Estructura), portada-ejemplo, flyer |
| Acerca | Doc del proyecto (Enfoque, Alcance, Objetivos, Origen del nombre) |
| Normas | Doc del proyecto (Normas de Publicación, Ética), normas-publicacion.pdf |
| Ética | Doc del proyecto (Normas de Ética) |
| Políticas | Doc del proyecto (Políticas Editorial), politicas-editorial.pdf |
| Enviar colaboración | Doc del proyecto (Formularios, instrucciones) |
| Comité Editorial | Doc del proyecto (autoridades CENFISS, Consejo Editorial) |
| Contacto | Doc del proyecto (ubicación, email), CENFISS |
| Enlaces | CENFISS, partners (externos) |
| Número individual | Doc del proyecto (Sumario, Editorial), portada, PDF |
| Artículo individual | Doc del proyecto (formato Artículo), articulo-01.pdf |
| Archivo de números | Doc del proyecto (estructura), portada-ejemplo |
| Archivo de artículos | Doc del proyecto (formato Artículo), article-placeholder |
| Noticias | Doc del proyecto (CENFISS presente, Misceláneas) |
| Header / Footer | logo-revista, logo-cenfiss, doc del proyecto (estructura de nav) |

---

## 9. Acciones pendientes

| Acción | Responsable | Estado |
|--------|-------------|--------|
| Crear `politicas-editorial.pdf` | Editorial | Pendiente |
| Crear `solicitud-publicacion-declaracion-etica.pdf` | Editorial | Pendiente |
| Crear `instrumento-arbitraje.pdf` | Editorial | Pendiente |
| Añadir `portada-ejemplo.png` (portada de número) | Diseño | Pendiente |
| Añadir `flyer.jpg` (eventos CENFISS) | CENFISS | Pendiente |
| Añadir `banner-main.png` (hero) | Diseño | Pendiente |
| Construir `404.html` | Dev | Pendiente |
| Construir `search.html` | Dev | Pendiente |

---

## 10. Regla de trazabilidad

Cada página/sección en `04-screen-map` debe trazarse a al menos una fuente de contenido. Si se crea contenido en WordPress sin fuente, documentar la decisión en este archivo.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
