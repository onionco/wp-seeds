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
 * Functionality for using CMB2 for forms with custom save logic.
 */
class CMB2_Custom_Handler {

	/**
	 * Hook up this metabox.
	 *
	 * @param CMB2 $cmb The metabox to hook up.
	 * @return CMB2_Custom_Handler The created handler.
	 */
	public static function hookup( $cmb ) {
		$handler = new CMB2_Custom_Handler();
		$handler->attach( $cmb );

		return $handler;
	}

	/**
	 * Attach this handler to a metabox.
	 * Rather than using this function directly, consider using the hookup
	 * function to create and hook up a handler with one call.
	 *
	 * @param CMB2 $cmb The metabox to hook up.
	 * @return void
	 */
	public function attach( $cmb ) {
		$this->cmb = $cmb;

		$this->option_key = $this->cmb->prop( 'option_key' );
		if ( is_array( $this->option_key ) ) {
			$this->option_key = $this->option_key[0];
		}

		$this->cmb->set_prop( 'display_cb', array( $this, 'display' ) );
		$this->cmb->set_prop( 'message_cb', array( $this, 'message' ) );

		add_filter( 'cmb2_override_meta_value', array( $this, 'meta_value' ), 10, 3 );

		$this->save_error = null;
		$this->save_result = null;

		if ( is_req_var( 'page' )
				&& get_req_str( 'page' ) == $this->option_key
				&& is_req_var( 'submit-cmb' ) ) {
			$this->handle_save();
		}
	}

	/**
	 * Handle save.
	 *
	 * @return void
	 */
	public function handle_save() {
		$cb = $this->cmb->prop( 'save_cb' );

		if ( $cb ) {
			try {
				call_user_func( $cb );
				$this->save_success = true;
			} catch ( Exception $e ) {
				$this->save_error = $e->getMessage();
			}
		}
	}

	/**
	 * Get a value for the form.
	 * This function is registered to listen to a filter.
	 *
	 * @param string $default Passed to the filter.
	 * @param string $context Passed to the filter.
	 * @param array  $a Passed to the filter.
	 * @return string The value
	 */
	public function meta_value( $default, $context, $a ) {
		if ( is_req_var( $a['field_id'] ) ) {
			return get_req_str( $a['field_id'] );
		} else {
			return null;
		}
	}

	/**
	 * Show a message.
	 * This function is registered to listen to an action.
	 *
	 * @param CMB2  $cmb The metabox.
	 * @param array $args Message parameters.
	 * @return void
	 */
	public function message( $cmb, $args ) {
		if ( $this->save_success ) {
			$save_message = $this->cmb->prop( 'save_message' );

			if ( ! $save_message ) {
				$save_message = 'Item saved';
			}

			add_settings_error( $args['setting'], '', $save_message, 'success' );
		}

		if ( $this->save_error ) {
			add_settings_error( $args['setting'], '', $this->save_error, 'error' );
		}
	}

	/**
	 * Show the form.
	 * This function is registered as the display_cb.
	 *
	 * @param CMB_Hookup $hookup The hookup related to the metabox.
	 * @return void
	 */
	public function display( $hookup ) {
		$target_url = admin_url( $hookup->cmb->prop( 'parent_slug' ) . '?page=' . $hookup->option_key );

		$tabs = $hookup->get_tab_group_tabs();
		?>
		<div class="wrap cmb2-options-page option-<?php echo esc_attr( $hookup->option_key ); ?>">
			<?php if ( $hookup->cmb->prop( 'title' ) ) : ?>
				<h2><?php echo wp_kses_post( $hookup->cmb->prop( 'title' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( ! $this->save_success ) { ?>
				<?php if ( ! empty( $tabs ) ) : ?>
					<h2 class="nav-tab-wrapper">
						<?php foreach ( $tabs as $option_key => $tab_title ) : ?>
							<a class="nav-tab
							<?php
							if ( CMB2_Options_Hookup::is_page( $option_key ) ) :
								?>
								 nav-tab-active<?php endif; ?>" href="<?php menu_page_url( $option_key ); ?>"><?php echo wp_kses_post( $tab_title ); ?></a>
						<?php endforeach; ?>
					</h2>
				<?php endif; ?>
				<form class="cmb-form"
						action="<?php echo esc_url( $target_url ); ?>"
						method="POST" 
						id="<?php echo esc_attr( $hookup->cmb->cmb_id ); ?>"
						enctype="multipart/form-data"
						encoding="multipart/form-data">
					<input type="hidden"
							name="action"
							value="<?php echo esc_attr( $hookup->option_key ); ?>">
					<?php $hookup->options_page_metabox(); ?>
					<?php submit_button( esc_attr( $hookup->cmb->prop( 'save_button' ) ), 'primary', 'submit-cmb' ); ?>
				</form>
			<?php } ?>
		</div>
		<?php
	}
};
