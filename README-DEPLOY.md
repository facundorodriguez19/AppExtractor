# Guia de Deploy - Clever Cloud

Este documento explica como colocar o **NotaIA** em produção no Clever Cloud.

## 1. Configuração da Aplicação
1. Acesse o [Console do Clever Cloud](https://console.clever-cloud.com/).
2. Clique em **"Create..."** > **"An application"**.
3. Selecione **PHP** como a stack da aplicação.
4. Conecte seu repositório GitHub ou escolha **"Git"** para fazer o push manualmente.
5. Escolha o tamanho da instância (Nano ou XS são suficientes para começar).

## 2. Add-ons Necessários
A aplicação requer dois complementos:

### MySQL
1. Vá em **"Create..."** > **"An add-on"** > **"MySQL"**.
2. Siga os passos e nomeie-o (ex: `notaia-db`).
3. Vincule o add-on à sua aplicação PHP.

### FS Bucket (Para Armazenamento de PDF/Imagens)
Como o sistema de arquivos do Clever Cloud é efêmero, precisamos de um Bucket para persistir os uploads.
1. Vá em **"Create..."** > **"An add-on"** > **"FS Bucket"**.
2. Vincule-o à sua aplicação.
3. No painel do FS Bucket, anote o ID.
4. Nas configurações da sua aplicação, vá em **"Cellar / FS Bucket"** e configure o mount point:
   - **Moun Point**: `/storage/app/public/notas`

## 3. Variáveis de Ambiente
No painel da aplicação, vá em **"Environment Variables"** e adicione as seguintes:

| Chave | Valor sugerido |
|-------|----------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | *(Gere uma localmente com `php artisan key:generate --show`)* |
| `DB_CONNECTION` | `mysql` |
| `OCR_SPACE_API_KEY` | *(Sua chave do ocr.space)* |
| `GEMINI_API_KEY` | *(Sua chave da Gemini API)* |
| `GEMINI_MODEL` | `gemini-3.1-pro-preview` |
| `GEMINI_THINKING_LEVEL` | `medium` |
| `QUEUE_CONNECTION` | `database` |

> **Dica**: As variáveis de conexão com o banco (host, user, password) são injetadas automaticamente pelo Clever Cloud se você vinculou o add-on.

## 4. Deploy via Git
Adicione o remote do Clever Cloud e faça o push:
```bash
clever link [APP_ID]
git push clever master
```

## 5. Configurando o Worker de Filas
Para que as notas sejam processadas em background, precisamos de um worker. No Clever Cloud, você pode usar uma variável especial para rodar um comando persistente:

1. Adicione a variável de ambiente:
   - **Key**: `CC_RUN_COMMAND`
   - **Value**: `php artisan queue:work --tries=3 --timeout=90`

2. Reinicie a aplicação.

---
**Nota**: O script em `scripts/post-deploy.sh` garantirá que as migrations e caches sejam executados automaticamente a cada novo deploy.
