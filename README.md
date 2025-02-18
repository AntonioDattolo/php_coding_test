# AUTOXY CODING TEST

Questa è un'applicazione Symfony 7.2 per il coding test di un'API RESTful.

## 1. Installazione 
 
**Clona il repository:**

`git clone "link_repository"`

## 2. Configurazione iniziale

**Installazione dipendenze**

`composer i`

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

5. **Aggiungere record**

**Inviare fixture per aggiungere i record nel db per eventuali test**

`php bin/console doctrine:fixtures:load --append`

# (OPZIONALE) Resettare il db

***Eliminare il db***

`php bin/console doctrine:database:drop --force`

**Cancellare migrations, manualmente e resettare la tabella delle migrations**

`php bin/console doctrine:migrations:version --delete --all`

## 3. Avviare server di sviluppo

***Linea di comando***

`symfony server:start`

## 4. TEST UNITARI:

**Linea di comando**

***Avviare test unitari***

`php bin/phpunit tests/unit`

## 5. TEST DI INTEGRAZIONE

1. **Configurare ambiente di test**

***file .env.test**

`DATABASE_URL="mysql://root:@127.0.0.1:3306/autoxy_coding_test_test"`

2. **Creare database per test**

***Linea di comando***

`php bin/console doctrine:database:create --env=test`

3. **Creare migrazione (nel caso non ci siano già. Controllare la cartella \migrations)**

***Linea di comando**

`php bin/console make:migration --env=test`

4. **Creazione tabelle database**

***Linea di comando***

`php bin/console doctrine:migration:migrate --env=test`

5. **Creazione record db**

***Linea di comando***

`php bin/console doctrine:fixtures:load --append --env=test`

6. **Iniziare test di integrazione**

***Linea di comando***

`php bin/phpunit tests/integration`

## OPZIONALE AGGIUNGERE DATI FITTIZI PER TEST // da rimuovere

**VISITARE LA ROTTA 'make_db'**

***saranno aggiunti 15 record nel db per i test***