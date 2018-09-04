<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;

/**
 * Array of settings
 */
wp_enqueue_media();
return array(
	'wf_dhl_tab_box_key' => array(
		'type' 			=> 'wf_dhl_tab_box'
		),
 
);