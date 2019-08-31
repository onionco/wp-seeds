<?php

require_once __DIR__ . '/../ext/wprecord/WpRecord.php';
require_once __DIR__ . '/lib.php';

class SeedsTransaction extends WpRecord {
	static function initialize() {
		self::field( 'id', 'integer not null auto_increment' );
		self::field( 'from_user_id', 'integer not null' );
		self::field( 'to_user_id', 'integer not null' );
		self::field( 'amount', 'integer not null' );
		self::field( 'transaction_id', 'varchar(255) not null' );
		self::field( 'notice', 'text not null' );
	}

	public static function formatUser( $user ) {
		return $user->data->user_nicename . ' (' . $user->data->user_email . ')';
	}

	public function getFromUserFormatted() {
		return self::formatUser( get_user_by( 'id', $this->from_user_id ) );
	}

	public function getToUserFormatted() {
		return self::formatUser( get_user_by( 'id', $this->to_user_id ) );
	}

	public function getOtherUserFormatted( $user ) {
		if ( $user->ID == $this->from_user_id ) {
			return $this->getToUserFormatted();
		} elseif ( $user->ID == $this->to_user_id ) {
			return $this->getFromUserFormatted();
		} else {
			return null;
		}
	}

	public function getRelativeAmount( $user ) {
		if ( $user->ID == $this->to_user_id ) {
			return $this->amount;
		} elseif ( $user->ID == $this->from_user_id ) {
			return -$this->amount;
		} else {
			return null;
		}
	}

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
