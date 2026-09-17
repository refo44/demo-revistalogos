# language: es
Característica: Apellido bibliográfico y override opcional de Cómo Citar
  Como revista académica
  quiero citar ambos apellidos hispanos y poder corregir una caja de Cómo Citar
  para que el nombre público no se rompa y un fallo de frase no exija un deploy
  # ADR 0021 / issue #64. Ejecutan:
  # tests/Unit/SplitNameTest
  # tests/Unit/CitationOverrideMergeTest
  # tests/Unit/AuthorNameBackfillTest
  # tests/WordPress/CitationSurnameOverrideTest
  # tests/WordPress/AuthorGivenFamilyNamesTest (composer test:wp)

  Escenario: Un apellido sigue la heurística de la última palabra
    Dado un autor cuyo título es un nombre con un solo apellido
    Y el campo apellido para citar está vacío
    Cuando se arma Cómo Citar
    Entonces el apellido bibliográfico es la última palabra del título

  Escenario: Dos apellidos hispanos usan el campo del autor
    Dado un autor titulado "Ana María Pérez Gómez"
    Y apellido para citar "Pérez Gómez"
    Cuando se arma Cómo Citar
    Entonces todos los formatos usan "Pérez Gómez" como apellido
    Y las iniciales son solo de pila

  Escenario: Override de una caja no toca las demás
    Dado un artículo con citas generadas
    Y un override relleno solo en MLA
    Cuando se muestra Cómo Citar
    Entonces MLA muestra el texto guardado
    Y APA sigue saliendo del builder

  Escenario: Regenerar borra ese override
    Dado un artículo con override de MLA
    Cuando el editor pulsa Regenerar en MLA y guarda
    Entonces el meta de MLA queda vacío
    Y esa caja vuelve al contenido automático
    Y el override de APA no se borra

  Escenario: Gutenberg expone apellido y overrides por REST
    Dado el modelo de autor y de artículo en el editor de bloques
    Entonces citation_surname forma parte de los metadatos editables del autor
    Y las siete citation_override_* forman parte de los metadatos editables del artículo

  Escenario: Apellido para citar en medio del título no se vuelve inicial
    Dado un autor titulado "Rafael Eduardo Figueredo Oropeza"
    Y apellido para citar "Figueredo"
    Cuando se arma Cómo Citar
    Entonces el apellido bibliográfico es "Figueredo"
    Y las iniciales son "R.E."

  Escenario: Sin apellido para citar se usan los apellidos de la persona
    Dado un autor con nombres "Sofía Camila" y apellidos "León Albino"
    Y apellido para citar vacío
    Cuando se arma Cómo Citar
    Entonces todos los formatos usan "León Albino" como apellido
    Y las iniciales son solo de pila
