# language: es
Característica: PDF obligatorio al publicar un artículo académico
  Como revista académica
  quiero que un artículo publicado tenga un PDF válido
  para que la versión descargable exista antes de que el registro sea público

  Escenario: Artículo con PDF válido ya asignado
    Dado un artículo listo para publicar
    Y ya tiene un PDF válido
    Cuando se solicita publicar
    Entonces se conserva ese PDF
    Y no se genera otro
    Y la publicación puede continuar

  Escenario: Artículo sin PDF válido y generación exitosa
    Dado un artículo listo para publicar
    Y no tiene un PDF válido
    Cuando se solicita publicar
    Y la generación del PDF tiene éxito
    Entonces el PDF generado queda asociado al artículo
    Y la publicación puede continuar

  Escenario: Artículo sin PDF válido y generación fallida
    Dado un artículo listo para publicar
    Y no tiene un PDF válido
    Cuando se solicita publicar
    Y la generación del PDF falla
    Entonces la publicación se rechaza
    Y el contenido del artículo se conserva
    Y el artículo permanece sin publicar
