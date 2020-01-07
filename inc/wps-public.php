<?php

register_activation_hook( __FILE__, 'flush_rewrite_rules' );

add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ){
    $wp_rewrite->rules = array_merge(
        ['seeds-account/?$' => 'index.php?wpsaccount=1'],
        ['seeds-account/send/?$' => 'index.php?wpssend=1'],
        ['seeds-account/request/?$' => 'index.php?wpsrequest=1'],
        $wp_rewrite->rules
    );
} );

add_filter( 'query_vars', function( $query_vars ){
    $query_vars[] = 'wpsaccount';
    $query_vars[] = 'wpssend';
    $query_vars[] = 'wpsrequest';
    return $query_vars;
} );

add_action( 'template_redirect', function(){
    $seeds_account = intval( get_query_var( 'wpsaccount' ) );
    $send_seeds = intval( get_query_var( 'wpssend' ) );
    $request_seeds = intval( get_query_var( 'wpsrequest' ) );

    $wps_id = get_option('wpseeds_wpsaccount_page_id');
    $wps_post = get_post($wps_id); 
    $wps_content = $wps_post->post_content;
    
    if ( $seeds_account || $send_seeds || $request_seeds ) {
        
        include( __DIR__ . '/wps-account/seeds-account.php' );

        exit();
    }
} );