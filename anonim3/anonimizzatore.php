<?php
require_once('dbConn.php');
require_once('db_config.php');
require_once('binding_cache.php');
require_once('AVL.php');
require_once('BCArray.php');
require_once('MailUsers.php');
require_once('functions.php');

//controllo delle variabili inizializzate, per evitare disastri nel caso la pagina venisse aperta per sbaglio.
$initialized = get('initialized') == 'true' or die('Variabili non inizializzate.');

//Si connette al DB
$db = new DBConn();
$db->connect();

$outfields = array();	//i campi della tabella di output

$AVL = get('AVL') or die('Parametro \'AVL\' non inizializzato correttamente');
$AVL = $AVL == 'true' ? true : false;

$binding_cache = $AVL ? new AVLTree() : new BCArray();	//Cache di binding indirizzo_reale/id_anonimo creata come AVL o array associativo.
$mail_users = new MailUsers();

//Se devo eliminare e ricreare le tabelle da zero
if($new_tables = get('new_tables') == 'true'){
	$tot_infields = (int)get('num');	//il numero di campi della tabella in input
	$binding_table_exists = get('binding_exists') == 'true';
	$output_table_exists = get('output_exists') == 'true';
	
	//Ricavo i campi della tabella in output
	$i=1;
	for($i=1; $i<=$tot_infields; $i++){
		if($field = get('field'.$i))
			$outfields[] = (int)$field;
	}
	
	//Chiede al DB lo schema della tabella in input
	$db->query("SHOW COLUMNS FROM $input_table_name");
	$infields = array();
	$i=1;
	while($obj = $db->fetch_object()){
		$infields[$obj->Field] = $obj->Type;
		$i++;
	}

	//Cancella le tabelle in output e di binding, se esistono
	if($binding_table_exists)
		$db->query("DROP TABLE $binding_table_name;");
	if($output_table_exists)
		$db->query("DROP TABLE $output_table_name;");
	
	//Crea le tabelle di output e di binding
	$valid_fields = array();
	$query_str = "CREATE TABLE $output_table_name ( ";
	$i=1;
	$started = false;
	foreach($infields as $field_name => $field_type){
		for($j=0; $j<count($outfields); $j++){
			if($i == $outfields[$j]){
				if($started) $query_str .= ", ";
				$started = true;
				$query_str .= "$field_name $field_type ";
				$valid_fields[] = $field_name;
			}
		}
		$i++;
	}
	$query_str.=", PRIMARY KEY (id( 14 ))) $collation;";
	$db->query($query_str) or die(mysql_error());
	$db->query("CREATE TABLE $binding_table_name (id varchar(30), address text, PRIMARY KEY (id) ) $collation");
}
//Altrimenti, uso le tabelle che ci sono già e carico le corrispondenze di binding già esistenti
else{
	$valid_fields = array();
	$i=1;
	while($field = get('field'.$i)){
		$valid_fields[] = $field;
		$i++;
	}
	$db->query("SELECT id, address FROM $binding_table_name;");
	while($obj = $db->fetch_object()){
		$binding_cache->insert($obj->address, $obj->id);
	}
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Anonimizzatore</title>
</head>

<body>
<?php
//************************ALGORITMO DI ANONIMIZZAZIONE**************************
//comincia dall'inizio della tabella, oppure dall'ultimo punto, se aveva già cominciato.
if(!$i = get('pag')){ 
	$i=0;
	$query_str = "SELECT COUNT(*) FROM $input_table_name $where_costraint;";
	$db->query($query_str);
	$arr = $db->fetch_array();
	$tot_records = (int)$arr[0];
}
else{
	$i = (int)$i;
	$tot_records = (int)get('tot');
}

$counter = 0;
$start_record = $i*$output_cache_length;
echo "Operazione in corso: ";
//Esegue fino a quando ci sono record, oppure si raggiunge il numero massimo di iterazioni previste
$iterations = $max_write_iterations;
$interrupted = true;
$select_str_increments = '';
if(!is_valid_field($headers_name, $valid_fields)){
	$select_str_increments .= ",$headers_name ";
}
if(!is_valid_field($to_SMTP_name, $valid_fields)){
	$select_str_increments .= ",$to_SMTP_name ";
}
if(!is_valid_field($to_SMTP_name, $valid_fields)){
	$select_str_increments .= ",$from_SMTP_name ";
} 
while($iterations){
	$res = $db->query("SELECT ".(implode(", ",$valid_fields))." $select_str_increments FROM $input_table_name $where_costraint LIMIT ".$i*$output_cache_length.", $output_cache_length;");
	if(!mysql_num_rows($res)){
		$interrupted = false;
		break;
	}
	
	//Preleva i record e riempie la cache (che è direttamente una query string)
	$j=0;
	$output_cache = "INSERT IGNORE INTO $output_table_name ( ".(implode(", ",$valid_fields)).") VALUES "; 
	while($res_array = $db->fetch_array($res)){
		$msg_id = $res_array[$msg_id_name];
		
		/* CONTINUARE DA QUI ##########################################################################################################
		$mail_users->reset_obj();
		fill_mail_users($mail_users);
		*/
			
		if($j>0) $output_cache .= ", ";
		$output_cache .= "(";
		$started = false;
		foreach($res_array as $key=>$val){
			if(is_string($key) && is_valid_field($key, $valid_fields)){
				if($started) $output_cache .= ", ";
				$val = anonymize($key, $val, $binding_cache, $to_SMTP_name, $all_world, $internal_domains);		//Scrive in cache solo i campi interessati
				if($to_SMTP_name == $key)
					$val = append_fake_recipients($val, $db, $msg_id, $mta_table_name, $user_unknown_flag);
				if($val === NULL) $val = "NULL";
				elseif($val === 0) $val = "0";
				elseif($val === false) $val = "0";
				elseif(is_string($val)) $val = "'".addslashes($val)."'";
				$output_cache .= $val;
				$started = true;
			}
		}
		$output_cache .= ")";
		$j++;
		$counter++;
	}
	
	$db->query($output_cache); //Svuota la cache nel DB
	echo '.';
	flush();
	$i++;
	$iterations--;
}
$current_record = $start_record+$counter;
if($tot_records == 0) $percent = 100;
else $percent = ($current_record/$tot_records)*100;
echo $percent,"%<br />
Anonimizzati i record da $start_record a $current_record";
echo '<br /> dimensione binding_cache: ' , $binding_cache->get_max();

$query_str = $binding_cache->save_query($binding_table_name, "address", "id");
if(trim($query_str) != '')
	$db->query($query_str);	//Salva le corrispondenze indirizzo_email/id_anonimo nel DB
$db->disconnect();
if($interrupted){
	echo '<br />
Per interrompere la procedura chiudere la finestra o premere "interrompi" sul browser.
	<form id="form_continua" action="anonimizzatore.php" method="get">
		<input type="hidden" name="initialized" value="true" />';
		$index = 1;
		foreach($valid_fields as $field){
			echo "<input type=\"hidden\" name=\"field$index\" value=\"$field\" /> ";
			$index++;
		}
		echo '<input type="hidden" name="pag" value="',$i,'" />
			<input type="hidden" name="tot" value="',$tot_records,'" />
			<input type="hidden" name="AVL" value="',$AVL ? 'true' : 'false' ,'" />
			<noscript>
			<input type="submit" id="submit_continua" value="Continua" />
			</noscript>';
	echo '
	</form>
	';?>
    <script type="text/javascript">
    	var form = document.getElementById("form_continua");
		form.submit();	
    </script>
    <?php
}
else{
	echo '<br />Operazione andata a buon fine.';
}
?>
</body>
</html>