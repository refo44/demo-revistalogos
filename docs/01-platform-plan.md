# Revista de Filosofía LOGO ET SPES — Plan Maestro de Plataforma

**Versión 1.0**

La identidad corporativa definida en `02-corporate-identity` es la capa visual cerrada.

Este documento define el sistema que permite que la Revista de Filosofía LOGO ET SPES exista, sea descubierta, navegada y sostenida en el tiempo.

Este sitio no es un embudo comercial, una landing page ni una publicación orientada al marketing.  
Es una plataforma de publicación digital académica para el pensamiento filosófico.

El problema que aborda este plan no es estético sino estructural y de orientación:

- ¿Cómo se presenta y accede al conocimiento filosófico en este espacio digital?
- ¿Cuál es la acción o resultado principal?
- ¿Cómo llega el visitante sin perder la orientación?

**Fuentes canónicas:**

- `content-source/PROYECTO REVISTA DE FILOSOFIA LOGO ET SPES nov 2025.md` — Documento del proyecto (estructura, políticas, normas editoriales)
- `assets/pdf/` — Normas editoriales, políticas, número y artículo de ejemplo
- `assets/img/` — Logos, favicon y placeholders editoriales
- `docs/02-corporate-identity` — Identidad visual de la plataforma

---

## Acción principal

Las acciones principales que soporta la plataforma son:

1. **Leer** contenido filosófico
2. **Explorar** números de la revista
3. **Enviar** contribuciones académicas
4. **Gestionar envíos** (autores autenticados: seguimiento de estado, editar borradores, subir archivos)

Toda la navegación y orientación debe apoyar estas acciones.

---

## Audiencias principales

La plataforma sirve a tres audiencias principales:

| Audiencia | Perfil |
|-----------|--------|
| **Lectores** | Investigadores, estudiantes, público general interesado en filosofía |
| **Autores** | Investigadores, académicos, estudiantes de posgrado, investigadores independientes |
| **Editores** | Miembros del consejo editorial que gestionan envíos y decisiones de publicación |

---

## Principios operativos

| Principio | Significado operativo |
|-----------|------------------------|
| **Acceso abierto** | Todo el contenido es gratuito. Sin costo para autores ni lectores. El conocimiento es accesible a la comunidad académica y al público. |
| **Rigor académico** | Arbitraje doble ciego. Cumplimiento de políticas editoriales, normas de publicación y ética. Antiplagio. |
| **Orientación** | Cada pantalla ofrece un siguiente paso o salida clara. Sin laberintos. El visitante siempre sabe cómo llegar a la acción principal (leer, enviar, contactar). |
| **Ancla institucional** | La identidad CENFISS es visible. El pie y las páginas clave refuerzan confianza, ISSN, Depósito Legal y contacto. |
| **Descubribilidad** | El contenido debe ser indexable y citable. Metadatos estructurados, HTML semántico y compatibilidad con motores de búsqueda académicos (Google Scholar, catálogos de bibliotecas). |
| **Permanencia** | Las URLs y estructuras de artículos deben permanecer estables en el tiempo para apoyar la citación académica. |

---

## Las capas del sistema

El sitio está diseñado como un sistema de capas independientes y complementarias. Cada capa tiene una función específica. (Capas 1–8; Capa 6.5 Envío de autores, subcapa; Capa 7.5 Descubribilidad, subcapa.)

---

### Capa 1. Identidad

Define cómo se ve y se siente el sitio.

Incluye:

- Tipografía
- Paleta de colores
- Ritmo visual
- Uso del espacio en blanco
- Jerarquía editorial
- Consistencia estética

**Estado:** Cerrada. Definida en `02-corporate-identity`.

---

### Capa 2. Arquitectura de información

Define qué existe dentro del sistema.

| Sección | Función |
|---------|---------|
| Inicio | Home. Hero, número actual, acceso rápido al contenido principal. |
| Revista | Número actual, archivo de números, vistas de artículos, vistas de autores. |
| Normas | Normas de publicación, ética editorial, políticas, enviar colaboración. |
| Acerca | Enfoque, alcance, objetivos, origen del nombre Logo et Spes. |
| Contacto | Contacto institucional, consejo editorial, CENFISS. |
| Noticias | CENFISS presente, eventos y misceláneas. |

---

### Capa 3. Modelo de contenido

Define qué es cada entidad dentro del sistema. Alineado con `03-wordpress-content-model`.

| Tipo | Función |
|------|---------|
| Issue (número) | Un volumen de la revista. Contiene artículos (incluido editorial), misceláneas. |
| Article (contenido arbitrado) | artículo, ensayo, reseña, editorial — todos como tipos de artículo. Resumen, palabras clave, referencias. |
| Author | Perfil de autor. Nombre, afiliación, ORCID, bio. |
| Misceláneas | Reseñas, CENFISS presente, número anterior. |

---

### Capa 4. Gramática de navegación

Define cómo se recorre el sitio.

Elementos:

- Menú principal (4–6 ítems recomendados): Inicio, Revista, Normas, Acerca, Contacto, Noticias
- CTA principal: Enviar Colaboración (para autores)
- Pie como ancla institucional: ISSN, Depósito Legal, CENFISS, Creative Commons, contacto

**Reglas:**

- El visitante siempre sabe cómo llegar a la acción principal o al contacto.
- La navegación no debe superar dos niveles jerárquicos.
- Las páginas de artículos deben ofrecer siempre: enlace al número, enlace al autor, enlace a artículos relacionados.

---

### Capa 5. Estrategia de publicación

Define el ritmo de vida del sitio.

Permite:

- Publicación anual de números (ordinarios y eventuales extraordinarios)
- Actualizaciones editoriales (editorial, misceláneas, CENFISS presente)
- Descarga de formularios (Solicitud de Publicación, Instrumento de Arbitraje)

**Principio CMS futuro:** El sistema debe permitir la publicación de nuevo contenido sin intervención de desarrollo.

**Flujo editorial:** envío → revisión interna → arbitraje → decisión → publicación.

---

### Capa 6. Sistema editorial

Define cómo comunica el sitio.

Incluye:

- Voz y tono: Académico, claro, riguroso. Sin presión comercial.
- Lenguaje claro y consistente: español principal; títulos en español e inglés.
- Sin presión comercial: acceso abierto, sin tarifas, sin embudos de marketing.

**Regla de consistencia editorial:** Todos los artículos deben presentar: título en español e inglés; resumen y palabras clave; información del autor; referencias.

---

### Capa 6.5. Envío de autores (subcapa)

Permite a autores autenticados enviar y gestionar manuscritos. Área privada; no altera la identidad editorial pública.

**Debe permitir:**

- Registro o creación controlada de cuentas
- Inicio de sesión
- Envío de manuscritos
- Subida de archivos (versión identificada, versión anonimizada según normas editoriales)
- Seguimiento básico de envíos (estado, historial)
- Acceso a normas y formularios desde el panel del autor

**Reglas:**

- La cuenta existe solo para facilitar el proceso editorial. El sitio sigue siendo una revista académica, no una plataforma comercial.
- Los envíos son visibles solo para: el autor que envía, editores, administradores.
- Solo los envíos aceptados generan artículos publicados.

---

### Capa 7. Infraestructura

La base silenciosa pero crítica.

Debe garantizar:

- Estabilidad
- Seguridad
- Copias de seguridad (incluyendo base de datos)
- Entorno de staging
- Escalabilidad
- Control de versiones (Git)
- Builds reproducibles
- Separación entre contenido y maquetación
- HTTPS y autenticación segura

---

### Capa 7.5. Descubribilidad (subcapa)

Asegura que la revista pueda ser descubierta y citada.

Incluye:

- HTML semántico
- Datos estructurados Schema.org
- Etiquetas de metadatos de citación (citation_title, citation_author, citation_journal_title, citation_publication_date, etc.)
- Metadatos Open Graph
- Compatibilidad con sistemas de indexación académica (Google Scholar, catálogos de bibliotecas)
- Posible integración DOI

---

### Capa 8. Ancla legal e institucional

- ISSN y Depósito Legal (Biblioteca Nacional de Venezuela)
- Creative Commons (Atribución, no comercial)
- Política de privacidad y confidencialidad (autores, árbitros, visitantes)
- Declaración de ética (alineación COPE)
- Política de derechos de autor (ley venezolana, SAPI)
- Política de retención de derechos del autor (autores conservan derechos según proyecto)

---

## Accesibilidad (capa transversal)

No es un añadido. Es una condición estructural del sistema. Definida en `19-accessibility-standards`.

**Línea base de accesibilidad:** Cumplimiento WCAG 2.1 AA obligatorio.

Debe atravesar:

- Identidad
- Navegación
- Contenido
- Diseño visual

---

## Qué permite el sistema

Sin tocar código, el propietario del sitio puede:

- Publicar nuevos números (editorial, artículos, ensayos, misceláneas)
- Publicar contenido editorial sin modificar plantillas de maquetación
- Gestionar metadatos de artículos para indexación
- Actualizar CENFISS presente e información de eventos
- Gestionar datos de autores y árbitros
- Actualizar normas, políticas y formularios

Sin modificar la estructura central.

---

## Qué NO es este sistema

No es:

- Un embudo
- Una landing page
- Un sitio comercial
- Un CMS orientado al marketing
- Un flujo de contenido de redes sociales

Es una plataforma de publicación digital académica para el pensamiento filosófico.

---

## Estado

| Capa | Estado | Notas |
|------|--------|-------|
| Identidad | Implementada | `tokens.css` y `02-corporate-identity` definen paleta y tipografía. |
| Arquitectura | Implementada | Páginas existen: Inicio, Revista (archivos de números/artículos), Normas, Acerca, Contacto, Noticias. |
| Modelo de contenido | Implementada | Vistas de número, artículo, ensayo, reseña. Plantillas single/archive. |
| Navegación | Implementada | Nav del header, footer, CTA principal (Enviar Colaboración). |
| Publicación | Solo estático | Sin CMS. Contenido hardcodeado. Formularios enlazan a PDFs. |
| Editorial | Implementada | Voz y tono en su lugar. Sin presión comercial. |
| Envío de autores | Planificado | Login, CPT de envíos, panel de autor. Implementación WordPress. |
| Infraestructura | Parcial | En vivo en GitHub Pages. `deploy.sh` referencia `prototype/` inexistente. GitLab CI espera `public/`. Sin 404.html. |
| Descubribilidad | Planificada | HTML semántico en maqueta. Metadatos, DOI, indexación por implementar en WordPress. |
| Legal | Parcial | Licencia CC en footer. Páginas de privacidad/ética existen. Placeholder ISSN. |

### Brechas de implementación

- **Página 404:** Falta. Añadir `404.html` para enlaces rotos.
- **Script de deploy:** `deploy.sh` busca `prototype/front-page.html`; el sitio vive en raíz con `index.html`.
- **Docs 02–20:** Completados para el proyecto. Trazabilidad en `00-order-documents`.
- **WordPress:** No iniciado. La maqueta estática es el entregable actual.

---

## Ruta de evolución

| Etapa | Descripción |
|-------|-------------|
| **Actual** | Maqueta HTML estática. |
| **Siguiente** | Implementación WordPress usando la misma arquitectura. |
| **Largo plazo** | Revista académica indexada con ritmo de publicación estable. |

---

**Versión:** 1.0  
**Depende de:** content-source  
**Proyecto:** Revista de Filosofía LOGO ET SPES
