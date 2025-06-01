
# Carteira Financeira API

Este é um projeto de uma API RESTful para uma carteira financeira, desenvolvido em Laravel 11. Ele permite que usuários se registrem, autentiquem, gerenciem saldos em carteiras, realizem depósitos, transferências entre carteiras e revertam transferências.

## Funcionalidades
- **Registro e autenticação de usuários** (`/api/register`, `/api/login`, `/api/logout`)
- **Depósito de valores** em uma carteira (`/api/deposit`)
- **Transferência de valores** entre carteiras (`/api/transfer`)
- **Reversão de transferências** (`/api/reverse`)

A API utiliza **MySQL** como banco de dados principal e a autenticação é feita via **Sanctum**.

## Pré-requisitos
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- SQLite (para testes)
- Node.js e NPM (opcional)
- Insomnia ou Postman
- Git
- Sistema: Windows

## Estrutura do Projeto
- `app/Http/Controllers`: Controladores da API
- `app/Models`: Modelos Eloquent
- `database/migrations`: Migrations
- `routes/api.php`: Rotas da API
- `tests/Feature`: Testes de integração

## Configuração do Ambiente

### 1. Clonar o Repositório
```bash
git clone https://github.com/jonjgc/carteira-financeira.git
cd carteira-financeira
```

### 2. Instalar Dependências
```bash
composer install
```

### 3. Configurar o .env
```bash
cp .env.example .env
```

Configure o MySQL no `.env`:
```env
DB_DATABASE=carteira_financeira
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Gerar chave da aplicação:
```bash
php artisan key:generate
```

### 4. Criar e Migrar Banco de Dados
```bash
php artisan migrate
```

### 5. Iniciar Servidor
```bash
php artisan serve
```

Acesse em: `http://localhost:8000`

## Testes no Insomnia

### 1. Registro
`POST /api/register`

- Método POST
- Endpoint `register`
- headers
  - `Content-Type: application/json`
  - `Accept: application/json` 
- BODY (JSON):
```
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

- Resposta esperada:
  - Status: 201
  - Body:
```
{
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "user": 
    {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com",
        ...
    }
}
```
- Copie o `token` e atualize a variável `token` no ambiente do Insomnia.

### 2. Login
`POST /api/login`
- Método: POST
- Endpoint: `/login`
- Headers:
    - `Content-Type: application/json`
    - `Accept: application/json`
- Body (JSON):
```
{
    "email": "test@example.com",
    "password": "password123"
}
```

- Resposta esperada:
    - Status: 200
    - Body:
```
{
    "token": "2|yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy",
    "user": {
        "id": 1,
        "email": "test@example.com",
        ...
    }
}
```

- Ação: Atualize a variável `token` no insomnia. 

### 3. Depósito
`POST /api/deposit`

- Método: POST
- Endpoint: `deposit`
- Headers:
    - `Content-Type: application/json`
    - `Accept: application/json`
    - `Authorization: Bearer {{token}}`

- Body (JSON):
```
{
    "amount": 100.00
}
```

- Resposta esperada:
    - Status: 200
    - Body:
```
{
    "message": "Depósito realizado com sucesso",
    "balance": 100.00
}
```

- Nota: O `amount` deve ser maior que 0.

### 4. Transferência
`POST /api/transfer`

- Método: POST
- Endpoint: `/transfer`
- Headers:
    - `Content-Type: application/json`
    - `Accept: application/json`
    - `Authorization: Bearer {{token}}`

- Body (JSON):
```
{
    "receiver_id": 2,
    "amount": 25.00
}
```

- Resposta Esperada:
    - Status: 200
    - Body:
```
{
    "message": "Transferência realizada com sucesso",
    "balance": 75.00
}
```

- Pré-requisito: Crie outro usuário (via `api/register`) para obter um `receiver_id`. Verifique o saldo suficiente.

### 5. Reversão de Transferência
`POST /api/reverse`

- Método: POST
- Endpoint: `/reverse`
- Headers:
    - `Content-Type: application/json`
    - `Accept: application/json`
    - `Authorization: Bearer {{token}}`

- Body (JSON):
```
{
    "transaction_id": 7
}
```

- Resposta Esperada:
    - Status: 200
    - Body:
```
{
    "message": "Transação revertida com sucesso",
    "balance": 100.00
}
```

- Pré-requisito:
    - Execute uma transferencia primeiro.
    - Consulte a tabela `transactions` para obter um `transaction_id` válido (ex: `SELECT id, wallet_id, type FROM transactions;`).
    - O usuário deve ser remetente ou destinatário da transação.

### 6. Logout
`POST /api/logout`

- Método: POST
- Endpoint: `/logout`
- Headers:
    - `Content-Type: application/json`
    - `Accept: application/json`
    - `Authorization: Bearer {{token}}`

- Body: Vazio
- Resposta Esperada:
    -Status: 200
    - Body: 
```
{
    "message": "Logout realizado com sucesso"
}
```

## Fluxo de Teste Completo no Insomnia
    - Registre dois usuários (`/api/register`).
    - Faça login com o primeiro usuário (`/api/login`) e salve o token.
    - Realize um depósito (`/api/deposit`).
    - Faça uma transferência para o segundo usuário (`/api/transfer`).
    - Consulte a tabela `transactions` no MySQL para obter o `transaction_id`.
    - Reverta a transferência (`/api/reverse`).
    - Faça logout (`/api/logout`).

## Estrutura do Banco de Dados
- **users**: id, name, email, password, timestamps
- **wallets**: id, user_id (FK), balance, timestamps
- **transactions**: id, wallet_id, amount, type, related_transaction_id, timestamps