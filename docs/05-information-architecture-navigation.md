# Revista de Filosofía LOGO ET SPES — Arquitectura de Información y Navegación

**Mapa de navegación y enlaces activos**  
**Versión 1.0**

Este documento define qué enlaces salen de cada pantalla, a dónde van, en qué orden, y cuáles no deben existir. No describe diseño ni maquetación. Conecta el sistema con la experiencia real del visitante.

**Referencia:** `04-screen-map`, `06-wireframes`, `10-user-journey`, `09-ui-copy-sheet`

---

## 1. Principios estructurales

| Tipo de enlace | Función |
|----------------|---------|
| **Primario** | Continuar leyendo o entrar al contenido principal. Un foco claro por contexto. |
| **Secundario** | Contexto o salida ordenada del flujo actual. |
| **Prohibido** | Ruido que rompe el recorrido. |

**Regla central:** Cada pantalla debe ofrecer un siguiente paso o una salida clara. Nunca un laberinto.

---

## 2. Navegación global

**Regla de cantidad:** Menú de 4–6 ítems recomendado. Agrupar bajo Revista para respetar la arquitectura.

### Header

| Enlace | Destino |
|--------|---------|
| Inicio | Página de inicio |
| Revista | Subnavegación (Número actual, Números publicados, Artículos, Autores) |
| Normas | Página Normas |
| Enviar colaboración | Página de envío |
| Noticias | Índice del blog |
| Acerca | Página Acerca |
| Contacto | Página de contacto |
| CENFISS ↗ | cenfiss.net (externo) |

**Nota:** CENFISS es un enlace institucional externo y abre en una nueva pestaña.

### Subnavegación Revista

| Enlace | Destino |
|--------|---------|
| Número actual | Número actual (single-issue) |
| Números publicados | Archivo de números |
| Artículos | Archivo de artículos |
| Autores | Archivo de autores |

### Footer

| Enlace | Destino |
|--------|---------|
| Archivo de números | Archivo de números |
| Artículos | Archivo de artículos |
| Normas | Página Normas |
| Ética editorial | Página Ética |
| Políticas | Página Políticas |
| Comité editorial | Página Comité |
| Contacto | Página de contacto, web CENFISS, email |
| Licencia Creative Commons | Licencia CC (externo) |
| Privacidad | Página de privacidad |
| CENFISS | Sitio institucional (externo) |

---

## 3. Enlaces por pantalla

| Pantalla | Enlace(s) primario(s) | Enlace(s) secundario(s) |
|----------|----------------------|--------------------------|
| Inicio | Ver número actual, Artículos recientes | Enviar colaboración, Normas, Acerca |
| Número actual (single-issue) | Enlaces a artículos en TOC, Descarga PDF | Archivo de números, Inicio (migas de pan) |
| Números publicados | Cada tarjeta de número → single-issue | Inicio, Enviar colaboración |
| Artículo | Leer artículo | Descargar PDF, Número padre, Autor, Sección |
| Archivo de artículos | Cada tarjeta de artículo → single-article | Filtrar por sección/tipo, Inicio |
| Enviar colaboración | mailto, formulario PDF, Normas, Políticas | Comité, Contacto |
| Normas | Descargas PDF, enlaces APA/Vancouver | Políticas, Enviar colaboración |
| Políticas | — | Normas, Enviar colaboración |
| Acerca | — | Enviar colaboración, Contacto |
| Contacto | mailto, web CENFISS | Enviar colaboración |
| Comité | — | Enviar colaboración, Normas |
| Enlaces | Enlaces externos (CENFISS, partners) | Contacto |
| Noticias | Cada entrada → entrada de noticia | Inicio, Enviar colaboración |
| Entrada de noticia | — | Noticias, Inicio |
| Autor individual | Artículos del autor | Inicio, Archivo de autores |
| Archivo de autores | Cada tarjeta de autor → single-author | Inicio |

---

## 4. Estados

### Vacío / sin resultados

| Enlace | Destino |
|--------|---------|
| Ir a Inicio | Página de inicio |
| Ver todos los números | Archivo de números |
| Ver todos los artículos | Archivo de artículos |

### 404

| Enlace | Destino |
|--------|---------|
| Volver al inicio | Página de inicio |
| Ver número actual | Número actual |

**Nunca** dejar una pantalla sin salida.

---

## 5. Reglas de migas de pan

- **Número individual:** Inicio → Revista → [Título del número]
- **Artículo individual:** Inicio → Revista → [Número] → [Título del artículo]
- **Páginas estáticas:** Inicio → [Título de la página]

Las migas de pan ofrecen salida secundaria; siempre enlazar a Inicio.

---

## 6. Regla final

Si un enlace no impulsa hacia:

- El contenido principal (leer)
- La exploración (números, autores, artículos)
- La acción principal (enviar)
- Contacto o siguiente paso

**no existe.**

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
