<?php

require_once("DBConn.php");
require_once("db_config.php");

$db = new DBConn();
$db->connect();

//Controllo se esiste già la tabella di output
$query_str = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$db_name' AND table_name = '$output_table_name';";
$db->query($query_str);
$arr =  $db->fetch_array();
$output_table_exists = $arr[0] == '1';

if($output_table_exists)
	$db->query("DROP TABLE $output_table_name;");
	
$db->query("CREATE TABLE $output_table_name (alias varchar(50), mailbox varchar(50), PRIMARY KEY (alias)) DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;");

//Ritorna il parametro p nella query string, se esiste e non è nullo.
function get($p){
	if(isset($_GET[$p]) && $_GET[$p] != "")
		return $_GET[$p];
	else
		return false;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Parsificatore Alias</title>
</head>

<body>
Operazione in corso...<br />

<?php
$query_str = "INSERT INTO $output_table_name (alias, mailbox) VALUES ";
$file_array = file($input_file_name, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
$iniziato = false;
foreach($file_array as $elem){
	$elem = trim($elem);
	if(!(substr($elem, 0, 1) == '#')){
		$alias_couple = explode(':', $elem);
		if(count($alias_couple) == 2){
			if($iniziato) $query_str .= ',';
			$alias = addslashes(trim(strtolower($alias_couple[0])));
			$mailbox = addslashes(trim(strtolower($alias_couple[1])));
			$query_str .= "('$alias','$mailbox')";
			$iniziato = true;
		}
	}
}
$db->query($query_str);
$db->disconnect();
?>

Operazione terminata
</body>
</html>