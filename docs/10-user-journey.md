# Revista de Filosofía LOGO ET SPES — Recorrido del Usuario

Describe cómo una persona real se mueve por el sitio. No es un flujo de conversión; es una serie de encuentros con **contenido filosófico y procesos editoriales**.

El objetivo del recorrido no es convertir visitantes en usuarios, sino facilitar el acceso al contenido filosófico y al proceso editorial de la revista.

Valida: arquitectura de información, navegación, microcopy, jerarquía de pantallas.

**Depende de:** `04-screen-map`, `05-information-architecture-navigation`  
**Referencia:** `07-voice-guide-microcopy-ux`, `09-ui-copy-sheet`, `19-accessibility-standards`

---

## 1. La persona llega y quiere leer el número actual

**Objetivo:** Entrar sin fricción. Llegar al contenido rápido.

**Ruta:**

- Home
- → Lee el hero y mensaje principal (enfoque, acceso abierto, arbitraje)
- → Clic en "Ver número actual"
- → Número individual: ve la tabla de contenidos, Ver PDF, Descargar PDF o Leer artículo
- → Clic en artículo en la tabla de contenidos → Artículo individual
- → Descargar PDF o Leer artículo

**Puntos de salida:** Migas de pan (Inicio, Revista), enlaces del footer, menú de nav.

---

## 2. La persona explora para entender la revista

**Objetivo:** Entender qué es LOGO ET SPES, su alcance y cómo participar.

**Ruta:**

- Home
- → Acerca (enfoque, alcance, objetivos, origen del nombre)
- → Lee el contenido
- → Enviar colaboración o Contacto

**Alternativa:** Home → Normas → Políticas → Enviar colaboración.

---

## 3. La persona quiere enviar un artículo

**Objetivo:** Encontrar instrucciones y enviar una colaboración.

**Ruta:**

- Home o menú
- → Enviar colaboración
- → Lee normas
- → Crear cuenta o Iniciar sesión
- → Panel de autor
- → Nuevo envío
- → Subir manuscrito
- → Confirmar envío

**Alternativa:** Envío por email (mailto revista.cenfiss@gmail.com) si el panel no está disponible.

**Ruta de apoyo:** Enviar colaboración → Normas → Políticas (para verificar requisitos antes de enviar).

---

## 4. La persona busca un artículo o tema específico

**Objetivo:** Encontrar contenido por sección, autor o palabra clave.

**Ruta:**

- Home o menú
- → Revista → Artículos
- → Usa filtros (Buscar, Sección, Año)
- → Clic en Buscar
- → Clic en tarjeta de artículo → Artículo individual
- → Descargar PDF o Ver PDF

**Alternativa:** Revista → Números publicados → Número individual → tabla de contenidos → Artículo individual.

---

## 5. La persona llega desde fuera (enlace directo)

**Objetivo:** No perderse al llegar vía Google, enlace DOI o URL compartida.

**Ruta:**

- Llega desde Google, DOI o enlace compartido
- → Aterriza en artículo individual, número individual o página estática
- → Ve header (nav) y footer
- → Las migas de pan ofrecen ruta: Inicio → Revista → [actual]
- → Navega a Inicio, Número actual, Enviar colaboración o Contacto

**Ninguna página es un callejón sin salida.** Toda página ofrece navegación principal, migas de pan o enlaces de salida.

---

## 6. La persona quiere contactar al Comité Editorial

**Objetivo:** Llegar al Comité Editorial.

**Ruta:**

- Home o menú
- → Contacto
- → Lee info de contacto (email, web CENFISS, dirección)
- → Clic en mailto o rellena formulario de contacto
- → Envía mensaje

**Alternativa:** Enviar colaboración → mailto en instrucciones. Comité Editorial → Contacto.

---

## 7. Regla de validación

**Un recorrido es correcto si:**

1. La persona puede entender **qué es la revista y cómo leer o enviar** en menos de tres clics.
2. Siempre hay un "siguiente" o "atrás" (migas de pan, nav, footer).
3. Nunca aterrizan en una pantalla muerta (404 tiene enlaces; estados vacíos tienen salidas).
4. Nunca sienten que entraron a una app o tienda (tono institucional, académico).

---

## 8. Accesibilidad del recorrido

El recorrido debe ser posible para cualquiera, independientemente de capacidades físicas, sensoriales o cognitivas.

La accesibilidad no define rutas alternas. Asegura que el mismo recorrido pueda ser atravesado por:

- Personas con discapacidad visual (lectores de pantalla, texto alt, estructura semántica)
- Personas con baja visión (contraste, redimensionar, indicadores de foco)
- Personas que navegan con teclado (navegación completa mediante teclado, enlace "Saltar al contenido principal", orden de foco, sin trampas)
- Personas con dificultades auditivas (sin información esencial solo en audio)
- Personas con alta carga cognitiva (etiquetas claras, estructura consistente, sin jerga en la UI)

Criterios técnicos y editoriales en `19-accessibility-standards`.

---

**Versión:** 1.0  
**Proyecto:** Revista de Filosofía LOGO ET SPES
