# BileMo API
P07-Openclassrooms_développeur_application_PHP_Symfony

BileMo is a web service that exposes an API allowing clients to access BileMo's mobile phone selections. 
The client is able to access his customers (list or detailed information) as well as create or delete a customer.

## Getting Started

### Requirements

PHP 8.1.0

Symfony 6.2

MySQL 5.7.836

### Installation

Install the project on your computer.
```
git clone git@github.com:CHBHR/BileMoApi.git
```

Install the dependencies using composer.
```
composer install
``` 

#### JWT
Set up JWT by executing the following commands 
```
mkdir config/jwt 
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Copy the `.env` file at the root of the project and rename it to `.env.local`.

In the `.env.local` file, replace the value of the variable `JWT_PASSPHRASE` with your passphrase.

#### Database and fixtures
In the `.env.local` file, adapt the `DATABASE_URL` variable by replacing the parameters `db_user`, `db_password` and `db_name` with your own configuration.

Create a new database: 
```
php bin/console doctrine:database:create. 
```
Then, create the different tables based on the entity mapping:
```
php bin/console doctrine:schema:update --force
```

If your MySQL version is inferior to 5.7.8, run the command `php bin/console doctrine:migrations:migrate` in order to create the tables.

Once your database has been properly set up, you can load the data fixtures:
```
php bin/console doctrine:fixtures:load
```

## Resources 
The API documentation is available at [Bilemo Documentation](https://127.0.0.1:8000/api/doc). 

Diagrams can be found in the 'ressources' file

The different issues can be found on [Github](https://github.com/CHBHR/BileMo/issues?q=is%3Aissue+is%3Aclosed)

## Versioning

I used [GitHub](https://github.com/CHBHR/BileMo) for versioning. 

## Authors

**Christopher Rey** 
