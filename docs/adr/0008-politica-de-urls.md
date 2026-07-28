# ADR 0008: Política de URLs

## Estado

Aceptada

## Fecha

2026-07-23

## Contexto

Existen dos «realidades» de URL en el proyecto:

- **Sitio estático actual:** URLs planas, sin extensión (`/page-acerca`, `/single-article`, home `/`); prototipo desechable y en `noindex` (ADR 0004).
- **Destino WordPress ([doc 11](../11-url-tree.md) / modelo de contenido):** el árbol `/revista/numeros/{slug}/`, `/revista/articulos/{slug}/`, `/acerca/`, etc.

Cambiar URLs tras el lanzamiento afecta SEO, enlaces externos, citaciones y Search Console, por lo que la política debe fijarse una vez y mantenerse estable. Además, los `<link rel="canonical">` y `og:url` de la maqueta aún apuntan a `refo44.github.io/demo-revistalogos` con `.html` (obsoletos).

Principio rector de esta decisión: **KISS / YAGNI — usar la solución nativa de WordPress más sencilla y no construir lo que no se necesita.**

## Decisión

### 1. Con barra final (trailing slash) — el valor por defecto de WordPress

La política canónica es **con barra final**, que es el comportamiento **por defecto** de WordPress.

- Se selecciona la estructura estándar **«Nombre de la entrada»** (`/%postname%/`) en Ajustes → Enlaces permanentes. WordPress genera y canonicaliza las URLs con barra final de forma nativa (páginas, archivos de CPT, paginación `/page/2/`, feeds `/feed/`).
- **No** se escribe código propio de rewrite ni de redirección: se usa el default del CMS. Es la opción con menos casos límite.
- El **prefijo de estructura** de los CPT (`/revista/numeros`, `/revista/articulos`, `/revista/autores`) se define en los argumentos `rewrite` de los CPT del plugin `revistalogos-core` (ADR 0005); la barra final la añade WordPress.
- **Coherente con doc 11**, que ya usa barra final: no requiere cambios de documentación.

### 2. Enlaces internos: funciones nativas en el theme; el prototipo se deja como está

- En el theme, los enlaces internos usan **siempre `home_url()` / `get_permalink()`**; nunca rutas escritas a mano. Es la forma nativa de WordPress y produce las URLs correctas con barra final.
- El **sitio estático** conserva sus enlaces actuales (`href="archive-issue.html"`, etc.): ya funcionan con el `.htaccess` vigente, el prototipo es desechable y sus enlaces **no** migran (WordPress genera los suyos). **No** se hace conversión de enlaces del estático (YAGNI): con barra final no aplica el problema de enlaces relativos que sí exigiría una política sin barra.

### 3. Canónicos y dominio

- Estructura canónica de producción: el árbol de **doc 11** en el dominio **`https://logo-et-spes.cenfiss.net`**.
- El sitio está en `noindex` hasta el lanzamiento (ADR 0004) y las URLs planas del prototipo son desechables, así que la **pasada definitiva de canónicos** (a las URLs reales de WordPress) se hace **al construir el theme**.
- **Ahora** solo se sanea el dominio obsoleto: reemplazar `refo44.github.io/demo-revistalogos` por `logo-et-spes.cenfiss.net` para que nada induzca a error.

### 4. Estabilidad

- Cambiar la estructura de URL tras el lanzamiento exige: 301 en `.htaccess`, actualización de `sitemap.xml`, reindexación en Search Console e incremento **MAJOR** (`VERSION.md`).
- Redirects 301 solo para **legacy** documentado.

### 5. Transición de `.htaccess` a WordPress

El `.htaccess` del prototipo estático y el de WordPress son distintos y no se mezclan:

- **Prototipo (ahora):** conserva sus reglas de archivos planos (servir `.html` sin extensión, eliminar barra final, redirects legacy). No se toca salvo correcciones puntuales (p. ej. el saneo de la regla que mutilaba `*index.html`, corregido el 2026-07-23).
- **WordPress (al lanzar):** WordPress **reemplaza** el `.htaccess` por su bloque estándar (`# BEGIN WordPress … RewriteRule . /index.php [L] … # END WordPress`) y enruta todo por `index.php`. La barra final la gestiona WordPress de forma nativa (`redirect_canonical`), no `.htaccess`.
- **Gotcha 1:** la regla que **elimina la barra final** del prototipo **no** debe sobrevivir a WordPress; entraría en conflicto con la canonicalización con barra final de WordPress y podría causar bucles de redirección.
- **Gotcha 2:** las reglas de archivos planos (`.html`) dejan de aplicar (WordPress no sirve archivos `.html`).
- **Conservar deliberadamente:** los redirects **legacy** que sigan siendo válidos (p. ej. `/blog` → índice de noticias), actualizados al destino con barra final (`/noticias/`).

## Alternativas consideradas

| Alternativa | Motivo de descarte |
| ----------- | ------------------ |
| Sin barra final (config admin) | Se evaluó y se descartó por KISS/YAGNI: desvía del default de WordPress, tiene más casos límite (paginación/feeds) y **obligaría** a convertir los enlaces del estático a absolutos de raíz para evitar el fallo de enlaces relativos. Con barra final nada de eso hace falta. |
| Código propio de rewrite/redirección para el slash | Innecesario: el default de WordPress ya hace el trabajo. |
| Convertir los enlaces del prototipo estático a absolutos de raíz | YAGNI: los enlaces actuales funcionan con `.htaccess`, el estático es desechable y no migra; sin barra final sería necesario, con barra final no. |
| Fijar canónicos finales en el prototipo plano | Apuntarían a URLs que aún no existen; con `noindex`, la pasada definitiva se hace al construir el theme. |

## Consecuencias

**Beneficios:**

- Máxima sencillez: se usa el default de WordPress, sin código propio de URLs ni configuración especial.
- Coherente con doc 11 (sin cambios de documentación).
- Elimina la tarea de conversión de enlaces del estático (YAGNI).
- Menos casos límite que la variante sin barra.

**Riesgos / costes:**

- El prototipo estático (sin barra, plano) y WordPress (con barra) difieren; es **inofensivo**: el estático es desechable, está en `noindex` y sus URLs no migran.
- Queda pendiente el saneo de canónicos `github.io` (inmediato) y la pasada canónica definitiva al construir el theme.

**Trabajo futuro:**

- Configurar Enlaces permanentes = «Nombre de la entrada» al montar WordPress.
- Sustituir los canónicos `github.io` por `logo-et-spes.cenfiss.net` en la maqueta (saneo inmediato); pasada canónica definitiva al construir el theme.

## Referencias

- `docs/11-url-tree` (árbol canónico; ya con barra final, sin cambios)
- ADR 0004 (noindex hasta el lanzamiento), ADR 0005 (rewrite de CPT en el plugin), ADR 0006 (preferir nativo)
- Contraste: *Camino del Dharma* ADR 0008 eligió **sin** barra final; aquí se prioriza el default de WordPress (KISS/YAGNI).
- `VERSION.md` (cambios de URL = MAJOR), `.htaccess`, `sitemap.xml`
