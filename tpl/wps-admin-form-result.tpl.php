<?php
/**
 * WP Seeds 🌱
 *
 * Custom functionality for transactions overview page.
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

?>
<?php if ( isset( $success ) ) { ?>
	<div class="notice notice-success is-dismissible">
		<p><?php echo esc_html( $success ); ?></p>
	</div>
	<style>
		<?php echo esc_html( $form_css_selector ); ?> {
			display: none;
		}
	</style>
<?php } ?>

<?php if ( isset( $error ) ) { ?>
	<div class="notice notice-error is-dismissible">
		<p><?php echo esc_html( $error->getMessage() ); ?></p>
	</div>

	<?php if ( $error instanceof WPS_Form_Exception ) { ?>
		<style>
			<?php echo esc_html( $form_css_selector ); ?>
			[name="<?php echo esc_attr( $error->field ); ?>"] {
				border-color: #f00 !important;
			}
		</style>
	<?php } ?>
<?php } ?>
