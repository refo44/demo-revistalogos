# language: es
Característica: Identificadores digitales de la revista
  Como administrador de LOGO ET SPES
  quiero guardar ISSN, depósito legal y prefijo DOI en Ajustes
  para que el pie, Acerca y /llms.txt muestren los valores oficiales
  # Issue #58. Ejecutan: tests/Unit/JournalIdentifierSettingsTest,
  # tests/Unit/JournalIdentifierSurfacesTest, tests/Unit/LlmsTxtDocumentTest
  # (composer test:unit) y tests/WordPress/JournalIdentifierSettingsTest
  # (composer test:wp).

  Escenario: Los campos vacíos no inventan un identificador
    Dado que ISSN, depósito legal y prefijo DOI no están guardados
    Cuando un visitante ve el pie o Acerca
    Entonces cada etiqueta muestra Próximamente
    Y /llms.txt omite esas líneas

  Escenario: Un administrador guarda los identificadores digitales
    Dado Ajustes → LOGO ET SPES
    Cuando guarda un ISSN, un depósito legal y un prefijo DOI
    Entonces el pie muestra ese ISSN y ese depósito legal
    Y Acerca muestra esos tres valores
    Y /llms.txt declara solo los valores guardados
    Y las etiquetas públicas no dicen electrónico ni digital

  Escenario: El ISSN de un número no sustituye el de la revista
    Dado un número con su propio campo issn
    Cuando se genera el pie o /llms.txt
    Entonces se lee el ajuste de revista, no el meta del número
