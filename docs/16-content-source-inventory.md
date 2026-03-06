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
├── img/
│   ├── logos/
│   │   ├── logo-revista.svg
│   │   └── logo-cenfiss.svg
│   ├── portada-numeros/
│   ├── autores/
│   ├── placeholders/
│   │   ├── article-placeholder.svg
│   │   ├── avatar-default.svg
│   │   └── placeholder-banner.jpg
│   └── banners/
└── pdf/
    ├── normas-publicacion.pdf
    ├── politicas-editoriales.pdf
    ├── rles-v12-n2-articulo-01.pdf
    └── rles-v12-n2-2025.pdf
```

**Regla:** `content-source/` es canónico. No modificar; usar tal cual está escrito. content-source es la fuente editorial; WordPress es el sistema de publicación. Esto evita que el CMS se convierta en la fuente primaria. Los assets en `assets/` son copias para implementación. Ver `.cursor/rules/content-source-priority.mdc`.

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
| `assets/pdf/politicas-editoriales.pdf` | Políticas editoriales | Políticas |

---

## 3. Fuentes de imágenes

| Archivo / carpeta | Contenido | Mapea a |
|-------------------|-----------|---------|
| `assets/img/logos/logo-revista.svg` | Logo de la revista | Header, todas las páginas |
| `assets/img/logos/logo-cenfiss.svg` | Logo CENFISS | Footer, Enlaces, referencias institucionales |
| `assets/img/logos/favicon.svg` | Favicon | Todas las páginas |
| `assets/img/placeholders/article-placeholder.svg` | Fallback de artículo | Archivo de artículos, tarjetas de artículo |
| `assets/img/placeholders/avatar-default.svg` | Fallback de avatar de autor | Artículo individual, tarjetas de autor |
| `assets/img/placeholders/placeholder-banner.jpg` | Placeholder de banner | Carrusel del Home |

**Por añadir:** `placeholders/portada-ejemplo.png` (portada de número), `banners/flyer.jpg` (eventos CENFISS), `placeholders/banner-main.png` (hero).  
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
| `assets/img/logos/logo-revista.svg` | Header, favicon, materiales digitales |
| `assets/img/logos/logo-cenfiss.svg` | Footer, Enlaces, institucional |
| `assets/img/logos/favicon.svg` | Favicon |
| `content-source/PROYECTO...md` | Referencia de identidad (02), estructura |

---

## 6. Fuentes PDF

| Archivo | Contenido | Mapea a |
|---------|-----------|---------|
| `assets/pdf/normas-publicacion.pdf` | Normas de publicación | Página Normas |
| `assets/pdf/rles-v12-n2-articulo-01.pdf` | Artículo de ejemplo | Artículo individual |
| `assets/pdf/rles-v12-n2-2025.pdf` | Número de ejemplo | Número individual |
| (Por crear) `politicas-editoriales.pdf` | Políticas editoriales | Página Políticas |
| (Por crear) `solicitud-publicacion.pdf` | Formulario según documento del proyecto | Enviar colaboración |
| (Por crear) `declaracion-etica.pdf` | Declaración ética | Enviar colaboración |
| (Por crear) `instrumento-arbitraje.pdf` | Formulario según documento del proyecto | Normas |

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
| Home | content-source/... (Presentación, Estructura), assets/img/placeholders/portada-ejemplo.png, assets/img/banners/flyer.jpg |
| Acerca | Documento del proyecto (Enfoque, Alcance, Objetivos, Origen del nombre) |
| Normas | Documento del proyecto (Normas de Publicación, Ética), assets/pdf/normas-publicacion.pdf |
| Ética | Documento del proyecto (Normas de Ética) |
| Políticas | Documento del proyecto (Políticas Editorial), assets/pdf/politicas-editoriales.pdf |
| Enviar colaboración | Documento del proyecto (Formularios, instrucciones) |
| Comité Editorial | Documento del proyecto (autoridades CENFISS, Comité Editorial) |
| Contacto | Documento del proyecto (ubicación, email), CENFISS |
| Enlaces | CENFISS, partners (externos) |
| Número individual | Documento del proyecto (Sumario, Editorial), portada, PDF |
| Artículo individual | Documento del proyecto (formato Artículo), assets/pdf/rles-v12-n2-articulo-01.pdf |
| Archivo de números | Documento del proyecto (estructura), assets/img/placeholders/portada-ejemplo.png |
| Archivo de artículos | Documento del proyecto (formato Artículo), assets/img/placeholders/article-placeholder.svg |
| Noticias | Documento del proyecto (CENFISS presente, Misceláneas) |
| Header / Footer | assets/img/logos/logo-revista.svg, assets/img/logos/logo-cenfiss.svg, Documento del proyecto (estructura de nav) |

---

## 9. Acciones pendientes

| Acción | Responsable | Estado |
|--------|-------------|--------|
| Crear `politicas-editoriales.pdf` | Editorial | Pendiente |
| Crear `solicitud-publicacion.pdf`, `declaracion-etica.pdf` | Editorial | Pendiente |
| Crear `instrumento-arbitraje.pdf` | Editorial | Pendiente |
| Añadir portada real del primer número publicado | Editorial | Pendiente |
| Añadir `placeholders/portada-ejemplo.png` (portada de número) | Diseño | Pendiente |
| Añadir `banners/flyer.jpg` (eventos CENFISS) | CENFISS | Pendiente |
| Añadir `placeholders/banner-main.png` (hero) | Diseño | Pendiente |
| Construir `404.html` | Dev | Pendiente |
| Construir `search.html` | Dev | Pendiente |

---

## 10. Reglas de trazabilidad

- Cada página/sección en `04-screen-map` debe trazarse a al menos una fuente de contenido. Si se crea contenido en WordPress sin fuente, documentar la decisión en este archivo.
- Todo asset visual o PDF publicado debe estar registrado en este inventario.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
