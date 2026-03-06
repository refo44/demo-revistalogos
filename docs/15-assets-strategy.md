# Revista de Filosofía LOGO ET SPES — Estrategia de Assets

**Imágenes, fuentes, iconos y archivos estáticos**

Define cómo se obtienen, optimizan y usan los assets.

**Depende de:** `02-corporate-identity`, `16-content-source-inventory`  
**Referencia:** `14-css-architecture`, `12-theme-file-structure`

---

## 1. Estrategia de imágenes

### Fuentes

- `content-source/` — El documento del proyecto puede referenciar imágenes de portada, assets de marca.
- `assets/img/` — Assets actuales de la maqueta. Trazar a la fuente cuando sea posible.
- Sin imágenes inventadas; todas trazables a fuente o documentadas como placeholder.

### Inventario actual

| Archivo | Uso | Fuente |
|---------|-----|--------|
| `logo-revista.svg` | Header, marca | Marca / 02 |
| `logo-cenfiss.svg` | Referencia institucional | CENFISS |
| `favicon.svg` | Favicon | Derivado del logo |
| `portada-ejemplo.png` | Placeholder de portada de número | Maqueta |
| `article-placeholder.svg` | Fallback de tarjeta de artículo | Maqueta |
| `avatar-default.svg` | Fallback de avatar de autor | Maqueta |
| `placeholder-banner.jpg` | Carrusel de banner | Maqueta |
| `flyer.jpg` | Eventos CENFISS (carrusel) | CENFISS |
| `banner-main.png` | Hero/banner | Maqueta |

### Optimización

- **Formato:** WebP con fallback JPEG/PNG cuando haga falta. SVG para logos, iconos.
- **Compresión:** Equilibrar calidad y tamaño. Portadas de números: máx ~800px de ancho para visualización.
- **Responsive:** `srcset` para portadas de números, banners cuando existan varios tamaños.
- **Carga diferida:** Para imágenes debajo del pliegue (tarjetas de artículos, grids de archivo).
- **Texto alt:** Siempre rellenar. Decorativas: `alt=""`. Ver `09-ui-copy-sheet`, `19-accessibility-standards`.

### Nomenclatura

- Minúsculas, kebab-case
- Descriptivo: `portada-vol-12-n2-2025.jpg`, `logo-revista.svg`, `flyer-diplomado-2025.jpg`

---

## 2. Tipografía

### Fuentes

| Uso | Familia | Fuente |
|-----|---------|--------|
| Display / títulos | Georgia, Times New Roman | Sistema |
| Cuerpo | -apple-system, Segoe UI, Roboto, Arial | Sistema |

### Implementación

- **Sin archivos de fuentes custom.** Usa fuentes del sistema. Sin carga externa de fuentes.
- **Privacidad y rendimiento:** Sin CDN de fuentes ni peticiones externas.
- **Fallback:** `serif` para títulos, `sans-serif` para cuerpo.
- **Futuro:** Si se adoptan fuentes custom, auto-hospedar woff2, subset latino, `font-display: swap`.

---

## 3. Iconos

- **SVG preferido** (escalable, ligero). Actual: favicon, placeholders.
- **Decorativos:** `aria-hidden="true"`, `focusable="false"`
- **Con significado:** Proporcionar nombre accesible o ocultar de tecnología asistiva según corresponda.
- **Evitar fuentes de iconos** si es posible; SVG inline o sprite.

---

## 4. Favicon

- **Archivo:** `favicon.svg` — Derivado del logo
- **Tamaños:** SVG cubre todo. Para legacy: 16×16, 32×32, 180×180 (Apple), 192×192
- **Uso:** `<link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">`

---

## 5. PDFs

| Archivo | Uso |
|---------|-----|
| `normas-publicacion.pdf` | Descarga en página Normas |
| `politicas-editorial.pdf` | Descarga en página Políticas |
| `solicitud-publicacion-declaracion-etica.pdf` | Formulario Enviar colaboración (según doc del proyecto) |
| `instrumento-arbitraje.pdf` | Normas (formulario de arbitraje; según doc del proyecto) |
| `articulo-01.pdf` | Artículo de ejemplo |
| `numero-v12n2-2025.pdf` | Número de ejemplo |

**Regla:** Los PDFs en la fase WordPress viven en la media library. Los enlaces desde las páginas apuntan a la URL del adjunto.

---

## 6. Ubicaciones de archivos

| Fase | Ubicación |
|------|-----------|
| Maqueta (estático) | `assets/img/`, `assets/pdf/`, `assets/css/`, `assets/js/` |
| WordPress | `revistalogos/assets/img/`, `revistalogos/assets/pdf/` (o media library para PDFs) |

---

## 7. Reglas de no hacer

- No añadir imágenes sin documentar la fuente en 16.
- No usar imágenes grandes sin comprimir; optimizar antes de commit.
- No incrustar imágenes base64 en CSS/HTML para assets no triviales.
- No usar CDNs de fuentes externos sin decisión explícita del proyecto.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
