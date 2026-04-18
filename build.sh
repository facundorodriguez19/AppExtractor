#!/usr/bin/env bash
# exit on error
set -o errexit

echo "--- Iniciando proceso de construcción para Render ---"

# Instalar dependencias de PHP
composer install --no-dev --optimize-autoloader

# Instalar y compilar dependencias de Node.js (Vite/Tailwind)
npm install
npm run build

# Limpiar y preparar caché de Laravel
php artisan optimize:clear
php artisan optimize

echo "--- Construcción finalizada con éxito ---"
