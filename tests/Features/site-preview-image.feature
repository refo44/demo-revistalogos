# language: es
Característica: Imagen de preview del sitio
  Como revista académica
  quiero que la portada declare una imagen de preview canónica
  para que Google Search Console y las tarjetas sociales puedan indexarla
  # Ejecutan: tests/WordPress/SitePreviewImageTest (composer test:wp).
  # La maqueta estática usaba /assets/img/logo-revista.png; esa ruta
  # ya no existe en WordPress (404). Facebook recorta og:image a ~1,91:1
  # y con el logo 1:1 perdía los textos del sello; X no pinta
  # summary_large_image con 1:1. og:image y twitter:image usan
  # og-twitter-card.jpg (1200×630). El JSON-LD conserva el logo cuadrado.

  Escenario: La portada sin imagen destacada usa la tarjeta apaisada
    Dado que la portada es una página sin imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image es la tarjeta apaisada 1200 por 630
    Y og:image:alt describe el sello de la revista
    Y twitter:image es la misma tarjeta
    Y twitter:card es summary_large_image
    Y twitter:title y twitter:description están declarados
    Y el JSON-LD de Periodical incluye el logo cuadrado

  Escenario: Una entrada con imagen destacada no sustituye la propia
    Dado un contenido singular con imagen destacada
    Cuando se emiten los metadatos del documento
    Entonces og:image es la imagen destacada
    Y twitter:image es esa misma imagen

  Escenario: El texto alternativo de la imagen destacada no se inventa
    Dado un contenido singular con imagen destacada
    Cuando la imagen tiene texto alternativo en la Media Library
    Entonces og:image:alt y twitter:image:alt repiten ese texto
    Pero si no lo tiene, no se emite og:image:alt
