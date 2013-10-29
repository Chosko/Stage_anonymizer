<?php
//***************************PARAMETRI CONFIGURABILI**********************//
//Parametri di connessione al DB
$db_user = "root";
$db_password = "admin";
$db_name = "stage";
$db_host = "localhost";

//Il nome della tabella di input
$input_table_name = "maillog";

//Il nome della tabella che conterrà i dati anonimizzati
$output_table_name = "maillog_anonymous";

//Il nome della tabella che conterrà il binding indirizzo_email/id_anonimo
$binding_table_name = "maillog_addressbinding";	

//Il campo della tabella di input da rendere anonimo
$field_to_anonymize = "from_address";

//La dimensione, in record, della cache di output (quanti record alla volta scrive sul DB).
$output_cache_length = 500;

/*
Il numero massimo di iterazioni di svuotamento della cache sul DB. 
Una volta raggiunto questo numero di iterazioni, il processo si interrompe e la pagina viene ricaricata. 
Dopo il refresh, il processo riparte da dove era rimasto.
Questo serve per evitare di dover aumentare il tempo massimo di esecuzione di PHP (ma non dovrebbe servire comunque).
*/
$max_write_iterations = 500;

$where_costraint = "WHERE 1";  	//Clausola WHERE per filtrare in input le mail da anonimizzare (per esempio per selezionare solo quelle in un range di date. SE RIMANE 'WHERE 1' TENTA DI PRENDERLE TUTTE.)
$default_checked = array('timestamp', 'id', 'from_address', 'from_domain', 'to_address', 'to_domain', 'isspam', 'ishighspam', 'issaspam'); //Campi da includere di default nella tabella di output (si possono comunque modificare a runtime).
//********************************************//

?>