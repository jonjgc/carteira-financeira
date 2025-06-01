
# Carteira Financeira API

Este é um projeto de uma API RESTful para uma carteira financeira, desenvolvido em Laravel 11. Ele permite que usuários se registrem, autentiquem, gerenciem saldos em carteiras, realizem depósitos, transferências entre carteiras e revertam transferências.

## Funcionalidades
- **Registro e autenticação de usuários** (`/api/register`, `/api/login`, `/api/logout`)
- **Depósito de valores** em uma carteira (`/api/deposit`)
- **Transferência de valores** entre carteiras (`/api/transfer`)
- **Reversão de transferências** (`/api/reverse`)

A API utiliza **MySQL** como banco de dados principal e **SQLite em memória** para testes automatizados. A autenticação é feita via **Sanctum**.

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

### 2. Login
`POST /api/login`

### 3. Depósito
`POST /api/deposit`

### 4. Transferência
`POST /api/transfer`

### 5. Reversão de Transferência
`POST /api/reverse`

### 6. Logout
`POST /api/logout`

Veja o README completo para exemplos detalhados de requisição e resposta.

## Estrutura do Banco de Dados
- **users**: id, name, email, password, timestamps
- **wallets**: id, user_id (FK), balance, timestamps
- **transactions**: id, wallet_id, amount, type, related_transaction_id, timestamps
