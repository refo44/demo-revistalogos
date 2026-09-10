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
