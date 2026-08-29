# language: es
Característica: Diseño editorial de la separata PDF del artículo
  Como revista académica arbitrada
  quiero que el PDF generado de un artículo sea una separata editorial
  para que el archivo sea citable, imprimible y reconocible como LOGO ET SPES
  fuera del sitio web

  Antecedentes:
    Dado que la separata es del artículo individual y no del número completo
    Y que la separata se imprime en blanco y negro sin ningún color

  Escenario: Separata completa con contexto bibliográfico del número
    Dado un artículo publicado en un número con volumen, número, año e ISSN
    Y el artículo tiene título en inglés, resúmenes, palabras clave y DOI
    Y sus autores tienen afiliación y ORCID registrados
    Cuando se genera la fuente de la separata
    Entonces la cabecera muestra el nombre de la revista
    Y la línea bibliográfica muestra volumen, número, año, paginación e ISSN
    Y el bloque de título muestra título, título en inglés y autores con su afiliación
    Y se muestran resumen, abstract, palabras clave y fechas editoriales

  Escenario: Los campos vacíos se omiten sin dejar etiquetas huérfanas
    Dado un artículo sin número asignado, sin resúmenes, sin DOI y sin palabras clave
    Cuando se genera la fuente de la separata
    Entonces no aparece ninguna etiqueta de un dato ausente
    Y la separata sigue siendo un documento válido

  Escenario: El mínimo editorial sigue generando
    Dado un artículo con solo título, cuerpo y autores
    Cuando se genera la fuente de la separata
    Entonces la separata se genera con ese mínimo
    Y el título, el cuerpo y los autores aparecen en el documento

  Escenario: Los identificadores académicos son texto inerte
    Dado un artículo con DOI y autores con ORCID registrados
    Cuando se genera la fuente de la separata
    Entonces el DOI y el ORCID aparecen exactamente como fueron almacenados
    Y no se construye ningún enlace ni identificador derivado

  Escenario: Las fechas editoriales se muestran en español
    Dado un artículo con fechas de recepción, aceptación y publicación
    Cuando se genera la fuente de la separata
    Entonces cada fecha se muestra como día, mes en español y año
