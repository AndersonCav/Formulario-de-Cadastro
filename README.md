# Cadastro System

Sistema web para cadastro e gestão de registros de profissionais de saúde em eventos.

**Versão pública sanitizada** — todos os dados contidos neste repositório são fictícios. Este projeto foi refinado a partir de um sistema interno e passou por processo de remoção de dados reais, endurecimento de segurança e reorganização estrutural para exposição como case técnico.

## Stack

- **PHP 8.0+** (server-side)
- **MySQL 5.7+** / **MariaDB 10.4+** (banco de dados)
- **Bootstrap 5.3** (front-end)
- **Composer** (gerenciamento de dependências)
- **PhpSpreadsheet** (exportação XLSX)
- **PHP-Dotenv / loader manual** (variáveis de ambiente)

## Arquitetura

```
.
├── config/              # Bootstrap de ambiente, sessão e banco
│   ├── env.php          # Loader manual de variáveis de ambiente (arquivo .env)
│   ├── database.php     # Conexão PDO com prepared statements
│   └── session.php      # Inicialização segura de sessão
├── src/                 # Classes utilitárias
│   ├── Csrf.php         # Proteção CSRF (token generation/validation)
│   ├── Flash.php        # Mensagens flash de sessão (sucesso/erro)
│   ├── Logger.php       # Logger em arquivos (storage/logs/)
│   └── Validator.php    # Validação server-side de inputs
├── public/              # Diretório raiz acessível via web server
│   ├── index.php        # Login
│   ├── dashboard.php    # Painel principal
│   ├── form.php         # Formulário de cadastro
│   ├── process_form.php # Processamento do formulário
│   ├── view_forms.php   # Listagem de cadastros
│   ├── edit_form.php    # Edição de cadastro (admin)
│   ├── update_form.php  # Atualização de cadastro (admin)
│   ├── remove_form.php  # Remoção de cadastro (admin, POST)
│   ├── create_user.php  # Formulário de criação de usuário (admin)
│   ├── create_user_process.php # Processamento de criação
│   ├── view_users.php   # Listagem de usuários (admin)
│   ├── edit_user.php    # Edição de usuário (admin)
│   ├── update_user.php  # Atualização de usuário (admin)
│   ├── remove_user.php  # Remoção de usuário (admin, POST)
│   ├── logout.php       # Logout com destruição de sessão
│   └── export.php       # Exportação XLSX (admin)
├── views/partials/      # Templates reutilizáveis
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── Database/            # Schema e seeds
│   ├── schema.sql       # Estrutura limpa do banco
│   └── seed_example.sql # Dados totalmente fictícios
├── storage/logs/        # Logs da aplicação (ignorados pelo git)
├── .env.example         # Modelo de configuração de ambiente
├── .gitignore
└── composer.json
```

## Configuração

### Pré-requisitos

- PHP 8.0 ou superior
- MySQL 5.7+ ou MariaDB 10.4+
- Composer
- Servidor web (Apache ou Nginx)

### Instalação

1. **Clone o repositório:**

   ```bash
   git clone https://github.com/SeuUsuario/cadastro-system.git
   cd cadastro-system
   ```

2. **Instale as dependências:**

   ```bash
   composer install
   ```

3. **Configure o ambiente:**

   ```bash
   cp .env.example .env
   ```

   Edite `.env` com suas credenciais de banco de dados.

4. **Prepare o banco de dados:**

   ```bash
   mysql -u root -p < Database/schema.sql
   ```

   Opcionalmente, carregue dados de exemplo fictícios:

   ```bash
   mysql -u root -p formulario < Database/seed_example.sql
   ```

5. **Configure o servidor web:**

   Aponte o document root para o diretório `public/`. Exemplo com Apache (`.htaccess`):

   ```apache
   DocumentRoot "/caminho/para/cadastro-system/public"
   <Directory "/caminho/para/cadastro-system/public">
       AllowOverride All
       Require all granted
   </Directory>
   ```

   Para testes rápidos com o servidor embutido do PHP:

   ```bash
   php -S localhost:8000 -t public/
   ```

### Credenciais de exemplo

Após carregar o seed:

| Usuário    | Senha      | Tipo          |
|------------|-----------|---------------|
| `admin_demo`  | `Demo@123` | Administrador |
| `user_demo`   | `Demo@123` | Usuário       |

> **Atenção:** altere estas credenciais em qualquer ambiente que não seja local/demo.

## Controle de Acesso

| Recurso                    | Admin | Usuário |
|---------------------------|-------|---------|
| Dashboard                 |       |         |
| Cadastrar formulário      |       |         |
| Ver cadastros próprios    |       |         |
| Ver todos os cadastros    |       |         |
| Editar/remover cadastros  |       |         |
| Exportar Excel            |       |         |
| Criar/editar/remover users|       |         |

Regras:
- **Admin** gerencia completamente o sistema (cadastros e usuários).
- **Usuário comum** acessa apenas os cadastros que criou (ownership via `created_by_user_id`).
- Todas as ações de escrita exigem token CSRF e método POST.
- Usuário não pode remover a si mesmo.

## Melhorias de Segurança Aplicadas

### Autenticação
- Substituição de `md5()` por `password_hash()` / `password_verify()` (bcrypt).
- Regeneração de session ID após login (`session_regenerate_id(true)`).
- Regeneração de token CSRF pós-login.
- Logout com destruição completa de sessão e cookie.

### Autorização
- Todas as ações administrativas verificam `is_admin = 1` no servidor.
- Usuário comum só visualiza seus próprios registros.
- Ownership definido por `created_by_user_id` (derivado da sessão, nunca do cliente).

### SQL
- 100% das queries usam **PDO com prepared statements** e placeholders nomeados.
- Concatenação de SQL eliminada — incluindo `export.php`.

### CSRF
- Todas as forms e ações mutáveis incluem e validam token CSRF.
- Token gerado via `random_bytes()` e verificado com `hash_equals()` (timing-safe).

### Validação
- Todos os campos validados no servidor (classe `Validator`).
- Representante e timestamp gerados no servidor, nunca recebidos do cliente.
- Mensagens de erro genéricas para o usuário; detalhes técnicos em `storage/logs/`.

### Sessão
- `HttpOnly`, `SameSite=Lax`, e `Secure` (quando HTTPS) habilitados.
- Configuração centralizada em `config/session.php`.

### Dados
- Nenhum dado real permanece no repositório.
- `Database/db.sql` substituído por `schema.sql` (estrutura) + `seed_example.sql` (dados fictícios).
- Seeds com dados 100% fictícios e senhas de demonstração.

### Configuração
- Credenciais via arquivo `.env` (ignorado pelo git).
- `.env.example` com valores de exemplo.
- Falha segura quando variáveis obrigatórias estão ausentes.

### Escritas
- Todas as deleções via POST (nenhuma via GET).
- Confirmação no front-end via JS `confirm()`.

## Notas

- A versão pública deste repositório foi sanitizada e não contém dados reais de qualquer empresa ou pessoa.
- Para criar o primeiro admin manualmente:

```sql
-- Senha: Demo@123 (troque em produção!)
INSERT INTO users (username, password, is_admin, nome, sobrenome) VALUES (
  'seu_admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1,
  'Seu',
  'Nome'
);
```
