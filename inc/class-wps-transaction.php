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
require_once plugin_dir_path( __FILE__ ) . '/../inc/class-wps-form-exception.php';

/**
 * Represents one transaction on the system.
 */
class WPS_Transaction extends WpRecord {
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
	 * Perform a seeding transaction.
	 *
	 * @return void
	 * @throws WPS_Form_Exception If the transaction can't be performed due to a form error.
	 * @throws Exception If the transaction can't be performed for an unknown reason.
	 */
	public function performCreate() {
		if ( isset( $this->transaction_id ) && $this->transaction_id ) {
			throw new Exception( 'This transaction already has an id!' );
		}

		if ( $this->sender ) {
			throw new WPS_Form_Exception( 'Seeding transactions should not have a sender.', 'sender' );
		}
		if ( ! $this->receiver ) {
			throw new WPS_Form_Exception( 'Please select receiver.', 'receiver' );
		}

		$this->transaction_id = 'C' . self::generate_random_id();
		$this->timestamp = time();
		$this->amount = intval( $this->amount );

		if ( $this->amount <= 0 ) {
			throw new WPS_Form_Exception( 'Amount cannot be zero or negative.', 'amount' );
		}

		$balance = intval( get_user_meta( $this->receiver, 'wps_balance', true ) );
		$balance += $this->amount;
		update_user_meta( $this->receiver, 'wps_balance', $balance );
		$this->save();
	}

	/**
	 * Perform a burning transaction.
	 *
	 * @return void
	 * @throws WPS_Form_Exception If the transaction can't be performed due to a form error.
	 * @throws Exception If the transaction can't be performed for an unknown reason.
	 */
	public function performBurn() {
		if ( isset( $this->transaction_id ) && $this->transaction_id ) {
			throw new Exception( 'This transaction already has an id!' );
		}

		if ( ! $this->sender ) {
			throw new WPS_Form_Exception( 'Please select sender.', 'sender' );
		}
		if ( $this->receiver ) {
			throw new WPS_Form_Exception( 'Burning transactions should have no receiver.', 'receiver' );
		}

		$this->transaction_id = 'B' . self::generate_random_id();
		$this->timestamp = time();
		$this->amount = intval( $this->amount );

		if ( $this->amount <= 0 ) {
			throw new WPS_Form_Exception( 'Amount cannot be zero or negative.', 'amount' );
		}

		$balance = intval( get_user_meta( $this->sender, 'wps_balance', true ) );
		if ( $balance < $this->amount ) {
			throw new WPS_Form_Exception( 'Insufficient funds on account.', 'amount' );
		}

		$balance -= $this->amount;
		update_user_meta( $this->sender, 'wps_balance', $balance );
		$this->save();
	}

	/**
	 * Actually perform the transaction. This function is only for normal transactions,
	 * i.e. transactions with a sender and a receiver.
	 *
	 * @return void
	 * @throws WPS_Form_Exception If the transaction can't be performed due to a form error.
	 * @throws Exception If the transaction can't be performed for an unknown reason.
	 */
	public function perform() {
		if ( isset( $this->transaction_id ) && $this->transaction_id ) {
			throw new Exception( 'This transaction already has an id!' );
		}
		$from_balance = intval( get_user_meta( $this->sender, 'wps_balance', true ) );
		$to_balance   = intval( get_user_meta( $this->receiver, 'wps_balance', true ) );

		if ( $from_balance < $this->amount ) {
			throw new WPS_Form_Exception( 'Insufficient funds on account.', 'amount' );
		}

		$this->timestamp = time();
		$this->amount = intval( $this->amount );

		if ( ! $this->sender ) {
			throw new WPS_Form_Exception( 'Please select sender.', 'sender' );
		}
		if ( ! $this->receiver ) {
			throw new WPS_Form_Exception( 'Please select receiver.', 'receiver' );
		}
		if ( $this->sender === $this->receiver ) {
			throw new WPS_Form_Exception( 'The accounts cannot be the same.', 'receiver' );
		}
		if ( $this->amount <= 0 ) {
			throw new WPS_Form_Exception( 'Amount cannot be zero or negative.', 'amount' );
		}
		$this->transaction_id = self::generate_random_id();
		$from_balance        -= $this->amount;
		$to_balance          += $this->amount;
		update_user_meta( $this->sender, 'wps_balance', $from_balance );
		update_user_meta( $this->receiver, 'wps_balance', $to_balance );
		$this->save();
	}

	/**
	 * Get the type of transaction.
	 * Possible values:
	 *   - normal
	 *   - create
	 *   - burn
	 *
	 * @return string
	 */
	public function getType() {
		if ( $this->sender && $this->receiver ) {
			return 'normal';
		}

		if ( $this->sender ) {
			return 'burn';
		}

		if ( $this->receiver ) {
			return 'create';
		}
	}
}
