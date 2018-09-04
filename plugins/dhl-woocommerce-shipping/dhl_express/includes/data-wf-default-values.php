<?php
	global $woocommerce;
    $general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
    if(empty($general_settings))
    {
        
	$wc_main_settings 							= array();
	$wc_main_settings['production'] 			= '';
	$wc_main_settings['account_number'] 		= '';
	$wc_main_settings['site_id'] 				= '';
	$wc_main_settings['site_password'] 			= '';
	$wc_main_settings['enabled'] 				= 'yes';
	$wc_main_settings['enabled_label'] 			= 'yes';
	$wc_main_settings['insure_contents'] 		= '';
	$wc_main_settings['debug'] 					= '';
	$wc_main_settings['dhl_currency_type'] 		= '';
	$wc_main_settings['delivery_time'] 			= '';
	$wc_main_settings['request_type'] 			= 'LIST';
	$wc_main_settings['show_dhl_extra_charges'] = '';
	$wc_main_settings['offer_rates'] 			= 'all';
	$wc_main_settings['title'] 					= __( 'DHL', 'wf-shipping-dhl' );
	$wc_main_settings['availability'] 			= 'all';
	$wc_main_settings['base_country'] 			= $woocommerce->countries->get_base_country();
	//$wc_main_settings['services'] = $_POST['dhl_service'];
	$main_country_data 							= include_once('data-wf-country-details.php');
	$wc_main_settings['region_code']			= isset($main_country_data[$wc_main_settings['base_country']]['region']) ? $main_country_data[$wc_main_settings['base_country']]['region'] : 'EU';
	$wc_main_settings['plt'] 					= '';
	$wc_main_settings['enable_saturday_delivery'] = '';
	$wc_main_settings['cash_on_delivery'] = '';
	$wc_main_settings['show_front_end_shipping_method'] = '';
	$wc_main_settings['output_format'] = '6X4_A4_PDF';
	$wc_main_settings['image_type'] = 'PDF';
	$wc_main_settings['return_label_key'] = '';
	$wc_main_settings['default_domestic_service'] = 'none';
	$wc_main_settings['default_international_service'] = 'none';
	$wc_main_settings['add_trackingpin_shipmentid'] = '';
	$wc_main_settings['packing_method'] = 'per_item';
	$wc_main_settings['dimension_weight_unit'] = isset($main_country_data[$wc_main_settings['base_country']]['weight']) ? $main_country_data[$wc_main_settings['base_country']]['weight'] : 'LBS_IN';
	$wc_main_settings['dhl_currency_type'] = isset($main_country_data[$wc_main_settings['base_country']]['currency']) ? $main_country_data[$wc_main_settings['base_country']]['currency'] : '';
	$wc_main_settings['shp_pack_type'] = 'BOX';
	$wc_main_settings['countries'] = array();

	$sort = 0;
	$ordered_services = array();
	foreach ( $this->services as $code => $name ) {
		if ( isset( $custom_services[ $code ]['order'] ) ) {
			$sort = $custom_services[ $code ]['order'];
			}
		while ( isset( $ordered_services[ $sort ] ) )
			$sort++;
			$ordered_services[ $sort ] = array( $code, $name );
			$sort++;
		}
		ksort( $ordered_services );
		$dhl_service = array();
		foreach ( $ordered_services as $value ) {
			
			$code = $value[0];
			$name = $value[1];
			$dhl_service[ $code]['order'] = isset( $custom_services[ $code ]['order'] ) ? $custom_services[ $code ]['order'] : '';
			$dhl_service[ $code]['name'] = isset( $custom_services[ $code ]['name'] ) ? $custom_services[ $code ]['name'] : '';
			$dhl_service[ $code]['enabled'] = in_array($code,array('P','N')) ? true : '';
			$dhl_service[ $code]['adjustment'] = isset( $custom_services[ $code ]['adjustment'] ) ? $custom_services[ $code ]['adjustment'] : '';
			$dhl_service[ $code]['adjustment_percent'] = isset( $custom_services[ $code ]['adjustment_percent'] ) ? $custom_services[ $code ]['adjustment_percent'] : '';
		}
		$wc_main_settings['services'] = $dhl_service;

		update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
    }else if(!isset($general_settings['base_country']))
    {
       $general_settings['base_country'] = $woocommerce->countries->get_base_country();
       update_option('woocommerce_wf_dhl_shipping_settings',$general_settings);
    }