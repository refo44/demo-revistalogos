# language: es
Característica: PDF al publicar un artículo académico
  Como revista académica
  quiero poder exigir un PDF válido al publicar un artículo
  para que la versión descargable exista antes de que el registro sea público
  Y poder dejar esa exigencia desactivada hasta que la redacción la active

  Escenario: Exigencia desactivada: publicar sin PDF sigue permitido
    Dado que la exigencia de PDF al publicar está desactivada
    Y un artículo listo para publicar
    Y no tiene un PDF válido
    Cuando se solicita publicar
    Entonces la publicación puede continuar
    Y no se genera un PDF automáticamente

  Escenario: Exigencia activada y artículo con PDF válido ya asignado
    Dado que la exigencia de PDF al publicar está activada
    Y un artículo listo para publicar
    Y ya tiene un PDF válido
    Cuando se solicita publicar
    Entonces se conserva ese PDF
    Y no se genera otro
    Y la publicación puede continuar

  Escenario: Exigencia activada, sin PDF válido y generación exitosa
    Dado que la exigencia de PDF al publicar está activada
    Y un artículo listo para publicar
    Y no tiene un PDF válido
    Cuando se solicita publicar
    Y la generación del PDF tiene éxito
    Entonces el PDF generado queda asociado al artículo
    Y la publicación puede continuar

  Escenario: Exigencia activada, sin PDF válido y generación fallida
    Dado que la exigencia de PDF al publicar está activada
    Y un artículo listo para publicar
    Y no tiene un PDF válido
    Cuando se solicita publicar
    Y la generación del PDF falla
    Entonces la publicación se rechaza
    Y el contenido del artículo se conserva
    Y el artículo permanece sin publicar
    Y la redacción puede volver a intentarlo o adjuntar un PDF a mano
