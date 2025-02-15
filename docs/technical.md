# Manuale tecnico

## Requisiti 

 * PHP versione 8.2
 * PostgreSQL 16.6

Si assume che il progetto venga eseguito su una distribuzione Linux, e' stato testato su NixOS 24.11; il file default.nix
serve in questo sistema operativo per evitare installazioni globali.

## Schema concettuale (ER)

![er](images/er.png)

## Schema logico (relazionale)

author(<ins>id</ins>, name, surname, bio, birth_date, death_date*)<br />
book(<ins>id</ins>, isbn, title, publisher, plot)<br />
author_book(<ins>id_author, id_book</ins>)<br />
physical_copy(<ins>id</ins>, id_book, id_branch)<br />
loan(<ins>id</ins>, id_reader, id_physical_copy, start_date, end_date*, length, is_returned)<br />
reader(<ins>id</ins>, fiscal_code, username, password, name, surname)<br />
librarian(<ins>id</ins>, username, password, email)<br />
library(<ins>id</ins>, name)<br />
library_reader(<ins>id_library, id_reader</ins>, overdue_returns, category)<br />
library_librarian(<ins>id_library, id_librarian</ins>)<br />
branch(<ins>id</ins>, id_library, city, address)<br />

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

### Lettore

![img.png](images/reader-login.png)
<figcaption style="margin-top: -25px; font-style: italic">Login</figcaption>

![img.png](images/reader-index.png)
<figcaption style="margin-top: -25px; font-style: italic">Index</figcaption>

![img.png](images/reader-loan-books.png)
<figcaption style="margin-top: -25px; font-style: italic">Loan books</figcaption>

![img.png](images/reader-loan-books-search.png)
<figcaption style="margin-top: -25px; font-style: italic">Loan books dopo una ricerca per titoilo</figcaption>

![img.png](images/reader-loaned-book-message.png)
<figcaption style="margin-top: -25px; font-style: italic">Loaned book messaggio</figcaption>

![img.png](images/reader-no-loaned-books.png)
<figcaption style="margin-top: -25px; font-style: italic">Niente prestiti per questo utente</figcaption>

![img.png](images/reader-with-loaned-books.png)
<figcaption style="margin-top: -25px; font-style: italic">Lettore con un eccellente gusto in fatto di libri</figcaption>

![img.png](images/reader-change-password.png)
<figcaption style="margin-top: -25px; font-style: italic">Change password</figcaption>

![img.png](images/reader-change-password-old-incorrect.png)
<figcaption style="margin-top: -25px; font-style: italic">Change password fallita perche' la vecchia password non e' corretta</figcaption>

![img.png](images/reader-change-password-no-match.png)
<figcaption style="margin-top: -25px; font-style: italic">Change password fallita perche' le password non corrispondono</figcaption>

![img.png](images/reader-change-password-successfull.png)
<figcaption style="margin-top: -25px; font-style: italic">Change password successo</figcaption>

### Bibliotecario

![img.png](images/librarian-login.png)
<figcaption style="margin-top: -25px; font-style: italic">Login</figcaption>

![img.png](images/librarian-index.png)
<figcaption style="margin-top: -25px; font-style: italic">Index</figcaption>

![img.png](images/librarian-manage-readers.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage readers</figcaption>

![img.png](images/librarian-manage-readers-found.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage readers, trovato un reader attraverso la ricerca</figcaption>

![img.png](images/librarian-manage-readers-edit.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage readers, modifica</figcaption>

![img.png](images/librarian-manage-books.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage books</figcaption>

![img.png](images/librarian-manage-books-found.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage books, trovati libri che iniziano per "The"</figcaption>

![img.png](images/librarian-manage-books-edit.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage books, edit</figcaption>

![img.png](images/librarian-manage-branches.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage branches</figcaption>

![img.png](images/librarian-manage-branches-search.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage branches, trovate le sedi di Rivendell</figcaption>

![img.png](images/librarian-manage-branches-edit.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage branches, edit</figcaption>

![img.png](images/librarian-manage-loans-edit.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage loans</figcaption>

![img.png](images/librarian-manage-loans-extend-loan-error.png)
<figcaption style="margin-top: -25px; font-style: italic">Manage loans, non si puo' estendere un loan overdue</figcaption>

![img.png](images/librarian-change-password.png)
<figcaption style="margin-top: -25px; font-style: italic">Change password, come per il lettore</figcaption>
