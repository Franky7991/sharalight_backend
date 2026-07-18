# SharaLight Backend

Backend Laravel 11 minimale — solo modello User con CRUD completo.

## Stack

- Laravel 11
- AdminLTE 3 (jeroennoten/laravel-adminlte)
- Yajra DataTables
- MySQL

## Setup

```bash
# 1. Installa le dipendenze
composer install

# 2. Copia e configura il file di ambiente
cp .env.example .env
php artisan key:generate

# 3. Configura il database in .env
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Esegui le migration
php artisan migrate

# 5. Pubblica gli asset di AdminLTE
php artisan adminlte:install

# 6. Popola il database con l'utente admin
php artisan db:seed

# 7. Avvia il server
php artisan serve
```

## Credenziali default

- **Email**: admin@email.it
- **Password**: admin

## Struttura

```
app/
├── Http/Controllers/
│   ├── Auth/LoginController.php   # Login sessione
│   ├── HomeController.php         # Dashboard
│   ├── UserController.php         # CRUD utenti
│   └── Controller.php
├── Models/
│   └── User.php
database/
├── migrations/                    # Solo users, cache, jobs
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php             # Admin di default
resources/views/
├── home.blade.php
└── user/
    ├── index.blade.php            # Lista utenti (DataTable)
    ├── create.blade.php           # Nuovo utente
    └── show.blade.php             # Modifica utente
routes/
└── web.php                        # Auth + /users resource
```

## Rotte disponibili

| Metodo | URL | Descrizione |
|--------|-----|-------------|
| GET | /login | Pagina login |
| GET | /home | Dashboard |
| GET | /users | Lista utenti |
| POST | /users/list/table | DataTable JSON |
| GET | /users/create | Form nuovo utente |
| POST | /users | Salva nuovo utente |
| GET | /users/{id} | Form modifica utente |
| PUT | /users/{id} | Aggiorna utente |
| DELETE | /users/{id} | Elimina utente |
| POST | /users/delete | Elimina multipli (bulk) |
