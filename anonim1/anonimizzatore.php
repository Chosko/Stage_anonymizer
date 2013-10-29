<?php
require_once('dbConn.php');
require_once('db_config.php');
require_once('AVL.php');

//Funzione di anonimizzazione
function anonymize($field, $value, $binding_cache, $field_to_anonymize){
	if($field == $field_to_anonymize)
		$value = $binding_cache->retrieve($value);
	return $value;
}

//Ritorna il parametro p nella query string, se esiste e non è nullo.
function get($p){
	if(isset($_GET[$p]) && $_GET[$p] != "")
		return $_GET[$p];
	else
		return false;
}

//controllo delle variabili inizializzate, per evitare disastri nel caso la pagina venisse aperta per sbaglio.
$initialized = get('initialized') == 'true' or die('Variabili non inizializzate.');

//Si connette al DB
$db = new DBConn();
$db->connect();

$outfields = array();	//i campi della tabella di output
$binding_cache = new AVLTree();	//Cache di binding indirizzo_reale/id_anonimo

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
	$query_str.=");";
	$db->query($query_str) or die(mysql_error());
	$db->query("CREATE TABLE $binding_table_name (id varchar(30), address text, PRIMARY KEY (id) ) ");
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
while($iterations){
	$res = $db->query("SELECT ".(implode(", ",$valid_fields))." FROM $input_table_name $where_costraint LIMIT ".$i*$output_cache_length.", ".$output_cache_length.";");
	if(!mysql_num_rows($res)){
		$interrupted = false;
		break;
	}
	
	//Preleva i record e riempie la cache (che è direttamente una query string)
	$j=0;
	$output_cache = "INSERT IGNORE INTO $output_table_name (".(implode(", ",$valid_fields)).") VALUES "; 
	while($res_array = $db->fetch_array()){
		if($j>0) $output_cache .= ", ";
		$output_cache .= "(";
		$started = false;
		foreach($res_array as $key=>$val){
			if(is_string($key)){
				if($started) $output_cache .= ", ";
				$val = anonymize($key,$val, $binding_cache, $field_to_anonymize);		//Scrive in cache solo i campi interessati
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
	flush();
	$i++;
	$iterations--;
}
$current_record = $start_record+$counter;
$percent = ($current_record/$tot_records)*100;
echo $percent,"%<br />
Anonimizzati i record da $start_record a $current_record";

$db->query($binding_cache->save_query($binding_table_name, "address", "id"));	//Salva le corrispondenze indirizzo_email/id_anonimo nel DB
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