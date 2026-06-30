# Sistema de Locação de Veículos — Locadora

Sistema de gestão para locadora de veículos desenvolvido em Laravel, contemplando cadastro de veículos, gestão de usuários com controle de acesso por perfil, contratos de locação, check-in/check-out e auditoria de atividades.

Projeto desenvolvido como parte do programa de estágio da **Sagatech**.

---

## Tecnologias utilizadas

- **Laravel 12** — framework PHP principal
- **Livewire 3** — componentes reativos (tabela de veículos com busca em tempo real)
- **PostgreSQL** — banco de dados relacional
- **Tailwind CSS** — estilização
- **Flowbite** — componentes de interface
- **Laravel Breeze** — autenticação
- **Spatie Permission** — base da estrutura de roles

---

## Funcionalidades

### Módulos do sistema

- **Veículos** — cadastro completo (placa, RENAVAM, marca, modelo, status), com busca e filtro em tempo real via Livewire
- **Usuários** — gestão de clientes, operadores e gerentes
- **Contratos** — abertura de locação com cliente, veículo, datas e valores
- **Check-in** — registro de saída do veículo (quilometragem, combustível, checklist de itens, avarias)
- **Check-out** — registro de devolução (quilometragem final, comparação de avarias, cálculo de valores extras)
- **Manutenção** — histórico de custos e serviços por veículo
- **Alarmes** — notificações automáticas (documentação vencida, devoluções atrasadas)
- **Logs de atividade** — auditoria de todas as ações realizadas no sistema

---

## Pré-requisitos

Antes de instalar, garanta que sua máquina possui:

- PHP 8.2 ou superior
- Composer
- Node.js e npm
- PostgreSQL 14 ou superior
- Git

---

## Como executar o projeto localmente

### 1. Clonar o repositório

```bash
git clone <url-do-repositorio>
cd locadora
```

### 2. Instalar as dependências PHP

```bash
composer install
```

### 3. Instalar as dependências JavaScript

```bash
npm install
```

### 4. Configurar o ambiente

Copie o arquivo de exemplo de variáveis de ambiente:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Abra o `.env` e configure a conexão com o PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=locadora
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

> Crie previamente o banco `locadora` no PostgreSQL (via `psql` ou pgAdmin) antes do próximo passo.

### 5. Rodar as migrations e popular o banco

```bash
php artisan migrate --seed
```

Esse comando cria todas as tabelas do sistema e popula os perfis de acesso (`cliente`, `operador`, `gerente`).

### 6. Compilar os assets

```bash
npm run build
```

Para desenvolvimento com hot-reload:

```bash
npm run dev
```

### 7. Iniciar o servidor

```bash
php artisan serve
```

A aplicação estará disponível em `http://localhost:8000`.

---

## Usuários de teste

Após rodar o seed, crie usuários de teste para cada perfil via Tinker:

```bash
php artisan tinker
```

```php
// Gerente
$role = App\Models\Role::where('name', 'gerente')->first();
App\Models\Usuario::create([
    'cpf' => '12345678901', 'name' => 'Gerente Teste',
    'login' => 'gerente@locadora.com',
    'password' => bcrypt('123456'), 'role_id' => $role->id,
]);

// Operador
$role = App\Models\Role::where('name', 'operador')->first();
App\Models\Usuario::create([
    'cpf' => '98765432100', 'name' => 'Operador Teste',
    'login' => 'operador@locadora.com',
    'password' => bcrypt('123456'), 'role_id' => $role->id,
]);

// Cliente
$role = App\Models\Role::where('name', 'cliente')->first();
App\Models\Usuario::create([
    'cpf' => '11122233344', 'name' => 'Cliente Teste',
    'login' => 'cliente@locadora.com',
    'password' => bcrypt('123456'), 'role_id' => $role->id,
]);
```

Senha padrão para todos: `123456`

---

## Estrutura do banco de dados

O sistema possui 12 tabelas principais:
roles                  → perfis de acesso (cliente, operador, gerente)
usuarios               → dados de login e cadastro de usuários
veiculos               → ficha técnica e status da frota
documento_veiculo      → IPVA, licenciamento, seguro
manutencao             → histórico de manutenções por veículo
alarmes                → notificações automáticas do sistema
contratos              → locações abertas, em andamento e encerradas
motorista_autorizado   → motoristas vinculados a um contrato
check_in               → registro de saída do veículo
check_out              → registro de devolução do veículo
valor_extra            → cobranças adicionais (km excedente, avarias, etc)
log_atividade          → auditoria de ações dos usuários

O relacionamento entre as tabelas segue o diagrama entidade-relacionamento definido na fase de planejamento do projeto (anexo ao repositório).

---

## Rotas principais

| Rota | Método | Descrição | Acesso |
|---|---|---|---|
| `/login` | GET/POST | Autenticação | Público |
| `/veiculos` | GET | Listagem de veículos | Todos os perfis logados |
| `/veiculos/create` | GET | Cadastro de veículo | Operador, Gerente |
| `/contratos` | GET | Listagem de contratos | Todos (filtrado por cliente) |
| `/contratos/create` | GET | Abertura de locação | Todos os perfis logados |
| `/check-in/{contrato}/create` | GET | Registro de saída | Operador, Gerente |
| `/check-out/{contrato}/create` | GET | Registro de devolução | Operador, Gerente |
| `/usuarios` | GET | Gestão de usuários | Operador, Gerente |
| `/relatorios` | GET | Relatórios gerenciais | Gerente |
| `/logs` | GET | Auditoria do sistema | Gerente |

---

## Decisões técnicas

**Autenticação customizada** — o sistema utiliza o campo `login` em vez de `email` como identificador de acesso, exigindo customização do fluxo padrão do Laravel Breeze (`LoginRequest`, `config/auth.php` e o Model `Usuario`).

**RBAC via Gates** — o controle de permissões por perfil foi implementado usando Gates nativos do Laravel (`is-staff`, `is-gerente`), definidos em `AppServiceProvider`, em vez de um pacote de permissões completo, dado o número reduzido e fixo de perfis do sistema.

**Fluxo de contrato em etapas** — a locação é dividida em três momentos (abertura, check-in, check-out), cada um com sua própria tabela e tela, refletindo o processo real de uma locadora e evitando formulários únicos excessivamente longos.

**Livewire na tabela de veículos** — aplicado seletivamente apenas onde a interatividade agrega valor real (busca e filtro em tempo real), mantendo o restante do CRUD em Blade tradicional para simplicidade.



---

## Autor

Desenvolvido por Sarah — estágio Sagatech, 2026.