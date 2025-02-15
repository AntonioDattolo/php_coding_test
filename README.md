# AUTOXY CODING TEST

Questa è un'applicazione Symfony 7.2 per il coding test di un'API RESTful.

## 1. Installazione del progetto
 
1.  **Crea il progetto Symfony:**

`composer create-project symfony/skeleton:"7.2.x" my_project_directory`

***OPZIONALE***

`composer require webapp`

## 2. Configurazione iniziale

1.  **Installazione ORM-PACK**

`composer require symfony/orm-pack`

2.  **Strumenti per generare entity & controller**

`composer require --dev symfony/maker-bundle`

## 3. Configurazione DATABASE

1.  **Definire connessione al DB**

***File .env***

`DATABASE_URL="mysql://root:@127.0.0.1:3306/autoxy_coding_test"`

2.  **Creare il DATABASE**

***eseguire il comando, dopo aver configurato la connessione nel file .env***

`php bin/console doctrine:database:create`

3. **Creare ENTITY**

***eseguire il comando***

`php bin/console make:entity nome_entity`
**dopo aver eseguito il comando, compilare i campi**

4. **Creare tabella nel db**

**creare la migration**

`php bin/console make:migration`

**eseguire la migration**

`php bin/console doctrine:migrations:migrate`

5. **Aggiungere righe alla tabella**

**Creare il controller**

`php bin/console make:controller CarController`

***implementare metodo createCar()***

***implementare metodo show()***

***implementare metodo edit()***

**implementare metodo delete()**

*** implementato softDelete ***

*** creata nuova migrazione per aggiungere la colonna 'deletedAt' ***








# (OPZIONALE) Resettare il db

***Eliminare il db***
`php bin/console doctrine:database:drop`

**Cancellare migrations, manualmente e resettare la tabella delle migrations**

`php bin/console doctrine:migrations:version --delete --all`

## 3. Avviare server di sviluppo

***Linea di comando***

`symfony server:start`