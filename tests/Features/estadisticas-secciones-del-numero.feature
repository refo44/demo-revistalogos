# language: es
Característica: Estadísticas de secciones del número
  Como lectora de la revista
  quiero ver la tarjeta Secciones solo cuando el número usa secciones
  para no leer un cero cuando el comité no las asigna
  # Vol. 1 Nº 1 no muestra secciones por decisión editorial.
  # Ejecutan: tests/WordPress/IssueSectionStatsTest (composer test:wp).

  Escenario: Sin secciones asignadas no se muestra la tarjeta
    Dado un número publicado con artículos sin término de sección
    Cuando se muestra la ficha pública del número
    Entonces el conteo de secciones es 0
    Y la tarjeta Secciones no aparece
    Y sí aparecen Artículos y Autores

  Escenario: Secciones asignadas se cuentan y se muestran
    Dado un número con artículos en Ética y Metafísica
    Cuando se muestra la ficha pública del número
    Entonces el conteo de secciones es 2
    Y la tarjeta Secciones aparece
