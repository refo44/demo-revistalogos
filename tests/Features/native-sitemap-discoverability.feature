# language: es
Característica: Sitemap nativo y visibilidad de /buscar/
  Como revista académica
  quiero que el sitemap nativo de WordPress liste el contenido público
  y no las cuentas de wp-admin ni el buscador
  para que Google y los agentes de IA indexen números, artículos y autores
  # Ejecutan: tests/WordPress/NativeSitemapDiscoverabilityTest (composer test:wp).
  # Workaround HTTP 200 en sitemaps: retirar cuando WordPress mínimo sea 7.1.1+
  # (Trac #65945).

  Escenario: El índice nativo no anuncia cuentas de WordPress
    Dado el sitemap nativo de WordPress
    Cuando se genera el índice
    Entonces no incluye el provider users
    Y sí incluye article, issue y author

  Escenario: La búsqueda no es una landing indexable
    Dado la página /buscar/ publicada
    Cuando se genera el sitemap de páginas
    Entonces /buscar/ no aparece
    Y la página envía noindex

  Escenario: Sin entradas nativas el sitemap no es 404
    Dado que no hay posts nativos publicados
    Y WordPress es 7.1
    Cuando se solicita /wp-sitemap.xml
    Entonces pre_handle_404 adelanta el 404
    Y la respuesta no es 404

  Escenario: Un 404 ordinario no lo convierte el workaround
    Dado una URL pública que no existe
    Cuando se resuelve la petición
    Entonces sigue siendo 404

  Escenario: WordPress 7.1.1 o posterior no usa el parche
    Dado WordPress 7.0, 7.1.1 o 7.2
    Cuando se evalúa el gate del workaround
    Entonces no se aplica
