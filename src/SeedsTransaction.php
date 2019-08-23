<?php

require_once __DIR__."/../ext/wprecord/WpRecord.php";
require_once __DIR__."/lib.php";

class SeedsTransaction extends WpRecord {
	static function initialize() {
		self::field("id","integer not null auto_increment");
		self::field("from_user_id","integer not null");
		self::field("to_user_id","integer not null");
		self::field("amount","integer not null");
		self::field("transaction_id","varchar(255) not null");
		self::field("notice","text not null");
	}

	public static function formatUser($user) {
		return $user->data->user_nicename." (".$user->data->user_email.")";
	}

	public function getFromUserFormatted() {
		return SeedsTransaction::formatUser(get_user_by("id",$this->from_user_id));
	}

	public function getToUserFormatted() {
		return SeedsTransaction::formatUser(get_user_by("id",$this->to_user_id));
	}

	public function perform() {
		if ($this->transaction_id)
			throw new Exception("This transaction already has an id!");

		$this->transaction_id=rand_chars(8);

		$fromBalance=intval(get_user_meta($this->from_user_id,"seeds_balance",TRUE));
		$toBalance=intval(get_user_meta($this->to_user_id,"seeds_balance",TRUE));
		if ($fromBalance<$this->amount)
			throw new Exception("Insufficient funds on account.");

		$fromBalance-=$this->amount;
		$toBalance+=$this->amount;

		update_user_meta($this->from_user_id,"seeds_balance",$fromBalance);
		update_user_meta($this->to_user_id,"seeds_balance",$toBalance);

		$this->save();
	}
}
