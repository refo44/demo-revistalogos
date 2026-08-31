# language: es
Característica: Metadatos del metabox en la publicación Gutenberg
  Como revista académica
  quiero que los campos del metabox clásico viajen en la misma petición
  de publicación del editor de bloques
  para no perder autores, número, PDF u otros metadatos al publicar
  # Regresión issue #30. Ejecutan:
  # tests/WordPress/ArticleAuthorPublicationRestTest (composer test:wp)
  # y tools/qa-article-editorial-ux.sh.

  Escenario: Borrador sin autor no se publica
    Dado un artículo en borrador
    Y no tiene un autor publicado asignado
    Cuando se solicita publicar por la API del editor
    Entonces la publicación se rechaza
    Y el artículo permanece sin publicar

  Escenario: Borrador con autor publicado se publica en la misma sesión
    Dado un artículo en borrador
    Y un autor con estado publicado asignado en la misma petición de publicación
    Cuando se solicita publicar por la API del editor
    Entonces la publicación puede continuar
    Y el autor permanece asignado en el artículo

  Escenario: Número y PDF asignados en la misma petición quedan guardados
    Dado un artículo en borrador con un autor publicado
    Y un número y un PDF enviados como metadatos en la misma petición
    Cuando se solicita publicar por la API del editor
    Entonces la publicación puede continuar
    Y el número permanece asignado
    Y el PDF permanece asignado

  Escenario: La API del editor expone los metadatos del artículo
    Dado el modelo de artículo en el editor de bloques
    Entonces autores, número y PDF forman parte de los metadatos editables
    Y una publicación con esos metadatos en la misma petición los deja guardados
    Y article, issue y author soportan custom-fields para exponer meta por REST

  Escenario: El PDF de un número no reescribe metadatos de artículo
    Dado la pantalla de edición de un número en el editor de bloques
    Cuando se selecciona o quita el PDF del número
    Entonces la sincronización al store solo incluye metadatos del número
    Y no envía autores ni la relación article→issue
