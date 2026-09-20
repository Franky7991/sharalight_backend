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

## Ordini cliente — regole su ingredienti e prezzi

Regole valide **sia nel backend** (pagina ordine, modal "Ingredienti") **sia
nella webapp** (`webapp/js/order-new.js`), così le due interfacce mostrano gli
stessi dati:

- **Ingredienti filtrati dalla ricetta** — ogni riga di ricetta
  (`recipes` + `recipe_details`) abilita solo alcuni prodotti della categoria:
  nel modal "Ingredienti" dell'ordine vengono proposti **solo quelli**. Se la
  ricetta non abilita nessun prodotto la categoria è libera (tutti i prodotti
  della categoria). La scelta già salvata resta comunque in elenco, anche se non
  più abilitata, e se il filtro non lascia nessun prodotto si torna a proporre
  l'intera categoria per non bloccare l'operatore.
- **Prezzo ingrediente visibile solo per le materie prime** — i semi-lavorati e
  i prodotti finiti possono comparire come ingredienti (es. *Candela Piccola*
  dentro *Candela Piccola con Busta*), ma il loro prezzo di listino non è un
  costo ingrediente: non entra nel prezzo della riga
  (`CustomerOrderHasProduct::recalculatePrice()` somma solo
  `products.type = raw_material`) e non viene mostrato nei badge ingredienti
  (`CustomerOrderHasProductDetail::visibleIngredientPrice()`).
