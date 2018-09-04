<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Array of settings
 */
return array(
   'licence'  => array(
		'type'            => 'activate_box'
	),
	'enabled'          => array(
		'title'           => __( 'Enable DHL Paket', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable this shipping method', 'wf-shipping-dhl' ),
		'default'         => 'no'
	),
	/*
	'title'            => array(
		'title'           => __( 'Method Title', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'wf-shipping-dhl' ),
		'default'         => __( 'DHL', 'wf-shipping-dhl' ),
		'desc_tip'        => true
	), */
	'account_number'           => array(
		'title'           => __( 'Account Number', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => '22222222220101'
    ),
	'return_account_number'           => array(
		'title'           => __( 'Return Billing Account Number', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Leave it blank if return label is not needed', 'wf-shipping-dhl' ),
		'default'         => '22222222220701'
    ),
    'production'      => array(
		'title'           => __( 'Production Key', 'wf-shipping-dhl' ),
		'label'           => __( 'This is a production key', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'If this is a production API key and not a developer key, check this box.', 'wf-shipping-dhl' )
	),
    'site_id'           => array(
		'title'           => __( 'Site ID', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'daz',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'site_password'           => array(
		'title'           => __( 'Site Password', 'wf-shipping-dhl' ),
		'type'            => 'password',
		'description'     => __( 'contact DHL.', 'wf-shipping-dhl' ),
		'default'         => 'Forum@007',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
	'api_user'           => array(
		'title'           => __( 'API User', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Get API User from DHL and place here', 'wf-shipping-dhl' ),
		'default'         => '2222222222_01',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
	'api_key'           => array(
		'title'           => __( 'API key', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Secret key for API.', 'wf-shipping-dhl' ),
		'default'         => 'pass',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),/*
	'region_code'   => array(
		'title'           => __( 'Region Code', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'AM',
		'options'         => array(
			'AP'       => __( 'AP-EM Region: Supports countries in Asia, Africa, Australia and Pacific', 'wf-shipping-dhl' ),
			'EU'       => __( 'EU Region: Supports countries in Europe', 'wf-shipping-dhl' ),
			'AM'       => __( 'AM Region: Supports USA and other countries in North and South Americas', 'wf-shipping-dhl' )
		),
		'description'     => __( 'Choose appropriate Region Code based on the country of origin', 'wf-shipping-dhl' ),
	),*/
    'custom_message'		=> array(
		'title'				=> __( 'Custom Shipment Message', 'wf-shipping-dhl' ),
		'type'				=> 'text',
		'description'		=> __( 'Define your own shipment message. Use the place holder tags [ID], [SERVICE] and [DATE] for Shipment Id, Shipment Service and Shipment Date respectively. Leave it empty for default message.<br>', 'wf-shipping-dhl' ),
		'css'				=> 'width:900px',
		'placeholder'		=> 'Your order was shipped on [DATE] via [SERVICE]. To track shipment, please follow the link of shipment ID(s) [ID]'
	),
	'dimension_weight_unit' => array(
		'title'           => __( 'Dimension/Weight Unit', 'wf-shipping-dhl' ),
		'label'           => __( 'This unit will be passed to DHL.', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'LBS_IN',
		'description'     => __('Product dimensions and weight will be converted to the selected unit and will be passed to DHL. Please change the box dimensions and weight accordingly as its preloaded with unit Pound and Inches.', 'wf-shipping-dhl'),
		'options'         => array(
			'LBS_IN'	=> __( 'Pounds & Inches', 'wf-shipping-dhl'),
			'KG_CM' 	=> __( 'Kilograms & Centimetres', 'wf-shipping-dhl')			
		)
	),		
	'packing_method'   => array(
		'title'           => __( 'Parcel Packing Method', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'per_item',
		'class'           => 'packing_method',
		'options'         => array(
			'per_item'       => __( 'Default: Pack items individually', 'wf-shipping-dhl' ),
			'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'wf-shipping-dhl' ),
			'weight_based'=> __( 'Weight based: Calculate shipping on the basis of order total weight', 'wf-shipping-dhl' ),
		),
		'description'     => __( 'Determine how items are packed before being sent to DHL.', 'wf-shipping-dhl' ),
	),
	'box_max_weight'           => array(
		'title'           => __( 'Max Package Weight', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => '10',
		'class'           => 'weight_based_option',
		'desc_tip'    => true,
		'description'     => __( 'Maximum weight allowed for single box.', 'wf-shipping-dhl' ),
	),
	'weight_packing_process'   => array(
		'title'           => __( 'Packing Process', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'pack_descending',
		'class'           => 'weight_based_option',
		'options'         => array(
			'pack_descending'       => __( 'Pack heavier items first', 'wf-shipping-dhl' ),
			'pack_ascending'    	=> __( 'Pack lighter items first.', 'wf-shipping-dhl' ),
			'pack_simple'    		=> __( 'Pack purely divided by weight.', 'wf-shipping-dhl' ),
		),
		'desc_tip'    => true,
		'description'     => __( 'Select your packing order.', 'wf-shipping-dhl' ),
	),
	'boxes'  => array(
		'type'            => 'box_packing',
		'default'       => include( 'data-wf-box-sizes.php' )
	),
/*
    'insure_contents'      => array(
		'title'       => __( 'Insurance', 'wf-shipping-dhl' ),
		'label'       => __( 'Enable Insurance', 'wf-shipping-dhl' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'desc_tip'    => true,
		'description' => __( 'Sends the package value to DHL for insurance.', 'wf-shipping-dhl' ),
	),
	'request_type'     => array(
		'title'           => __( 'Request Type', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => 'LIST',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'LIST'        => __( 'List rates', 'wf-shipping-dhl' ),
			'ACCOUNT'     => __( 'Account rates', 'wf-shipping-dhl' ),
		),
		'description'     => __( 'Choose whether to return List or Account (discounted) rates from the API.', 'wf-shipping-dhl' )
	),
	'dutypayment_type'     => array(
		'title'           => __( 'Duty Tax Payment Type', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			''      => __( 'None', 'wf-shipping-dhl' ),
			'S'     => __( 'Shipper', 'wf-shipping-dhl' ),
			'R'     => __( 'Recipient', 'wf-shipping-dhl' ),
			'T'     => __( 'Third Party/Other', 'wf-shipping-dhl' ),
		),
		'description'     => __( 'Duty and tax charge payment type. It is required for non-doc or dutiable products.', 'wf-shipping-dhl' )
	),
	'dutyaccount_number'           => array(
		'title'           => __( 'Duty Account Number', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Duty Billing account number. Required if the DutyPaymentType is T', 'wf-shipping-dhl' ),
		'default'         => ''
    ),	
	'offer_rates'   => array(
		'title'           => __( 'Offer Rates', 'wf-shipping-dhl' ),
		'type'            => 'select',
		'description'     => '',
		'default'         => 'all',
		'options'         => array(
		    'all'         => __( 'Offer the customer all returned rates', 'wf-shipping-dhl' ),
		    'cheapest'    => __( 'Offer the customer the cheapest rate only, anonymously', 'wf-shipping-dhl' ),
		),
    ),*/
	/*
	'services'  => array(
		'type'            => 'services'
	),*/
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'description'     => __( 'Enter postcode for the <strong>Shipper</strong>.', 'wf-shipping-dhl' ),
		'default'         => '90403'
    ),
	'shipper_person_name'           => array(
			'title'           => __( 'Shipper Person Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Mr Shipper',
			'description'     => __('Required for label Printing', 'wf-shipping-dhl' )
	),	
	'shipper_company_name'           => array(
			'title'           => __( 'Shipper Company Name', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'Company Name'	,
			'description'     => __( 'Required for label Printing', 'wf-shipping-dhl' )
	),	
	'shipper_phone_number'           => array(
			'title'           => __( 'Shipper Phone Number', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => '1 234 1234567'	,
			'description'     => __( 'Required for label Printing', 'wf-shipping-dhl' )
    ),
	'shipper_email'           => array(
			'title'           => __( 'Shipper Email', 'wf-shipping-dhl' ),
			'type'            => 'text',
			'default'         => 'test@test.com'	,
			'description'     => __('Required for label Printing', 'wf-shipping-dhl' )
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'Am Hallertor',
		'description'     => __('Required for label Printing', 'wf-shipping-dhl' )
    ),
    'shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Number (House Number)', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => '3',
		'description'     => __('Required for label Printing', 'wf-shipping-dhl' )
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'Bavaria',
		'description'     => __('Required for label Printing', 'wf-shipping-dhl' )
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'Nürnberg',
		'description'     => __('Name of state. Field length must be less than or equal to 9.', 'wf-shipping-dhl' )
    ),
	'export_doc_terms_of_trade'           => array(
		'title'           => __( 'Export Terms Of Trade', 'wf-shipping-dhl' ),
		'type'            => 'text',
		'default'         => 'DDU',
		'description'     => __('terms of trades, i.e. incoterms codes like DDU, CIP et al. Field length must be = 3', 'wf-shipping-dhl' )
    ),
	'export_doc_desc'           => array(
		'title'           => __( 'Export Item Description', 'wf-shipping-dhl' ),
		'type'            => 'textarea',
		'default'         => 'Goods',
		'description'     => __('Description text for the document', 'wf-shipping-dhl' )
    ),/*
	'add_trackingpin_shipmentid' => array(
			'title'           => __( 'Tracking PIN', 'wf-shipping-dhl' ),
			'label'           => __( 'Add Tracking PIN to customer order notes', 'wf-shipping-dhl' ),
			'type'            => 'checkbox',
			'default'         => 'no',
			'description'     => ''
		),*/
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'wf-shipping-dhl' ),
		'label'           => __( 'Enable debug mode', 'wf-shipping-dhl' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'wf-shipping-dhl' )
	),
	'advanced_settings'   => array(
		'title'           => __( 'Advanced Settings', 'wf-shipping-dhl' ),
		'type'            => 'title',
		'class'			  => 'woocommerce_wf_dhl_paket_shipping_advanced_settings'
	),
	'europaket_enabled'      => array(
		'title'           => __( 'Euro Paket', 'wf-shipping-dhl' ),
		'label'           => __( 'Enable', 'wf-shipping-dhl' ),
		'description'     => __( 'Check this if you have euro paket enabled in your account', 'wf-shipping-dhl' ),
		'desc_tip'           => true,
		'type'            => 'checkbox',
		'default'         => 'no'
	),
	'packstation_enabled'      => array(
		'title'           => __( 'Packstation', 'wf-shipping-dhl' ),
		'label'           => __( 'Enable', 'wf-shipping-dhl' ),
		'description'     => __( 'This will allow users to select packstations during checkout.', 'wf-shipping-dhl' ),
		'desc_tip'           => true,
		'type'            => 'checkbox',
		'default'         => 'no'
	),
);
