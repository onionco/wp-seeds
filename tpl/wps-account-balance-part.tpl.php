<?php
/**
 * WP Seeds 🌱
 *
 * @package   wp-seeds/tpl
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange & Derek Smith
 * @copyright 2019 Mikael Lindqvist & Niels Lange & Derek Smith
 * @license   GPL v2 or later
 */

?>

<?php

$user_id = get_current_user_id();
$user_meta = get_user_meta( $user_id );

$user_info = get_userdata( $user_id );
$user_email = $user_info->user_email;

$user_first = $user_meta['first_name'][0];
$user_last = $user_meta['last_name'][0];
$user_balance = $user_meta['wps_balance'][0];

// var_dump($user_meta);
// var_dump($user_info);

?>

<div class="seeds">
    <h2>Welcome <?php echo $user_first . ' ' . $user_last; ?></h2>

    <div class="seeds-balance">
        <p>Your Current Balance is:</p>
        <p class="CurrSeeds"><?php echo "{$user_balance} Seed" . ( $user_balance == 1 ? '' : 's' ); ?></p>
    </div>
</div>