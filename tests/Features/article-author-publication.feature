# language: es
Característica: Autor publicado al publicar un artículo
  Como revista académica
  quiero que un artículo solo se publique con al menos un autor publicado
  Y que asignar ese autor en el editor baste para poder publicar
  para no bloquear un artículo que ya tiene autor
  # Regresión issue #30. Ejecutan: tests/WordPress/ArticleAuthorPublicationRestTest
  # (composer test:wp) y tools/qa-article-editorial-ux.sh.

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

  Escenario: La API del editor expone y guarda la asignación de autores
    Dado el modelo de artículo en el editor de bloques
    Entonces la asignación de autores forma parte de los metadatos editables
    Y una publicación con autor en la misma petición deja ese autor guardado
