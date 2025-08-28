# Teste OnFly para full Stack 

Projeto **full-stack** com frontend em **Quasar SPA** e backend em **Laravel API**. Este documento fornece todas as informações necessárias para a configuração e execução do projeto, incluindo variáveis de ambiente, Docker e testes.

-----

## Começando

Siga estas etapas para configurar e executar o projeto pela primeira vez.

### Pré-requisitos

Certifique-se de ter o **Docker** e o **Docker Compose** instalados em sua máquina.

### Passo 1: Clonar o Repositório

Primeiro, clone o projeto para sua máquina local:

```bash
git clone <URL_DO_REPOSITORIO>
cd onfly-project
```

### Passo 2: Configurar as Variáveis de Ambiente

As configurações de ambiente são essenciais para o correto funcionamento da aplicação.

#### Backend (Laravel)

1.  Na pasta `backend`, renomeie o arquivo `.env.example` para `.env`:

    ```bash
    cp .env.example .env
    ```

2.  Abra o arquivo `.env` e configure as variáveis de **banco de dados** e **e-mail**:

    ```env
    # Configurações do Banco de Dados
    DB_CONNECTION=mysql
    DB_HOST=mysql # O nome do serviço no docker-compose.yml
    DB_PORT=3306
    DB_DATABASE=onfly
    DB_USERNAME=onfly
    DB_PASSWORD=onfly

    # Configurações de E-mail
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.mailtrap.io # Exemplo de host
    MAIL_PORT=2525
    MAIL_USERNAME=seu_usuario
    MAIL_PASSWORD=sua_senha
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="no-reply@onfly.com"
    MAIL_FROM_NAME="${APP_NAME}"
    ```

> Se você não for usar o banco de dados via Docker, ajuste `DB_HOST`, `DB_PORT`, `DB_USERNAME` e `DB_PASSWORD` para corresponder à sua configuração local.

#### Frontend (Quasar)

1.  Na pasta `frontend`, abra o arquivo `quasar.config.js`.

2.  Edite a variável `API` para apontar para o endpoint do seu backend. Se estiver usando Docker, `localhost:8000` é o valor padrão.

    ```javascript
    env: {
      API: 'http://localhost:8000'
    },
    ```

3.  Se a sua aplicação frontend estiver em um subdiretório do servidor web, descomente e ajuste a variável `publicPath`:

    ```javascript
    // publicPath: '/onfly/'
    ```

### Passo 3: Iniciar os Containers do Docker

Suba os serviços do Docker com o comando a seguir. A flag `--build` garante que as imagens sejam construídas.

```bash
docker compose up -d --build
```

### Passo 4: Configurar o Backend

Execute os comandos abaixo para gerar a chave de criptografia do Laravel e a chave secreta do JWT (JSON Web Token) no container do backend.

```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan jwt:secret
```

### Passo 5: Executar Migrações e Seeds do Banco de Dados

Crie as tabelas necessárias e o usuário administrador padrão com os seguintes comandos:

```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

### Passo 6: Iniciar o Serviço de Filas

O projeto utiliza o sistema de filas do Laravel para o envio de e-mails. É fundamental manter o processo `queue:work` ativo em um terminal separado.

```bash
docker compose exec backend php artisan queue:work
```

-----

## Testes

### Executar Testes do Backend

Para rodar os testes unitários e de integração do backend, execute o seguinte comando:

```bash
docker compose exec backend php artisan test
```
