# Revista de Filosofía LOGO ET SPES — Estrategia de Assets

**Imágenes, fuentes, iconos y archivos estáticos**

Define cómo se obtienen, optimizan y usan los assets.

**Depende de:** `02-corporate-identity`, `16-content-source-inventory`  
**Referencia:** `14-css-architecture`, `12-theme-file-structure`

---

## 1. Estrategia de imágenes

### Estructura de carpetas

```
assets/img/
├── logos/
├── portada-numeros/
├── autores/
├── placeholders/
└── banners/
```

Ejemplo: `assets/img/logos/logo-revista.svg`, `assets/img/placeholders/article-placeholder.svg`, `assets/img/portada-numeros/portada-vol-12-n2.jpg`.

### Fuentes

- `content-source/` — El documento del proyecto puede referenciar imágenes de portada, assets de marca.
- `assets/img/` — Assets actuales de la maqueta. Trazar a la fuente cuando sea posible.
- Sin imágenes inventadas; todas trazables a fuente o documentadas como placeholder.
- **Trazabilidad:** Todo asset publicado debe existir en el inventario de `16-content-source-inventory`.

### Inventario actual

| Archivo | Uso | Fuente |
|---------|-----|--------|
| `logos/logo-revista.svg` | Header, marca | Marca / 02 |
| `logos/logo-cenfiss.svg` | Referencia institucional | CENFISS |
| `logos/favicon.svg` | Favicon | Derivado del logo |
| `placeholders/portada-ejemplo.png` | Placeholder de portada de número | Placeholder (maqueta) |
| `placeholders/article-placeholder.svg` | Fallback de tarjeta de artículo | Placeholder (maqueta) |
| `placeholders/avatar-default.svg` | Fallback de avatar de autor | Placeholder (maqueta) |
| `placeholders/placeholder-banner.jpg` | Carrusel de banner | Placeholder (maqueta) |
| `placeholders/banner-main.png` | Hero/banner | Placeholder (maqueta) |
| `banners/flyer.jpg` | Eventos CENFISS (carrusel) | CENFISS |

### Optimización

- **Formato:** WebP con fallback JPEG/PNG cuando haga falta. SVG para logos, iconos.
- **Compresión:** Equilibrar calidad y tamaño. Portadas de números: máx 1200px de ancho para visualización.
- **Responsive:** `srcset` con 400w, 800w, 1200w para portadas de números y banners.
- **Carga diferida:** Usar `loading="lazy"` en imágenes fuera del viewport inicial.
- **Layout:** Usar `width` y `height` en etiquetas `<img>` para evitar CLS.
- **Texto alt:** Siempre rellenar. Decorativas: `alt=""`. Portadas de números: alt descriptivo, ej. "Portada del volumen 12 número 2 de LOGO ET SPES (2025)". Ver `09-ui-copy-sheet`, `19-accessibility-standards`.

### Nomenclatura

- Minúsculas, kebab-case, sin acentos ni espacios
- Descriptivo: `portada-vol-12-n2-2025.jpg`, `logo-revista.svg`, `flyer-diplomado-2025.jpg`

---

## 2. Tipografía

### Fuentes

| Uso | Familia | Fuente |
|-----|---------|--------|
| Títulos | Georgia | Sistema |
| Fallback títulos | Times New Roman | Sistema |
| Cuerpo | System UI stack (-apple-system, Segoe UI, Roboto, Arial) | Sistema |

### Implementación

- **Sin archivos de fuentes custom.** Usa fuentes del sistema. Sin carga externa de fuentes.
- **Privacidad y rendimiento:** Sin CDN de fuentes ni peticiones externas.
- **Fallback:** `serif` para títulos, `sans-serif` para cuerpo.
- **Futuro:** Si se adoptan fuentes custom, auto-hospedar woff2, subset latino, `font-display: swap`.

---

## 3. Iconos

- **SVG preferido** (escalable, ligero). Actual: favicon, placeholders.
- **Layout:** Incluir `width` y `height` explícitos para evitar layout shift.
- **Inline:** Preferir SVG inline cuando el icono participe en el flujo del contenido.
- **Decorativos:** `aria-hidden="true"`, `focusable="false"`
- **Con significado:** Proporcionar nombre accesible o ocultar de tecnología asistiva según corresponda.
- **Evitar fuentes de iconos** si es posible; SVG inline o sprite.

---

## 4. Favicon

- **Archivos:** `favicon.svg`, `favicon-16.png`, `favicon-32.png`, `apple-touch-icon.png`, `site.webmanifest`
- **Derivado del logo.** SVG cubre navegadores modernos; PNG para legacy.
- **Tamaños:** 16×16, 32×32, 180×180 (Apple), 192×192
- **Uso:** `<link rel="icon" type="image/svg+xml" href="assets/img/logos/favicon.svg">`

---

## 5. PDFs

| Archivo | Uso |
|---------|-----|
| `normas-publicacion.pdf` | Descarga en página Normas |
| `politicas-editorial.pdf` | Descarga en página Políticas |
| `solicitud-publicacion.pdf` | Formulario Enviar colaboración |
| `declaracion-etica.pdf` | Declaración ética (Enviar colaboración) |
| `instrumento-arbitraje.pdf` | Normas (formulario de arbitraje) |
| `rles-v12-n2-articulo-01.pdf` | Artículo de ejemplo (nombre estable para citabilidad) |
| `rles-v12-n2-2025.pdf` | Número de ejemplo (nombre estable para citabilidad) |

**Regla de citabilidad:** Los PDFs deben mantener nombre estable para citabilidad. Ejemplo: `rles-v12-n2-articulo-01.pdf`.

**Regla de ubicación:** `assets/pdf/` solo para maqueta. En WordPress los PDFs deben subirse a Media Library. Evita perder archivos al actualizar el tema.

**Seguridad:** No servir archivos PDF ejecutables o con macros.

---

## 6. Ubicaciones de archivos

| Fase | Ubicación |
|------|-----------|
| Maqueta (estático) | `assets/img/`, `assets/pdf/`, `assets/css/`, `assets/js/`, `assets/fonts/` |
| WordPress | `theme-revistalogos/assets/img/`, `theme-revistalogos/assets/fonts/`. PDFs en Media Library. |

### Estructura completa

```
assets/
├── css/
├── js/
├── img/
│   ├── logos/
│   ├── portada-numeros/
│   ├── autores/
│   ├── placeholders/
│   └── banners/
├── fonts/
└── pdf/          (solo maqueta; WordPress: Media Library)
```

---

## 7. Reglas de no hacer

- No añadir imágenes sin documentar la fuente en 16.
- No usar imágenes grandes sin comprimir; optimizar antes de commit.
- No incrustar imágenes base64 en CSS/HTML para assets no triviales.
- No usar CDNs de fuentes externos sin decisión explícita del proyecto.
- No servir archivos PDF ejecutables o con macros.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
