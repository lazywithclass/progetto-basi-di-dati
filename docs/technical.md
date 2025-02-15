# Manuale tecnico

## Requisiti 

 * PHP versione 8.2
 * PostgreSQL 16.6

Si assume che il progetto venga eseguito su una distribuzione Linux, e' stato testato su NixOS 24.11; il file default.nix
serve in questo sistema operativo per evitare installazioni globali.

## Schema concettuale (ER)


## Schema logico (relazionale)


## Configurazione

Il progetto non ha bisogno di credenziali di superuser per poter essere eseguito.
I seguenti comandi si intendono come eseguiti dalla root del progetto.

Creare un database seguendo i dati di configurazione nella cartella scripts, eseguire il setup iniziale e 
far partire il servizio di PostgreSQL

```shell
$ ./scripts/postgresql-setup.sh
$ ./scripts/postgresql-start.sh
```

Far partire PHP in ascolto localmente

```shell
$ ./scripts/php-start.sh
```

Si puo' fare reset del database per riportarsi in una situazione iniziale pre-configurata 

```shell
$ ./scripts/postgresql-reset.sh
```

Per fermare il servizio di PostgreSQL

```shell
$ ./scripts/postgresql-stop.sh
```

## Struttura del progetto

Segue una spiegazione di come e' strutturato il progetto

### config

Contiene la configurazione di PostgreSQL, su NixOS solo alcune cartelle sono in scrittura (ad esempio la home dell'utente)
quindi ho preferito far partire l'istanza del database nella cartella corrente, per fare questo ho dovuto modificare
la posizione della socket 

### db

Contiene il dump del database, da cui si puo' ripartire agevolmente con l'apposito script nel caso si facciano modifiche 
indesiderate in fase di esposizione.

### docs

Contiene la documentazione del progetto.

### pgsql

Contiene i dati relativi all'istanza di PostgreSQL.

### scripts

Contiene gli script utili a far partire il progetto.

### src

Suddivisa in admin/ e reader/ in base al tipo di utente che ha effettuato il login.
Eventuali file condivisi sono dentro src/ stessa.

## Screenshot di funzionamento


