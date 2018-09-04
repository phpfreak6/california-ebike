<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_dhl_paket_woocommerce_shipping_method extends WC_Shipping_Method {
	private $found_rates;
	private $services;

	public function __construct() {
		$this->id                               = WF_DHL_PAKET_ID;
		$this->method_title                     = __( 'DHL Paket', 'wf-shipping-dhl' );
		$this->method_description               = __( 'Print shipping labels via DHL Shipping API.', 'wf-shipping-dhl' );
		$this->services                         = include( 'data-wf-service-codes.php' );
		$this->init();
	}

	private function init() {
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title           = $this->get_option( 'title', $this->method_title );
		$this->origin          = apply_filters( 'woocommerce_dhl_origin_postal_code', str_replace( ' ', '', strtoupper( $this->get_option( 'origin' ) ) ) );
		$this->origin_country  = apply_filters( 'woocommerce_dhl_origin_country_code', 'DE' );
		$this->account_number  = $this->get_option( 'account_number' );
		$this->site_id         = $this->get_option( 'site_id' );
		$this->site_password        = $this->get_option( 'site_password' );
		$this->freight_shipper_city  = $this->get_option( 'freight_shipper_city' );
		
		$_stagingUrl = 'https://cig.dhl.de/services/sandbox/soap';
		$_productionUrl = 'https://cig.dhl.de/services/production/soap';
	
		$this->production      = (!empty($this->settings[ 'production' ]) && $this->settings[ 'production' ] == 'yes') ? true : false;
		$this->service_url     = ($this->production == true) ? $_productionUrl  : $_stagingUrl ;
		
		
		$this->debug           = ( $bool = $this->get_option( 'debug' ) ) && $bool == 'yes' ? true : false;
		$this->insure_contents = ( $bool = $this->get_option( 'insure_contents' ) ) && $bool == 'yes' ? true : false;
		$this->request_type    = $this->get_option( 'request_type', 'LIST' );
		$this->packing_method  = $this->get_option( 'packing_method', 'per_item' );
		$this->boxes           = $this->get_option( 'boxes');
		$this->custom_services = $this->get_option( 'services', array( ));
		$this->offer_rates     = $this->get_option( 'offer_rates', 'all' );
		
		$this->dutypayment_type = $this->get_option('dutypayment_type', '' );
		$this->dutyaccount_number = $this->get_option('dutyaccount_number', '' );
		
		$this->dimension_unit = $this->get_option('dimension_weight_unit') == 'LBS_IN' ? 'IN' : 'CM';
		$this->weight_unit = $this->get_option('dimension_weight_unit') == 'LBS_IN' ? 'LBS' : 'KG';
		
		$this->quoteapi_dimension_unit = $this->dimension_unit;
		$this->quoteapi_weight_unit = $this->weight_unit == 'LBS' ? 'LB' : 'KG';
		
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			wc_add_notice( $message, $type );
		}
	}

	private function environment_check() {
			
		if ( ! $this->origin && $this->enabled == 'yes' ) {
			echo '<div class="error">
				<p>' . __( 'DHL is enabled, but the origin postcode has not been set.', 'wf-shipping-dhl' ) . '</p>
			</div>';
		}
	}

	public function admin_options() {
		// Check users environment supports this method
		$this->environment_check();

		// Show settings
		parent::admin_options();
	}

	public function init_form_fields() {
		$this->form_fields  = include( 'data-wf-settings.php' );
	}

	public function generate_activate_box_html() {
	    ob_start();
	    $plugin_name = 'dhl';
	   include( WF_DHL_PAKET_EXPRESS_ROOT_PATH . 'wf_api_manager/html/html-wf-activation-window.php' );
	    return ob_get_clean();
	}

	public function generate_services_html() {
		ob_start();
		include( 'html-wf-services.php' );
		return ob_get_clean();
	}

	public function generate_box_packing_html() {
		ob_start();
		include( 'html-wf-box-packing.php' );
		return ob_get_clean();
	}

	public function validate_box_packing_field( $key ) {
		$boxes_id     = isset( $_POST['boxes_id'] ) ? $_POST['boxes_id'] : array();
		$boxes_name     = isset( $_POST['boxes_name'] ) ? $_POST['boxes_name'] : array();
		$boxes_length     = isset( $_POST['boxes_length'] ) ? $_POST['boxes_length'] : array();
		$boxes_width      = isset( $_POST['boxes_width'] ) ? $_POST['boxes_width'] : array();
		$boxes_height     = isset( $_POST['boxes_height'] ) ? $_POST['boxes_height'] : array();
		$boxes_box_weight = isset( $_POST['boxes_box_weight'] ) ? $_POST['boxes_box_weight'] : array();
		$boxes_max_weight = isset( $_POST['boxes_max_weight'] ) ? $_POST['boxes_max_weight'] :  array();
		$boxes_enabled    = isset( $_POST['boxes_enabled'] ) ? $_POST['boxes_enabled'] : array();

		$boxes = array();

		if ( ! empty( $boxes_length ) && sizeof( $boxes_length ) > 0 ) {
			for ( $i = 0; $i <= max( array_keys( $boxes_length ) ); $i ++ ) {

				if ( ! isset( $boxes_length[ $i ] ) )
					continue;

				if ( $boxes_length[ $i ] && $boxes_width[ $i ] && $boxes_height[ $i ] ) {

					$boxes[] = array(
						'id'     => $boxes_id[ $i ] ,
						'name'     => $boxes_name[ $i ] ,
						'length'     => floatval( $boxes_length[ $i ] ),
						'width'      => floatval( $boxes_width[ $i ] ),
						'height'     => floatval( $boxes_height[ $i ] ),
						'box_weight' => floatval( $boxes_box_weight[ $i ] ),
						'max_weight' => floatval( $boxes_max_weight[ $i ] ),
						'enabled'    => isset( $boxes_enabled[ $i ] ) ? true : false
					);
				}
			}
		}
		return $boxes;
	}

	public function validate_services_field( $key ) {
		$services         = array();
		$posted_services  = $_POST['dhl_service'];

		foreach ( $posted_services as $code => $settings ) {
			$services[ $code ] = array(
				'name'               => wc_clean( $settings['name'] ),
				'order'              => wc_clean( $settings['order'] ),
				'enabled'            => isset( $settings['enabled'] ) ? true : false,
				//'adjustment'         => woocommerce_clean( $settings['adjustment'] ),
				//'adjustment_percent' => str_replace( '%', '', woocommerce_clean( $settings['adjustment_percent'] ))
			);
		}

		return $services;
	}

	public function get_dhl_packages( $package ) {
		switch ( $this->packing_method ) {
			case 'box_packing' :
				return $this->box_shipping( $package );
			break;
			case 'per_item' :
			default :
				return $this->per_item_shipping( $package );
			break;
		}
	}

	private function per_item_shipping( $package ) {
		$to_ship  = array();
		$group_id = 1;

		// Get weight of order
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product # is missing weight. Aborting.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				return;
			}

			$group = array();

			$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => max( '0.5', round( wc_get_weight( $values['data']->get_weight(), $this->weight_unit ), 2 ) ),
					'Units' => $this->weight_unit
				),
				'packed_products' => array( $values['data'] )
			);

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height( $values['data'] ) && wf_get_product_width( $values['data'] ) ) {

				$dimensions = array( wf_get_product_length( $values['data'] ), wf_get_product_width( $values['data'] ), wf_get_product_height( $values['data'] ) );

				sort( $dimensions );

				$group['Dimensions'] = array(
					'Length' => max( 1, round( wc_get_dimension( $dimensions[2], $this->dimension_unit ), 0 ) ),
					'Width'  => max( 1, round( wc_get_dimension( $dimensions[1], $this->dimension_unit ), 0 ) ),
					'Height' => max( 1, round( wc_get_dimension( $dimensions[0], $this->dimension_unit ), 0 ) ),
					'Units'  => $this->dimension_unit
				);
			}

			$group['InsuredValue'] = array(
				'Amount'   => round( $values['data']->get_price() ),
				'Currency' => get_woocommerce_currency()
			);
			
			for ( $i = 0; $i < $values['quantity']; $i++)
				$to_ship[] = $group;
			
			$group_id++;
		}

		return $to_ship;
	}

	private function box_shipping( $package ) {
		if ( ! class_exists( 'WF_Boxpack' ) ) {
			include_once 'class-wf-packing.php';
		}

		$boxpack = new WF_Boxpack();

		// Define boxes
		foreach ( $this->boxes as $key => $box ) {
			if ( ! $box['enabled'] ) {
				continue;
			}

			$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'], $box['box_weight'] );

			if ( isset( $box['id'] ) ) {
				$newbox->set_id( current( explode( ':', $box['id'] ) ) );
			}

			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
		}

		// Add items
		foreach ( $package['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				continue;
			}

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height( $values['data'] ) && wf_get_product_width( $values['data'] ) && wf_get_product_weight( $values['data'] ) ) {

				$dimensions = array( wf_get_product_length( $values['data'] ), wf_get_product_height( $values['data'] ), wf_get_product_width( $values['data'] ) );

				for ( $i = 0; $i < $values['quantity']; $i ++ ) {
					$boxpack->add_item(
						wc_get_dimension( $dimensions[2], $this->dimension_unit ),
						wc_get_dimension( $dimensions[1], $this->dimension_unit ),
						wc_get_dimension( $dimensions[0], $this->dimension_unit ),
						wc_get_weight( $values['data']->get_weight(), $this->weight_unit),
						$values['data']->get_price(),
						array(
							'data' => $values['data']
						)
					);
				}

			} else {
				$this->debug( sprintf( __( 'Product #%s is missing dimensions. Aborting.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				return;
			}
		}

		// Pack it
		$boxpack->pack();
		$packages = $boxpack->get_packages();
		$to_ship  = array();
		$group_id = 1;

		foreach ( $packages as $package ) {
			if ( $package->unpacked === true ) {
				$this->debug( 'Unpacked Item' );
			} else {
				$this->debug( 'Packed ' . $package->id );
			}

			$dimensions = array( $package->length, $package->width, $package->height );

			sort( $dimensions );

			$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => max( '0.5', round( $package->weight, 2 ) ),
					'Units' => $this->weight_unit
				),
				'Dimensions'        => array(
					'Length' => max( 1, round( $dimensions[2], 0 ) ),
					'Width'  => max( 1, round( $dimensions[1], 0 ) ),
					'Height' => max( 1, round( $dimensions[0], 0 ) ),
					'Units'  => $this->dimension_unit
				),
				'InsuredValue'      => array(
					'Amount'   => round( $package->value ),
					'Currency' => get_woocommerce_currency()
				),
				'packed_products' => array(),
				'package_id'      => $package->id
			);

			if ( ! empty( $package->packed ) && is_array( $package->packed ) ) {
				foreach ( $package->packed as $packed ) {
					$group['packed_products'][] = $packed->get_meta( 'data' );
				}
			}

			$to_ship[] = $group;

			$group_id++;
		}

		return $to_ship;
	}

	private function get_dhl_requests( $dhl_packages, $package) {

		$mailing_date = date('Y-m-d');		
		$mailing_datetime = date('Y-m-d') . 'T'. date('H:i:s');
		$destination_postcode = str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ));
		$pieces = $this->wf_get_package_piece($dhl_packages);
		$fetch_accountrates = $this->request_type == "ACCOUNT" ? "<PaymentAccountNumber>".$this->account_number."</PaymentAccountNumber>" : "";
		
		$total_value = $this->wf_get_package_total_value($dhl_packages);
		$currency = get_woocommerce_currency();
			
		$insurance_details =  $this->insure_contents ? "<InsuredValue>{$total_value}</InsuredValue><InsuredCurrency >{$currency}</InsuredCurrency>" : "";
		
		$is_dutiable = ($package['destination']['country'] == 'DE' || wf_dhl_paket_is_eu_country( 'DE', $package['destination']['country'])) ? "N" : "Y"; 
		$dutiable_content = $is_dutiable == "Y" ? "<Dutiable><DeclaredCurrency>{$currency}</DeclaredCurrency><DeclaredValue>{$total_value}</DeclaredValue></Dutiable>" : "";
		
		$destination_city = strtoupper( $package['destination']['city'] );
		
		$origin_postcode_city =  $this->wf_get_postcode_city($this->origin_country,$this->freight_shipper_city,$this->origin) ;
		$destination_postcode_city =  $this->wf_get_postcode_city($package['destination']['country'],$destination_city,$destination_postcode);
			
$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetQuote>
    <Request>
		<ServiceHeader>
			<MessageTime>{$mailing_datetime}</MessageTime>
			<MessageReference>1234567890123456789012345678901</MessageReference>
			<SiteID>{$this->site_id}</SiteID>
			<Password>{$this->site_password}</Password>
		</ServiceHeader>
    </Request>
    <From>
	  <CountryCode>{$this->origin_country}</CountryCode>
	  {$origin_postcode_city}
    </From>
    <BkgDetails>
      <PaymentCountryCode>{$this->origin_country}</PaymentCountryCode>
      <Date>{$mailing_date}</Date>
      <ReadyTime>PT10H21M</ReadyTime>
      <DimensionUnit>{$this->quoteapi_dimension_unit}</DimensionUnit>
      <WeightUnit>{$this->quoteapi_weight_unit}</WeightUnit>
      <Pieces>
		{$pieces}
	  </Pieces>
	  {$fetch_accountrates}
	  <IsDutiable>{$is_dutiable}</IsDutiable>
	  <NetworkTypeCode>AL</NetworkTypeCode>
	  {$insurance_details}
	  </BkgDetails>
    <To>
      <CountryCode>{$package['destination']['country']}</CountryCode>
	  {$destination_postcode_city}
    </To>
	{$dutiable_content}
  </GetQuote>
</p:DCTRequest>
XML;

		return $xmlRequest;
	}
	
	private function wf_get_package_piece($dhl_packages){
		$pieces = "";
		if ( $dhl_packages ) {
			foreach ( $dhl_packages as $key => $parcel ) {
				$index = $key + 1;
				$pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
				$pieces .= '<PackageTypeCode>BOX</PackageTypeCode>';
				$pieces .= '<Height>' . $parcel['Dimensions']['Height'] . '</Height>';
				$pieces .= '<Depth>' . $parcel['Dimensions']['Length'] . '</Depth>';
				$pieces .= '<Width>' . $parcel['Dimensions']['Width'] . '</Width>';
				$pieces .= '<Weight>' . $parcel['Weight']['Value'] . '</Weight></Piece>';
			}			
		}
		return $pieces;
		
	}
	
	private function wf_get_postcode_city($country, $city, $postcode){
		$no_postcode_country = array('AE','AF','AG','AI','AL','AN','AO','AW','BB','BF','BH','BI','BJ','BM','BO','BS','BT','BW','BZ','CD','CF','CG','CI','CK',
		'CL','CM','CO','CR','CV','DJ','DM','DO','EC','EG','ER','ET','FJ','FK','GA','GD','GH','GI','GM','GN','GQ','GT','GW','GY','HK','HN','HT','IE','IQ','IR',
		'JM','JO','KE','KH','KI','KM','KN','KP','KW','KY','LA','LB','LC','LK','LR','LS','LY','ML','MM','MO','MR','MS','MT','MU','MW','MZ','NA','NE','NG','NI',
		'NP','NR','NU','OM','PA','PE','PF','PY','QA','RW','SA','SB','SC','SD','SL','SN','SO','SR','SS','ST','SV','SY','TC','TD','TG','TL','TO','TT','TV','TZ',
		'UG','UY','VC','VE','VG','VN','VU','WS','XA','XB','XC','XE','XL','XM','XN','XS','YE','ZM','ZW');
		
		return in_array($country,$no_postcode_country) && !empty($city) ? "<City>{$city}</City>" : "<Postalcode>{$postcode}</Postalcode>";
	}
	
	private function wf_get_package_total_value($dhl_packages){
		$total_value = 0;
		if ( $dhl_packages ) {
			foreach ( $dhl_packages as $key => $parcel ) {
				$total_value   += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];				
			}			
		}
		return $total_value;
	}
	public function calculate_shipping( $package=array() ) {
		// Clear rates
		$this->found_rates = array();

		// Debugging
		$this->debug( __( 'dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl' ) );
		
	}

	public function run_package_request( $requests ) {
		try {
			$this->process_result( $this->get_result( $requests ) , $requests);			
		} catch ( Exception $e ) {
			$this->debug( print_r( $e, true ), 'error' );
			return false;
		}
	}

	private function get_result( $request ) {
		$this->debug( 'DHL REQUEST: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r( htmlspecialchars($request,ENT_SUBSTITUTE), true ) . '</pre>' );
		
		$result = wp_remote_post( $this->service_url,
						array(
							'method'           => 'POST',
							'timeout'          => 70,
							'sslverify'        => 0,
							//'headers'          => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
							'body'             => $request
						)
					);
	
		$this->debug( 'DHL RESPONSE: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r( $result, true ),ENT_SUBSTITUTE) . '</pre>' );

		wc_enqueue_js( "
			jQuery('a.debug_reveal').on('click', function(){
				jQuery(this).closest('div').find('.debug_info').slideDown();
				jQuery(this).remove();
				return false;
			});
			jQuery('pre.debug_info').hide();
		" );

		if ( is_array($result) && ! empty( $result['body'] ) ) {
			$result = $result['body'];
		} else {
			$result = '';
		}  

		libxml_use_internal_errors(true);
		$xml = simplexml_load_string( $result);
		$shipmentErrorMessage  = "";
		if ($xml) {
			return $xml; 
		} else {
			return null;			
		}		
	}

	private function wf_get_cost_based_on_currency($qtdsinadcur, $default_charge){
		if(!empty($qtdsinadcur)){
			foreach($qtdsinadcur as $multiple_currencies){
				if((string)$multiple_currencies->CurrencyCode == get_woocommerce_currency() && !empty($multiple_currencies->TotalAmount))
					return $multiple_currencies->TotalAmount;
			}				
		}
		return $default_charge;
	}
	
	private function process_result( $result = '' ) {
		if ( $result && ! empty ( $result->GetQuoteResponse->BkgDetails->QtdShp) ) {
			foreach ( $result->GetQuoteResponse->BkgDetails->QtdShp as $quote ) {
				if($quote->POfferedCustAgreement == 'N'){	
					if((string)$quote->CurrencyCode == get_woocommerce_currency()){
						$rate_cost = floatval( (string)$quote->ShippingCharge );
					}else{
						$rate_cost = floatval( (string)$this->wf_get_cost_based_on_currency($quote->QtdSInAdCur, $quote->ShippingCharge));	
					}
					
					$rate_code = strval( (string)$quote->GlobalProductCode );
					$rate_id   = $this->id . ':' . $rate_code;
					$rate_name = strval( (string)$quote->ProductShortName );
					$this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost );
				}
			}
		}
	}
	
	private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost ) {

		// Name adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['name'] ) ) {
			$rate_name = $this->custom_services[ $rate_code ]['name'];
		}

		// Cost adjustment %
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment_percent'] ) ) {
			$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $this->custom_services[ $rate_code ]['adjustment_percent'] ) / 100 ) );
		}
		// Cost adjustment
		if ( ! empty( $this->custom_services[ $rate_code ]['adjustment'] ) ) {
			$rate_cost = $rate_cost + floatval( $this->custom_services[ $rate_code ]['adjustment'] );
		}

		// Enabled check
		if ( isset( $this->custom_services[ $rate_code ] ) && empty( $this->custom_services[ $rate_code ]['enabled'] ) ) {
			return;
		}

		// Merging
		if ( isset( $this->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->found_rates[ $rate_id ]['packages'];
		} else {
			$packages  = 1;
		}

		// Sort
		if ( isset( $this->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->custom_services[ $rate_code ]['order'];
		} else {
			$sort = 999;
		}

		$this->found_rates[ $rate_id ] = array(
			'id'       => $rate_id,
			'label'    => $rate_name,
			'cost'     => $rate_cost,
			'sort'     => $sort,
			'packages' => $packages
		);
	}

	public function add_found_rates() {
		if ( $this->found_rates ) {

			if ( $this->offer_rates == 'all' ) {

				uasort( $this->found_rates, array( $this, 'sort_rates' ) );

				foreach ( $this->found_rates as $key => $rate ) {
					$this->add_rate( $rate );
				}
			} else {
				$cheapest_rate = '';

				foreach ( $this->found_rates as $key => $rate ) {
					if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
						$cheapest_rate = $rate;
					}
				}

				$cheapest_rate['label'] = $this->title;

				$this->add_rate( $cheapest_rate );
			}
		}
	}

	public function sort_rates( $a, $b ) {
		if ( $a['sort'] == $b['sort'] ) return 0;
		return ( $a['sort'] < $b['sort'] ) ? -1 : 1;
	}
}
