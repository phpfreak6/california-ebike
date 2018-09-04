<?php

if (!defined('ABSPATH')) {
	exit;
}
class wf_dhl_ecommerce_shipping_admin_helper {

	private $service_code;
	private $accesstoken;
	private $mail_type;
	private $expected_delivery;

	public function __construct() {
		$this->id = WF_DHL_ECOMMERCE_ID;
		$this->init();
	}

	private function init() {
		$this->settings = get_option('woocommerce_' . WF_DHL_ECOMMERCE_ID . '_settings', null);

		$this->add_trackingpin_shipmentid = $this->settings['add_trackingpin_shipmentid'];


		$this->origin = str_replace(' ', '', strtoupper($this->settings['origin']));
		$this->origin_country = WC()->countries->get_base_country();
		$this->account_number = $this->settings['account_number'];

		$this->site_id = $this->settings['site_id'];
		$this->site_password = $this->settings['site_password'];
		$this->client_id = $this->settings['client_id'];
		$this->region_code = '';

		$this->facility_code = $this->settings['facility_code'];


		$_stagingUrl = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';
		$_productionUrl = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';

		$this->production = false;
		$this->plt = false;
		$this->service_url = ($this->production == true) ? $_productionUrl : $_stagingUrl;
		
		$this->debug = ( $bool = $this->settings['debug'] ) && $bool == 'yes' && !isset($_REQUEST['post']) ? true : false; //$__REQUEST['post'] to confirm its not coming from bulk action. Bulk action needs to forcefully turn off debug
		
		$this->insure_contents = false;
		$this->request_type = '';
		$this->packing_method ='';
		$this->boxes = $this->settings['boxes'];
		$this->custom_services = $this->settings['services'];
		$this->offer_rates = '';

		$this->freight_shipper_person_name	= $this->settings['shipper_person_name'];
		$this->freight_shipper_company_name   = $this->settings['shipper_company_name'];
		$this->freight_shipper_phone_number   = $this->settings['shipper_phone_number'];
		$this->shipper_email				  = $this->settings['shipper_email'];

		$this->freight_shipper_street	 = $this->settings['freight_shipper_street'];
		$this->freight_shipper_street_2   = $this->settings['shipper_street_2'];
		$this->freight_shipper_city	   = $this->settings['freight_shipper_city'];
		$this->freight_shipper_state	  = $this->settings['freight_shipper_state'];

		$this->output_format  = $this->settings['output_format'];
		$this->image_type	 = $this->settings['image_type'];

		$this->dutypayment_type   = isset($this->settings['dutypayment_type']) ? $this->settings['dutypayment_type'] : '';
		$this->dutyaccount_number = isset($this->settings['dutyaccount_number']) ? $this->settings['dutyaccount_number'] : '';

		$this->dimension_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'IN' : 'CM';
		$this->weight_unit	= isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'LBS' : 'KG';

		$this->labelapi_weight_unit	   = $this->weight_unit == 'LBS' ? 'LB' : 'KG';
		
		$this->conversion_rate = !empty($this->settings['conversion_rate']) ? $this->settings['conversion_rate'] : '';

		//Time zone adjustment, which was configured in minutes to avoid time diff with server. Convert that in seconds to apply in date() functions.
		$this->timezone_offset = !empty($this->settings['timezone_offset']) ? intval($this->settings['timezone_offset']) * 60 : 0;

		$this->ship_from_address	=   $this->settings['ship_from_address'];
	
		$this->label_contents_text = !empty($this->settings['label_contents_text']) ? $this->settings['label_contents_text'] : 'Shipment contents';

		$this->weight_packing_process = !empty($this->settings['weight_packing_process']) ? $this->settings['weight_packing_process'] : 'pack_descending';
		$this->box_max_weight		 = !empty($this->settings['box_max_weight']) ? $this->settings['box_max_weight'] : '';
		$this->non_plt_commercial_invoice ='';

	}

	public function debug($message, $type = 'notice') {
		if ($this->debug) {
			echo( $message);
		}
	}

	public function get_dhl_packages($package) {
		switch ($this->packing_method) {
			case 'box_packing' :
				return $this->box_shipping($package);
				break;
			case 'weight_based' :
				return $this->weight_based_shipping($package);
				break;
			case 'per_item' :
			default :
				return $this->per_item_shipping($package);
				break;
		}
	}

	private function per_item_shipping($package) {
		$to_ship = array();
		$group_id = 1;

		// Get weight of order
		foreach ($package['contents'] as $item_id => $values) {

			if (!$values['data']->needs_shipping()) {
				$this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
				continue;
			}

			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

			if (!$values['data']->get_weight()) {
				$this->debug(sprintf(__('Product # is missing weight. Aborting.', 'wf-shipping-dhl'), $item_id), 'error');
				return;
			}

			$group = array();
			$insurance_array = array(
				'Amount' => round($values['data']->get_price()),
				'Currency' => get_woocommerce_currency()
			);
			if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
				$crate = 1 / $this->conversion_rate;
				$insurance_array['Amount'] = round($values['data']->get_price() * $crate, 2);
				$insurance_array['Currency'] = $this->settings['dhl_currency_type'];
			}
			$group = array(
				'GroupNumber' => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => round(wc_get_weight($values['data']->get_weight(), $this->weight_unit), 3),
					'Units' => $this->weight_unit
				),
				'packed_products' => array($values['data'])
			);

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height( $values['data'] ) && wf_get_product_width( $values['data'] )) {

				$dimensions = array( wf_get_product_length( $values['data'] ), wf_get_product_width( $values['data'] ), wf_get_product_height($values['data']) );

				sort($dimensions);

				$group['Dimensions'] = array(
					'Length' => max(1, round(wc_get_dimension($dimensions[2], $this->dimension_unit), 0)),
					'Width' => max(1, round(wc_get_dimension($dimensions[1], $this->dimension_unit), 0)),
					'Height' => max(1, round(wc_get_dimension($dimensions[0], $this->dimension_unit), 0)),
					'Units' => $this->dimension_unit
				);
			}
			$group['InsuredValue'] = $insurance_array;
			$group['packtype'] = isset($this->settings['shp_pack_type'])?$this->settings['shp_pack_type'] : 'OD';
			for ($loop = 0; $loop < $values['quantity']; $loop++) {
				$to_ship[] = $group;
			}
			$group_id++;
		}
		return $to_ship;
	}

	/**
	 * weight_based_shipping function.
	 *
	 * @access private
	 * @param mixed $package
	 * @return void
	 */
	private function weight_based_shipping($package) {
		global $woocommerce;
		if ( ! class_exists( 'WeightPack' ) ) {
			include_once 'weight_pack/class-wf-weight-packing.php';
		}
		$weight_pack=new WeightPack($this->weight_packing_process);
		$weight_pack->set_max_weight($this->box_max_weight);
		
		$package_total_weight = 0;
		$insured_value = 0;
		
		
		$ctr = 0;
		foreach ($package['contents'] as $item_id => $values) {
			$ctr++;
			
			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}
			
			if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
				$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'wf-shipping-dhl'), $ctr));
				continue;
			}

			if (!$values['data']->get_weight()) {
				$this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
				return;
			}
			$weight_pack->add_item(wc_get_weight( $values['data']->get_weight(), $this->weight_unit ), $values['data'], $values['quantity']);
		}
		
		$pack   =   $weight_pack->pack_items();	 
		$errors =   $pack->get_errors();
		if( !empty($errors) ){
			//do nothing
			return;
		} else {
			$boxes	  =   $pack->get_packed_boxes();
			$unpacked_items =   $pack->get_unpacked_items();
			
			$insured_value		  =   0;
			
			$packages	  =   array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
			$package_count  =   sizeof($packages);
			// get all items to pass if item info in box is not distinguished
			$packable_items =   $weight_pack->get_packable_items();
			$all_items	  =   array();
			if(is_array($packable_items)){
				foreach($packable_items as $packable_item){
					$all_items[]	=   $packable_item['data'];
				}
			}
			//pre($packable_items);
			
			$order_total = '';
			if(isset($this->order)){
				$order_total	=   $this->order->get_total();
			}
			
			$to_ship  = array();
			$group_id = 1;
			foreach($packages as $package){//pre($package);
			
				$packed_products = array();
				if(($package_count  ==  1) && isset($order_total)){
					$insured_value  =   $order_total;
				}else{
					$insured_value  =   0;
					if(!empty($package['items'])){
						foreach($package['items'] as $item){						
							$insured_value		  =   $insured_value+$item->get_price();
							
						}
					}else{
						if( isset($order_total) && $package_count){
							$insured_value  =   $order_total/$package_count;
						}
					}
				}
				$packed_products	=   isset($package['items']) ? $package['items'] : $all_items;
				// Creating package request
				$package_total_weight   =   $package['weight'];
				
				$insurance_array = array(
					'Amount' => round($values['data']->get_price()),
					'Currency' => get_woocommerce_currency()
				);
				if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
					$crate = 1 / $this->conversion_rate;
					$insurance_array['Amount']	  = round($values['data']->get_price() * $crate, 2);
					$insurance_array['Currency']	= $this->settings['dhl_currency_type'];
				}
				$group = array(
					'GroupNumber' => $group_id,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => round(wc_get_weight($package['weight'], $this->weight_unit), 3),
						'Units' => $this->weight_unit
					),
					'packed_products' => $packed_products,
				);
				$group['InsuredValue'] = $insurance_array;
				$group['packtype'] = isset($this->settings['shp_pack_type'])?$this->settings['shp_pack_type'] : 'OD';
				
				$to_ship[] = $group;
				$group_id++;
			}
		}
		return $to_ship;
	}

	private function box_shipping($package) {
		
		// Add items
		foreach ($package['contents'] as $item_id => $values) {

			if (!$values['data']->needs_shipping()) {
				$this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
				continue;
			}

			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height( $values['data'] ) && wf_get_product_width( $values['data'] ) && wf_get_product_weight( $values['data'] )) {

				$dimensions = array( wf_get_product_length( $values['data'] ), wf_get_product_height( $values['data'] ), wf_get_product_width( $values['data'] ));

				for ($i = 0; $i < $values['quantity']; $i++) {
					$boxpack->add_item(
							wc_get_dimension($dimensions[2], $this->dimension_unit), wc_get_dimension($dimensions[1], $this->dimension_unit), wc_get_dimension($dimensions[0], $this->dimension_unit), wc_get_weight($values['data']->get_weight(), $this->weight_unit), $values['data']->get_price(), array(
						'data' => $values['data']
							)
					);
				}
			} else {
				$this->debug(sprintf(__('Product #%s is missing dimensions. Aborting.', 'wf-shipping-dhl'), $item_id), 'error');
				return;
			}
		}

		// Pack it
		$boxpack->pack();
		$packages = $boxpack->get_packages();
		$to_ship = array();
		$group_id = 1;

		foreach ($packages as $package) {
			if ($package->unpacked === true) {
				$this->debug('Unpacked Item');
			} else {
				$this->debug('Packed ' . $package->id);
			}

			$dimensions = array($package->length, $package->width, $package->height);

			sort($dimensions);

			$insurance_array = array(
				'Amount' => round($package->value),
				'Currency' => get_woocommerce_currency()
			);
			if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
				$crate = 1 / $this->conversion_rate;
				$insurance_array['Amount'] = round($package->value * $crate, 2);
				$insurance_array['Currency'] = $this->settings['dhl_currency_type'];
			}

			$group = array(
				'GroupNumber' => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => round($package->weight, 3),
					'Units' => $this->weight_unit
				),
				'Dimensions' => array(
					'Length' => max(1, round($dimensions[2], 0)),
					'Width' => max(1, round($dimensions[1], 0)),
					'Height' => max(1, round($dimensions[0], 0)),
					'Units' => $this->dimension_unit
				),
				'InsuredValue' => $insurance_array,
				'packed_products' => array(),
				'package_id' => $package->id,
				'packtype' => isset($package->packtype)?$package->packtype:'OD'
			);

			if (!empty($package->packed) && is_array($package->packed)) {
				foreach ($package->packed as $packed) {
					$group['packed_products'][] = $packed->get_meta('data');
				}
			}



			$to_ship[] = $group;

			$group_id++;
		}

		return $to_ship;
	}

	//shipper as parameter, because if multiventor plug-in is there, it couldn't take origin address. 
	private function generate_commercial_invoice( $packages, $shipper, $toaddress ){
		include_once("fpdf/wf-dhl-commercial-invoice-template.php");
		$commercial_invoice = new wf_dhl_ec_commercial_invoice();
		
		$fromaddress =array();
		$fromaddress['sender_name']			 	= $shipper['contact_person_name'];
		$fromaddress['sender_address_line1'] 	= $shipper['address_line'];
		$fromaddress['sender_address_line2'] 	= $shipper['address_line2'];
		$fromaddress['sender_city']			 	= $shipper['city'];
		$fromaddress['sender_country']		 	= $shipper['country_name'];
		$fromaddress['sender_postalcode']	 	= $shipper['postal_code'];
		$fromaddress['phone_number']			= $shipper['contact_phone_number'];
		$fromaddress['sender_company']			= $shipper['company_name'];
		$fromaddress['sender_state_code']		= $shipper['division_code'];
		$fromaddress['sender_email']			= $shipper['contact_email'];
		
		$products_details = array();
		if ($packages) {
			$total_weight = 0;
			$total_value = 0;
			
			$currency = get_woocommerce_currency();
			$weight_unit  = $this->weight_unit;
			$total_units =0;
			$i=0;
			$pre_product_id = '';
			$net_weight = 0;
			$pre_package = 0;
			$items = $this->order->get_items();
			foreach ($items as $item_id => $orderItem) {
				$item_id 		= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
				$product 		= wc_get_product( $item_id );

				$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
				$post_id 	= $par_id ? $par_id : wf_get_product_id($product);

				$wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
				$manufacture 	= get_post_meta( $post_id, '_wf_manufacture_country', 1); //this works for variable 

				$products_details[$i]['quantity'] 		= $orderItem['qty'];
				$products_details[$i]['description'] 	= $product->get_title();
				$products_details[$i]['weight'] 		= number_format($product->get_weight(), 2);
				$products_details[$i]['price'] 			= number_format( $product->get_price(), 2 );
				$products_details[$i]['total'] 			= (float)$product->get_price() * (int)$orderItem['qty'];
				$products_details[$i]['hs'] 			= $wf_hs_code;
				$products_details[$i]['weight_unit'] 	= $weight_unit;
				$products_details[$i]['manufacture'] 	= $manufacture;
				$products_details[$i]['no_package'] 	= 1;

				$products_details[$i] = apply_filters( 'wf_dhl_commecial_invoice_product_details', $products_details[$i], $product );
				
				$total_value += $products_details[$i]['total'];
				$i++;
			}
		}
		foreach($products_details as $product){
			$total_units 	+= $product['quantity'];
			$net_weight 	+= number_format($product['weight'], 2);
			$total_weight 	+= number_format($product['weight'], 2);
		}
	
		$package_details = array(
			'value' 		=> number_format( $total_value, 2 ), //total product price sum
			'diccount'	 	=> number_format( $this->order->get_total_discount(), 2 ),
			'other' 		=> '0.00',
			'total' 		=> number_format( $total_value - (float)$this->order->get_total_discount(), 2 ),
			'net_weight' 	=> number_format( $net_weight, 2 ),
			'gross_weight' 	=> number_format( $total_weight, 2 ),
			'currency' 		=>$currency,
			'weight_unit' 	=>$weight_unit,
			'total_unit' 	=>$total_units,
			'total_package' =>count($packages),
			'originator' 	=> $shipper['company_name'],
		);

		$exta_details = array(
			'Terms Of Trade' 	=> ($this->dutypayment_type == 'S') ? 'DDP' : ( ($this->dutypayment_type == 'R') ? 'DAP' : '' ),
			'Terms Of Payment' 	=> '',
			'Contract number' 	=> '',
			'Contract Date' 	=> '',
		);
	
		$designated_broker = array(
		  'dutypayment_type' 	=> '',
		  'dutyaccount_number' 	=> '',
		);
		
		$commercial_invoice->init( 2 );
		$commercial_invoice->addShippingToAddress( apply_filters( 'wf_dhl_commecial_invoice_destination_address', $toaddress, $packages, $this->order ) );
		$commercial_invoice->addShippingFromAddress( apply_filters( 'wf_dhl_commecial_invoice_source_address', $fromaddress, $packages, $this->order ) );
		$commercial_invoice->designated_broker( apply_filters( 'wf_dhl_commecial_invoice_designated_broker', $designated_broker, $packages, $this->order ) );
		$commercial_invoice->addProductDetails( $products_details );
		$commercial_invoice->addPackageDetails( apply_filters( 'wf_dhl_commecial_invoice_package_details', $package_details, $packages, $this->order ) );
		$commercial_invoice->addExtraDetails( apply_filters( 'wf_dhl_commecial_invoice_exta_details', $exta_details, $packages, $this->order ) );
		return base64_encode( $commercial_invoice->Output('S') );			
	}

	public function get_package_signature($products){
	   $signature_priority_array = array(
				0 => 'SX',
				1 => 'SA',
				2 => 'SB',
				3 => 'SC',
				4 => 'SD',
				5 => 'SE',
				6 => 'SW',
			);
		$higher_signature_option = 0;
		foreach( $products as $key => $product ){
			$par_id 	= wp_get_post_parent_id( wf_get_product_id($product['data']) );
			$post_id 	= $par_id ? $par_id : wf_get_product_id($product['data']);


			$wf_dcis_type = get_post_meta($post_id, '_wf_dhl_signature', true);
			if( empty($wf_dcis_type) ){
				$wf_dcis_type = 0;
			}
			
			if( $wf_dcis_type > $higher_signature_option ){
				$higher_signature_option = $wf_dcis_type;
			}
		}
		return $signature_priority_array[$higher_signature_option];
	}
	
	private function get_dhl_requests($dhl_packages, $package) {
		// Time is modified to avoid date diff with server.
		
		$mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);
		$destination_city = strtoupper($package['destination']['city']);
		$destination_state = strtoupper($package['destination']['state']);
		$destination_postcode = str_replace(' ', '', strtoupper($package['destination']['postcode']));
		$destination_country_name = isset(WC()->countries->countries[$package['destination']['country']]) ? WC()->countries->countries[$package['destination']['country']] : $package['destination']['country'];
		$consignee_name = wf_get_order_shipping_first_name($this->order) . ' ' . wf_get_order_shipping_last_name($this->order);
		$order_subtotal = wc_format_decimal($this->order->get_subtotal($this->order), 2);
		$order_currency = wf_get_order_currency($this->order);

		$is_dutiable = ($package['destination']['country'] == WC()->countries->get_base_country() || wf_dhl_is_eu_country(WC()->countries->get_base_country(), $package['destination']['country'])) ? "N" : "Y";

		$endpoint = 'https://api.dhlglobalmail.com/v1/auth/access_token.xml?username='.$this->site_id.'&password='.$this->site_password;
		$response	= wp_remote_post( $endpoint,
			array(
				'method'	=> 'GET',
				'timeout'	=> 70, 
				'sslverify'	=> 0,
				'headers'	=> array('Accept' => 'application/xml', 'Content-Type' => 'application/xml;charset=UTF-8'))
		);
		
		if ( is_wp_error( $response ) ) {
		   // error
		}
		else if( isset( $response["body"] ) ) {
			$xml_response 		= simplexml_load_string( $response['body'] );
			$xml_code 	= $xml_response->Meta->Code;
			
			if($xml_code == '200')
			{
				$xml_accesstoken 	= $xml_response->Data->AccessToken;
				$this->accesstoken  = $xml_accesstoken;
				$xml_expiresin 	= $xml_response->Data->ExpiresIn;
				$xml_scope 	= $xml_response->Data->Scope;

			}else
			{
				//Errror
			}
			
		}

		$shipment_details = $this->wf_get_shipment_details($dhl_packages, $is_dutiable);

		$origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;

		$special_service = "";

		$shipping_company = wf_get_order_shipping_company( $this->order );
		$consignee_companyname = htmlspecialchars(!empty( $shipping_company ) ? $shipping_company : $consignee_name);

		$dutypayment_type_accountnumber = "";
		if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
			$dutypayment_type_accountnumber = "<DutyPaymentType>{$this->dutypayment_type}</DutyPaymentType>";
			if (!empty($this->dutyaccount_number)) {
				$dutypayment_type_accountnumber .= "<DutyAccountNumber>{$this->dutyaccount_number}</DutyAccountNumber>";
			}
		}
		
		$shipper	=   array(
			'shipper_id'			=>  $this->account_number,
			'company_name'		  =>  $this->freight_shipper_company_name,
			'registered_account'	=>  $this->account_number,
			'address_line'		  =>  $this->freight_shipper_street,
			'address_line2'		 =>  $this->freight_shipper_street_2,
			'city'				  =>  $this->freight_shipper_city,
			'division'			  =>  $this->freight_shipper_state,
			'division_code'		 =>  $this->freight_shipper_state,
			'postal_code'		   =>  $this->origin,
			'country_code'		  =>  $this->origin_country,
			'country_name'		  =>  $origin_country_name,
			'contact_person_name'   =>  $this->freight_shipper_person_name,
			'contact_phone_number'  =>  $this->freight_shipper_phone_number,
			'contact_email'		 =>  $this->shipper_email,
		);
		
		// If package have different origin, use it instead of admin settings
		if(isset($package['origin'])	&&  !empty($package['origin'])){
			// Check if vendor have atleast provided origin address
			if(isset($package['origin']['country']) && !empty($package['origin']['country'])){
				$shipper['company_name']			=   $package['origin']['company'];
				$shipper['address_line']			=   $package['origin']['address_1'];
				$shipper['address_line2']		   =   $package['origin']['address_2'];
				$shipper['city']					=   $package['origin']['city'];
				$shipper['division']				=   $package['origin']['state'];
				$shipper['division_code']		   =   $package['origin']['state'];
				$shipper['postal_code']			 =   $package['origin']['postcode'];
				$shipper['country_code']			=   $package['origin']['country'];
				$shipper['country_name']			=   isset(WC()->countries->countries[$package['origin']['country']]) ? WC()->countries->countries[$package['origin']['country']] : $package['origin']['country'];
				$shipper['contact_person_name']	 =   $package['origin']['first_name'].' '.$package['origin']['last_name'];
				$shipper['contact_phone_number']	=   $package['origin']['phone'];
				$shipper['contact_email']		   =   $package['origin']['email'];
			}
		}
		


		$toaddress = array(
				'first_name'	=> wf_get_order_shipping_first_name($this->order),
				'last_name'	 => wf_get_order_shipping_last_name($this->order),
				'company'	   => wf_get_order_shipping_company($this->order),
				'address_1'	 => wf_get_order_shipping_address_1( $this->order),
				'address_2'	 => wf_get_order_shipping_address_2($this->order),
				'city'		  => $destination_city,
				'postcode'	  => $destination_postcode,
				'country'	   => $destination_country_name,
				'email'		 => wf_get_order_billing_email($this->order),
				'phone'		 => wf_get_order_billing_phone($this->order),
			);

		$sample_base64_encoded_pdf = $this->generate_commercial_invoice( $dhl_packages, $shipper, $toaddress);
		$RequestArchiveDoc = ''; $docImage = '';
		if($this->expected_delivery > 1)
		{
			$expected_delivery_date = date('Ymd',strtotime("+".$this->expected_delivery." days"));
			
		}else
		{
			if($this->expected_delivery  != '0')
			{
				$expected_delivery_date = date('Ymd',strtotime("+".$this->expected_delivery." day"));
			}else{
				$expected_delivery_date = date('Ymd',strtotime("now"));
			}
			
		}
		$address = $this->get_valid_address( wf_get_order_shipping_address_1($this->order), wf_get_order_shipping_address_2($this->order) );

		$destination_address = '<Address1>'.htmlspecialchars($address['valid_line1']).'</Address1>';
		if( !empty( $address['valid_line2'] ) )
			$destination_address .= '<Address2>'.htmlspecialchars($address['valid_line2']).'</Address2>';
		
		$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>

<EncodeRequest>
  <CustomerId>{$this->account_number}</CustomerId>
  <BatchRef>1346421550190</BatchRef>
  <MpuList>
    <Mpu>
      <ConsigneeAddress>
        <StandardAddress>
          <Name>{$consignee_name}</Name>
          <Firm>{$consignee_companyname}</Firm>
          {$destination_address}
          <City>{$destination_city}</City>
          <State>{$destination_state}</State>
          <Zip>{$destination_postcode}</Zip>
          <CountryCode>{$package['destination']['country']}</CountryCode>
        </StandardAddress>
      </ConsigneeAddress>
      <ReturnAddress>
        <StandardAddress>
          <Name>{$shipper['contact_person_name']}</Name>
          <Firm>{$shipper['company_name']}</Firm>
          <Address1>{$shipper['address_line']}</Address1>
          <Address2>{$shipper['address_line2']}</Address2>
          <City>{$shipper['city']}</City>
          <State>{$shipper['division']}</State>
          <Zip>{$shipper['postal_code']}</Zip>
          <CountryCode>{$shipper['country_code']}</CountryCode>
        </StandardAddress>
      </ReturnAddress>
      <OrderedProductCode>{$this->service_code}</OrderedProductCode>
      <ServiceEndorsement>1</ServiceEndorsement>
      {$shipment_details}
      <BillingRef1></BillingRef1>
      <BillingRef2></BillingRef2>
      <MailTypeCode>{$this->mail_type}</MailTypeCode>
      <FacilityCode>{$this->facility_code}</FacilityCode>
      <ExpectedShipDate>{$expected_delivery_date}</ExpectedShipDate>
    </Mpu>
  </MpuList>
</EncodeRequest>
XML;
		$xmlRequest = apply_filters('wf_dhl_label_request', $xmlRequest, $this->order_id);
		
		return $xmlRequest;
	}

	private function get_valid_address( $line1, $line2='', $line3='' ){
		$valid_address = array();
		
		if( strlen($line1) > 35 ){
			$valid_address['valid_line1'] = $this->substr_upto_space( $line1, 35 );
			$line1_rem = trim( str_replace( $valid_address['valid_line1'], "", $line1 ) );
			$line2 = $line1_rem." ".$line2;
		}else{
			$valid_address['valid_line1'] = $line1;
		}

		if( strlen($line2) > 35 ){
			$valid_address['valid_line2'] = $this->substr_upto_space( $line2, 35 );
			$line2_rem = trim( str_replace( $valid_address['valid_line2'], "", $line2 ) );
			$line3 = $line2_rem." ".$line3;
		}else{
			$valid_address['valid_line2'] = $line2;
		}

		// not limiting line3 charecters upto 35, because DHL API handle the case and throws error. 
		if( !empty($line3) ){
			$valid_address['valid_line3'] = $line3;
		}
		return $valid_address;
	}

	public function substr_upto_space( $str, $l ){
		$pos =strrpos($str,' ');
		if($pos>$l){
			return $this->substr_upto_space( substr($str,0,$pos), $l );
		}
		else{
			return substr($str,0,$pos);
		}
	}

	private function wf_get_shipment_details($dhl_packages, $is_dutiable = 'N') {
		$pieces = "";
		$total_packages = 0;
		$total_weight = 0;
		$total_value = 0;
		$currency = get_woocommerce_currency();

		if ($dhl_packages) {
			foreach ($dhl_packages as $group_kay => $package_group) {
				foreach ($package_group as $key => $parcel) {
					$index = $key + 1;
					$total_packages += $parcel['GroupPackageCount'];
					$total_weight += $parcel['Weight']['Value'] * $parcel['GroupPackageCount'];
					$total_value += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];
					$pack_type = $this->wf_get_pack_type($parcel['packtype']);
					$pieces .= '<Piece><PieceID>' . $index . '</PieceID>';
					$pieces .= '<PackageType>'.$pack_type.'</PackageType>';
					$pieces .= '<Weight>' . $parcel['Weight']['Value'] . '</Weight>';
					if( !empty($parcel['Width']['Value']) && $parcel['Height']['Value'] && $parcel['Length']['Value'] ){
						$pieces .= '<Width>' . $parcel['Dimensions']['Width'] . '</Width>';
						$pieces .= '<Height>' . $parcel['Dimensions']['Height'] . '</Height>';
						$pieces .= '<Depth>' . $parcel['Dimensions']['Length'] . '</Depth>';
					}
					$pieces .= '</Piece>';
				}
			}
		}
		
		$shipment_details = <<<XML

	 <Weight>
        <Value>{$total_weight}</Value>
        <Unit>{$this->labelapi_weight_unit}</Unit>
      </Weight>
XML;
		return $shipment_details;
	}

	private function get_local_product_code( $global_product_code, $origin_country='', $destination_country='' ){
		$countrywise_local_product_code = array( 
			'SA' => 'global_product_code',
			'ZA' => 'global_product_code',
			'CH' => 'global_product_code'
		);
		
		if( array_key_exists($origin_country, $countrywise_local_product_code) ){
			return ($countrywise_local_product_code[$this->origin_country] == 'global_product_code') ? $global_product_code : $countrywise_local_product_code[$this->origin_country];
		}
		return null;
	}

	public function wf_get_package_from_order($order) {
		$orderItems = $order->get_items();
		foreach ($orderItems as $orderItem) {
			$product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
			$items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
		}
		$package['contents'] = $items;
		$package['destination']['country']	  = wf_get_order_shipping_country($order);
		$package['destination']['first_name']   = wf_get_order_shipping_first_name($order);
		$package['destination']['last_name']	= wf_get_order_shipping_last_name($order);
		$package['destination']['company']	  = wf_get_order_shipping_company($order);
		$package['destination']['address_1']	= wf_get_order_shipping_address_1($order);
		$package['destination']['address_2']	= wf_get_order_shipping_address_2($order);
		$package['destination']['city']		 = wf_get_order_shipping_city($order);
		$package['destination']['state']		= wf_get_order_shipping_state($order);
		$package['destination']['postcode']	 = wf_get_order_shipping_postcode($order);
		
		$package = apply_filters( 'wf_dhl_filter_label_packages', array($package) , $this->ship_from_address );
		return $package;
	}

	private function get_dummy_dhl_package(){
		return array(
			'GroupNumber'	   => 1,
			'GroupPackageCount' => 1,
			'packtype'		  => 'BOX',
			'InsuredValue'	  => 0,
			'packed_products'   => array(),
		);
	}

	public function manual_packages($packages){
		if(!isset($_GET['weight'])){
			return $packages;
		}
		$length_arr	 =   json_decode(stripslashes(html_entity_decode($_GET["length"])));
		$width_arr	  =   json_decode(stripslashes(html_entity_decode($_GET["width"])));
		$height_arr	 =   json_decode(stripslashes(html_entity_decode($_GET["height"])));
		$weight_arr	 =   json_decode(stripslashes(html_entity_decode($_GET["weight"])));	 

		$no_of_package_entered  =   count($weight_arr);
		$no_of_packages = 0;
		foreach ($packages as $key => $package) {
			$no_of_packages += count($package);
		}
	   
		// Populate extra packages, if entered manual values
		if($no_of_package_entered > $no_of_packages){ 
			$package_clone  =   is_array( $packages[0] ) ? current($packages[0])  : $this->get_dummy_dhl_package(); //get first package to clone default data
			for($i=$no_of_packages; $i<$no_of_package_entered; $i++){
				$packages[0][$i]   = $package_clone;
			}
		}

		// Overridding package values
		foreach ($packages as $package_num=> $stored_package) {
			foreach($stored_package as $key => $package){
				if(isset($length_arr[$key])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Length']  =   $length_arr[$key];
				}
				if(isset($width_arr[$key])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Width']   =   $width_arr[$key];
				}
				if(isset($height_arr[$key])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Height']  =   $height_arr[$key];
				}
				if(isset($weight_arr[$key])){// If not available in GET then don't overwrite.

					$weight =   $weight_arr[$key];
					/*if($package['Package']['PackageWeight']['UnitOfMeasurement']['Code']=='OZS'){
						if($this->weight_unit=='LBS'){ // make sure weight from pounds to ounces
							$weight =   $weight*16;
						}else{
							$weight =   $weight*35.274; // From KG to ounces
						}
					}*/
					$packages[$package_num][$key]['Weight']['Value']   =   $weight;
				}
			}
		}
		return $packages;
	}

	public function print_label($order, $service_code, $order_id,$mail_type='',$expected_delivery='1') {
		
		$this->order = $order;
		$this->order_id = $order_id;
		$this->service_code = $service_code;
		$this->mail_type = $mail_type;
		$this->expected_delivery = $expected_delivery;
		
		$packages   =   array();
		$packages   =   array_values( $this->wf_get_package_from_order($order) );

		$stored_packages	=   get_post_meta( $order_id, '_wf_dhl_stored_packages_ec', true );
		if(!$stored_packages){
			
			foreach ($packages as $key => $package) {
				$stored_packages[] = $this->get_dhl_packages($package);
			}
		}
		
		$dhl_packages = $this->manual_packages( $stored_packages );
		
		foreach($dhl_packages as $key => $dhl_package){
			$this->print_label_processor( array($dhl_package), $packages[$key] );
			if( !empty( $this->shipmentErrorMessage) ){
				$this->shipmentErrorMessage .= "</br>Some error occured for package $key: ".$this->shipmentErrorMessage;
			}
		}

		if ($this->debug) {
			echo '<a href="' . admin_url('/post.php?post=' . $_GET['wf_dhl_ecommerce_createshipment'] . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
			//For the debug information to display in the page
			die();
		}

	}

	public function print_label_processor($dhl_package, $package) {
		$this->shipmentErrorMessage = '';
		$this->master_tracking_id = '';
		
		// Debugging
		$this->debug(__('dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl'));
		
		// Get requests
		$dhl_requests = $this->get_dhl_requests($dhl_package, $package);

		if ($dhl_requests) {
			$this->run_package_request($dhl_requests, $dhl_package);
		}

		update_post_meta($this->order_id, 'wf_woo_dhl_ecommerceshipmentErrorMessage', $this->shipmentErrorMessage);

	}

	public function run_package_request($request, $dhl_packages = null) {
		/* try {			
		 */
		
		$this->process_result($this->get_result($request), $request, $dhl_packages);

		/*  } catch ( Exception $e ) {
		  $this->debug( print_r( $e, true ), 'error' );
		  return false;
		  } */
	}

	private function get_result($request) {
		$this->debug('DHL REQUEST: <pre class="debug_info_ec" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($request, ENT_IGNORE), true) . '</pre>');
		$wf_service_url = 'https://api.dhlglobalmail.com/v1/label/US/'.$this->account_number.'/image.xml?access_token='. $this->accesstoken .'&client_id='.$this->client_id;
		$result = wp_remote_post($wf_service_url, array(
			'method' => 'POST',
			'timeout' => 70,
			'sslverify' => 0,
			'headers'		  => array('Accept' => 'application/xml', 'Content-Type' => 'application/xml;charset=UTF-8'),
			'body' => $request
				)
		);
		$this->debug('DHL RESPONSE: <pre class="debug_info_ec" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($result['body'], true), ENT_IGNORE) . '</pre>');
		
		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			$this->debug('DHL WP ERROR: <a href="#" class="debug_reveal_ec">Reveal</a><pre class="debug_info_ec" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($error_message), true) . '</pre>');
		}
		elseif (is_array($result) && !empty($result['body'])) {
			$result = $result['body'];
		} else {
			$result = '';
		}

		libxml_use_internal_errors(true);

		$result = utf8_encode($result);
		$xml = simplexml_load_string($result);

		$shipmentErrorMessage = "";
		$shipment_id = '';
		$labelimage = '';
		if (!$xml) {
			$shipmentErrorMessage .= 'Failed loading XML' . "\n";
			foreach (libxml_get_errors() as $error) {
				$shipmentErrorMessage .= "\t" . $error->message;
			}
			$return = array(
				'ErrorMessage' => $shipmentErrorMessage
			);
		} else {
			$errorMsg = "";

			if ($xml->Meta->Error->ErrorType && (string) $xml->Meta->Code != '')
			{
				

					$errorMsg = ((string) $xml->Meta->Error->ErrorType) . ' : ' . ((string) $xml->Meta->Error->ErrorMessage) . '('. ((string) $xml->Data->MpuList->Mpu->ErrorList->Error) .')';
				
			}else
			{

			}
			if(isset($xml->Data->LabelDetail->Value))
			{
				$shipment_id = ((string) $xml->Data->LabelDetail->Value );
				$labelimage = ((string) $xml->Data->LabelDetail->LabelImage);
			}

			$return = array('ShipmentID' =>  $shipment_id,
				'LabelImage' => $labelimage,
				'ErrorMessage' => $errorMsg
			);

			$xml_request	=   simplexml_load_string($request);
			 
		}
		return $return;
	}

	private function process_result($result = '', $request, $dhl_packages) {
		if (!empty($result['ShipmentID']) && !empty($result['LabelImage'])) {
			$shipmentId = $result['ShipmentID'];
			$shippingLabel = $result['LabelImage'];
			
			add_post_meta($this->order_id, 'wf_woo_dhl_ecommerceshipmentId', $shipmentId, false);
			add_post_meta($this->order_id, 'wf_woo_dhl_ecommerceshippingLabel_' . $shipmentId, $shippingLabel, true);
			add_post_meta($this->order_id, 'wf_woo_dhl_ecommerceshipping_commercialInvoice_' . $shipmentId, $shippingLabel, true);
			

			// Shipment Tracking (Auto)
			if ('' != $admin_notice) {
				WF_Tracking_Admin_DHLecommerce::display_admin_notification_message($this->order_id, $admin_notice);
			} else {
				//Do your plugin's desired redirect.
				//exit;
			}

			if (!empty($this->service_code)) {
				add_post_meta($this->order_id, 'wf_woo_dhl_ecommerceservice_code', $this->service_code, true);
			}

		}

		if (!empty($result['ErrorMessage'])) {
			$this->shipmentErrorMessage .= $result['ErrorMessage'];
		}
	}

	private function wf_get_parcel_details($dhl_packages) {
		$complete_box = array();
		if ($dhl_packages) {
			foreach ($dhl_packages as $key => $parcel) {
				$box_details = "";
				if (!empty($parcel['package_id'])) {
					$box_details .= '<strong>BOX:  </strong>' . $parcel['package_id'] . '<br />';
				}
				if (isset($parcel['Weight'])) {
					$box_details .= '<strong>Weight:  </strong>' . $parcel['Weight']['Value'] . ' ' . $parcel['Weight']['Units'] . '<br />';
				}
				if (isset($parcel['Dimensions'])) {
					$box_details .= '<strong>Height:  </strong>' . $parcel['Dimensions']['Height'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';
					$box_details .= '<strong>Width:  </strong>' . $parcel['Dimensions']['Width'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';
					$box_details .= '<strong>Length:  </strong>' . $parcel['Dimensions']['Length'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';
				}
				$box_details .= '<hr>';
				$complete_box[] = $box_details;
			}
		}
		return $complete_box;
	}
	// Alter package type as per user selection in settings - for print label API
	private function wf_get_pack_type($selected) {
			$pack_type = 'OD';
			if ($selected == 'FLY') {
				$pack_type = 'DF';
			} elseif ($selected == 'BOX') {
				$pack_type = 'OD';
			}
			elseif ($selected == 'YP') {
				$pack_type = 'YP';
			}
		return $pack_type;	
	}

}