# Cadastro System

Sistema web para cadastro e gestão de registros de profissionais de saúde em eventos. Permite coletar informações como nome, telefone, email, profissão, conselho regional e vínculo com eventos, com exportação dos dados para Excel.

## O que faz

- Cadastro de profissionais através de formulário com validação
- Painel com listagem paginada e pesquisa nos registros
- Exportação filtrada por período para planilha XLSX
- Gestão de usuários com dois níveis de acesso (admin e usuário comum)
- Controle de ownership: cada usuário só vê os registros que criou

## Stack

PHP 8+, MySQL, Bootstrap 5.3, Composer.

Sem frameworks — a ideia foi resolver com o que a linguagem oferece de base, organizando o código em módulos simples.

## Estrutura

```
config/          env, sessão e banco
src/             classes (Csrf, Flash, Logger, Validator) + helpers globais
public/          documentos acessíveis via web (toda a lógica e páginas)
views/partials/  header, navbar e footer compartilhados
Database/        schema e seed fictício
storage/logs/    logs da aplicação (não versionado)
```

O Document Root do servidor aponta para `public/`. `config/`, `src/`, `views/` e `storage/` ficam fora do alcance do browser, protegidos por `.htaccess`.

## Como rodar

```bash
# 1. Clonar e entrar no diretório
git clone https://github.com/SeuUsuario/cadastro-system.git
cd cadastro-system

# 2. Instalar dependências
composer install

# 3. Copiar e editar o .env com suas credenciais
cp .env.example .env
# editar .env

# 4. Criar o banco
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS formulario CHARACTER SET utf8mb4;"
mysql -u root -p formulario < Database/schema.sql

# 5. Subir (se estiver com PHP built-in server)
php -S localhost:8000 -t public/
```

## Credenciais de teste

Depois de carregar o seed (`Database/seed_example.sql`):

| Usuário     | Senha      | Perfil |
|-------------|------------|--------|
| `admin_demo` | `Demo@123` | admin  |
| `user_demo`  | `Demo@123` | user   |

Ambos têm a mesma senha porque é apenas um seed de demonstração. Troque antes de subir em produção.

## Regras de acesso

**Admin** — acesso completo. Cria, edita e remove usuários e cadastros. Vê todos os registros e pode exportar para Excel.

**Usuário comum** — cria cadastros, vê apenas os que criou. Não tem acesso à gestão de usuários, exportação nem edição de cadastros alheios. Usuário não pode se deletar.

## Decisões de segurança

- Senhas com `password_hash()` (bcrypt). Nada de MD5.
- Todas as queries usam PDO com prepared statements — não existe concatenação de valores em SQL em lugar nenhum do código.
- Token CSRF em cada formulário, verificado com `hash_equals()`. Gerado com `random_bytes()` e regenerado após login.
- `session_regenerate_id(true)` no login bem-sucedido.
- Sessão configurada com `HttpOnly`, `SameSite=Lax` e `Secure` quando HTTPS.
- Ações destrutivas só via POST. Deleção de usuários e cadastros protegida por CSRF + verificação de admin.
- Limite de 5 tentativas de login por minuto por IP.
- Credenciais do banco de dados em `.env`, nunca no código.
- Dados sensíveis (representante, timestamp) são derivados da sessão e gerados no servidor, nunca recebidos do cliente.
- Logs técnicos separados em `storage/logs/`, sem exposição de stack trace para o usuário final.
- Validação server-side em todos os campos do formulário.

## Dados de exemplo

O arquivo `Database/seed_example.sql` contém dados 100% fictícios. O schema original do projeto usava dados reais que foram removidos. O repositório público não contém nenhum dado real de pessoas ou empresas.

## Criando o primeiro admin manualmente

Se não quiser usar o seed, rode:

```sql
INSERT INTO users (username, password, is_admin, nome, sobrenome) VALUES (
  'seu_admin',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1,
  'Seu',
  'Nome'
);
```

Senha: `Demo@123` — o hash já está na query, então basta trocar o username e nome.
