#!/usr/bin/env python3
"""
Script para actualizar el menú de navegación en todas las páginas HTML
"""

import re
import os
from pathlib import Path

# Nuevo menú de navegación
NEW_NAV = '''            <div class="nav__container">
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
            </div>'''

# Patrón para encontrar el bloque de navegación completo
# Captura desde nav__container hasta su cierre
NAV_PATTERN = re.compile(
    r'(<div class="nav__container">.*?</div>\s*</div>)',
    re.DOTALL
)

# Lista de archivos HTML a actualizar
FILES = [
    "index.html",
    "page-acerca.html",
    "page-comite.html",
    "page-contacto.html",
    "page-enviar-colaboracion.html",
    "page-normas.html",
    "page-revista.html",
    "single-issue.html",
    "single-article.html",
    "archive-issue.html",
    "archive-article.html",
    "blog-index.html",
    "single-post.html",
]

def update_nav_in_file(filepath):
    """Actualiza el menú de navegación en un archivo HTML"""
    if not os.path.exists(filepath):
        print(f"⚠️  Archivo no encontrado: {filepath}")
        return False
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Buscar y reemplazar el bloque de navegación
        # Reemplazar todo el contenido entre nav__container y su cierre
        new_content = re.sub(
            r'<div class="nav__container">.*?</div>\s*</div>',
            NEW_NAV,
            content,
            flags=re.DOTALL
        )
        
        # Verificar si hubo cambios
        if content != new_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"✅ Actualizado: {filepath}")
            return True
        else:
            print(f"⏭️  Sin cambios: {filepath}")
            return False
    except Exception as e:
        print(f"❌ Error en {filepath}: {e}")
        return False

def main():
    """Función principal"""
    print("🔄 Actualizando menú de navegación...")
    print("")
    
    updated = 0
    for file in FILES:
        if update_nav_in_file(file):
            updated += 1
    
    print("")
    print(f"✨ Proceso completado! {updated} archivos actualizados.")

if __name__ == "__main__":
    main()

