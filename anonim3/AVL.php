<?php

require_once('binding_cache.php');

//Implementa Binding_cache utilizzando un Albero AVL
class AVLTree implements Binding_cache{
	private $root;		//La radice
	private $max_id;	//Il valore massimo presente nell'albero
	
	function AVLTree(){
		$this->root = false;
		$this->max_id = 0;
	}
	
	//Ritorna il valore massimo contenuto nell'albero
	function get_max(){
		return $this->max_id;
	}
	
	//Cerca la chiave e ritorna il valore se la trova, false altrimenti.
	function ricerca($key){
		return AVLNode::ricerca($key, $this->root);
	}
	
	//Inserisce un nodo nell'albero (coppia chiave-valore)
	function insert($key, $value){
		if($first_root = AVLNode::insert($key, $value, $this->root))
			$this->root = $first_root;
		$value_noid = (int)substr($value, 2);
		if($value_noid > $this->max_id)
			$this->max_id = $value_noid;
	}
	
	//Cerca la chiave nell'albero o la inserisce se non è presente, e ne restituisce il valore.
	function retrieve($key){
		$result = $this->ricerca($key);
		if($result === false){
			$this->max_id++;
			$new_val = "id".$this->max_id;
			$this->insert($key, "$new_val");
			return $new_val;
		}
		else
			return $result;
	}
	
	//Ritorna una query string per il salvataggio su DB
	function save_query($table_name, $key_field_name, $value_field_name){
		if (!$this->root) return false;
		$query_str = "INSERT IGNORE INTO $table_name ($key_field_name, $value_field_name) VALUES ";
		$query_str .= AVLNode::save_query($this->root);
		$query_str .= ";";
		return $query_str;
	}
	
	//Stampa l'albero
	function display(){
		AVLNode::display($this->root);
	}
}

//Nodo AVL
class AVLNode{
	private $l = false; //figlio sinistro
	private $r = false; //figlio destro
	private $k = false; //chiave di ricerca del nodo
	private $v = false; //valore del nodo
	private $h = -1; //altezza dell'albero
	
	//cerca nell'albero
	static function ricerca($k, $t){
		if(!$t)
		{ 
			return false; //se l'albero è nullo ritorna false
		}
		else{
			if($t->k == $k) //elemento trovato ritorna il valore
				return $t->v;
			else if($k > $t->k) //se l'elemento da ricercare è maggiore del valore del nodo
				{
					return AVLNode::ricerca($k, $t->r); //controlla nel ramo destro
				}
			else
				{
					return AVLNode::ricerca($k, $t->l); //controllo nel ramo sinistro
				}
		}
	}

	/*trova il valore minimo contenuto nell'albero */
	static function minimo($t){
		if(!($t->l)) // se il nodo sinistro è null
			return $t->k; //ritorna il minimo
		return AVLNode::minimo($t->l); //altrimenti percorre il ramo sinistro ricorsivamente
	
	}

	static function altezza($t){
		if(!$t) return -1; //se l'albero è nullo ritorna -1
		else return $t->h;
	}
	
	/* verifica lo sbilanciamento */
	static function sbil($t){
		if(!$t) //se l'albero è nullo ritorna 0
			return 0;
		return AVLNode::altezza($t->l) - AVLNode::altezza($t->r); //altrimenti ritorna la differenza tra l'altezza sinistra e quella destra
	}
	
	//rotazione dd
	static function dd($t){
		$b = $t;
		$a = $b->r;		
		if(AVLNode::sbil($a) == 0){
			$b->h -= 1;
			$a->h += 1;
		}
		else{
			$b->h -= 2;
		}
		$b->r = $a->l;
		$a->l = $b;
		$t = $a;
		return $t;
	}
	
	//rotazione ss
	static function ss($t){
		$b = $t;
		$a = $b->l;		
		if(AVLNode::sbil($a) == 0){
			$b->h -= 1;
			$a->h += 1;
		}
		else{
			$b->h -= 2;
		}
		$b->l = $a->r;
		$a->r = $b;
		$t = $a;
		return $t;
	}
	
	//rotazione sd
	static function sd($t){
		$c = $t;
		$a = $t->l;
		$b = $t->l->r;
		
		$b->h += 1;
		$a->h -= 1;
		$c->h -= 2;
		
		$a->r = $b->l;
		$c->l = $b->r;
		$b->l = $a;
		$b->r = $c;
		return $b;
	}
	
	//rotazione ds
	static function ds($t){
		$c = $t;
		$a = $t->r;
		$b = $t->r->l;
		
		$b->h += 1;
		$a->h -= 1;
		$c->h -= 2;
		
		$a->l = $b->r;
		$c->r = $b->l;
		$b->r = $a;
		$b->l = $c;
		return $b;
	}
	/* bilancia il nodo */
	static function bilancia($t){
		if($t){
			$t->h = 1 + max(array(AVLNode::altezza($t->l), AVLNode::altezza($t->r))); //aggiorna le altezze
			if(abs(AVLNode::sbil($t)) >= 2){ //controlla se il nodo è ancora 1-bilanciato
				if(AVLNode::sbil($t) == 2){ //se l'albero è sbilanciato e pende a sinistra
					if(AVLNode::sbil($t->l) == -1){ //se il sotto albero sinistro pende a destra (e logicamente è ancora 1-bilanciato)
						$t = AVLNode::sd($t);
					}
					else{ //invece se il sotto albero sinistro pende a sinistra oppure è perfettamente bilanciato
						$t = AVLNode::ss($t);
					}
				}
				else{ //se l'albero è sbilanciato e pende a destra
					if(AVLNode::sbil($t->r) == 1){ //se il sotto albero destro pende a sinistra
						$t = AVLNode::ds($t);
					}
					else{ //invece se il sotto albero destro pende a destra oppure è perfettamente bilanciato
						$t = AVLNode::dd($t);
					}
				}
			}
		}
		return $t;
	}
	
	static function insert($k, $v, $t){
		if(!$t){ //caso base se l'albero è nullo
			$t = new AVLNode();
			$t->k=$k; 	//assegna la chiave del nodo
			$t->v=$v;	//assegna il valore al nodo
			$t->l=false; //pone left a null
			$t->r=false; //pone right a null
			$t->h = 0; //pone l'altezza a 0
			return $t;
		}
		else 
		{
			if($k == $t->k){ //se l'elemento è già presente nell'albero
				$t->k = $k; //lo lascia immutato
			}
			else if($k < $t->k){ 
				if($ins = AVLNode::insert($k,$v,$t->l))
					$t->l = $ins; //se l'elemento è minore dell'elemento contenuto nel nodo continua l'inserimento nel ramo sinistro     
			}
			else if($ins = AVLNode::insert($k,$v,$t->r)) //altrimenti continua l'inserimento nel ramo destro 
				$t->r = $ins;
			return AVLNode::bilancia($t);
		}
	}
	
	static function display_aux($t, $n){
		if(!$t) return;
		AVLNode::display_aux($t->r,$n+1);
		for($i=0;$i<$n;$i++) echo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
		echo substr($t->k,0,5) , "..->" , $t->v , "<br />";
		AVLNode::display_aux($t->l,$n+1);
	}
	
	static function display($t){
		if(!$t){
			echo "L'albero è vuoto.<br />" ;
		}
		else{
			AVLNode::display_aux($t,0);
		}
	}
	
	static function save_query($t){
		$query_str = "";
		if($t->l){
			$query_str .= AVLNode::save_query($t->l);
			$query_str .= ', ';
		}
		$query_str .= "('".addslashes($t->k)."', '".addslashes($t->v)."') ";
		if($t->r){
			$query_str .= ', ';
			$query_str .= AVLNode::save_query($t->r);
		}
		return $query_str;
	}
}

?>