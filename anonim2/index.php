<?php
require_once('dbConn.php');
require_once('db_config.php');
$db = new DBConn();
$db->connect();

//Controllo se esiste già la tabella di output
$query_str = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$db_name' AND table_name = '$output_table_name';";
$db->query($query_str);
$arr =  $db->fetch_array();
$output_table_exists = $arr[0] == '1';

//Controllo se esiste già la tabella di binding indirizzo_email/id_anonimo
$query_str = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$db_name' AND table_name = '$binding_table_name';";
$db->query($query_str);
$arr =  $db->fetch_array();
$binding_table_exists = $arr[0] == '1';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Anonimizzatore</title>
</head>

<body>
<h1>Programma di anonimizzazione del mail log</h1>
<p>Questo programma legge i record della tabella <strong>&quot;<?php echo $input_table_name ?>&quot;</strong>, e li copia nella tabella <strong>&quot;<?php echo $output_table_name ?>&quot;</strong>, rendendo anonimo il campo <strong>&quot;<?php echo $field_to_anonymize ?>&quot;</strong>.<br />
Inoltre inserisce nella tabella <strong>&quot;<?php echo $binding_table_name ?>&quot;</strong> le corrispondenze tra i valori originali e gli identificatori anonimi del campo <strong>&quot;<?php echo $field_to_anonymize ?>&quot;</strong>.<br />
Come feature aggiuntiva, questo programma aggiunge al campo <strong>&quot;<?php echo $field_to_anonymize ?>&quot;</strong>
quei destinatari che sono stati scartati, perchè inesistenti (user_unknown), leggendoli dall'mtalog.<br />
</p>
<p>
	<strong>Prima di eseguire questa procedura per la prima volta, configurare i parametri corretti nel file db_config.php</strong>
</p>
<p><strong>Stato attuale del DB:</strong><br />
<?php
if($output_table_exists && $binding_table_exists){
	?>
<form action="anonimizzatore.php" method="get">
    <?php
	$db->query("SHOW COLUMNS FROM $output_table_name");
	$output_table_fields = array();
	while($obj = $db->fetch_object()){
		$output_table_fields[] = $obj->Field;
	}
	echo "La tabelle <strong>&quot;$output_table_name&quot;</strong> e <strong>&quot;$binding_table_name&quot;</strong> esistono già. È possibile continuare a riempirle utilizzando i loro campi attuali, oppure cancellarle per riempirle da zero.<hr />";
	$i=1;
	foreach($output_table_fields as $field){
		echo "<input type=\"hidden\" name=\"field$i\" value=\"$field\" /> ";
		$i++;
	}?>
	<input type="hidden" name="initialized" value="true" />
    <p>
    Schema della tabella esistente: <?php echo "$output_table_name(",implode(", ", $output_table_fields),")<br />"; ?>
    <input type="radio" id="AVL1" name="AVL" value="true" checked="checked" />
    <label for="AVL1">Utilizza AVL</label>
    <br />
    <input type="radio" id="AVL2" name="AVL" value="false" />
    <label for="AVL2">Utilizza array associativi</label>
    <br />
	<input type="submit" value="Avvia procedura" /> (Utilizza le tabelle esistenti)
    </p>
</form>
<hr />
<?php
}
else{
	echo "La tabella <strong>&quot;$output_table_name&quot;</strong> e/o la tabella <strong>&quot;$binding_table_name&quot;</strong> non sono state trovate nel DB.<br />
Questo può voler dire che:<br />
<ul>
	<li>La procedura di anonimizzazione è stata avviata per la prima volta</li>
	<li>Una delle due tabelle è stata eliminata dal DB</li>
	<li>Le tabelle esistono, ma il file db_config.php non è stato configurato con i nomi corretti delle tabelle. In questo caso andare a controllarlo.</li>
</ul>
Dato che una tabella non ha senso senza l'altra associata, se una delle due dovesse esistere verrà distrutta e ricreata da zero.<br />
La tabella <strong>&quot;$output_table_name&quot;</strong> sarà creata con i campi selezionati.";
}
?>
</p>
<form action="anonimizzatore.php" method="get">
<p>Selezionare i campi da copiare in <strong>&quot;<?php echo $output_table_name ?>&quot;</strong></p>
<table>
    <?php
	$db->query("SHOW COLUMNS FROM $input_table_name");
	$i=1;
	while($obj = $db->fetch_object()){ 
		$fn = $obj->Field;
		if(($i%5) == 1) echo '<tr>';
		?>
        <td>
            <input type="checkbox" id="field<?php echo $i ?>" name="field<?php echo $i ?>" value="<?php echo $i ?>" <?php if(!(array_search($fn, $default_checked) === false)) echo 'checked="checked" '; if(!(array_search($fn, $default_mandatory) === false)) echo 'disabled="disabled" '; ?> />
            <label for="field<?php echo $i ?>"><?php echo $fn ?></label>
            <?php if(!(array_search($fn, $default_checked) === false) && !(array_search($fn, $default_mandatory) === false)) 
				echo '<input type="hidden" name="field',$i,'" value="',$i,'" />';?>
        </td>
		<?php
		if(($i%5) == 0) echo '</tr>';
		$i++;
	}
	if(($i%5) != 0) echo '</tr>';
	?>
</table>
<input type="hidden" name="num" value="<?php echo $i ?>" />
<input type="hidden" name="new_tables" value="true" />
<input type="hidden" name="output_exists" value="<?php echo $output_table_exists ? 'true' : 'false' ; ?>" />
<input type="hidden" name="binding_exists" value="<?php echo $binding_table_exists ? 'true' : 'false' ; ?>" />
<input type="hidden" name="initialized" value="true" />
<input type="radio" id="AVL3" name="AVL" value="true" checked="checked" />
<label for="AVL3">Utilizza AVL</label>
<br />
<input type="radio" id="AVL4" name="AVL" value="false" />
<label for="AVL4">Utilizza array associativi</label>
<br />
<input type="submit" value="Avvia procedura" /> (Cancella le due tabelle esistenti e ne crea di nuove)
</form>
</body>
</html>
<?php $db->disconnect(); ?>