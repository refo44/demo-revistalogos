# language: es
Característica: Licencia del contenido del sitio
  Como editora de la revista
  quiero que el sitio declare todos los derechos reservados
  y no ofrezca Creative Commons
  para que el contenido editorial no se pueda reutilizar sin permiso
  # Ejecutan: tests/Unit/SiteContentLicenseTest (composer test:unit).

  Escenario: El pie reserva el contenido y mantiene MIT para el código
    Dado el pie del sitio
    Cuando una lectora lee la licencia
    Entonces ve todos los derechos reservados
    Y no ve Creative Commons Atribución 4.0
    Y el código del sitio sigue bajo MIT

  Escenario: Acerca, ética y privacidad coinciden con el pie
    Dado las fichas institucionales de licencia
    Cuando se muestra Acerca, Ética y Privacidad
    Entonces cada una declara todos los derechos reservados
    Y ninguna ofrece CC BY 4.0

  Escenario: El copyright del pie es el año en curso
    Dado el pie del sitio
    Cuando una lectora lee el aviso de CENFISS
    Entonces ve © 2026 CENFISS
    Y no ve © 2025

  Escenario: Políticas y Contacto retiran Creative Commons
    Dado el cuerpo de Políticas y de Contacto
    Cuando una lectora lee la licencia del contenido
    Entonces ve todos los derechos reservados
    Y no ve Creative Commons Atribución 4.0
