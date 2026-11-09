# Revista de Filosofía LOGOS ET SPES — Estrategia de Assets

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
| `placeholders/portada-ejemplo.jpg` | Placeholder de portada de número | Placeholder (maqueta) |
| `placeholders/article-placeholder.svg` | Fallback de tarjeta de artículo | Placeholder (maqueta) |
| `placeholders/avatar-default.svg` | Fallback de avatar de autor | Placeholder (maqueta) |
| `placeholders/placeholder-banner.jpg` | Carrusel de banner y fallback de portada de número sin thumbnail (ruta real plana: `assets/img/placeholder-banner.jpg`). Debe ser JPEG real, no data URI. | Placeholder (maqueta) |
| `placeholders/banner-main.jpg` | Hero/banner | Placeholder (maqueta) |
| `banners/flyer.jpg` | Eventos CENFISS (carrusel) | CENFISS |

### Masters de marca

El sello «LOGOS ET SPES» (ADR 0023) vive en
`wordpress/wp-content/themes/revistalogos/assets/img/`, junto al resto de
imágenes del theme. El deploy FTPS sube el theme completo, así que quedan
accesibles públicamente bajo la URL del theme. Ninguna página los carga:
el sitio usa solo los derivados de la tabla siguiente, y sus URLs llevan
`?ver=` con la versión del theme para que navegadores, redes y buscadores
pidan la imagen nueva cuando cambia el sello.

| Derivado | Uso | Formato |
|----------|-----|---------|
| `logo-revista-cabecera.png` | Logo de la cabecera (se muestra a 52 px; 40 px en móvil) | PNG 156×156 (3×) |
| `logo-revista.png` | `og:image` de la portada y `image` del JSON-LD | PNG 1024×1024, 256 colores (≈229 KB) |
| `og-twitter-card.jpg` | `twitter:image` (`summary_large_image`) | JPEG 1200×630 |
| `favicon.png`, `favicon.svg` | Favicon (copia de `favicon-32.png`) | PNG 32×32 |
| `logo-revista.svg` | Sello con fondo blanco, sin uso en plantillas | SVG con el búho en PNG de 256 colores |

En producción el favicon lo pone WordPress: desde el 2026-11-09 hay un
**Icono del sitio** (Personalizar → Identidad del sitio), subido desde
`favicon-512.png` como `icono-sitio-logos-et-spes-512.png` (medio 209).
Con un Icono del sitio, `revistalogos_favicon()` no imprime `favicon.png`
(`has_site_icon()`), así que no hay dos favicons. WordPress sirve el icono
desde sus copias de 150 y 300 px. Sin Icono del sitio (Docker local, una
instalación nueva) vuelve el `favicon.png` del theme.

`logo-revista.png` queda en 256 colores a propósito: en color verdadero pesa
≈626 KB, y WhatsApp deja de mostrar la vista previa de enlaces con imágenes
de más de ≈300 KB. La maqueta `static/` usa la misma imagen en su cabecera
y no se modifica.

| Archivo | Contenido |
|---------|-----------|
| `logo-logos-et-spes.svg`, `logo-logos-et-spes-fondo-blanco.svg` | Masters vectoriales, transparente y con fondo blanco |
| `logo-logos-et-spes-{1024,2048}.png`, `logo-logos-et-spes-fondo-blanco-{1024,2048}.png` | Exportaciones PNG |
| `favicon-32.png`, `favicon-512.png`, `apple-touch-icon-180.png` | Iconos del sitio |
| `buho-y-lampara.png` | Búho y lámpara aislados |

Estos archivos se guardan aunque el theme no los use: son el material de
marca para el diseñador y para otros medios (documentos, correo, impresos).
No se recomprimen: la compresión sin pérdida disponible ahorra ≈2 % y cada
reescritura suma otra copia completa al historial de Git.

El búho y la lámpara son una imagen raster de 972×654 px dentro del lienzo
de 1024. En los masters de 2048 px ese raster está ampliado al doble, así que
solo los anillos y los textos ganan nitidez; el búho no tiene más detalle que
en 1024. Para impresos grandes hace falta un búho de mayor resolución o
vectorizado.

Los textos del sello («REVISTA DE FILOSOFÍA» y «LOGOS ET SPES») van
convertidos a contornos con **Frank Ruhl Libre Black** (peso 900, licencia
SIL OFL 1.1), elegida por el owner como la más parecida al sello original,
cuya fuente no se conoce. Es la tipografía institucional de la marca
(`02-corporate-identity` §2.3). Al ser contornos, el SVG no depende de que la
fuente esté instalada; el diseñador la necesita solo para componer textos
nuevos con el mismo estilo.

### Optimización

- **Formato:** WebP con fallback JPEG/PNG cuando haga falta. SVG para logos, iconos.
- **Compresión:** Equilibrar calidad y tamaño. Portadas de números: máx 1200px de ancho para visualización.
- **Responsive:** `srcset` con 400w, 800w, 1200w para portadas de números y banners.
- **Carga diferida:** Usar `loading="lazy"` en imágenes fuera del viewport inicial.
- **Layout:** Usar `width` y `height` en etiquetas `<img>` para evitar CLS.
- **Texto alt:** Siempre rellenar. Decorativas: `alt=""`. Portadas de números: alt descriptivo, ej. "Portada del volumen 12 número 2 de LOGOS ET SPES (2025)". Ver `09-ui-copy-sheet`, `19-accessibility-standards`.

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
| Sello y piezas de marca (fuera del sitio) | Frank Ruhl Libre Black (900) | Google Fonts, SIL OFL 1.1; tipografía de marca (`02-corporate-identity` §2.3) |

### Implementación

- **Sin archivos de fuentes custom.** Usa fuentes del sistema. Sin carga externa de fuentes. La tipografía de marca no se carga en el sitio: solo aparece dentro de las imágenes del sello, convertida a contornos.
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
| Maqueta (estático) | `static/assets/img/`, `static/assets/pdf/`, `static/assets/css/`, `static/assets/js/`, `static/assets/fonts/` |
| WordPress | `wordpress/wp-content/themes/revistalogos/assets/img/`, `…/assets/fonts/`. PDFs en Media Library. |

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

**Versión:** 1.2  
**Proyecto:** Revista de Filosofía LOGOS ET SPES
