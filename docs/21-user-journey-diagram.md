# Revista de Filosofía LOGO ET SPES — Diagrama de Recorrido del Usuario

Diagrama Mermaid. Basado en `10-user-journey`. Puntuación 1–5: fluidez del flujo (5 = sin fricción). Actor: **Visitante**.

---

```mermaid
journey
    title Revista LOGO ET SPES — Recorridos del visitante
    section Lee número actual
      Ve inicio (hero, mensaje): 5: Visitante
      Clic Ver número actual: 5: Visitante
      Single issue (TOC, PDF): 5: Visitante
      Clic artículo en TOC: 5: Visitante
      Single article (lee o descarga PDF): 5: Visitante
    section Explora la revista
      Home → Acerca: 5: Visitante
      Lee enfoque, alcance, objetivos: 5: Visitante
      Enviar colaboración o Contacto: 5: Visitante
    section Envía colaboración
      Home o menú → Enviar colaboración: 5: Visitante
      Lee normas: 5: Visitante
      Crear cuenta: 5: Visitante
      Nuevo envío: 5: Visitante
      Subir manuscrito: 5: Visitante
      Confirmar envío: 5: Visitante
    section Busca artículo
      Archivo de artículos: 5: Visitante
      Filtros (Buscar, Sección, Año): 5: Visitante
      Clic artículo → Single article: 5: Visitante
    section Explora números publicados
      Ve archivo de números: 5: Visitante
      Clic en número: 5: Visitante
      Ve TOC del número: 5: Visitante
      Clic artículo: 5: Visitante
    section Llega desde afuera
      Google, DOI o enlace compartido: 5: Visitante
      Aterriza en artículo, número o página: 5: Visitante
      Ve breadcrumb, nav, footer: 5: Visitante
      Navega a Inicio, Número actual, Enviar: 5: Visitante
    section Contacta al Comité Editorial
      Home o menú → Contacto: 5: Visitante
      Lee email, CENFISS, dirección: 5: Visitante
      mailto o formulario: 5: Visitante
    section Estado de fricción
      Contenido no existe o 404: 2: Visitante
      Breadcrumbs o Volver: 5: Visitante
```

---

**Referencia:** `10-user-journey`
