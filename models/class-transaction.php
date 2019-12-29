<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

/**
 * Include required classes and files.
 *
 * @since 1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . '/../ext/wprecord/WpRecord.php';

/**
 * Represents one transaction on the system.
 */
class Transaction extends WpRecord {
	/**
	 * Initialize. Set up database fields.
	 *
	 * @return void
	 */
	public static function initialize() {
		self::field( 'id', 'integer not null auto_increment' );
		self::field( 'transaction_id', 'varchar(255) not null' );
		self::field( 'sender', 'integer not null' );
		self::field( 'receiver', 'integer not null' );
		self::field( 'amount', 'integer not null' );
		self::field( 'timestamp', 'integer not null' );
		self::field( 'notes', 'text' );
	}

	/**
	 * Generate a random id.
	 *
	 * @return string The random id.
	 */
	public static function generate_random_id() {
		return substr( md5( microtime() ), 0, 16 );
	}

	/**
	 * Actually perform the transaction.
	 *
	 * @return void
	 * @throws Exception If the transaction can't be performed.
	 */
	public function perform() {
		if ( $this->transaction_id ) {
			throw new Exception( 'This transaction already has an id!' );
		}
		$from_balance = intval( get_user_meta( $this->sender, 'seeds_balance', true ) );
		$to_balance   = intval( get_user_meta( $this->receiver, 'seeds_balance', true ) );

		/*
		TODO: Enable me!
		if ( $from_balance < $this->amount ) {
			throw new Exception( 'Insufficient funds on account.' );
		}
		*/

		$this->amount = intval( $this->amount );
		if ( $this->amount <= 0 ) {
			throw new Exception( 'Amount cannot be zero or negative.' );
		}
		if ( ! $this->sender || ! $this->receiver ) {
			throw new Exception( "The user doesn't exist." );
		}
		if ( $this->sender === $this->receiver ) {
			throw new Exception( 'The accounts cannot be the same.' );
		}
		$this->transaction_id = self::generate_random_id();
		$from_balance        -= $this->amount;
		$to_balance          += $this->amount;
		update_user_meta( $this->sender, 'seeds_balance', $from_balance );
		update_user_meta( $this->receiver, 'seeds_balance', $to_balance );
		$this->save();
	}
}
