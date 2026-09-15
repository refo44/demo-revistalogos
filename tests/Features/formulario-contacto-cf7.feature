# language: es
Característica: Formulario de contacto con Contact Form 7
  Como visitante de LOGO ET SPES
  quiero enviar un mensaje desde /contacto/
  para que llegue al buzón de la revista sin cookies ni almacenamiento
  # Issue #62. ADR 0010. Ejecutan: tests/Unit/ContactFormDefinitionTest
  # (composer test:unit), tests/WordPress/ContactFormProvisionTest
  # (composer test:wp) y tools/qa-contact-form.sh.

  Escenario: Sin Contact Form 7 la página sigue siendo usable
    Dado que Contact Form 7 no está activo
    Cuando un visitante abre /contacto/
    Entonces ve el fallback mailto a revista.cenfiss@gmail.com
    Y no se crea un formulario en la base de datos

  Escenario: Con Contact Form 7 el plugin provisiona el formulario
    Dado que Contact Form 7 está activo
    Y no hay un formulario gestionado
    Cuando corre el provisionamiento
    Entonces existe un formulario con Nombre, Email, Asunto y Mensaje
    Y el destinatario es revista.cenfiss@gmail.com
    Y la opción revistalogos_contact_form_id apunta a ese formulario
    Y un segundo provisionamiento no duplica el formulario

  Escenario: /contacto/ muestra el formulario y no el mailto
    Dado Contact Form 7 activo y la opción configurada
    Cuando un visitante abre /contacto/
    Entonces ve el formulario CF7 y el enlace al aviso de privacidad
    Y no hay reCAPTCHA ni Flamingo
