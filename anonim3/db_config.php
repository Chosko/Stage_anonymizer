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

//Il nome della tabella che contiene il log dell'MTA
$mta_table_name = "mtalog";

//Il campo della tabella di input che contiene i destinatari SMTP
$to_SMTP_name = "to_address";

//Il compo della tabella di input che contiene il mittente SMTP
$from_SMTP_name = "from_address";

//Il nome del campo che contiene gli headers
$headers_name = 'headers';

//Il msg_id
$msg_id_name = 'id';

//La dimensione, in record, della cache di output (quanti record alla volta scrive sul DB).
$output_cache_length = 200;

/*
Il numero massimo di iterazioni di svuotamento della cache sul DB. 
Una volta raggiunto questo numero di iterazioni, il processo si interrompe e la pagina viene ricaricata. 
Dopo il refresh, il processo riparte da dove era rimasto.
Questo serve per evitare di dover aumentare il tempo massimo di esecuzione di PHP (ma non dovrebbe servire comunque).
*/
$max_write_iterations = 25;

//Clausola WHERE per filtrare in input le mail da anonimizzare (per esempio per selezionare solo quelle in un range di date. SE RIMANE 'WHERE 1' TENTA DI PRENDERLE TUTTE.)
$where_costraint = "WHERE 1"; //"WHERE from_address LIKE '%1234567890%' OR from_address LIKE '%0987654321%'";

//Set di caratteri
$collation = "DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs";

//Contrassegna le mail di utenti sconosciuri
$user_unknown_flag = 'FAKE';

//Contrassegna i domini considerati come 'resto del mondo'
$all_world = 'OTHER';

//Domini considerati come 'interni'
$internal_domains = array('di.unito.it', 'educ.di.unito.it');

//Campi da includere di default nella tabella di output (si possono comunque modificare a runtime).
$default_checked = array('timestamp', 'id', 'from_address', 'from_domain', 'to_address', 'to_domain', 'isspam', 'ishighspam', 'issaspam'); 

//Campi da includere obbligatoriamente nella tabella di output.
$default_mandatory = array('id');

//********************************************//

?>