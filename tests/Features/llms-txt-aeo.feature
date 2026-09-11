# language: es
Característica: /llms.txt para agentes de IA
  Como revista académica
  quiero un mapa en texto de la revista que cite el número actual publicado
  para que los agentes de IA encuentren números y artículos vivos
  # Issue #47. Ejecutan: tests/Unit/LlmsTxtDocumentTest (composer test:unit)
  # y tests/WordPress/LlmsTxtCatalogTest (composer test:wp).

  Escenario: El archivo apunta a los archivos vivos y al sitemap
    Dado el catálogo público de la revista
    Cuando se genera /llms.txt
    Entonces nombra Revista de Filosofía LOGO ET SPES
    Y enlaza números, artículos, autores y el sitemap nativo

  Escenario: El número vigente se lee de objetos publicados
    Dado un número publicado con artículos publicados
    Y un número o artículo en borrador
    Cuando se genera /llms.txt
    Entonces aparece el número vigente y sus artículos publicados
    Y no aparecen los borradores

  Escenario: Un administrador actualiza /llms.txt desde el plugin
    Dado un número publicado con artículos publicados
    Cuando un administrador pulsa Actualizar llms.txt en Ajustes → LOGO ET SPES — llms.txt
    Entonces el archivo público refleja ese número
    Y la ruta /llms.txt queda registrada

  Escenario: Las páginas institucionales publicadas entran en el mapa
    Dado páginas publicadas de normas, ética, políticas, comité y donaciones
    Y una página institucional en borrador
    Cuando se genera /llms.txt
    Entonces enlaza esas páginas publicadas con su título vigente
    Y no enlaza el borrador ni la búsqueda

  Escenario: Un administrador puede desactivar la publicación
    Dado que llms.txt está desactivado en Ajustes
    Cuando se solicita la dirección pública
    Entonces el mapa no se publica
