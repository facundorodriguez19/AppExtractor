#!/usr/bin/env bash
# Instalar dependencias de Node
npm install
# Compilar los estilos y scripts con Vite
npm run build
# Forzar la limpieza y creación del enlace de imágenes
rm -rf public/storage
php artisan storage:link
