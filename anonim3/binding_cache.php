<?php

interface Binding_cache{
	function ricerca($key);
	
	//Inserisce un elemento nella cache (coppia chiave-valore)
	function insert($key, $value);
	
	//Cerca la chiave nella cache o la inserisce se non è presente, e ne restituisce il valore.
	function retrieve($key);
	
	//Ritorna una query string per il salvataggio su DB
	function save_query($table_name, $key_field_name, $value_field_name);
	
	//Ritorna il valore massimo contenuto nella cache
	function get_max();
}

?>