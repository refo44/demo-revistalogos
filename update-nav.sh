#!/bin/bash

# Script para actualizar el menú de navegación en todas las páginas HTML

# Nuevo menú de navegación
NEW_NAV='
            <div class="nav__container">
              <!-- Main Navigation Row -->
              <ul class="nav__list nav__list--main" id="main-nav">
                <li class="nav__item">
                  <a href="index.html" class="nav__link">Inicio</a>
                </li>
                <li class="nav__item">
                  <a href="single-issue.html" class="nav__link">N Actual</a>
                </li>
                <li class="nav__item">
                  <a href="page-acerca.html" class="nav__link">Acerca de</a>
                </li>
                <li class="nav__item">
                  <a href="page-comite.html" class="nav__link">Directorio</a>
                </li>
                <li class="nav__item">
                  <a href="page-enviar-colaboracion.html" class="nav__link">Como publicar</a>
                </li>
                <li class="nav__item">
                  <a href="page-politicas.html" class="nav__link">Políticas de la editorial</a>
                </li>
                <li class="nav__item">
                  <a href="page-produccion.html" class="nav__link">Producción Intelectual</a>
                </li>
                <li class="nav__item">
                  <a href="page-contacto.html" class="nav__link">Contactos</a>
                </li>
                <li class="nav__item">
                  <a href="page-enlaces.html" class="nav__link">Enlaces de Interés</a>
                </li>
              </ul>
            </div>'

# Lista de archivos HTML a actualizar (excluyendo partials y backups)
FILES=(
  "index.html"
  "page-acerca.html"
  "page-comite.html"
  "page-contacto.html"
  "page-enviar-colaboracion.html"
  "page-normas.html"
  "page-revista.html"
  "single-issue.html"
  "single-article.html"
  "archive-issue.html"
  "archive-article.html"
  "blog-index.html"
  "single-post.html"
)

# Función para actualizar el menú en un archivo
update_nav_in_file() {
  local file="$1"
  
  if [ ! -f "$file" ]; then
    echo "⚠️  Archivo no encontrado: $file"
    return
  fi
  
  # Crear archivo temporal
  local temp_file=$(mktemp)
  
  # Usar awk para reemplazar el bloque de navegación
  awk -v new_nav="$NEW_NAV" '
    BEGIN { in_nav = 0; nav_start = 0; }
    /<div class="nav__container">/ { 
      in_nav = 1; 
      nav_start = NR; 
      print new_nav; 
      next; 
    }
    in_nav && /<\/div>/ && /nav__container/ { 
      in_nav = 0; 
      next; 
    }
    in_nav { next; }
    { print; }
  ' "$file" > "$temp_file"
  
  # Verificar si hubo cambios
  if ! cmp -s "$file" "$temp_file"; then
    mv "$temp_file" "$file"
    echo "✅ Actualizado: $file"
  else
    rm "$temp_file"
    echo "⏭️  Sin cambios: $file"
  fi
}

# Actualizar todos los archivos
echo "🔄 Actualizando menú de navegación..."
echo ""

for file in "${FILES[@]}"; do
  update_nav_in_file "$file"
done

echo ""
echo "✨ Proceso completado!"

