<?php

require_once('binding_cache.php');

//Implementa Binding_cache utilizzando un array associativo
class BCArray implements Binding_cache{
	public $cache;		//L'array
	private $max_id;	//Il valore massimo presente nell'array
	
	function BCArray(){
		$this->cache = array();
		$this->max_id = 0;
	}
	
	//Ritorna il valore massimo contenuto nell'array
	function get_max(){
		return $this->max_id;
	}
	
	//Cerca la chiave e ritorna il valore se la trova, false altrimenti.
	function ricerca($key){
		if(isset($this->cache[$key]))
			return $this->cache[$key];
		else return false;
	}
	
	//Inserisce un nodo nell'array (coppia chiave-valore)
	function insert($key, $value){
		if(!isset($this->cache[$key])){
			$this->cache[$key] = $value;
			$value_noid = (int)substr($value, 2);
			if($value_noid > $this->max_id)
				$this->max_id = $value_noid;
		}
	}
	
	//Cerca la chiave nell'albero o la inserisce se non è presente, e ne restituisce il valore.
	function retrieve($key){
		if(!isset($this->cache[$key])){
			$this->max_id++;
			$this->cache[$key] = 'id'.$this->max_id;
		}
		return $this->cache[$key];
	}
	
	//Ritorna una query string per il salvataggio su DB
	function save_query($table_name, $key_field_name, $value_field_name){
		if (count($this->cache) == 0) return false;
		else{
			$query_str = "INSERT IGNORE INTO $table_name ($key_field_name, $value_field_name) VALUES ";
			$iniziato = false;
			foreach($this->cache as $k=>$v){
				if($iniziato) $query_str.= ',';
				$query_str .= "('".addslashes($k)."','".addslashes($v)."')";
				$iniziato = true;
			}
			$query_str .= ";";
		}
		return $query_str;
	}
}

?>