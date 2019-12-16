<?php

require_once plugin_dir_path( __FILE__ ) . '/../ext/wprecord/WpRecord.php';

class Transaction extends WpRecord {
	public static function initialize() {
		self::field("id","integer not null auto_increment");
		self::field("sender","integer not null");
		self::field("receiver","integer not null");
		self::field("amount","integer not null");
		self::field("timestamp","integer not null");
		self::field("notes","text");
	}

	// Old code! fix me!
	public function perform() {
		if ( $this->transaction_id ) {
			throw new Exception( 'This transaction already has an id!' );
		}
		$fromBalance = intval( get_user_meta( $this->from_user_id, 'seeds_balance', true ) );
		$toBalance   = intval( get_user_meta( $this->to_user_id, 'seeds_balance', true ) );
		if ( $fromBalance < $this->amount ) {
			throw new Exception( 'Insufficient funds on account.' );
		}
		$this->amount = intval( $this->amount );
		if ( $this->amount <= 0 ) {
			throw new Exception( 'Amount cannot be zero or negative.' );
		}
		if ( ! $this->from_user_id || ! $this->to_user_id ) {
			throw new Exception( "The user doesn't exist." );
		}
		if ( $this->from_user_id == $this->to_user_id ) {
			throw new Exception( 'The accounts cannot be the same.' );
		}
		$this->transaction_id = rand_chars( 8 );
		$fromBalance -= $this->amount;
		$toBalance   += $this->amount;
		update_user_meta( $this->from_user_id, 'seeds_balance', $fromBalance );
		update_user_meta( $this->to_user_id, 'seeds_balance', $toBalance );
		$this->save();
	}
}
