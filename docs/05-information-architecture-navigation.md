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

**Regla de cantidad:** Menú de 4–6 ítems recomendado. La implementación actual tiene 8; considerar agrupar (ej. submenú Revista) para reducir carga cognitiva.

### Header

| Enlace | Destino |
|--------|---------|
| Inicio | Home |
| Número Actual | Número actual (single-issue) |
| Números Publicados | Archivo de números |
| Enviar colaboración | Página de envío (CTA principal) |
| Acerca | Página Acerca |
| Noticias | Índice del blog |
| Contacto | Página de contacto |
| Enlaces | Página de enlaces |
| CENFISS | cenfiss.net (externo) |

### Footer

| Enlace | Destino |
|--------|---------|
| Número Actual | Número actual |
| Archivos | Archivo de números |
| Artículos | Archivo de artículos |
| Enviar Colaboración | Página de envío |
| Búsqueda | Búsqueda |
| Normas de Publicación | Página Normas |
| Ética Editorial | Página Ética |
| Políticas | Página Políticas |
| Comité Editorial | Página Comité |
| Contacto | Contacto, web CENFISS, email |
| Creative Commons | Licencia CC (externo) |

---

## 3. Enlaces por pantalla

| Pantalla | Enlace(s) primario(s) | Enlace(s) secundario(s) |
|----------|----------------------|--------------------------|
| Home | Ver número actual | Enviar colaboración, Normas, Acerca |
| Número Actual (single-issue) | Descarga PDF, enlaces a artículos en TOC | Archivo de números, Inicio (migas de pan) |
| Números Publicados | Cada tarjeta de número → single-issue | Inicio, Enviar colaboración |
| Artículo individual | PDF, Leer texto completo | Número padre, Archivo de artículos, Inicio (migas de pan) |
| Archivo de artículos | Cada tarjeta de artículo → single-article | Filtrar por sección/tipo, Inicio |
| Enviar colaboración | mailto, formulario PDF, Normas, Políticas | Comité, Contacto |
| Normas | Descargas PDF, enlaces APA/Vancouver | Políticas, Enviar colaboración |
| Políticas | — | Normas, Enviar colaboración |
| Acerca | — | Enviar colaboración, Contacto |
| Contacto | mailto, web CENFISS | Enviar colaboración |
| Comité | — | Enviar colaboración, Normas |
| Enlaces | Enlaces externos (CENFISS, partners) | Contacto |
| Noticias | Cada entrada → entrada individual | Inicio, Enviar colaboración |
| Entrada individual | — | Noticias, Inicio |

---

## 4. Estados

### Vacío / sin resultados

| Enlace | Destino |
|--------|---------|
| Ir a Inicio | Home |
| Ver todos los números | Archivo de números |
| Ver todos los artículos | Archivo de artículos |

### 404

| Enlace | Destino |
|--------|---------|
| Volver al inicio | Home |
| Ver número actual | Número actual |

**Nunca** dejar una pantalla sin salida.

---

## 5. Reglas de migas de pan

- **Número individual:** Inicio → Archivos → [Título del número]
- **Artículo individual:** Inicio → Archivos → [Número] → [Título del artículo]
- **Páginas estáticas:** Inicio → [Título de la página]

Las migas de pan ofrecen salida secundaria; siempre enlazar a Inicio.

---

## 6. Regla final

Si un enlace no impulsa hacia:

- El contenido principal (leer)
- La acción principal (enviar)
- Contacto o siguiente paso

**no existe.**

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
