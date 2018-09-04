<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ship_from_address_options = array(
	'origin_address' => __('Origin Address', 'wf-shipping-dhl'),
);
$ship_from_address_options = apply_filters('wf_filter_label_ship_from_address_options', $ship_from_address_options);

global $woocommerce;

/**
 * Array of settings
 */
return array(
   'licence'  => array(
		'type'            => 'activate_box'
	),
	'enabled'          => array(
		'title'           => __( 'DHL Ecommerce', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable', 'wf-shipping-dhl' ),
		'default'         => 'no'
	),
	'account_number'           => array(
		'title'           => __( 'Pickup Account Number', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => '130000279'
    ),
    'client_id'           => array(
		'title'           => __( 'Client ID', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => ''
    ),
    'site_id'           => array(
		'title'           => __( 'Site ID', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'CIMGBTest',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'site_password'           => array(
		'title'           => __( 'Site Password', 'wf-shipping-dhl' ),
		'type'            => 'password',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'DLUntOcJma',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
   'facility_code'           => array(
		'title'           => __( 'Facility Code', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    
	'dimension_weight_unit' => array(
			'title'           => __( 'Dimension/Weight Unit', 'woocommerce-shipping-dhl' ),
			'label'           => __( 'This unit will be passed to DHL.', 'woocommerce-shipping-dhl' ),
			'type'            => 'select',
			'default'         => 'LBS_IN',
			'description'     => 'Product dimensions and weight will be converted to the selected unit and will be passed to DHL. Please change the box dimensions and weight accordingly as its preloaded with unit Pound and Inches.',
			'options'         => array(
				'LBS_IN'	=> __( 'Pounds & Inches', 'woocommerce-shipping-dhl'),
				'KG_CM' 	=> __( 'Kilograms & Centimetres', 'woocommerce-shipping-dhl')			
			)
		),
	
	'boxes'  => array(
		'type'            => 'box_packing',
		'default'       => include( 'data-wf-box-sizes.php' )
	),
   
	'services'  => array(
		'type'            => 'services'
	),
	'ship_from_address'   => array(
		'title'           => __( 'Ship From Address Preference', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'origin_address',
		'options'         => $ship_from_address_options,
		'description'     => __( 'Change to desired option if you ship from a different location other than shipment origin address given below.', 'wf-shipping-dhl' ),
		'desc_tip'        => true
	),
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Enter postcode for the <strong>Shipper</strong>.', 'wf-shipping-dhl' ),
		'default'         => '10027'
    ),
	'shipper_person_name'           => array(
			'title'           => __( 'Shipper Person Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Mr Shipper',
			'description'     => 'Required for label Printing'			
	),	
	'shipper_company_name'           => array(
			'title'           => __( 'Shipper Company Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Company Name'	,
			'description'     => 'Required for label Printing'
	),	
	'shipper_phone_number'           => array(
			'title'           => __( 'Shipper Phone Number', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => '1 234 1234567'	,
			'description'     => 'Required for label Printing'
    ),
	'shipper_email'           => array(
			'title'           => __( 'Shipper Email', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'test@test.com'	,
			'description'     => 'Required for label Printing'
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => '1 New Orchard Road',
		'description'     => 'Required for label Printing.'
    ),
    'shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Address 2', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'Armonk',
		'description'     => 'Required for label Printing.'
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'New York',
		'description'     => 'Required for label Printing.'
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State Code', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'NY',
		'description'     => 'Required for label Printing.'
    ),
	'output_format'   => array(
		'title'           => __( 'Label print size', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => '8x4 indicates A4 size and 6x4 indicates thermal size.',
		'default'         => '6X4_A4_PDF',
		'options'         => array(
			'8X4_A4_PDF' 						      	=> __( '8X4_A4_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_thermal' 						      	=> __( '8X4_thermal', 						'woocommerce-shipping-dhl'),
			'8X4_A4_TC_PDF' 						    => __( '8X4_A4_TC_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_CI_PDF' 						      	=> __( '8X4_CI_PDF', 						'woocommerce-shipping-dhl'),
			'8X4_CI_thermal' 						    => __( '8X4_CI_thermal', 						'woocommerce-shipping-dhl'),
			'6X4_A4_PDF' 					      		=> __( '6X4_A4_PDF', 					'woocommerce-shipping-dhl'),
			)				
		),		
	'image_type'   => array(
		'title'           => __( 'Image Type', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => 'Please use printer driver / browser plugin to identify ZPL2/EPL2 format and print directly to the printer.',
		'default'         => 'PDF',
		'options'         => array(
			'PDF' 						      	=> __( 'PDF', 						'woocommerce-shipping-dhl'),
			'ZPL2' 						      	=> __( 'ZPL2', 						'woocommerce-shipping-dhl'),
			'EPL2' 						      	=> __( 'EPL2', 						'woocommerce-shipping-dhl'),
			
			)				
		)	,
	'add_trackingpin_shipmentid' => array(
			'title'           => __( 'Tracking PIN', 'woocommerce-shipping-dhl' ),
			'label'           => __( 'Add Tracking PIN to customer order notes', 'woocommerce-shipping-dhl' ),
			'type'            => 'checkbox',
			'default'         => 'no',
			'description'     => ''
		),
    'custom_message'		=> array(
            'title'				=> __( 'Custom Shipment Message', 'woocommerce-shipment-tracking' ),
            'type'				=> 'text',
            'description'		=> __( 'Define your own shipment message. Use the place holder tags [ID], [SERVICE] and [DATE] for Shipment Id, Shipment Service and Shipment Date respectively. Leave it empty for default message.<br>', 'woocommerce-shipment-tracking' ),
            'css'				=> 'width:900px',
            //'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_MESSAGE_KEY,
            'placeholder'		=> 'Your order was shipped on [DATE] via [SERVICE]. To track shipment, please follow the link of shipment ID(s) [ID]',
            'desc_tip'           => true
        ),
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'wf-shipping-dhl' ),
		'label'           => __( 'Enable debug mode', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'wf-shipping-dhl' )
	),

    'advanced_settings'	=> array(
		'title'           => __( 'Advanced Settings', 'wf-shipping-dhl' ),
		'type'            => 'title',
		'class'           => 'wf_advanced_settings_title wf_show_tab',
    ),

    'label_contents_text'           => array(
		'title'           => __( 'Contents description', 'wf-shipping-dhl' ),
		'type'            => 'text',
    	'desc_tip'    => true,
		'default'         => '',
        'placeholder'		=> 'Shipment contents description',
		'description'     => 'Provide here a description about shipment contents.'
    ),	
    
    'default_domestic_service'   => array(
    	'title'           => __( 'Default service for domestic', 'wf-shipping-dhl' ),
    	'desc_tip'    => true,
    	'type'            => 'select',
    	'description'     => 'Please select one if you need to set a default service for domestic shipment',
    	'options'         => array_merge(
			array( ''=> 'none'),
			$this->services
		)
    ),
    'default_international_service'   => array(
    	'title'           => __( 'Default service for international', 'wf-shipping-dhl' ),
    	'desc_tip'    => true,
    	'type'            => 'select',
    	'description'     => 'Please select one if you need to set a default service for international shipment',
		'default'         => 'none',
    	'options'         => array_merge(
			array( ''=> 'none'),
			$this->services
    	)
    ),
);