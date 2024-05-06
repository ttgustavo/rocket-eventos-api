<h1 align="center">Rocket Eventos API</h1>

## 📌 Sobre

Este projeto é uma API REST para que os usuários se inscrevam em eventos e
administradores realizarem a gestão.

Faz parte do conjunto do projeto **Rocket Eventos**.


## 🔨 Tecnologias

- PHP (8.2)
- Composer
- Laravel (v11)
- SQLite
- Swagger


## 📦 Pacotes adicionados ao projeto
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum) - Para realizar a autenticação
- [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger) - Para criar a documentação das rotas


## 🚀 Como executar

1 - Faça o clone do projeto
```bash
git clone https://github.com/ttgustavo/rocket-eventos-api.git
```

2 - Instale os pacotes do composer
```bash
composer install
```

3 - Inicialize o banco de dados com as _migrations_
```bash
php artisan migrate
```

4 - Rode a _seed_ para inserir dados no banco de dados
```
php artisan db:seed
```

5 - Inicie o projeto
```bash
php artisan serve
```

6 - Rode os testes (opcional)
```
php artisan test
```

## 📝 Documentação

Para acessar a documentação da API, rode o projeto e acesse a rota `/api/doc`

## ⚙️ Progresso

### Rotas para usuários
- [x] Autenticação
    - [x] Registro
    - [x] Login
- [x] Atualizar informações
- [x] Atualizar senha
- [ ] Excluir conta
- [x] Listagem de eventos
- [x] Inscrições
    - [x] Registrar inscrição
    - [x] Remover inscrição


### Rotas para administração
- [ ] Usuários
    - [ ] Listagem de usuários
    - [ ] Atualizar usuário
    - [ ] Banir usuário
- [x] Eventos
    - [x] Listagem de eventos
    - [x] Detalhes do evento
    - [x] Atualização de evento
    - [x] Excluir evento
- [ ] Participantes
    - [ ] Listagem de participantes
    - [ ] Realizar checkin
    - [ ] Atualizar status do participante
