#!/usr/bin/env python3
"""
Script para actualizar todas las referencias de page-produccion.html a archive-issue.html
"""

import os
import re

# Lista de archivos HTML a actualizar
FILES = [
    "index.html",
    "page-acerca.html",
    "page-comite.html",
    "page-contacto.html",
    "page-enviar-colaboracion.html",
    "page-normas.html",
    "page-revista.html",
    "page-politicas.html",
    "page-enlaces.html",
    "single-issue.html",
    "single-article.html",
    "archive-issue.html",
    "archive-article.html",
    "blog-index.html",
    "single-post.html",
]

def update_file(filepath):
    """Actualiza las referencias en un archivo HTML"""
    if not os.path.exists(filepath):
        print(f"⚠️  Archivo no encontrado: {filepath}")
        return False
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Reemplazar page-produccion.html por archive-issue.html
        new_content = content.replace('page-produccion.html', 'archive-issue.html')
        
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
    print("🔄 Actualizando referencias de Números Publicados...")
    print("")
    
    updated = 0
    for file in FILES:
        if update_file(file):
            updated += 1
    
    print("")
    print(f"✨ Proceso completado! {updated} archivos actualizados.")

if __name__ == "__main__":
    main()

