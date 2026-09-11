# language: es
Característica: Imagen de preview del sitio
  Como revista académica
  quiero que la portada declare una imagen de preview canónica
  para que Google Search Console y las tarjetas sociales puedan indexarla
  # Ejecutan: tests/WordPress/SitePreviewImageTest (composer test:wp).
  # La maqueta estática usaba /assets/img/logo-revista.png; esa ruta
  # ya no existe en WordPress (404). El fallback es el logo del theme.

  Escenario: La portada sin imagen destacada usa el logo de la revista
    Dado que la portada es una página sin imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image apunta al logo de la revista
    Y twitter:image es la misma URL
    Y twitter:card es summary porque el logo es 1:1
    Y el JSON-LD de Periodical incluye esa misma imagen

  Escenario: Una entrada con imagen destacada no sustituye la propia
    Dado un contenido singular con imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image es la imagen destacada
    Y twitter:image es esa misma imagen
