<?php

//Funzione di anonimizzazione
function anonymize($field, $value,  $binding_cache, $to_SMTP_name, $other_name, $internal_domains){
	if($field == $to_SMTP_name)
	{
		$recipients = explode(',', $value);
		$recipients_anonym = array();
		foreach($recipients as $rec){
			$domain = explode('@', $rec);
			if(isset($domain[1]))
				$domain = $domain[1];
			else
				$domain = 'UNKNOWN';
			if(!(array_search($domain, $internal_domains) === false))
				$recipients_anonym[] = $binding_cache->retrieve($rec);
			else {
				$recipients_anonym[] = $domain;
			}
		}
		$value = implode(',',$recipients_anonym);
	}
	return $value;
}

//Inserisce gli indirizzi fake (user unknown).
function append_fake_recipients($recipients, $db, $msg_id, $mta_table, $user_unknown_flag){
	$query_str = "SELECT status from $mta_table WHERE msg_id = '$msg_id' AND type = 'unknown_user'; ";
	$res = $db->query($query_str);
	$iniziato = trim($recipients) == '';
	while($obj = $db->fetch_object($res)){
		$recipients .= ','.$user_unknown_flag;
	}
	return $recipients;
}

//Ritorna il parametro p nella query string, se esiste e non è nullo.
function get($p){
	if(isset($_GET[$p]) && $_GET[$p] != "")
		return $_GET[$p];
	else
		return false;
}

//Verifica se il campo inserito fa parte dei campi validi
function is_valid_field($field, $valid_fields){
	return !(array_search($field, $valid_fields) === false);
}

?>