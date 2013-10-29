<?php

//Questa classe contiene gli utenti di una mail (mittente e destinatari)
class MailUsers{
	//Le variabili vengono riempite sotto forma di stringa; i destinatari di uno stesso campo sono separati da una virgola.
	private $from;
	private $to;
	private $cc;
	private $bcc;
	private $strange_senders;	//di norma vuoto, compare quando il mittente nell'header della mail è diverso dal mittente del protocollo SMTP
	private $strange_recipients;	//di norma vuoto, compare quando ci sono destinatari che compaiono nell'header ma non nei destinatari del protocollo SMTP
	
	//costruttore
	public function MailUsers(){
		$this->reset_obj();
	}
	
	//Inserisce un mittente From
	public function insert_from($from){
		if($this->from == '')
			$this->from = $from;
		else
			$this->from .= ','.$from;
	}
	
	//Inserisce un destinatario To
	public function insert_to($to){
		if($this->to == '')
			$this->to = $to;
		else
			$this->to .= ','.$to;
	}
	
	//Inserisce un destinatario Cc
	public function insert_cc($cc){
		if($this->cc == '')
			$this->cc = $cc;
		else
			$this->cc .= ','.$cc;
	}
	
	//Inserisce un destinatario bcc
	public function insert_bcc($bcc){
		if($this->bcc == '')
			$this->bcc = $bcc;
		else
			$this->bcc .= ','.$bcc;
	}
	
	//Inserisce un destinatario 'strano'
	public function insert_strange_sender($strange_sender){
		if($this->strange_senders == '')
			$this->strange_senders = $strange_sender;
		else
			$this->strange_senders .= ','.$strange_sender;
	}
	
	//Inserisce un mittente 'strano'
	public function insert_strange_recipient($strange_recipient){
		if($this->strange_recipients == '')
			$this->strange_recipients = $strange_recipient;
		else
			$this->strange_recipients .= ','.$strange_recipient;
	}
	
	//Svuota l'oggetto
	public function reset_obj(){
		$this->from = '';
		$this->to = '';
		$this->cc = '';
		$this->bcc = '';
		$this->strange_senders = '';
		$this->strange_recipients = '';
	}
	
	//Queste funzioni ritornano i campi
	public function get_from(){
		return $this->from;
	}
	
	public function get_to(){
		return $this->to;
	}
	
	public function get_cc(){
		return $this->cc;
	}
	
	public function get_bcc(){
		return $this->bcc;
	}
	
	public function get_strange_recipients(){
		return $this->strange_recipients;
	}
	
	public function get_strange_senders(){
		return $this->strange_senders;
	}
}

?>