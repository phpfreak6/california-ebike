<?php

if (!defined('ABSPATH')) {
	exit;
}

class wf_dhl_woocommerce_shipping_admin_helper {

	private $service_code;

	public function __construct() {
		$this->id = WF_DHL_ID;
		$this->init();
	}

	private function init() {
		$this->settings = get_option('woocommerce_' . WF_DHL_ID . '_settings', null);

		$this->add_trackingpin_shipmentid = $this->settings['add_trackingpin_shipmentid'];
		$this->errorMsg = "";

		$this->origin = strtoupper($this->settings['origin']);
		$this->origin_country = isset($this->settings['base_country']) ? $this->settings['base_country'] : WC()->countries->get_base_country();
		$this->account_number = $this->settings['account_number'];

		$this->site_id = $this->settings['site_id'];
		$this->site_password = $this->settings['site_password'];
		$this->region_code = $this->settings['region_code'];
		
		$this->latin_encoding = isset($this->settings['latin_encoding']) && $this->settings['latin_encoding'] == 'yes' ? true : false;
		$utf8_support = $this->latin_encoding ? '?isUTF8Support=true' : '';

		$_stagingUrl = 'https://xmlpitest-ea.dhl.com/XMLShippingServlet'.$utf8_support;
		$_productionUrl = 'https://xmlpi-ea.dhl.com/XMLShippingServlet'.$utf8_support;
		
		$this->insure_currency   = isset( $this->settings['insure_currency'] ) ?  $this->settings['insure_currency'] : '';
		$this->insure_converstion_rate   = !empty($this->settings['insure_converstion_rate']) ? $this->settings['insure_converstion_rate'] : '';
		
		
		$this->production = ( !empty($this->settings['production']) && $this->settings['production'] ==='yes'  ) ? true : false;
		$this->plt = ( !empty($this->settings['plt']) && $this->settings['plt'] === 'yes' ) ? true : false;
		$this->service_url = ($this->production == true) ? $_productionUrl : $_stagingUrl;
		
		$this->debug = ( !empty($this->settings['debug']) && $this->settings['debug'] ==='yes' && !isset($_REQUEST['post']) ) ? true : false; //$__REQUEST['post'] to confirm its not coming from bulk action. Bulk action needs to forcefully turn off debug
		
		$this->insure_contents = ( !empty($this->settings['insure_contents']) && $this->settings['insure_contents'] ==='yes' ) ? true : false;
		$this->request_type = $this->settings['request_type'];
		$this->packing_method = $this->settings['packing_method'];
		$this->return_label_key 				 = isset( $this->settings['return_label_key'] ) ? $this->settings['return_label_key'] : '';
		$this->return_label_acc_number 				 = isset( $this->settings['return_label_acc_number'] ) ? $this->settings['return_label_acc_number'] : '';
		
		$this->boxes = isset($this->settings['boxes']) ? $this->settings['boxes'] :'';

		$this->custom_services = $this->settings['services'];
		$this->offer_rates = $this->settings['offer_rates'];

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
		
		$this->pickupdate = isset($this->settings['pickup_date']) ? $this->settings['pickup_date'] : '0';
		$this->pickupfrom = isset($this->settings['pickup_time_from']) ? $this->settings['pickup_time_from'] : '';
		$this->pickupto = isset($this->settings['pickup_time_to']) ? $this->settings['pickup_time_to'] : '';
		$this->pickupperson = isset($this->settings['pickup_person']) ? $this->settings['pickup_person'] : '';
		$this->pickupcontct = isset($this->settings['pickup_contact']) ? $this->settings['pickup_contact'] : '';

		$this->dimension_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'IN' : 'CM';
		$this->weight_unit	= isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'LBS' : 'KG';
		$this->product_weight_unit	= isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'L' : 'K';
	
		$this->labelapi_dimension_unit	= $this->dimension_unit == 'IN' ? 'I' : 'C';
		$this->labelapi_weight_unit	   = $this->weight_unit == 'LBS' ? 'L' : 'K';
		
		$this->conversion_rate = !empty($this->settings['conversion_rate']) ? $this->settings['conversion_rate'] : '';
		$this->conversion_rate = apply_filters('wf_dhl_conversion_rate',	$this->conversion_rate, $this->settings['dhl_currency_type']);
		
		//Time zone adjustment, which was configured in minutes to avoid time diff with server. Convert that in seconds to apply in date() functions.
		$this->timezone_offset = !empty($this->settings['timezone_offset']) ? intval($this->settings['timezone_offset']) * 60 : 0;
		
		if(class_exists('wf_vendor_addon_setup'))
		{
			if(isset($this->settings['vendor_check']) && $this->settings['vendor_check'] === 'yes')
			{
				$this->ship_from_address	=   'vendor_address'; 
			}
			else
			{
				$this->ship_from_address	=   'origin_address';
			}
		}else
		{
			$this->ship_from_address	=   'origin_address';
		}
		
		$this->label_contents_text = (isset($_GET['shipment_content']) && $_GET['shipment_content'] != '') ? $_GET['shipment_content'] : 'Shipment contents';

		$this->weight_packing_process = !empty($this->settings['weight_packing_process']) ? $this->settings['weight_packing_process'] : 'pack_descending';
		$this->box_max_weight		 = !empty($this->settings['box_max_weight']) ? $this->settings['box_max_weight'] : '';
		$this->non_plt_commercial_invoice ='';
		$this->local_product_code = '';
		$this->user_settings = get_option('woocommerce_wf_dhl_shipping_settings');
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
		if(!empty($package['contents']))
		{
			foreach ($package['contents'] as $item_id => $values) {

				try{
                    if (!$values['data']->needs_shipping()) {
                        $this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
                        continue;
                    }

                    $skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $package['contents']);
                    if($skip_product){
                        continue;
                    }
                    if(isset($values['measured_weight']) && $values['measured_weight'] !=0 )
                    {
                        $weight = $values['measured_weight'];
                    }
                    else
                    {
                        $weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
                    }
                    if (!$weight) {
                        $this->debug(sprintf(__('Product # is missing weight. Aborting.', 'wf-shipping-dhl'), $item_id), 'error');
                        return;
                    }

                    $group = array();
                    $insurance_array = array(
                        'Amount' => round($values['data']->get_price()),
                        'Currency' => get_woocommerce_currency()
                    );
                    //if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
                        //$crate = 1 / $this->conversion_rate;
                        //$insurance_array['Amount'] = round($values['data']->get_price() * $crate, 2);
                        //$insurance_array['Currency'] = $this->settings['dhl_currency_type'];
                    //}
                                    if($weight<0.001){
                                        $weight = 0.001;
                                    }else{
                                        $weight = round($weight,3);
                                    }
                    $group = array(
                        'GroupNumber' => $group_id,
                        'GroupPackageCount' => 1,
                        'Weight' => array(
                            'Value' => $weight,
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
                }catch(Exception $e){
                    $this->debug("Error ".$e);
                }
			}
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
	private function weight_based_shipping($order) {
		if ( ! class_exists( 'WeightPack' ) ) {
			include_once 'weight_pack/class-wf-weight-packing.php';
		}
		$weight_pack=new WeightPack($this->weight_packing_process);
		$weight_pack->set_max_weight($this->box_max_weight);
		
		$package_total_weight = 0;
		
		
		$ctr = 0;
		foreach ($order['contents'] as $item => $values) {
			$ctr++;
            $data = $values['data'];
            $itemData = $data->get_data();
			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $order['contents']);
			if($skip_product){
				continue;
			}
			
			if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
				$this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'wf-shipping-dhl'), $ctr));
				continue;
			}
			if(isset($values['measured_weight']) && $values['measured_weight'] !=0 )
			{
				$weight = $values['measured_weight'];
			}
			else
			{
				$weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
			}
			
			if (!$weight) {
				$this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
				return;
			}
			$weight_pack->add_item($weight, $values['data'], $values['quantity']);
		}
		
		$pack   =   $weight_pack->pack_items();	 
		$errors =   $pack->get_errors();
        $to_ship  = array();
		if( !empty($errors) ){
			//do nothing
			return;
		} else {
			$boxes	  =   $pack->get_packed_boxes();
			$unpacked_items =   $pack->get_unpacked_items();
			
			$parcels	  =   array_merge( $boxes, $unpacked_items ); // merge items if unpacked are allowed
			$package_count  =   sizeof($parcels);
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
			
			$group_id = 1;
			foreach($parcels as $parcel){//pre($package);
                $insured_value		  =   0;
				$packed_products = array();
				if(!empty($parcel['items'])){
                    foreach($parcel['items'] as $item){						
                        $insured_value		  =   $insured_value+$item->get_price();

                    }
                }else{
                    if( isset($order_total) && $package_count){
                        $insured_value  =   $order_total/$package_count;
                    }
                }
                
				$packed_products	=   isset($parcel['items']) ? $parcel['items'] : $all_items;
				// Creating package request
				
				$package_total_weight   =   $parcel['weight'];
				
				$insurance_array = array(
					'Amount' => round($insured_value),
					'Currency' => get_woocommerce_currency()
				);
				//if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
					//$crate = 1 / $this->conversion_rate;
					//$insurance_array['Amount']	  = round($values['data']->get_price() * $crate, 2);
					//$insurance_array['Currency']	= $this->settings['dhl_currency_type'];
				//}
				$group = array(
					'GroupNumber' => $group_id,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => round($package_total_weight, 3),
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

	private function box_shipping($order) {
		if (!class_exists('WF_Boxpack')) {
			include_once 'class-wf-packing.php';
		}

		$boxpack = new WF_Boxpack();

		// Define boxes
		foreach ($this->boxes as $key => $box) {

			if (!$box['enabled']) {
				continue;
			}

			$newbox = $boxpack->add_box($box['length'], $box['width'], $box['height'], $box['box_weight'], $box['pack_type']);
			$newbox->set_inner_dimensions($box['inner_length'], $box['inner_width'], $box['inner_height']);

			if (!empty($box['id'])) {
				$newbox->set_id(current(explode(':', $box['id'])));
			}

			if ($box['max_weight']) {
				$newbox->set_max_weight($box['max_weight']);
			}
			if ($box['pack_type']) {
				$newbox->set_packtype($box['pack_type']);
			}
		}


		// Add items
		foreach ($order['contents'] as $item_id => $values) {

			if (!$values['data']->needs_shipping()) {
				$this->debug(sprintf(__('Product # is virtual. Skipping.', 'wf-shipping-dhl'), $item_id), 'error');
				continue;
			}

			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $order['contents']);
			if($skip_product){
				continue;
			}

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height( $values['data'] ) && wf_get_product_width( $values['data'] ) && wf_get_product_weight( $values['data'] )) {

				$dimensions = array( wf_get_product_length( $values['data'] ), wf_get_product_height( $values['data'] ), wf_get_product_width( $values['data'] ));

				for ($i = 0; $i < $values['quantity']; $i++) {
				
						
					if(isset($values['measured_weight']) && $values['measured_weight'] !=0 )
					{
						$weight = $values['measured_weight'];
					}
					else
					{
						$weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
					}		
					
                    $boxpack->add_item(
                        wc_get_dimension($dimensions[2], $this->dimension_unit), wc_get_dimension($dimensions[1], $this->dimension_unit), wc_get_dimension($dimensions[0], $this->dimension_unit), $weight, $values['data']->get_price(), array(
                    'data' => $values['data'])
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
				$this->debug('Unpacked Item '.$package->id."<br>");
			} else {
				$this->debug('Packed ' . $package->id."<br>");
			}

			$dimensions = array($package->length, $package->width, $package->height);

			sort($dimensions);

			$insurance_array = array(
				'Amount' => round($package->value),
				'Currency' => get_woocommerce_currency()
			);
			//if ($this->settings['insure_contents'] == 'yes' && !empty($this->conversion_rate)) {
				//$crate = 1 / $this->conversion_rate;
				//$insurance_array['Amount'] = round($package->value * $crate, 2);
				//$insurance_array['Currency'] = $this->settings['dhl_currency_type'];
			//}

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
		$commercial_invoice = new wf_dhl_commercial_invoice();

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
		if (!empty($packages)) {
			$total_weight = 0;
			$total_value = 0;
			
			$currency = get_woocommerce_currency();
			$weight_unit  = $this->weight_unit;
			$total_units =0;
			$i=0;
			$pre_product_id = '';
			$net_weight = 0;
			$pre_package = 0;
			
			if($this->packing_method == 'per_item')
			{
			foreach($packages as $package)
			{
				
				foreach($package as $i => $parcel)
				{
					if(!isset($parcel['package_type']))
					{
					$products_details[$i]['weight'] 		= number_format( $parcel['Weight']['Value'], 3 );
					$product_id = $parcel['packed_products'][0]->get_id();
					$product 		= wc_get_product( $product_id );
					if (!$product->needs_shipping()) {
						continue;
					}
					$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
					$post_id 	= $par_id ? $par_id : wf_get_product_id($product);
                    $wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
					$manufacture 	= get_post_meta( $post_id, '_wf_manufacture_country', 1); //this works for variable 

					$products_details[$i]['quantity'] 		= 1;
					$products_details[$i]['description'] 	= $product->get_title();
					$products_details[$i]['price'] 			= number_format( $product->get_price(), 2 );
					$products_details[$i]['total'] 			= (float)$product->get_price();
					$products_details[$i]['hs'] 			= $wf_hs_code;
					$products_details[$i]['weight_unit'] 	= $weight_unit;
					$products_details[$i]['manufacture'] 	= $manufacture;
					$products_details[$i]['no_package'] 	= 1;

					$products_details[$i] = apply_filters( 'wf_dhl_commecial_invoice_product_details', $products_details[$i], $product );
					
					$total_value += $products_details[$i]['total'];
					}
				}
				
			}
			}
			else{
			$items = $this->order->get_items();
			if(!empty($items))
			{
				foreach ($items as $item_id => $orderItem) {
					$item_id 		= $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'];
					$product 		= wc_get_product( $item_id );
					if (!$product->needs_shipping()) {
						continue;
					}
					$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
					$post_id 	= $par_id ? $par_id : wf_get_product_id($product);

					$wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
					$manufacture 	= get_post_meta( $post_id, '_wf_manufacture_country', 1); //this works for variable 

					$products_details[$i]['quantity'] 		= $orderItem['qty'];
					$products_details[$i]['description'] 	= $product->get_title();
					$products_details[$i]['weight'] 		= number_format( wc_get_weight( $product->get_weight(), $weight_unit ), 2 );
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
			}
		}
		if(!empty($products_details))
		{
			foreach($products_details as $product){
				$i=0;
				foreach($packages as $package)
				{
					$total_units 	+= $product['quantity'];
					$net_weight 	+= number_format($product['weight'], 3);
					$total_weight 	+= number_format($product['weight'], 3)*$product['quantity'];
				}
				$i++;
			}
		}
		
		$package_details = array(
			'value' 		=> number_format( $total_value, 2 ), //total product price sum
			'diccount'	 	=> number_format( $this->order->get_total_discount(), 2 ),
			'other' 		=> '0.00',
			'total' 		=> number_format( $total_value - (float)$this->order->get_total_discount(), 2 ),
			'net_weight' 	=> number_format( $net_weight,3 ),
			'gross_weight' 	=> number_format( $total_weight, 3 ),
			'currency' 		=>$currency,
			'weight_unit' 	=>$weight_unit,
			'total_unit' 	=>$total_units,
			'total_package' =>count($packages[0]),
			'originator' 	=> $shipper['company_name'],
		);

		$exta_details = array(
			'Terms Of Trade' 	=> ($this->dutypayment_type == 'S') ? 'DDP' : ( ($this->dutypayment_type == 'R') ? 'DAP' : '' ),
			'Terms Of Payment' 	=> '',
			'Contract number' 	=> '',
			'Contract Date' 	=> '',
		);
		
		$designated_broker = array(
		  'dutypayment_type' 	=> isset($this->settings['dutypayment_type']) ? $this->settings['dutypayment_type'] : '',
		  'dutyaccount_number' 	=> isset($this->settings['dutyaccount_number']) ? $this->settings['dutyaccount_number'] : '',
		);
		
		$commercial_invoice->get_package_total( $total_units );
		$commercial_invoice->init( 2 );
		$commercial_invoice->addShippingToAddress( apply_filters( 'wf_dhl_commecial_invoice_destination_address', $toaddress, $packages, $this->order ) );
		$commercial_invoice->addShippingFromAddress( apply_filters( 'wf_dhl_commecial_invoice_source_address', $fromaddress, $packages, $this->order ) );
		$commercial_invoice->designated_broker( apply_filters( 'wf_dhl_commecial_invoice_designated_broker', $designated_broker, $packages, $this->order ) );
		$commercial_invoice->addExtraDetails( apply_filters( 'wf_dhl_commecial_invoice_exta_details', $exta_details, $packages, $this->order ) );
		$commercial_invoice->addProductDetails( $products_details );
		$commercial_invoice->addPackageDetails( apply_filters( 'wf_dhl_commecial_invoice_package_details', $package_details, $packages, $this->order ) );
		return base64_encode( $commercial_invoice->Output('S') );			
	}
	private function generate_return_commercial_invoice( $packages, $shipper, $toaddress,$selected_items=""){
	
		include_once("fpdf/wf-dhl-commercial-invoice-template.php");
		$commercial_invoice = new wf_dhl_commercial_invoice();
		
		$toaddress = array(
				'first_name'	=> $toaddress['company_name'],
				'last_name'	 => $toaddress['division'],
				'company'	   => $toaddress['company_name'],
				'address_1'	 => $toaddress['address_line'],
				'address_2'	 => $toaddress['address_line2'],
				'city'		  => $toaddress['city'],
				'postcode'	  => $toaddress['postal_code'],
				'country'	   => $toaddress['country_name'],
				'email'		 => $toaddress['contact_email'],
				'phone'		 => $toaddress['contact_phone_number'],
			);


		$fromaddress =array();
		$fromaddress['sender_name']			 	= $shipper['first_name'].' '.$shipper['last_name'];
		$fromaddress['sender_address_line1'] 	= $shipper['address_1'];
		$fromaddress['sender_address_line2'] 	= $shipper['address_2'];
		$fromaddress['sender_city']			 	= $shipper['city'];
		$fromaddress['sender_country']		 	= $shipper['country'];
		$fromaddress['sender_postalcode']	 	= $shipper['postcode'];
		$fromaddress['phone_number']			= $shipper['phone'];
		$fromaddress['sender_company']			= $shipper['company'];
		$fromaddress['sender_email']			= $shipper['email'];
		$fromaddress['sender_state_code']		= '';
		
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
				if(empty($selected_items))
				{
				$product 		= wc_get_product( $item_id );
				if (!$product->needs_shipping()) {
					continue;
				}
				$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
				$post_id 	= $par_id ? $par_id : wf_get_product_id($product);

				$wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
				$manufacture 	= get_post_meta( $post_id, '_wf_manufacture_country', 1); //this works for variable 
				
				
				$products_details[$i]['quantity'] 		=  $orderItem['qty'];
					
				$products_details[$i]['description'] 	= $product->get_title();
				$products_details[$i]['weight'] 		= number_format( wc_get_weight( $product->get_weight(), $weight_unit ), 2 );
				$products_details[$i]['price'] 			= number_format( $product->get_price(), 2 );
				$products_details[$i]['total'] 			= (float)$product->get_price() * (int)$products_details[$i]['quantity'];
				$products_details[$i]['hs'] 			= $wf_hs_code;
				$products_details[$i]['weight_unit'] 	= $weight_unit;
				$products_details[$i]['manufacture'] 	= $manufacture;
				$products_details[$i]['no_package'] 	= 1;

				$products_details[$i] = apply_filters( 'wf_dhl_commecial_invoice_product_details', $products_details[$i], $product );
				
				$total_value += $products_details[$i]['total'];
				$i++;
				}
				else 
				{
					$product_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] ;
					foreach ($selected_items as $key => $value) {
					if(in_array($product_id,$value))
					{
						$product 		= wc_get_product( $item_id );
				if (!$product->needs_shipping()) {
					continue;
				}
				$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
				$post_id 	= $par_id ? $par_id : wf_get_product_id($product);

				$wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
				$manufacture 	= get_post_meta( $post_id, '_wf_manufacture_country', 1); //this works for variable 
				$products_details[$i]['quantity'] 		=  $value[1];
				
				$products_details[$i]['description'] 	= $product->get_title();
				$products_details[$i]['weight'] 		= number_format( wc_get_weight( $product->get_weight(), $weight_unit ), 2 );
				$products_details[$i]['price'] 			= number_format( $product->get_price(), 2 );
				$products_details[$i]['total'] 			= (float)$product->get_price() * (int)$products_details[$i]['quantity'];
				$products_details[$i]['hs'] 			= $wf_hs_code;
				$products_details[$i]['weight_unit'] 	= $weight_unit;
				$products_details[$i]['manufacture'] 	= $manufacture;
				$products_details[$i]['no_package'] 	= 1;

				$products_details[$i] = apply_filters( 'wf_dhl_commecial_invoice_product_details', $products_details[$i], $product );
				
				$total_value += $products_details[$i]['total'];
				$i++;
					}
				}
				}
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
			'originator' 	=> $shipper['company'],
		);

		$exta_details = array(
			'Terms Of Trade' 	=> ($this->dutypayment_type == 'S') ? 'DDP' : ( ($this->dutypayment_type == 'R') ? 'DAP' : '' ),
			'Terms Of Payment' 	=> '',
			'Contract number' 	=> '',
			'Contract Date' 	=> '',
		);
		
		$designated_broker = array(
		  'dutypayment_type' 	=> $this->settings['dutypayment_type'],
		  'dutyaccount_number' 	=> $this->settings['dutyaccount_number'],
		);
		
		$commercial_invoice->get_package_total( $total_units );
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
		//$mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);
		$mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));
		$destination_city = strtoupper($package['destination']['city']);
		$destination_postcode = strtoupper($package['destination']['postcode']);
		$destination_country_name = isset(WC()->countries->countries[$package['destination']['country']]) ? WC()->countries->countries[$package['destination']['country']] : $package['destination']['country'];
		$consignee_name = wf_get_order_shipping_first_name($this->order) . ' ' . wf_get_order_shipping_last_name($this->order);
		$order_subtotal = wc_format_decimal($this->order->get_subtotal($this->order), 2);
		$order_currency = wf_get_order_currency($this->order);
		$cod_order_total	=   wc_format_decimal($this->order->get_total());
		$is_dutiable = ($package['destination']['country'] == $this->origin_country || wf_dhl_is_eu_country($this->origin_country, $package['destination']['country'])) ? "N" : "Y";
		$dutiable_content = $is_dutiable == "Y" ? "<Dutiable><DeclaredValue>{$order_subtotal}</DeclaredValue><DeclaredCurrency>{$order_currency}</DeclaredCurrency>" : "";
		if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
			if ($this->dutypayment_type == "S") {
				$dutiable_content .= "<TermsOfTrade>DDP</TermsOfTrade>";
			} elseif ($this->dutypayment_type == "R") {
				$dutiable_content .= "<TermsOfTrade>DAP</TermsOfTrade>";
			}
		}

		$dutiable_content .= $is_dutiable == "Y" ? "</Dutiable>" : "";
		$shipment_details = $this->wf_get_shipment_details($dhl_packages, $is_dutiable,'shipment');
		$origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;
			
		$special_service = "";

		//signature option
		$signature_option = $this->get_package_signature( $package['contents'] );
		if( $signature_option != 'SX' ){ // SX for no signature required.
			$special_service .= "<SpecialService><SpecialServiceType>".$signature_option."</SpecialServiceType></SpecialService>";
		}
		
		if(isset($_GET['cash_on_delivery']) && $_GET['cash_on_delivery'] === 'true'){
			$special_service .= "<SpecialService><SpecialServiceType>KB</SpecialServiceType><ChargeValue>".$cod_order_total."</ChargeValue><CurrencyCode>".$order_currency."</CurrencyCode></SpecialService>";
		}
		
		$customer_insurance = true;
		
		$shipping_method_datas = $this->order->get_shipping_methods();
		
		if(!empty($shipping_method_datas))
		{
                    $shipping_method_datas = array_shift($shipping_method_datas);
                    if(!empty($shipping_method_datas))
                    {
                        if(WC()->version > '2.7'){
                            $shipping_method_meta_datas = $shipping_method_datas->get_meta_data();
                        }else{
                            $shipping_method_meta_datas = $shipping_method_datas;
                        }
			if(!empty($shipping_method_meta_datas))
			{
				foreach($shipping_method_meta_datas as $each_meta)
					{
						$getting_shipping_data = (array)$each_meta;
						if(isset($getting_shipping_data['key']) && $getting_shipping_data['key'] == 'insurance' && $getting_shipping_data['value'] == 'no')
						{
							$customer_insurance = false;
						}
					}
                        }
                    }
		}
		
		if ($this->insure_contents && $customer_insurance)
			$special_service .= "<SpecialService><SpecialServiceType>II</SpecialServiceType></SpecialService>";

		if ($is_dutiable == "Y" && $this->dutypayment_type == "S") {
			$special_service .= "<SpecialService><SpecialServiceType>DD</SpecialServiceType></SpecialService>";
		} elseif ($is_dutiable == "Y" && $this->dutypayment_type == "R") {
			$special_service .= "<SpecialService><SpecialServiceType>DS</SpecialServiceType></SpecialService>";
		}
		if(isset($_GET['sat_delivery']) && $_GET['sat_delivery'] === 'true'){
			$sat_delivery_val = ($is_dutiable != 'Y') ? 'AG' : 'AA';
			$special_service .= "<SpecialService><SpecialServiceType>$sat_delivery_val</SpecialServiceType></SpecialService>";
		}
		

		$shipping_company = wf_get_order_shipping_company( $this->order );
		$consignee_companyname = substr(htmlspecialchars(!empty( $shipping_company ) ? $shipping_company : $consignee_name), 0, 35) ;

		$dutypayment_type_accountnumber = "";
		if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
			$dutypayment_type_accountnumber = "<DutyPaymentType>{$this->dutypayment_type}</DutyPaymentType>";
			if (!empty($this->dutyaccount_number) && $this->dutypayment_type == 'T') {
				$dutypayment_type_accountnumber .= "<DutyAccountNumber>{$this->dutyaccount_number}</DutyAccountNumber>";
			}
		}
		
		$shipper	=   array(
			'shipper_id'			=>  $this->account_number,
			'company_name'		  =>  str_replace("&",'&amp;', $this->freight_shipper_company_name),
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
			if(isset($package['origin']['country']) && !empty($package['origin']['country']) && isset($this->settings['vendor_check']) && $this->settings['vendor_check'] === 'yes'){
				$shipper['company_name']			=   str_replace("&",'&amp;',$package['origin']['company']);
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
				'company'	   => str_replace("&","&amp;",wf_get_order_shipping_company($this->order)),
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
		if( $this->plt && $is_dutiable == 'Y'){
			
			$special_service	.= "<SpecialService>
										<SpecialServiceType>WY</SpecialServiceType>
									</SpecialService>";
			
			$docImage		   =   "<DocImages>
										<DocImage>
											<Type>CIN</Type>
											<Image>$sample_base64_encoded_pdf</Image>
											<ImageFormat>PDF</ImageFormat>
										</DocImage>
									</DocImages>";
		}else{
				
			$this->non_plt_commercial_invoice = $sample_base64_encoded_pdf;
		
		}
		$address = $this->get_valid_address( wf_get_order_shipping_address_1($this->order), wf_get_order_shipping_address_2($this->order) );

		$destination_address = '<AddressLine>'.htmlspecialchars($address['valid_line1']).'</AddressLine>';
		if( !empty( $address['valid_line2'] ) )
			$destination_address .= '<AddressLine>'.htmlspecialchars($address['valid_line2']).'</AddressLine>';
		
		if( !empty( $address['valid_line3'] ) )
			$destination_address .= '<AddressLine>'.htmlspecialchars($address['valid_line3']).'</AddressLine>';
		
		$export_declaration = '';
		if( $is_dutiable == 'Y' ){

			$export_line_item = '';
			
			foreach ($package['contents'] as $i => $item){
				
				$export_line_item .= '<ExportLineItem>';
				$export_line_item .= '  <LineNumber>'.++$i.'</LineNumber>';
				$export_line_item .= '  <Quantity>'.$item['quantity'].'</Quantity>';
				$export_line_item .= '  <QuantityUnit>tens</QuantityUnit>'; //not sure about this value
				$export_line_item .= '  <Description>'.substr( htmlspecialchars( $item['data']->get_title() ), 0, 75 ).'</Description>';
				$export_line_item .= '  <Value>'.$item['data']->get_price().'</Value>';
				
				$par_id 	= wp_get_post_parent_id( wf_get_product_id($item['data']) );
				$post_id 	= $par_id ? $par_id : wf_get_product_id($item['data']);

				$wf_hs_code = get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
				
				if( !empty($wf_hs_code) ){
					$export_line_item .= '  <ScheduleB>'.$wf_hs_code.'</ScheduleB>';
				}
                                $xa_send_dhl_weight = $item['data']->get_weight();
								if($this->weight_unit == 'LBS'){
									if($xa_send_dhl_weight < 0.12){
										$xa_send_dhl_weight = 0.12;// 0.12 lbs, minimum product for DHL
									}else{
										$xa_send_dhl_weight = round((float)$xa_send_dhl_weight,2);
									}
								}else{
									if($xa_send_dhl_weight < 0.01){
										$xa_send_dhl_weight = 0.01;// 0.12 lbs, minimum product for DHL
									}else{
										$xa_send_dhl_weight = round((float)$xa_send_dhl_weight,2);
									}
								}
                                
                                $xa_send_dhl_weight = (string)$xa_send_dhl_weight;
                                $xa_send_dhl_weight = str_replace(',', '.', $xa_send_dhl_weight);
				$export_line_item .= '  <Weight><Weight>'.$xa_send_dhl_weight.'</Weight><WeightUnit>'.$this->product_weight_unit.'</WeightUnit></Weight>';
				
				$export_line_item .= '</ExportLineItem>';
			}
			$export_declaration = '<ExportDeclaration>' .$export_line_item. '</ExportDeclaration>';
		}

		$billing_phone = wf_get_order_billing_phone( $this->order );
		$billing_email = wf_get_order_billing_email( $this->order );
		$archive_bill_settings = isset($this->settings['request_archive_airway_label']) ? $this->settings['request_archive_airway_label'] : '';
		$number_of_bills = isset($this->settings['no_of_archive_bills']) ? $this->settings['no_of_archive_bills'] : '';
		$number_of_bills_xml='';
		$dhl_email_enable =  isset($this->settings['dhl_email_notification_service']) ? $this->settings['dhl_email_notification_service'] : '';
		$dhl_email_message =  isset($this->settings['dhl_email_notification_message']) ? $this->settings['dhl_email_notification_message']: '';
		$dhl_notification = '';

		$customer_logo_url =  isset($this->settings['customer_logo_url']) ? $this->settings['customer_logo_url'] : '';
		$customer_logo_xml ='';

		if(!empty($customer_logo_url) && @file_get_contents($customer_logo_url))
		{

			$type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
			$data = file_get_contents($customer_logo_url);
			$base64 = base64_encode($data);
			$customer_logo_xml = '<CustomerLogo><LogoImage>'.$base64.'</LogoImage><LogoImageFormat>'.strtoupper($type).'</LogoImageFormat></CustomerLogo>';
		}

		if($this->add_trackingpin_shipmentid == 'yes' && !empty($dhl_email_enable) && $dhl_email_enable === 'yes')
		{
			$dhl_notification = '<Notification><EmailAddress>'.$toaddress['email'].'</EmailAddress><Message>'.$dhl_email_message.'</Message></Notification>';
		}
		
		if(!empty($archive_bill_settings) && $archive_bill_settings === 'yes')
		{
			$request_archive_airway_bill = 'Y';
		}
		else
		{
			$request_archive_airway_bill = 'N';
		}
		
		if(empty($number_of_bills) && $request_archive_airway_bill === 'Y')
		{
			$number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
		}

		if(!empty($number_of_bills) && $request_archive_airway_bill === 'Y')
		{
			$number_of_bills_xml = '<NumberOfArchiveDoc>'.$number_of_bills.'</NumberOfArchiveDoc>';
		}



		$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req-6.0.xsd" schemaVersion="6.0">
	<Request>
		<ServiceHeader>
			<MessageTime>{$mailingDate}</MessageTime>
			<MessageReference>1234567890123456789012345678901</MessageReference>
			<SiteID>{$this->site_id}</SiteID>
			<Password>{$this->site_password}</Password>
		</ServiceHeader>
	</Request>
	<RegionCode>{$this->region_code}</RegionCode>
	<RequestedPickupTime>Y</RequestedPickupTime>
	<LanguageCode>en</LanguageCode>
	<PiecesEnabled>Y</PiecesEnabled>
	<Billing>
		<ShipperAccountNumber>{$this->account_number}</ShipperAccountNumber>
		<ShippingPaymentType>S</ShippingPaymentType>
		<BillingAccountNumber>{$this->account_number}</BillingAccountNumber>
		{$dutypayment_type_accountnumber}
	</Billing>
	<Consignee>
		<CompanyName>{$consignee_companyname}</CompanyName>
		{$destination_address}
		<City>{$destination_city}</City>
		<PostalCode>{$destination_postcode}</PostalCode>
		<CountryCode>{$package['destination']['country']}</CountryCode>
		<CountryName>{$destination_country_name}</CountryName>
		<Contact>
			<PersonName>{$consignee_name}</PersonName>
			<PhoneNumber>{$billing_phone}</PhoneNumber>
			<Email>{$billing_email}</Email>
		</Contact>
	</Consignee>
	<Commodity>
		<CommodityCode>cc</CommodityCode>
		<CommodityName>cn</CommodityName>
	</Commodity>
	{$dutiable_content}
	{$export_declaration}
	<Reference>
		<ReferenceID>{$this->order->get_order_number()}</ReferenceID>	   
	</Reference>	
	{$shipment_details}
	<Shipper>
		<ShipperID>{$shipper['shipper_id']}</ShipperID>
		<CompanyName>{$shipper['company_name']}</CompanyName>
		<RegisteredAccount>{$shipper['registered_account']}</RegisteredAccount>
		<AddressLine>{$shipper['address_line']}</AddressLine>
		<AddressLine>{$shipper['address_line2']}</AddressLine>
		<City>{$shipper['city']}</City>
		<Division>{$shipper['division']}</Division>
		<PostalCode>{$shipper['postal_code']}</PostalCode>
		<CountryCode>{$shipper['country_code']}</CountryCode>
		<CountryName>{$shipper['country_name']}</CountryName>
		<Contact>
			<PersonName>{$shipper['contact_person_name']}</PersonName>
			<PhoneNumber>{$shipper['contact_phone_number']}</PhoneNumber>
			<Email>{$shipper['contact_email']}</Email>
		</Contact>
	</Shipper>
	{$special_service}
	{$dhl_notification}
	{$docImage}
	<LabelImageFormat>{$this->image_type}</LabelImageFormat> 
	<RequestArchiveDoc>{$request_archive_airway_bill}</RequestArchiveDoc>
	{$number_of_bills_xml}
	{$RequestArchiveDoc}
	<Label><HideAccount>Y</HideAccount><LabelTemplate>{$this->output_format}</LabelTemplate>{$customer_logo_xml}</Label>
</req:ShipmentRequest>
XML;
		$xmlRequest = apply_filters('wf_dhl_label_request', $xmlRequest, $this->order_id);

		return $xmlRequest;
	}
function wf_picup_request_handler($order)
{
	$output = $this->wf_picup_request($order);
	$result = wp_remote_post('https://xmlpitest-ea.dhl.com/XMLShippingServlet', array(
			'method' => 'POST',
			'timeout' => 70,
			'sslverify' => 0,
			//'headers'		  => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
			'body' => $output
				)
	);
	
	$this->debug('DHL REQUEST: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($output, true), ENT_IGNORE) . '</pre>');
	$this->debug('DHL RESPONSE: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($result, true), ENT_IGNORE) . '</pre>');
	

		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			$this->debug('DHL WP ERROR: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($error_message), true) . '</pre>');
		}
		elseif (is_array($result) && !empty($result['body'])) {
			$result = $result['body'];
		} else {
			$result = '';
		}
		$order_id = wf_get_order_id($order);
		libxml_use_internal_errors(true);
		$result = utf8_encode($result);
		$xml = simplexml_load_string($result);
		if(isset($xml->Note->ActionNote) && $xml->Note->ActionNote == 'Success')
		{
			$ConfirmationNumber =isset($xml->ConfirmationNumber) ? (String)  $xml->ConfirmationNumber : '';
			$NextPickupDate =isset($xml->NextPickupDate) ? (String) $xml->NextPickupDate : '';
			$ReadyByTime = isset($xml->ReadyByTime) ? (String) $xml->ReadyByTime : '';
			update_post_meta($order_id,'_wf_dhl_picup_shipment',array($ConfirmationNumber,$NextPickupDate,$ReadyByTime));
			update_post_meta($order_id,'_wf_dhl_picup_shipment_error','');
		}
		else if(isset($xml->Response->Status->ActionStatus) && $xml->Response->Status->ActionStatus == 'Error')
		{
			if ($xml->Response->Status && (string) $xml->Response->Status->Condition->ConditionCode != '')
			{
				$error_msg = ((string) $xml->Response->Status->Condition->ConditionCode) . ' : ' . ((string) $xml->Response->Status->Condition->ConditionData);
				update_post_meta($order_id,'_wf_dhl_picup_shipment_error',$error_msg );
				update_post_meta($order_id,'_wf_dhl_picup_shipment',array());
			}
			
		}
		if ($this->debug) {
			echo '<a href="' . admin_url('/post.php?post=' . $order_id . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
			//For the debug information to display in the page
			die();
		}
		
}
private function wf_picup_request($order)
{
	$order_id			= wf_get_order_id($order);
	$airwaybill_number	= get_post_meta($order_id, 'wf_woo_dhl_shipmentId','');
	$dhl_packages		= get_post_meta($order_id,'wf_woo_dhl_package_'.$airwaybill_number[0],array());
	$weight				= 0;
	$pieces				= 0;
	if ($dhl_packages) {
			foreach ($dhl_packages[0] as $key => $parcel) {
				foreach ($parcel as $key => $value) {
					if (isset($value['Weight'])) {
						$weight = $weight + $value['Weight']['Value'];
						$pieces = $pieces +1;
					}
					if (isset($value[0]['Weight'])) {
						$weight = $weight + $value[0]['Weight']['Value'];
						$pieces = $pieces +1;
					}
					
				}
				
			}
		}
	if(!empty($airwaybill_number) && !empty($dhl_packages))
	{
		//$mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);
		$mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));
		
		$shipper	=   array(
			'shipper_id'			=>  $this->account_number,
			'company_name'		  =>  str_replace("&",'&amp;', $this->freight_shipper_company_name),
			'registered_account'	=>  $this->account_number,
			'address_line'		  =>  $this->freight_shipper_street,
			'address_line2'		 =>  $this->freight_shipper_street_2,
			'city'				  =>  $this->freight_shipper_city,
			'division'			  =>  $this->freight_shipper_state,
			'division_code'		 =>  $this->freight_shipper_state,
			'postal_code'		   =>  $this->origin,
			'country_code'		  =>  $this->origin_country,
			'contact_person_name'   =>  $this->freight_shipper_person_name,
			'contact_phone_number'  =>  $this->freight_shipper_phone_number,
			'contact_email'		 =>  $this->shipper_email,
		);
		
		$picup_date = ($this->pickupdate <= 1) ? (date('Y-m-d', strtotime("+1 day", current_time('timestamp')))) : (date('Y-m-d', strtotime("+".$this->pickupdate." days", current_time('timestamp'))));
		$xmlRequest = <<<XML
<req:BookPURequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://www.dhl.com book-pickup-global-req.xsd" schemaVersion="1.0">
    <Request>
        <ServiceHeader>
            <MessageTime>{$mailingDate}</MessageTime>
            <MessageReference>1234567890123456789012345678901</MessageReference>
            <SiteID>{$this->site_id}</SiteID>
            <Password>{$this->site_password}</Password>
        </ServiceHeader>
   </Request>
   <RegionCode>{$this->region_code}</RegionCode>
    <Requestor>
        <AccountType>D</AccountType>
        <AccountNumber>{$this->account_number}</AccountNumber>
        <RequestorContact>
            <PersonName>{$this->freight_shipper_person_name}</PersonName>
            <Phone>{$this->freight_shipper_phone_number}</Phone>
        </RequestorContact>
        <CompanyName>{$shipper['company_name']}</CompanyName>
    </Requestor>
    <Place>
        <LocationType>B</LocationType>
        <CompanyName>{$this->freight_shipper_company_name}</CompanyName>
        <Address1>{$this->freight_shipper_street}</Address1>
        <Address2>{$this->freight_shipper_street_2}</Address2>
        <PackageLocation>{$this->freight_shipper_city}</PackageLocation>
        <City>{$this->freight_shipper_city}</City>
        <DivisionName>{$this->freight_shipper_state}</DivisionName>	
        <CountryCode>{$this->origin_country}</CountryCode>
		<PostalCode>{$this->origin}</PostalCode>
        <Suburb>England</Suburb>
    </Place>
    <Pickup>
		<PickupDate>{$picup_date}</PickupDate>
        <ReadyByTime>{$this->pickupfrom}</ReadyByTime>
        <CloseTime>{$this->pickupto}</CloseTime>
        <Pieces>{$pieces}</Pieces>
        <weight>
            <Weight>{$weight}</Weight>
            <WeightUnit>{$this->product_weight_unit}</WeightUnit>
        </weight>
    </Pickup>
    <PickupContact>
        <PersonName>{$this->pickupperson}</PersonName>
        <Phone>{$this->pickupcontct}</Phone>
    </PickupContact>
	<ShipmentDetails>
		<AccountType>D</AccountType>
		<AccountNumber>{$this->user_settings['account_number']}</AccountNumber>
		<AWBNumber>{$airwaybill_number[0]}</AWBNumber>
		<NumberOfPieces>{$pieces}</NumberOfPieces>
		<Weight>{$weight}</Weight>
		<WeightUnit>{$this->labelapi_weight_unit}</WeightUnit>
		<DoorTo>DD</DoorTo>
		<DimensionUnit>{$this->labelapi_dimension_unit}</DimensionUnit>
		<Pieces>
			<Weight>{$weight}</Weight>
		</Pieces>
	</ShipmentDetails>
</req:BookPURequest>
XML;
	
	return $xmlRequest;
	}
	else
	{
		return false;
	}
}


	private function get_dhl_shipping_return_label_requests($dhl_packages, $package) {
		
		// Time is modified to avoid date diff with server.
		//$mailingDate = date('Y-m-d', time() + $this->timezone_offset) . 'T' . date('H:i:s', time() + $this->timezone_offset);
                $mailingDate = date('Y-m-d', current_time('timestamp')) . 'T' . date('H:i:s', current_time('timestamp'));
		$destination_city = strtoupper($package['destination']['city']);
		$destination_postcode = strtoupper($package['destination']['postcode']);
		$destination_country_name = isset(WC()->countries->countries[$package['destination']['country']]) ? WC()->countries->countries[$package['destination']['country']] : $package['destination']['country'];
		$consignee_name = wf_get_order_shipping_first_name($this->order) . ' ' . wf_get_order_shipping_last_name($this->order);
		$order_subtotal = wc_format_decimal($this->order->get_subtotal($this->order), 2);
		$order_currency = wf_get_order_currency($this->order);

		$is_dutiable = ($package['destination']['country'] == $this->origin_country || wf_dhl_is_eu_country($this->origin_country, $package['destination']['country'])) ? "N" : "Y";
		$dutiable_content = $is_dutiable == "Y" ? "<Dutiable><DeclaredValue>{$order_subtotal}</DeclaredValue><DeclaredCurrency>{$order_currency}</DeclaredCurrency>" : "";
		if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
			if ($this->dutypayment_type == "S") {
				$dutiable_content .= "<TermsOfTrade>DDP</TermsOfTrade>";
			} elseif ($this->dutypayment_type == "R") {
				$dutiable_content .= "<TermsOfTrade>DAP</TermsOfTrade>";
			}
		}

		$archive_bill_settings = isset($this->settings['request_archive_airway_label']) ? $this->settings['request_archive_airway_label'] : '';
		$number_of_bills = isset($this->settings['no_of_archive_bills']) ? $this->settings['no_of_archive_bills'] : '';

		$dutiable_content .= $is_dutiable == "Y" ? "</Dutiable>" : "";
		$shipment_details = $this->wf_get_shipment_details($dhl_packages, $is_dutiable);
		$origin_country_name = isset(WC()->countries->countries[$this->origin_country]) ? WC()->countries->countries[$this->origin_country] : $this->origin_country;

		$special_service = "";
		$shipping_company = wf_get_order_shipping_company( $this->order );
		$consignee_companyname = htmlspecialchars(!empty( $shipping_company ) ? $shipping_company : '--');

		$dutypayment_type_accountnumber = "";
		if (!empty($this->dutypayment_type) && $is_dutiable == "Y") {
			$dutypayment_type_accountnumber = "<DutyPaymentType>{$this->dutypayment_type}</DutyPaymentType>";
			if (!empty($this->dutyaccount_number) && $this->dutypayment_type == 'T') {
				$dutypayment_type_accountnumber .= "<DutyAccountNumber>{$this->dutyaccount_number}</DutyAccountNumber>";
			}
		}
		
		$shipper	=   array(
			'shipper_id'			=>  $this->return_label_acc_number,
			'company_name'		  =>  str_replace("&",'&amp;',$this->freight_shipper_company_name),
			'registered_account'	=>  $this->return_label_acc_number,
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
				$shipper['company_name']			=   str_replace("&",'&amp;',$package['origin']['company']);
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
				'company'	   => str_replace("&",'&amp;',wf_get_order_shipping_company($this->order)),
				'address_1'	 => wf_get_order_shipping_address_1( $this->order),
				'address_2'	 => wf_get_order_shipping_address_2($this->order),
				'city'		  => $destination_city,
				'postcode'	  => $destination_postcode,
				'country'	   => $destination_country_name,
				'email'		 => wf_get_order_billing_email($this->order),
				'phone'		 => wf_get_order_billing_phone($this->order),
			);
		$order_id = (WC()->version > '2.7.0') ? $this->order->get_id() : $this->order->ID;
		
		$check_items = get_post_meta($order_id,'_wf_dhl_stored_return_products',true);
		if(!empty($check_items))
		{
			$check_items = explode(',',$check_items);

			$selected_items = array();
			foreach ( $check_items as $k => $v )
			{
			  $selected_items[] = explode( '|', $v );
			}
		}else
		{
			$selected_items = '';
		}
		
		$special_service .= "<SpecialService><SpecialServiceType>PT</SpecialServiceType></SpecialService>";

		$sample_base64_encoded_pdf = $this->generate_return_commercial_invoice( $dhl_packages, $toaddress, $shipper, $selected_items);
		$RequestArchiveDoc = ''; $docImage = '';	
		if( $this->plt && $is_dutiable == 'Y'){
			
			$special_service	.= "<SpecialService>
										<SpecialServiceType>WY</SpecialServiceType>
									</SpecialService>";
			
			$docImage		   =   "<DocImages>
										<DocImage>
											<Type>CIN</Type>
											<Image>$sample_base64_encoded_pdf</Image>
											<ImageFormat>PDF</ImageFormat>
										</DocImage>
									</DocImages>";
		}else{
				
			$this->non_plt_commercial_invoice = $sample_base64_encoded_pdf;
		
		}
		$address = $this->get_valid_address( wf_get_order_shipping_address_1($this->order), wf_get_order_shipping_address_2($this->order) );

		$destination_address = '<AddressLine>'.htmlspecialchars($address['valid_line1']).'</AddressLine>';
		if( !empty( $address['valid_line2'] ) )
			$destination_address .= '<AddressLine>'.htmlspecialchars($address['valid_line2']).'</AddressLine>';
		
		if( !empty( $address['valid_line3'] ) )
			$destination_address .= '<AddressLine>'.htmlspecialchars($address['valid_line3']).'</AddressLine>';
		
		$export_declaration = '';
		if( $is_dutiable == 'Y' ){

			$export_line_item = '';
			
			foreach ($package['contents'] as $i => $item){
				
				$export_line_item .= '<ExportLineItem>';
				$export_line_item .= '  <LineNumber>'.++$i.'</LineNumber>';
				$export_line_item .= '  <Quantity>'.$item['quantity'].'</Quantity>';
				$export_line_item .= '  <QuantityUnit>tens</QuantityUnit>'; //not sure about this value
				$export_line_item .= '  <Description>'.substr( htmlspecialchars( $item['data']->get_title() ), 0, 75 ).'</Description>';
				$export_line_item .= '  <Value>'.$item['data']->get_price().'</Value>';
				
				
				$par_id 	= wp_get_post_parent_id( wf_get_product_id($item['data']) );
				$post_id 	= $par_id ? $par_id : wf_get_product_id($item['data']);

				$wf_hs_code = get_post_meta( $post_id, '_wf_hs_code', 1); //this works for variable product also
				
				if( !empty($wf_hs_code) ){
					$export_line_item .= '  <ScheduleB>'.$wf_hs_code.'</ScheduleB>';
				}
				
				$export_line_item .= '  <Weight><Weight>'.$item['data']->get_weight().'</Weight><WeightUnit>'.$this->product_weight_unit.'</WeightUnit></Weight>';
				
				$export_line_item .= '</ExportLineItem>';
			}
			$export_declaration = '<ExportDeclaration>' .$export_line_item. '</ExportDeclaration>';
		}
		$number_of_bills_xml = '';
		$billing_phone = wf_get_order_billing_phone( $this->order );
		$billing_email = wf_get_order_billing_email( $this->order );
		
		$dhl_email_enable =  $this->settings['dhl_email_notification_service'];
		$dhl_email_message =  $this->settings['dhl_email_notification_message'];
		$dhl_notification = '';

		$customer_logo_url =  $this->settings['customer_logo_url'];
		$customer_logo_xml ='';
		if(!empty($archive_bill_settings) && $archive_bill_settings === 'yes')
		{
			$request_archive_airway_bill = 'Y';
		}
		else
		{
			$request_archive_airway_bill = 'N';
		}
		
		if(empty($number_of_bills) && $request_archive_airway_bill === 'Y')
		{
			$number_of_bills_xml = '<NumberOfArchiveDoc>1</NumberOfArchiveDoc>';
		}

		if(!empty($number_of_bills) && $request_archive_airway_bill === 'Y')
		{
			$number_of_bills_xml = '<NumberOfArchiveDoc>'.$number_of_bills.'</NumberOfArchiveDoc>';
		}
		if(!empty($customer_logo_url) && @file_get_contents($customer_logo_url))
		{

			$type = pathinfo($customer_logo_url, PATHINFO_EXTENSION);
			$data = file_get_contents($customer_logo_url);
			$base64 = base64_encode($data);
			$customer_logo_xml = '<CustomerLogo><LogoImage>'.$base64.'</LogoImage><LogoImageFormat>'.strtoupper($type).'</LogoImageFormat></CustomerLogo>';
		}

		$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req-6.0.xsd" schemaVersion="6.0">
	<Request>
		<ServiceHeader>
			<MessageTime>{$mailingDate}</MessageTime>
			<MessageReference>order number of package is {$order_id}</MessageReference>
			<SiteID>{$this->site_id}</SiteID>
			<Password>{$this->site_password}</Password>
		</ServiceHeader>
	</Request>
	<RegionCode>{$this->region_code}</RegionCode>
	<RequestedPickupTime>Y</RequestedPickupTime>
	<LanguageCode>en</LanguageCode>
	<PiecesEnabled>Y</PiecesEnabled>
	<Billing>
		<ShipperAccountNumber>{$this->return_label_acc_number}</ShipperAccountNumber>
		<ShippingPaymentType>S</ShippingPaymentType>
		<BillingAccountNumber>{$this->return_label_acc_number}</BillingAccountNumber>
		{$dutypayment_type_accountnumber}
	</Billing>
	<Consignee>
		<CompanyName>{$shipper['company_name']}</CompanyName>
		<AddressLine>{$shipper['address_line']}</AddressLine>
		<AddressLine>{$shipper['address_line2']}</AddressLine>
		<City>{$shipper['city']}</City>
		<Division>{$shipper['division']}</Division>
		<PostalCode>{$shipper['postal_code']}</PostalCode>
		<CountryCode>{$shipper['country_code']}</CountryCode>
		<CountryName>{$shipper['country_name']}</CountryName>
		<Contact>
			<PersonName>{$shipper['contact_person_name']}</PersonName>
			<PhoneNumber>{$shipper['contact_phone_number']}</PhoneNumber>
			<Email>{$shipper['contact_email']}</Email>
		</Contact>
	</Consignee>
	{$dutiable_content}
	{$shipment_details}
	<Shipper>
		<ShipperID>{$shipper['shipper_id']}</ShipperID>
		<CompanyName>{$consignee_companyname}</CompanyName>
		<RegisteredAccount>{$shipper['registered_account']}</RegisteredAccount>
		{$destination_address}
		<City>{$destination_city}</City>
		<PostalCode>{$destination_postcode}</PostalCode>
		<CountryCode>{$package['destination']['country']}</CountryCode>
		<CountryName>{$destination_country_name}</CountryName>
		<Contact>
			<PersonName>{$consignee_name}</PersonName>
			<PhoneNumber>{$billing_phone}</PhoneNumber>
			<Email>{$shipper['contact_email']}</Email>
		</Contact>
	</Shipper>
        {$special_service}
	{$docImage}
	<LabelImageFormat>{$this->image_type}</LabelImageFormat>
	<RequestArchiveDoc>{$request_archive_airway_bill}</RequestArchiveDoc>
	{$number_of_bills_xml}
	{$RequestArchiveDoc} 
	<Label><HideAccount>Y</HideAccount><LabelTemplate>{$this->output_format}</LabelTemplate>{$customer_logo_xml}</Label>
	</req:ShipmentRequest>
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

	private function wf_get_shipment_details($dhl_packages, $is_dutiable = 'N', $check_return = 'return') {
		$pieces = "";
		$total_packages = 0;
		$total_weight = 0;
		$total_value = 0;
		
		if ($dhl_packages) {
			foreach ($dhl_packages as $group_key => $package_group) {
				foreach ($package_group as $key => $parcel) {
					$index = $key + 1;
					$total_packages += $parcel['GroupPackageCount'];
						if($this->weight_unit == 'LBS'){
							if($parcel['Weight']['Value'] < 0.12){
								$parcel['Weight']['Value'] = 0.12;
							}else{
								$parcel['Weight']['Value'] = round((float)$parcel['Weight']['Value'], 2); 
							}
						}else{
							if($parcel['Weight']['Value'] < 0.01){
								$parcel['Weight']['Value'] = 0.01;
							}else{
								$parcel['Weight']['Value'] = round((float)$parcel['Weight']['Value'], 2); 
							}
						}
						  
					$total_weight += $parcel['Weight']['Value'] * $parcel['GroupPackageCount'];
					$total_value += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];
					$pack_type = $this->wf_get_pack_type($parcel['packtype']); 
                                        $parcel['Weight']['Value'] = (string)$parcel['Weight']['Value'];
                                        $parcel['Weight']['Value'] = str_replace(',','.',$parcel['Weight']['Value']);
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
		$total_weight = (string)$total_weight;
		$total_weight = str_replace(',','.',$total_weight);
                
               
		// Time is modified to avoid date diff with server.
		//$mailingDate = date('Y-m-d', time() + $this->timezone_offset);
		$mailingDate = current_time('Y-m-d');
		$special_service_insurance = ($this->insure_contents && $check_return != 'return' && $total_value !=0) ? "<InsuredAmount>{$total_value}</InsuredAmount>" : "";
		$currency = ($this->insure_contents && !empty($this->insure_currency) && $check_return != 'return' && $total_value !=0) ? $this->insure_currency : $this->settings['dhl_currency_type'];
		$local_product_code = $this->get_local_product_code($this->service_code, $this->origin_country); 
		
		$local_product_code_node = $local_product_code ? "<LocalProductCode>{$local_product_code}</LocalProductCode>" : '';
		
		$shipment_details = <<<XML
	<ShipmentDetails>
		<NumberOfPieces>$total_packages</NumberOfPieces>
		<Pieces>
			{$pieces}
		</Pieces>
		<Weight>{$total_weight}</Weight>
		<WeightUnit>{$this->labelapi_weight_unit}</WeightUnit>
		<GlobalProductCode>{$this->service_code}</GlobalProductCode>
			{$local_product_code_node}
		<Date>{$mailingDate}</Date>
		<Contents>{$this->label_contents_text}</Contents>
		<DoorTo>DD</DoorTo>
		<DimensionUnit>{$this->labelapi_dimension_unit}</DimensionUnit>
		{$special_service_insurance}
		<IsDutiable>{$is_dutiable}</IsDutiable>
		<CurrencyCode>{$currency}</CurrencyCode>
	</ShipmentDetails>
XML;
		return $shipment_details;
	}

	private function get_local_product_code( $global_product_code, $origin_country='', $destination_country='' ){
		
		if(!empty($this->local_product_code) )
		{
			return $this->local_product_code;
		}else
		{
		$countrywise_local_product_code = array( 
			'SA' => 'global_product_code',
			'ZA' => 'global_product_code',
			'CH' => 'global_product_code'
		);
		
		if( array_key_exists($origin_country, $countrywise_local_product_code) ){
			return ($countrywise_local_product_code[$this->origin_country] == 'global_product_code') ? $global_product_code : $countrywise_local_product_code[$this->origin_country];
		}
	}
		return $global_product_code;
	}

	public function wf_get_package_from_order($order) {
		$orderItems = $order->get_items();
        
		$items = array();
		foreach ($orderItems as $orderItem) {
			$product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
			if(WC()->version > '2.7'){
                            $data = $orderItem->get_meta_data();
                        }else{
                            $data = $orderItem;
                        }
			$measured_weight = 0;
			if(isset($data[1]->value['weight']['value']))
			{
				$measured_weight = (wc_get_weight($data[1]->value['weight']['value'],$this->weight_unit,$data[1]->value['weight']['unit']));
			}
			$items[] = array('data' => $product_data, 'quantity' => $orderItem['qty'], 'measured_weight' => $measured_weight);
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
    
	public function wf_get_return_package_return_from_order($order,$selected_items='') {
		$orderItems = $order->get_items();
		$items = array();

		foreach ($orderItems as $orderItem) {
			$product_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] ;
			if(!is_array($selected_items) )
			{
								
				$product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
				$items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
			}else{
				foreach ($selected_items as $key => $value) {
					if(in_array($product_id,$value))
					{
						$product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
						$items[] = array('data' => $product_data, 'quantity' => $value[1]);
					}	
				}
				
			}
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
		public function wf_get_return_package_from_order($order,$selected_items='') {
		$orderItems = $order->get_items();
		$items = array();
		foreach ($orderItems as $orderItem) {
			$product_id = $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] ;
			if(!is_array($selected_items) || in_array($product_id,$selected_items))
			{
				
				$product_data = wc_get_product($orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
				$items[] = array('data' => $product_data, 'quantity' => $orderItem['qty']);
			}
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
		$insurance_arr	 =  isset($_GET["insurance"]) ? json_decode(stripslashes(html_entity_decode($_GET["insurance"]))) : array();	 

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
				$packages[0][$i]['package_type'] = 'custom';
			}
		}
		// Overridding package values
		$index = 0;
		foreach ($packages as $package_num=> $stored_package) {
			foreach($stored_package as $key => $package){
				if(isset($length_arr[$index])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Length']  =   $length_arr[$index];
				}
				if(isset($width_arr[$index])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Width']   =   $width_arr[$index];
				}
				if(isset($height_arr[$index])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['Dimensions']['Height']  =   $height_arr[$index];
				}
				if(isset($weight_arr[$index])){// If not available in GET then don't overwrite.

					$weight =   $weight_arr[$index];
					/*if($package['Package']['PackageWeight']['UnitOfMeasurement']['Code']=='OZS'){
						if($this->weight_unit=='LBS'){ // make sure weight from pounds to ounces
							$weight =   $weight*16;
						}else{
							$weight =   $weight*35.274; // From KG to ounces
						}
					}*/
					$packages[$package_num][$key]['Weight']['Value']   =   $weight;
				}
				
				if(isset($insurance_arr[$index])){// If not available in GET then don't overwrite.
					$packages[$package_num][$key]['InsuredValue']['Amount']  =   $insurance_arr[$index];
				}
				if(!isset($length_arr[$index])&& !isset($width_arr[$index]) && !isset($height_arr[$index]) && !isset($weight_arr[$index]) && !isset($insurance_arr[$index]) )
				{
					unset($packages[$package_num][$key]);
				}
				$index++;
			}
			
		}
		return $packages;
	}

	public function print_label($order, $service_code, $order_id,$post_plt = '0') {
		$this->order = $order;
		$this->order_id = $order_id;
		$this->service_code = $service_code;
		$this->plt = ($post_plt != '0') ? false : $this->plt;
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method = array_shift($shipping_methods);
		$shipping_output = explode('|',$shipping_method['method_id']);		
		$this->local_product_code = isset($shipping_output[1]) ? $shipping_output[1] : '';
		
		$packages   =   array();
		$packages   =   array_values( $this->wf_get_package_from_order($order) );

		$stored_packages = array();
		//$stored_packages	=   get_post_meta( $order_id, '_wf_dhl_stored_packages', true );
	
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
			echo '<a href="' . admin_url('/post.php?post=' . $_GET['wf_dhl_createshipment'] . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
			//For the debug information to display in the page
			die();
		}
	}
	
	
	public function print_return_label($order, $service_code, $order_id,$post_plt = '0') {
		$this->order = $order;
		$this->order_id = $order_id;
		$this->service_code = $service_code;
		$this->plt = ($post_plt != '0') ? false : $this->plt;
		
		$packages   =   array();

		$check_items = get_post_meta($order_id,'_wf_dhl_stored_return_products',true);
		if(!empty($check_items))
		{
			$selected_items = explode(',',$check_items);

		}else
		{
			$selected_items = '';
		}
		
		$packages   =   array_values( $this->wf_get_return_package_from_order($order,$selected_items) );

		$stored_packages = array();
		//$stored_packages	=   get_post_meta( $order_id, '_wf_dhl_stored_return_packages', array() );

		if(!$stored_packages){
			
			foreach ($packages as $key => $package) {
				$stored_packages[] = $this->get_dhl_packages($package);
			}
		}
		
		$dhl_packages = $this->manual_packages( $stored_packages );
		
		foreach($dhl_packages as $key => $dhl_package){
			$this->print_return_label_processor( array($dhl_package), $packages[$key] );
			if( !empty( $this->shipmentErrorMessage) ){
				$this->shipmentErrorMessage .= "</br>Some error occured for package $key: ".$this->shipmentErrorMessage;
			}
		}
		if ($this->debug) {
			echo '<a href="' . admin_url('/post.php?post=' . $_GET['wf_dhl_create_return_shipment'] . '&action=edit') . '">' . __('Back to Order', 'wf-shipping-dhl') . '</a>';
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
		update_post_meta($this->order_id, 'wf_woo_dhl_shipmentErrorMessage', $this->shipmentErrorMessage);
		
	}

	public function print_return_label_processor($dhl_package, $package) {
		$this->shipmentErrorMessage = '';
		$this->master_tracking_id = '';
		

		// Debugging
		$this->debug(__('dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl'));
		
		// Get requests
		$dhl_requests = $this->get_dhl_shipping_return_label_requests($dhl_package, $package);

		if ($dhl_requests) {
			$this->run_package_request($dhl_requests, $dhl_package,'return_label');
		}
		

		update_post_meta($this->order_id, 'wf_woo_dhl_shipmentReturnErrorMessage', $this->shipmentErrorMessage);

		
	}



	public function run_package_request($request, $dhl_packages = null, $return_label ='') {
		/* try {			
		 */
		if($return_label != '')
		{
			$this->process_result($this->get_result($request), $request, $dhl_packages,'return');
		}
		else
		{
			$this->process_result($this->get_result($request), $request, $dhl_packages);
		}
		

		/*  } catch ( Exception $e ) {
		  $this->debug( print_r( $e, true ), 'error' );
		  return false;
		  } */
	}

	private function get_result($request) {
		$this->debug('<br>DHL REQUEST: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($request, ENT_IGNORE), true) . '</pre>');

		$result = wp_remote_post($this->service_url, array(
			'method' => 'POST',
			'timeout' => 70,
			'sslverify' => 0,
			//'headers'		  => $this->wf_get_request_header('application/vnd.cpc.shipment-v7+xml','application/vnd.cpc.shipment-v7+xml'),
			'body' => $request
				)
		);

		$this->debug('DHL RESPONSE: <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . htmlspecialchars(print_r($result, true), ENT_IGNORE) . '</pre>');
		
		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			$this->debug('DHL WP ERROR: <a href="#" class="debug_reveal">Reveal</a><pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r(htmlspecialchars($error_message), true) . '</pre>');
		}
		elseif (is_array($result) && !empty($result['body'])) {
			$result = $result['body'];
		} else {
			$result = '';
		}

		libxml_use_internal_errors(true);
		if(!empty($result))
		{
			$result = utf8_encode($result);
		}
		$xml = simplexml_load_string($result);

		$shipmentErrorMessage = "";
		if (!$xml) {
			$shipmentErrorMessage .= 'Failed loading XML' . "\n";
			foreach (libxml_get_errors() as $error) {
				$shipmentErrorMessage .= "\t" . $error->message;
			}
			$return = array(
				'ErrorMessage' => $shipmentErrorMessage
			);
		} else {
			if ($xml->Response->Status && (string) $xml->Response->Status->Condition->ConditionCode != '')
			{
				if((string) $xml->Response->Status->Condition->ConditionCode === 'PLT006')
				{	 
					$this->errorMsg .= __(' PLT ( Paperless Trade ) is Not Available. <b>Please print the Commercial Invoice and physically attach them to your shipments.</b> <br>','wf-shipping-dhl');
					$this->print_label($this->order, $this->service_code, $this->order_id, '1' );
					//$errorMsg .= ((string) $xml->Response->Status->Condition->ConditionCode) . ' : ' . ((string) $xml->Response->Status->Condition->ConditionData);
					
				}else{

					$this->errorMsg .= ((string) $xml->Response->Status->Condition->ConditionCode) . ' : ' . ((string) $xml->Response->Status->Condition->ConditionData);
				}
			}

			$return = array('ShipmentID' => (string) $xml->AirwayBillNumber,
				'LabelImage' => (string) $xml->LabelImage->OutputImage,
				'ErrorMessage' => $this->errorMsg
			);
			$xml_request	=   simplexml_load_string($request);
			if(isset($xml_request->DocImages->DocImage->Image)){
				$return['CommercialInvoice']	=   (string)$xml_request->DocImages->DocImage->Image;
			}
		}
		return $return;
	}

	private function process_result($result = '', $request, $dhl_packages,$return_label_process = '') {

		if (!empty($result['ShipmentID']) && !empty($result['LabelImage'])) {
			$shipmentId = $result['ShipmentID'];
			$shippingLabel = $result['LabelImage'];
			 
			if($return_label_process !='')
			{
				add_post_meta($this->order_id, 'wf_woo_dhl_return_shipmentId', $shipmentId, false);
				add_post_meta($this->order_id, 'wf_woo_dhl_return_shippingLabel_' . $shipmentId, $shippingLabel, true);
				add_post_meta($this->order_id, 'wf_woo_dhl_return_packageDetails_' . $shipmentId, $this->wf_get_parcel_details($dhl_packages), true);
				if(isset($result['CommercialInvoice'])){
					add_post_meta($this->order_id, 'wf_woo_dhl_shipping_return_commercialInvoice_' . $shipmentId, $result['CommercialInvoice'], true);
				}
				if($this->non_plt_commercial_invoice !='')
				{
					add_post_meta($this->order_id, 'wf_woo_dhl_shipping_return_commercialInvoice_' . $shipmentId, $this->non_plt_commercial_invoice, true);
				}
			}else{
				add_post_meta($this->order_id, 'wf_woo_dhl_shipmentId', $shipmentId, false);
				add_post_meta($this->order_id, 'wf_woo_dhl_shippingLabel_' . $shipmentId, $shippingLabel, true);
				add_post_meta($this->order_id, 'wf_woo_dhl_packageDetails_' . $shipmentId, $this->wf_get_parcel_details($dhl_packages), true);
				add_post_meta($this->order_id, 'wf_woo_dhl_package_' . $shipmentId, $dhl_packages, true);

				if(isset($result['CommercialInvoice'])){
					add_post_meta($this->order_id, 'wf_woo_dhl_shipping_commercialInvoice_' . $shipmentId, $result['CommercialInvoice'], true);
				}
				if($this->non_plt_commercial_invoice !='')
				{
					add_post_meta($this->order_id, 'wf_woo_dhl_shipping_commercialInvoice_' . $shipmentId, $this->non_plt_commercial_invoice, true);
				}
			}
			// Shipment Tracking (Auto)
			try {
				$shipment_id_cs = $shipmentId;
				$admin_notice = WfTrackingUtil::update_tracking_data($this->order_id, $shipment_id_cs, 'dhl-express', WF_Tracking_Admin_DHLExpress::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin_DHLExpress::SHIPMENT_RESULT_KEY);
			} catch (Exception $e) {
				$admin_notice = '';
				// Do nothing.
			}
			
			// Shipment Tracking (Auto)
			if ('' != $admin_notice) {
				WF_Tracking_Admin_DHLExpress::display_admin_notification_message($this->order_id, $admin_notice);
			} else {
				//Do your plugin's desired redirect.
				//exit;
			}

			if (!empty($this->service_code)) {
				add_post_meta($this->order_id, 'wf_woo_dhl_service_code', $this->service_code, true);
			}
			if (!empty($this->service_code) && $return_label_process !='') {
				add_post_meta($this->order_id, 'wf_woo_dhl_return_service_code', $this->service_code, true);
			}
			if ($this->add_trackingpin_shipmentid == 'yes' && !empty($shipmentId)) {
				$this->order->add_order_note(sprintf(__('DHL Tracking-pin #: <a href="http://www.dhl.com/en/express/tracking.html?AWB=%s" target="_blank">%s</a>', 'wf-shipping-dhl'), $shipmentId,$shipmentId), true);
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
				$box_details = "<br/><table class='wf-shipment-package-table' style='border:1px solid lightgray;margin: 5px;margin-top: 5px;box-shadow:.5px .5px 5px lightgrey;'>
						<tr>
							<td style='font-weight: bold;
						    padding: 5px;
						'>BOX</td><td style='
						    padding: 5px;
						font-weight: bold;'>Weight</td><td style='
						    padding: 5px;
						font-weight: bold;'>Height</td><td style='
						    padding: 5px;
						font-weight: bold;'>Width</td><td style='
						    padding: 5px;
						    font-weight: bold;
						'>Length </td>";
                                if($this->settings['insure_contents'] == 'yes'){
                                $box_details .=  "<td style='
						    padding: 5px;
						    font-weight: bold;
						'>Insurance </td>";
                                    }
				$box_details .= "</tr>";
				foreach ($parcel as $key => $value) {
					$box_details .= "<tr>";
					if (!empty($value['package_id'])) {
						$box_details .= '<td style="padding: 5px;">' . strtoupper(str_replace('_',' ',$value['package_id'])) . '</td>';
					}else
					{
						$box_details .= '<td style="padding: 5px;">-</td>';
					}
					if (isset($value['Weight'])) {
						$box_details .= '<td style="padding: 5px;">' . $value['Weight']['Value'] . ' ' . $value['Weight']['Units'] . '</td>';
					}else
					{
						$box_details .= '<td style="padding: 5px;">-</td>';
					}
					
					if (isset($value['Dimensions'])) {
						$value['Dimensions']['Units'] = isset($value['Dimensions']['Units']) ? $value['Dimensions']['Units'] : '';
					$box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Height'] . ' ' . $value['Dimensions']['Units'] . '</td>';
					$box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Width'] . ' ' . $value['Dimensions']['Units'] . '</td>';
					$box_details .= '<td style="padding: 5px;">' . $value['Dimensions']['Length'] . ' ' . $value['Dimensions']['Units'] . '</td>';
				}else
				{
					$box_details .= '<td style="padding: 5px;">-</td><td style="padding: 5px;">-</td><td style="padding: 5px;">-</td>';
				}
				if(isset($value['InsuredValue']) && $this->settings['insure_contents'] == 'yes')
				{
					$box_details .= '<td style="padding: 5px;">' . $value['InsuredValue']['Amount'] . ' ' . $value['InsuredValue']['Currency'] . '</td>';
				}
				}
				$box_details .= '</tr></table>';	
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
