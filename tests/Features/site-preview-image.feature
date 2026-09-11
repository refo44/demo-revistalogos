# language: es
Característica: Imagen de preview del sitio
  Como revista académica
  quiero que la portada declare una imagen de preview canónica
  para que Google Search Console y las tarjetas sociales puedan indexarla
  # Ejecutan: tests/WordPress/SitePreviewImageTest (composer test:wp).
  # La maqueta estática usaba /assets/img/logo-revista.png; esa ruta
  # ya no existe en WordPress (404). og:image cae al logo 1:1.
  # X no pinta summary_large_image con 1:1; twitter:image usa
  # og-twitter-card.jpg (1200×630).

  Escenario: La portada sin imagen destacada usa el logo de la revista
    Dado que la portada es una página sin imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image apunta al logo de la revista
    Y twitter:image es la tarjeta apaisada 1200 por 630
    Y twitter:card es summary_large_image
    Y twitter:title y twitter:description están declarados
    Y el JSON-LD de Periodical incluye el logo

  Escenario: Una entrada con imagen destacada no sustituye la propia
    Dado un contenido singular con imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image es la imagen destacada
    Y twitter:image es esa misma imagen
