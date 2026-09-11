# language: es
Característica: Visitas y descargas en Estadísticas del Número
  Como lectora de la revista
  quiero ver las vistas de la ficha del número y las descargas de su PDF
  para conocer el uso de ese volumen
  # Visitas: WP Statistics gratis, solo esa URL. Se oculta si el plugin
  # falta, está apagado, no hay datos o el valor es 0.
  # Descargas: código propio; Ver PDF y Descargar PDF del número cuentan.
  # No artículos. Sin visitantes únicos. Sin Páginas.
  # Ejecutan: tests/Unit/IssuePageViewsTest (composer test:unit)
  # y tests/WordPress/IssueSectionStatsTest,
  # tests/WordPress/IssuePdfDownloadsTest (composer test:wp).

  Escenario: Visitas y descargas del número se muestran si hay cifras
    Dado un número con vistas de su propia ficha
    Y descargas de su PDF completo
    Cuando se muestran las Estadísticas del Número
    Entonces aparece la tarjeta Visitas
    Y aparece la tarjeta Descargas

  Escenario: Sin visitas ni descargas no se muestran esas tarjetas
    Dado un número sin visitas registradas
    Y sin descargas de su PDF
    Cuando se muestran las Estadísticas del Número
    Entonces no aparece la tarjeta Visitas
    Y no aparece la tarjeta Descargas
