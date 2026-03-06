# Revista de Filosofía LOGO ET SPES — Content Source Inventory

**Canonical list of content sources**

Single source for what content exists, where it lives and how it maps to the site.

**Depends on:** `03-wordpress-content-model`, `04-screen-map`  
**Reference:** `09-ui-copy-sheet`, `15-assets-strategy`

---

## 1. Document sources

| File | Content | Maps to |
|------|---------|---------|
| `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` | Project document: structure, policies, norms, forms, portada model | All pages |
| — Sección Primera (Estructura Básica) | Portada, Sumario, Editorial, Artículos, Autores, Árbitros, Misceláneas | Home, Single issue, Single article, Archive |
| — Sección Segunda (Descripción de Partes) | Enfoque, Alcance, Objetivos, Políticas, Normas de Publicación, Normas de Ética, Formularios | Acerca, Normas, Ética, Políticas, Enviar colaboración |
| — Sección Tercera | Simulación teórica del sitio | Wireframes, structure |
| — Formularios (Solicitud, Arbitraje) | Form templates | Enviar colaboración, Normas |
| — Origen del nombre Logo et Spes | Prof. Luis Felipe Ramírez | Acerca |
| `assets/pdf/normas-publicacion.pdf` | Normas de publicación (extract/adapt) | Normas |
| `assets/pdf/politicas-editorial.pdf` | Políticas editoriales | Políticas |

---

## 2. Image sources

| File / folder | Content | Maps to |
|---------------|---------|---------|
| `assets/img/logo-revista.svg` | Revista logo | Header, all pages |
| `assets/img/logo-cenfiss.svg` | CENFISS logo | Footer, Enlaces, institutional refs |
| `assets/img/favicon.svg` | Favicon | All pages |
| `assets/img/article-placeholder.svg` | Article fallback | Archive articles, article cards |
| `assets/img/avatar-default.svg` | Author avatar fallback | Single article, author cards |
| `assets/img/placeholder-banner.jpg` | Banner placeholder | Home carousel |

**To add:** `portada-ejemplo.png` (issue cover), `flyer.jpg` (CENFISS events), `banner-main.png` (hero).  
**Source for real content:** Issue covers from editorial; author photos from authors; event images from CENFISS.

---

## 3. Video / audio

| Source | Content | Implementation |
|--------|---------|-----------------|
| (None currently) | — | — |

**Rule:** If added, provide subtitles or transcript for informative content. See `19-accessibility-standards`.

---

## 4. Brand assets

| File | Use |
|------|-----|
| `assets/img/logo-revista.svg` | Header, favicon, digital materials |
| `assets/img/logo-cenfiss.svg` | Footer, Enlaces, institutional |
| `assets/img/favicon.svg` | Favicon |
| `content-source/PROYECTO...md` | Identity reference (02), structure |

---

## 5. PDF sources

| File | Content | Maps to |
|------|---------|---------|
| `assets/pdf/normas-publicacion.pdf` | Normas de publicación | Normas page |
| `assets/pdf/articulo-01.pdf` | Sample article | Single article |
| `assets/pdf/numero-v12n2-2025.pdf` | Sample issue | Single issue |
| (To create) `politicas-editorial.pdf` | Políticas editoriales | Políticas page |
| (To create) `solicitud-publicacion-declaracion-etica.pdf` | Form per project doc | Enviar colaboración |
| (To create) `instrumento-arbitraje.pdf` | Form per project doc | Normas |

---

## 6. Traceability matrix

| Page / Section | Source(s) |
|----------------|----------|
| Home | Project doc (Presentación, Estructura), portada-ejemplo, flyer |
| Acerca | Project doc (Enfoque, Alcance, Objetivos, Origen del nombre) |
| Normas | Project doc (Normas de Publicación, Ética), normas-publicacion.pdf |
| Ética | Project doc (Normas de Ética) |
| Políticas | Project doc (Políticas Editorial), politicas-editorial.pdf |
| Enviar colaboración | Project doc (Formularios, instrucciones) |
| Comité Editorial | Project doc (CENFISS autoridades, Consejo Editorial) |
| Contacto | Project doc (ubicación, email), CENFISS |
| Enlaces | CENFISS, partners (external) |
| Single issue | Project doc (Sumario, Editorial), portada, PDF |
| Single article | Project doc (Artículo format), articulo-01.pdf |
| Archive issues | Project doc (estructura), portada-ejemplo |
| Archive articles | Project doc (Artículo format), article-placeholder |
| Noticias | Project doc (CENFISS presente, Misceláneas) |
| Header / Footer | logo-revista, logo-cenfiss, project doc (nav structure) |

---

## 7. Traceability rule

Each page/section in `04-screen-map` should trace to at least one content source. If content is created in WordPress without a source, document the decision in this file.

---

**Version:** 1.0  
**Project:** Revista de Filosofía LOGO ET SPES
