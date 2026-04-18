#!/bin/bash

# Script de pós-deploy para Clever Cloud
# Garante que o ambiente esteja otimizado e o banco atualizado

echo "--- Iniciando tarefas de pós-deploy ---"

echo "1. Rodando Migrations..."
php artisan migrate --force

echo "2. Criando link simbólico de storage..."
php artisan storage:link

echo "3. Otimizando configurações..."
php artisan config:cache

echo "4. Otimizando rotas..."
php artisan route:cache

echo "5. Otimizando views..."
php artisan view:cache

echo "--- Deploy finalizado com sucesso! ---"
