#!/bin/bash

# Script para desplegar automáticamente a GitHub Pages
# Uso: ./deploy.sh "mensaje de commit"

echo "🚀 Desplegando Logos et Spes a GitHub Pages..."

# Verificar que estamos en el directorio correcto
if [ ! -f "prototype/front-page.html" ]; then
    echo "❌ Error: No se encontró el prototipo. Ejecuta desde el directorio raíz."
    exit 1
fi

# Agregar todos los archivos
git add .

# Commit con mensaje personalizado o por defecto
if [ -n "$1" ]; then
    git commit -m "$1"
else
    git commit -m "Deploy: Actualización del prototipo Logos et Spes"
fi

# Push a GitHub
git push origin main

echo "✅ Despliegue completado!"
echo "🌐 Tu sitio estará disponible en: https://tu-usuario.github.io/revistalogos"
