# Revista de Filosofía LOGO ET SPES — Estructura de Archivos del Tema

**Arquitectura de archivos del tema WordPress**

Define la estructura de archivos del tema: qué plantillas existen y qué partes se reutilizan. Rutas oficiales en 11; este documento define cómo se renderizan.

**Depende de:** `03-wordpress-content-model`, `04-screen-map`, `11-url-tree`, `05-information-architecture-navigation`  
**Referencia:** `02-corporate-identity`, `06-wireframes`

---

## 1. Plantillas por función

| Función | Plantilla |
|---------|-----------|
| Home | `front-page.php` |
| Acerca | `page-acerca.php` |
| Contacto | `page-contacto.php` |
| Normas | `page-normas.php` |
| Ética | `page-etica.php` |
| Políticas | `page-politicas.php` |
| Enviar colaboración | `page-enviar-colaboracion.php` |
| Comité Editorial | `page-comite.php` |
| Enlaces | `page-enlaces.php` |
| Páginas fallback | `page.php` |
| Fallback global | `index.php` |
| Login | `page-login.php` o redirección a `wp-login.php` |
| Panel de autor | `page-mi-cuenta.php` |

---

## 2. Custom Post Types

| CPT | Archivo | Individual |
|-----|---------|------------|
| issue (numeros) | `archive-issue.php` | `single-issue.php` |
| article (articulos) | `archive-article.php` | `single-article.php` |
| author (autores) | `archive-author.php` | `single-author.php` |
| submission (envios) | — | Solo admin (CPT privado) |

---

## 3. Blog (entradas)

| Vista | Plantilla |
|------|-----------|
| Índice de Noticias | `home.php` |
| Entrada individual | `single.php` |

---

## 4. Estados

| Estado | Archivo |
|--------|---------|
| No encontrado | `404.php` |
| Resultados de búsqueda | `search.php` |

## 4.0 Área privada (envío de autores)

| Pantalla | Implementación |
|----------|----------------|
| Login | `page-login.php` o `wp-login.php` |
| Panel de autor | `page-mi-cuenta.php` (shortcodes o plantilla custom) |
| Nuevo envío, Mis envíos | Shortcodes o páginas custom; CPT submission gestionado en admin |

---

## 4.1 Archivos de taxonomía (artículos filtrados)

Rutas `/articulos/seccion/{section}/` y `/articulos/tipo/{type}/` (según `11-url-tree`).

| Opción | Plantilla | Uso |
|--------|-----------|-----|
| A | `archive-article.php` | Una sola plantilla maneja todo. Usar `get_queried_object()` para detectar sección/tipo y ajustar título. |
| B | `taxonomy-section.php` | Maquetación custom al filtrar por sección. Fallback a lógica archive-article. |
| C | `taxonomy-article_type.php` | Maquetación custom al filtrar por tipo. |

**Recomendación:** Opción A. Mantener `archive-article.php` como plantilla única de archivo; recibe la query principal ya filtrada por taxonomía. Añadir título/descripción condicional según `is_tax()`.

---

## 5. Partes reutilizables

| Archivo | Función |
|---------|---------|
| `header.php` | Entrada para `get_header()`; carga `parts/header.php` |
| `footer.php` | Entrada para `get_footer()`; carga `parts/footer.php` |
| `parts/header.php` | Header (logo + navegación) |
| `parts/footer.php` | Footer |
| `parts/breadcrumbs.php` | Ruta de migas de pan (Inicio → ruta) |
| `parts/issue-card.php` | Tarjeta de número (portada, título, meta, PDF, Ver contenido) |
| `parts/article-card.php` | Tarjeta de artículo (título, autores, resumen, PDF, Leer más) |
| `parts/hero-issue.php` | Bloque hero del número actual (Home) |
| `parts/metadata-box.php` | Metadatos del artículo (autores, DOI, palabras clave, cita) |
| `parts/toc.php` | Tabla de contenidos (número individual) |
| `parts/pagination.php` | Anterior/siguiente, números de página (archivos, búsqueda) |
| `parts/sidebar-card.php` | Bloque de sidebar (enlaces relacionados, info) |

---

## 6. Árbol del tema

```
revistalogos/
├── style.css              (solo metadatos, requerido por WP)
├── theme.json             (tokens de diseño: paleta, tipografía)
├── screenshot.png
├── functions.php
├── header.php
├── footer.php
├── index.php
├── front-page.php
├── home.php
├── single.php
├── search.php
├── page.php
├── page-acerca.php
├── page-contacto.php
├── page-normas.php
├── page-etica.php
├── page-politicas.php
├── page-enviar-colaboracion.php
├── page-comite.php
├── page-enlaces.php
├── archive-issue.php
├── archive-article.php
├── single-issue.php
├── single-article.php
├── comments.php            (mínimo; comentarios deshabilitados para artículos)
├── 404.php
├── inc/
│   ├── cpt-issue.php
│   ├── cpt-article.php
│   ├── cpt-author.php
│   ├── cpt-submission.php
│   ├── taxonomies.php
│   └── template-tags.php
├── assets/
│   ├── css/
│   │   ├── main.css       (entrada: importa o concatena)
│   │   ├── tokens.css
│   │   ├── base.css
│   │   ├── layout.css
│   │   ├── components.css
│   │   ├── pages.css
│   │   └── utilities.css
│   ├── js/
│   │   └── main.js
│   ├── img/
│   │   ├── logo-revista.svg
│   │   ├── logo-cenfiss.svg
│   │   ├── favicon.svg
│   │   └── ...
│   └── pdf/               (o media library)
│       └── ...
└── parts/
    ├── header.php
    ├── footer.php
    ├── breadcrumbs.php
    ├── issue-card.php
    ├── article-card.php
    ├── hero-issue.php
    ├── metadata-box.php
    ├── toc.php
    ├── pagination.php
    └── sidebar-card.php
```

---

## 7. Estrategia CSS

| Archivo | Rol |
|---------|-----|
| `style.css` | Solo metadatos del tema (requerido por WordPress). Sin estilos. |
| `theme.json` | Tokens de diseño: paleta, tipografía, espaciado (editor de bloques). |
| `assets/css/main.css` | Punto de entrada. Importa: tokens, base, layout, components, pages, utilities. |
| `assets/css/tokens.css` | Tokens de diseño (colores, fuentes, espaciado). |
| `assets/css/base.css` | Resets, tipografía base. |
| `assets/css/layout.css` | Contenedor, grid, estructura. |
| `assets/css/components.css` | Botones, tarjetas, formularios, nav. |
| `assets/css/pages.css` | Estilos específicos de página. |
| `assets/css/utilities.css` | Clases de utilidad. |

Encolar `main.css` en `functions.php` vía `wp_enqueue_style`. Un solo punto de entrada.

---

## 8. inc/ (requerido)

Registro de CPTs y helpers. Mantiene `functions.php` limpio.

```
inc/
├── cpt-issue.php          (registrar CPT issue)
├── cpt-article.php        (registrar CPT article)
├── cpt-author.php          (registrar CPT author)
├── cpt-submission.php      (registrar CPT submission, privado)
├── taxonomies.php          (section, article_type, keyword)
└── template-tags.php      (funciones helper)
```

Registrar en `functions.php` vía `require_once`. Orden de carga: taxonomías después de CPTs.

---

## 8.1 comments.php

Incluir un `comments.php` mínimo aunque los comentarios estén deshabilitados. WordPress puede buscarlo; una plantilla vacía o deshabilitada evita avisos.

```php
<?php
/**
 * Plantilla de comentarios. Requerida por WordPress.
 * Comentarios deshabilitados para revista académica.
 */
if ( post_password_required() ) {
	return;
}
// Comentarios cerrados. No se necesita salida.
?>
```

---

## 9. Buenas prácticas

- **Plantillas limpias:** Markup y llamadas simples; lógica en `functions.php` o `inc/`.
- **inc/ obligatorio:** CPTs y taxonomías siempre en `inc/`; nunca inline en `functions.php`.
- **Una sola entrada CSS:** Un `main.css`; imports o paso de build para módulos.
- **Reutilizar vía parts:** `get_template_part('parts/header')`, `get_template_part('parts/issue-card')`.
- **Seguridad:** Escapar salida (`esc_html`, `esc_attr`); sanitizar entrada; nonces para formularios.
- **Sin dependencia de builders:** Evitar Elementor/page builders salvo decisión explícita.
- **Accesibilidad:** Preservar estructura semántica, enlace saltar, navegación por teclado, contraste (19).

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
