# language: es
Característica: Hero de la portada
  Como lectora de la revista
  quiero ver el nombre solo en el título del hero
  y un banner de paisaje sin letras superpuestas
  para que "LOGO ET SPES" no se repita en la imagen ni en el párrafo
  # Issue #40. Ejecutan: tests/Unit/HomeHeroPresentationTest (composer test:unit).

  Escenario: El título del hero es el único sitio del bloque con el nombre
    Dado el hero de la portada
    Cuando se muestra el título, el subtítulo y la descripción
    Entonces el título es LOGO ET SPES
    Y el subtítulo es Revista de Filosofía
    Y la descripción no contiene LOGO ET SPES
    Y el resto de la descripción institucional no cambia

  Escenario: El banner del hero no lleva letras pintadas
    Dado el archivo de banner del hero
    Cuando se sirve la imagen de fondo
    Entonces es un JPEG con la proporción 1714 por 356 del hero
    Y no lleva el letrero LOGO ET SPES pintado en la foto
