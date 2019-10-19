<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

/**
 * Class for doing form validation.
 */
class WPS_Form_Validator {

	/**
	 * The request vars we are processing.
	 *
	 * @var array $request_vars
	 */
	private $request_vars;

	/**
	 * The checks to perform. This array is indexed by the field name. For each field
	 * there is an array with the checks as key and parameters as values.
	 *
	 * @var array $checks
	 */
	private $checks;

	/**
	 * The url where the form should be submitted.
	 *
	 * @var string $action_url
	 */
	private $action_url;

	/**
	 * Have the checks been performed already?
	 *
	 * @var bool checks_performed
	 */
	private $checks_performed;

	/**
	 * Constructor.
	 *
	 * @param array $request_vars The request vars we are processing, defaults to $_REQUEST.
	 * @return void
	 */
	public function __construct( $request_vars = null ) {
		if ( ! $request_vars ) {
			$request_vars = $_REQUEST; //phpcs:ignore
		}

		$this->request_vars      = $request_vars;
		$this->checks            = array();
		$this->checks_performed  = false;
		$this->messages          = array();
		$this->messages['error'] = array();
	}

	/**
	 * Do the actual checks.
	 *
	 * @return void
	 * @throws Exception For invalid checks.
	 */
	private function do_checks() {
		if ( $this->checks_performed ) {
			throw new Exception( 'The checks have already been performed.' );
		}

		foreach ( $this->checks as $field_name => $field_checks ) {
			foreach ( $field_checks as $check => $params ) {
				$value = $this->get_unchecked( $field_name );

				switch ( $check ) {
					case 'wp_user_id':
						$value = intval( $value );
						$u     = get_user_by( 'id', $value );

						if ( ! $u->ID ) {
							$this->messages['error'][] = 'Please select a user.';
						}
						break;

					case 'positive_number':
						if ( intval( $value ) <= 0 ) {
							$this->messages['error'][] = 'Please enter a positive number.';
						}
						break;

					default:
						throw new Exception( 'Invalid check' );
				}

				if ( array_key_exists( $field_name, $this->request_vars ) ) {
					$this->request_vars[ $field_name ] = $value;
				}
			}
		}

		$this->checks_performed = true;
	}

	/**
	 * Ensure the checks have been performed.
	 *
	 * @return void
	 */
	private function ensure_checked() {
		if ( ! $this->checks_performed ) {
			$this->do_checks();
		}
	}

	/**
	 * Set the action url.
	 *
	 * @param string $action_url The action url.
	 */
	public function set_action_url( $action_url ) {
		$this->action_url = $action_url;
	}

	/**
	 * Get a form field value.
	 * This function can only be called if there is a valid submission, otherwise
	 * an exception will be thrown.
	 *
	 * @param array $field The field name.
	 * @return mixed The field value.
	 * @throws Exception If there are validation errors for the form.
	 */
	public function get_checked( $field ) {
		if ( ! $this->is_valid_submission() ) {
			throw new Exception( 'Cannot get checked values, there are errors.' );
		}

		return $this->request_vars[ $field ];
	}

	/**
	 * Get unchecked value.
	 * Should only be used when rendering the form.
	 * For business login use get_checked.
	 *
	 * @param string $field The field name.
	 */
	public function get_unchecked( $field ) {
		return $this->request_vars[ $field ];
	}

	/**
	 * Add a check for a field.
	 *
	 * @param string $field The field to check.
	 * @param string $check The check to perform.
	 * @param array  $params The parameters for the check.
	 *
	 * @throws Exception If the checks have already been performed.
	 */
	protected function add_check( $field, $check, $params = array() ) {
		if ( $this->checks_performed ) {
			throw new Exception( 'The checks have already been perform, cannot add check.' );
		}

		if ( ! array_key_exists( $field, $this->checks ) ) {
			$this->checks[ $field ] = array();
		}

		$this->checks[ $field ][ $check ] = $params;
	}

	/**
	 * Check that this is a valid WordPress user id.
	 *
	 * @param string $field The form field to check.
	 * @return void
	 */
	public function check_wp_user_id( $field ) {
		$this->add_check( $field, 'wp_user_id' );
	}

	/**
	 * Check for a positive number.
	 *
	 * @param string $field The form field to check.
	 * @return void
	 */
	public function check_positive_number( $field ) {
		$this->add_check( $field, 'positive_number' );
	}

	/**
	 * Do we have a valid submission?
	 *
	 * @return bool
	 */
	public function is_valid_submission() {
		if ( ! $this->is_submitted() ) {
			return false;
		}

		$this->ensure_checked();

		if ( count( $this->messages['error'] ) > 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Output a temporary field value to render in a form.
	 *
	 * @param string $field The field name.
	 */
	public function echo_esc_attr_unchecked( $field ) {
		echo esc_attr( $this->request_vars[ $field ] );
	}

	/**
	 * Display error messages, if any.
	 *
	 * @return void
	 */
	public function echo_messages() {
		if ( $this->is_submitted() ) {
			$this->ensure_checked();
		}

		display_notices( $this->messages );
	}

	/**
	 * Is the form submitted? This check is done by looking at the request
	 * var. If all the variables exist, then the form is considered to
	 * be submitted.
	 *
	 * @return bool
	 */
	public function is_submitted() {
		foreach ( $this->checks as $field_name => $field_checks ) {
			if ( ! array_key_exists( $field_name, $this->request_vars ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Echo the action for the form.
	 *
	 * @return void
	 */
	public function echo_esc_attr_action() {
		echo esc_attr( $this->action_url );
	}

	/**
	 * Mark the form as done. The request variables will be cleared,
	 * and the message will be shown.
	 * 
	 * @param string $message The success message to show.
	 * @return void
	 */
	public function done( $message ) {
		$this->request_vars=array();
		$this->messages[ 'success' ][] = $message;
	}
}
