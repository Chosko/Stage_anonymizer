<?php
class DBConn{
	private $db; //La connessione
	private $result; //Il risultato dell'ultima query. Se è vuoto è settato a false.
	private $name;  //Il nome del database
	private $host;  //Il nome dell'host
	private $user;  //L'utente del database con cui si effettua la connessione
	private $password;  //La password
	private $connected;  //Variabile booleana. True se è già connesso. False altrimenti.
	
	// Costruttore
	function DBconn(){
		require('db_config.php'); // Importa il file di configurazione del database.
		$this->name = $db_name;
		$this->host = $db_host;
		$this->user = $db_user;
		$this->password = $db_password;
		$this->connected = false;
		$this->result = false;
	}
	
	// Effettua la connessione al database se ce n'è già una attiva.
	function connect(){
		if(!$this->connected){
			$this->db = mysql_connect($this->host, $this->user, $this->password) or die("Impossibile connettersi al database: ".mysql_error());
			mysql_select_db($this->name) or die("Impossibile connettersi al database: ".mysql_error());
			$this->connected = true;
		}
	}
	
	// Effettua la disconnessione dal database.
	function disconnect(){
		if($this->connected){
			mysql_close() or die('Impossibile disconnettersi dal database: '.mysql_error());
			$this->connected = false;
		}
	}
	
	// Effettua una query al database. ATTENZIONE: la stringa $query_str non viene sottoposta a escape. Bisogna assicurarsi di averlo già fatto.
	function query($query_str){
		if($this->connected){
			$this->result = mysql_query($query_str) or die("Impossibile eseguire la query '".$query_str."': ".mysql_error());
		}
		else{
			$this->connect();
			$this->result = mysql_query($query_str) or die("Impossibile eseguire la query: '".$query_str."'".mysql_error());
			$this->disconnect();
		}
		return $this->result;
	}
	
	// Fa eseguire al database l'escape di una stringa.
	function escape($unescaped_str){
		if($unescaped_str == '') die("Impossibile effettuare l'escape di una stringa vuota.");
		if($this->connected){
			$escaped_str = mysql_real_escape_string($unescaped_str) or die("Impossibile effettuare l'escape della stringa '".$unescaped_str."': ".mysql_error());
			return $escaped_str;
		}
		else{
			$this->connect();
			$escaped_str = mysql_real_escape_string($unescaped_str) or die("Impossibile effettuare l'escape della stringa: '".$unescaped_str."'".mysql_error());
			$this->disconnect();
			return $escaped_str;
		}
	}
	
	/* 	Effettua l'inserimento di un record in una tabella del database.
		$table_name -> il nome della tabella 
		$columns_array -> un array con il nome delle colonne in cui inserire i valori
		$values_array -> un array con il valore delle variabili (A CUI è GIà STATO EFFETTUATO L'ESCAPE!!!).
		Si presuppone che i valori di tipo string non abbiano apici o quotes agli estremi (vengono aggiunti automaticamente).*/
	function insert($table_name, $columns_array, $values_array){
		//controlla se le variabili inserite sono state inizializzate correttamente.
		(is_string($table_name) && is_array($columns_array) && is_array($values_array) && count($columns_array) == count($values_array)) 
			or die("Impossibile effettuare l'inserimento: variabili non inizializzate correttamente.");
		
		//Costruisce la query per il database
		$query_str = 'INSERT INTO '.$table_name.' (';
		$n = count($columns_array);
		for($i=0; $i<$n; $i++){
			if($i>0) $query_str .= ',';
			is_string($columns_array[$i]) or die("Impossibile effettuare l'inserimento: variabili non inizializzate correttamente");
			$query_str .= $columns_array[$i];
		}
		$query_str .= ') VALUES (';
		for($i=0; $i<$n; $i++){
			if($i>0) $query_str .= ',';
			if($values_array[$i] === NULL)
				$query_str .= 'NULL ';
			elseif(is_string($values_array[$i]))
				$query_str .= "'".$values_array[$i]."'";
			else
				$query_str .= $values_array[$i];
			
		}
		$query_str .= ') ;';
		
		//Effettua la query
		return $this->query($query_str);
	}
	
	/* 	Effettua la modifica dei campi di una tabella del database.
		$table_name -> il nome della tabella 
		$columns_array -> un array con il nome delle colonne di cui modificare i valori
		$values_array -> un array con il valore delle variabili (A CUI è GIà STATO EFFETTUATO L'ESCAPE!!!).
		Si presuppone che i valori di tipo string non abbiano apici o quotes agli estremi (vengono aggiunti automaticamente).*/
	function update($table_name, $columns_array, $values_array, $where){
		//controlla se le variabili inserite sono state inizializzate correttamente.
		(is_string($table_name) && is_array($columns_array) && is_array($values_array) && count($columns_array) == count($values_array)) 
			or die("Impossibile effettuare l'inserimento: variabili non inizializzate correttamente.");
		
		//Costruisce la query per il database
		$query_str = 'UPDATE '.$table_name.' SET ';
		$n = count($columns_array);
		for($i=0; $i<$n; $i++){
			if($i>0) $query_str .= ',';
			is_string($columns_array[$i]) or die("Impossibile effettuare la modifica: variabili non inizializzate correttamente");
			$query_str .= $columns_array[$i].' = ';
			if($values_array[$i] === NULL)
				$query_str .= 'NULL ';
			elseif(is_string($values_array[$i]))
				$query_str .= "'".$values_array[$i]."' ";
			else
				$query_str .= $values_array[$i].' ';
		}
		$query_str .= "WHERE $where;";
		
		//Effettua la query
		return $this->query($query_str);
	}
	
	//Elimina dati dal database. NON FA NESSUN TIPO DI CONTROLLO
	function delete($table_name, $where){
		$query_str = 'DELETE FROM '.$table_name.' WHERE '.$where;
		return $this->query($query_str);
	}
	
	// Preleva dal risultato dell'ultima query (o di quella inserita come parametro) il prossimo record.
	function fetch_object($result = false){
		if($result === false){
			if(!$obj = mysql_fetch_object($this->result))
				$this->result = false;
		}
		else{
			$obj = mysql_fetch_object($result);
		}
		return $obj;
	}
	
	// Preleva dal risultato dell'ultima query (o di quella inserita come parametro il prossimo record.
	function fetch_array($result = false){
		if($result === false){
			if(!$arr = mysql_fetch_array($this->result))
				$this->result = false;
		}
		else{
			$arr = mysql_fetch_array($result);
		}
		return $arr;
	}
}
?>