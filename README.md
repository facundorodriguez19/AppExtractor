# Sistema de Processamento de Notas Fiscais (OCR + IA)

Este projeto é uma aplicação Laravel completa para extração automatizada de dados de notas fiscais brasileiras.

## Requisitos
- PHP 8.2+
- MySQL 8+
- Composer
- Chave de API [OCR.space](https://ocr.space/ocrapi)
- Chave de API [OpenAI](https://platform.openai.com/)

## Instalação

1. Clone o repositório e acesse a pasta.
2. Copie o arquivo `.env.example` para `.env`:
   ```bash
   cp .env.example .env
   ```
3. Preencha as chaves `OCR_SPACE_API_KEY` e `OPENAI_API_KEY` no `.env`.
4. Instale as dependências do Composer:
   ```bash
   composer install
   ```
5. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```
6. Configure o banco de dados no `.env` e rode as migrations:
   ```bash
   php artisan migrate
   ```
7. Crie o link simbólico para o storage:
   ```bash
   php artisan storage:link
   ```
8. Inicie o worker da fila (necessário para processar notas):
   ```bash
   php artisan queue:work
   ```
9. Inicie o servidor:
   ```bash
   php artisan serve
   ```

## Stack
- **Backend**: Laravel 10
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **OCR**: OCR.space
- **IA**: OpenAI GPT-4o
