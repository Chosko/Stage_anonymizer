<?php

require_once("db_config.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Parsificatore alias</title>
</head>
<body>
<h1>Parsificatore alias</h1>
<p>
	Questo programma parsifica il file di testo <?php echo $input_file_name ?> e inserisce le informazioni nella tabella <?php echo $output_table_name ?>.<br />
	Se <?php echo $output_table_name ?> esiste già sarà cancellata e ricreata.
    <form action="aliases.php" method="get">
    	<input type="hidden" name="init" value="true" />
    	<input type="submit" value="Avvia programma" />
    </form>
</p>
</body>
</html>