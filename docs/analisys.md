# progetto-basi-di-dati

## Appunti preliminari

1. analisi dei requisiti - prendere dal libro le info e seguire
2. identificazione delle funzionalita da sviluppare - ez
3. progettazione e realizzazione della base di dati - progettazione concettuale (ER) e progettazione logica (relazionale), da libro etc
4. progettazione e realizzazione della struttura e della presentazione delle pagine Web necessarie per interfacciarsi con la base di dati - in base alle funzionalita' creare pagine
5. preparazione della documentazione e del materiale da consegnare - se ho fatto i punti precedenti questo e' fatto, ricordarsi di fare screenshot delle feature

Ricordarsi del manuale utente per lettore e bibliotecario

Il progetto si pone l’obiettivo di sviluppare un’applicazione di basi di dati per la gestione di una
biblioteca, dislocata su diverse sedi, con funzionalita sia per i lettori iscritti alla biblioteca, che
per i bibliotecari che la gestiscono

### Utente lettore
* visualizzare le informazioni sui cataloghi e sui libri
* prendere in prestito fintanto che non supera la soglia massima di libri consegnati in ritardo
* Ogni prestito ha una durata massima di default pari a un mese
* prestito per un libro specificandone il titolo oppure il codice ISBN: se esiste (almeno) una copia disponibile, il prestito viene registrato su una copia specifica
* inserire richieste di prestito (a meno di casi specifici come il blocco delle richieste per i lettori ritardatari o limitare il numero di volumi in prestito allo stesso tempo, una richiesta inserita nel sistema da un lettore viene considerata automaticamente concessa)
* sono identificati dal proprio codice fiscale
* anche nome e cognome, categoria (che pu`o essere di due tipi: base e premium), ed il numero di volumi che il lettore ha restituito in ritardo rispetto alla data prevista
* il numero massimo di volumi che possono essere contemporaneamente in prestito puo cambiare
* per ogni libro prestato occorre tenere traccia della data in cui viene effettuata la restituzione

### Utente bibliotecario
* inserisce l'avvenuta riconsegna del libro
* puo azzerare il numero di volumi restituiti in ritardo dal lettore
* puo' estendere la durata massima del prestito
* aggiunge o rimuove o aggiorna libri
* aggiunge o modifica lettori
* aggiunge o modifica sedi

### Biblioteca
* su diversi indirizzi

### Libro
* ogni libro nella libreria ha almeno: ISBN, titolo, autori, trama, e casa editrice (la biblioteca puo possedere diverse copie, identificate da un codice univoco, che possono essere distribuite anche su diverse sedi)
* per ogni copia di un dato libro sapere quale sede lo gestisce e se e disponibile oppure al momento gia prestato
* puo avere diversi autori
* I libri gestiti dalla biblioteca possono essere presi in prestito dai lettori iscritti alla biblioteca

### Autore
* Degli autori, identificati da un codice univoco, la biblioteca mantiene informazioni quali nome e cognome, data di nascita ed eventualmente di morte, ed una breve biografia

## Feature obbligatoriamente da implementare usando strutture interne al db

Per strutture interne al db si intendono trigger, procedure, funzioni, viste materializzate

* Blocco prestiti a lettori ritardatari. Un prestito pu`o essere concesso solo se il lettore che lo richiede ha meno di 5 riconsegne in ritardo all’attivo
* Numero massimo di prestiti. I lettori di categoria base possono avere al massimo 3 volumi in prestito allo stesso tempo, mentre i lettori di categoria premium possono averne al massimo 5
* Ritardi nelle restituzioni. Alla restituzione di un volume, se effettuata in ritardo, e necessario aggiornare il contatore dei ritardi del lettore
* Disponibilita dei volumi. Un prestito puo essere concesso solo se il volume richiesto e disponibile. La disponibilita di ogni volume va sempre mantenuta aggiornata rispetto ai prestiti in atto
* Proroga della durata di un prestito. La proroga della durata di un prestito (fatta dal bibliotecario, si vedano i requisiti dell’applicazione Web) puo essere concessa solo se il prestito non si trova gia in ritardo.
* Selezione della sede. Per inserire un prestito, insieme al codice ISBN o al titolo del libro, il lettore puo specificare una delle sedi della biblioteca. Se il lettore specifica una sede, il prestito pu`o essere fatto solo su una delle copie presenti nella sede specificata. Solo se il
libro richiesto non ha copie disponibili presso la sede specificata, e possibile considerare copie presso le altre sedi, a patto che il lettore ne venga opportunamente avvisato.
* Statistiche per ogni sede. E necessario mantenere, per ogni sede, il numero totale delle copie gestite dalla sede, il numero totale dei codici ISBN gestiti dalla sede, ed il numero totale di prestiti in corso per volumi mantenuti dalla sede.
* Ritardi per ogni sede. E necessario generare un report, per ogni sede, dove sono indicati i libri in prestito in ritardo e i lettori che li hanno in carico
