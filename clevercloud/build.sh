#!/usr/bin/env bash
# Instalar dependencias de Node
npm install
# Compilar los estilos y scripts con Vite
npm run build
# Enlace de storage para las imágenes
php artisan storage:link --force
