<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class wf_dhl_paket_woocommerce_shipping_admin_helper  {
	private $service_code;

	public function __construct() {
		$this->id                               = WF_DHL_PAKET_ID;
		$this->init();		
	}

	private function init() {		
		$this->settings = get_option( 'woocommerce_'.WF_DHL_PAKET_ID.'_settings', null );
		
		$this->add_trackingpin_shipmentid = @$this->settings['add_trackingpin_shipmentid'];
		
		
		$this->origin          			= str_replace( ' ', '', strtoupper( $this->settings[ 'origin' ] ) );
		$this->origin_country  			= 'DE';//WC()->countries->get_base_country();
		$this->account_number  			= $this->settings[ 'account_number' ];
		$this->return_account_number  	= isset($this->settings[ 'return_account_number' ])?$this->settings[ 'return_account_number' ]:'';
		
		$this->production      = ( $bool = $this->settings[ 'production' ] ) && $bool == 'yes' ? true : false;
		
		$this->site_id         	= $this->production?'WooForceDHLPaket_v2_1':$this->settings[ 'site_id' ];	// Site ID and Pass static for live mode 
		$this->site_password	= $this->production?'uAPpRrvKCefDto0GesZeee498Tg9U4':$this->settings[ 'site_password' ];
		$this->api_user         = $this->production?$this->settings[ 'api_user' ]:'2222222222_01'; // API user info is static for test mode
		$this->api_key			= $this->production?$this->settings[ 'api_key' ]:'pass';
		//$this->region_code	= $this->settings[ 'region_code' ];
		
		$_stagingUrl 	= 'https://cig.dhl.de/services/sandbox/soap';
		$_productionUrl = 'https://cig.dhl.de/services/production/soap';
		
		$this->api_url			='https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/2.0/geschaeftskundenversand-api-2.0.wsdl';
		$this->soap_header_url	='http://dhl.de/webservice/cisbase';
	
		
		$this->service_url     = ($this->production == true) ? $_productionUrl  : $_stagingUrl ;
		$this->debug           = ( $bool = $this->settings[ 'debug' ] ) && $bool == 'yes' ? true : false;
		$this->insure_contents = ( $bool = @$this->settings[ 'insure_contents' ] ) && $bool == 'yes' ? true : false;
		$this->request_type    = @$this->settings[ 'request_type'];
		$this->packing_method  = $this->settings[ 'packing_method'];
		$this->box_max_weight			=	$this->settings['box_max_weight'];
		$this->weight_packing_process	=	$this->settings['weight_packing_process'];
		$this->boxes           = $this->settings[ 'boxes'];
		$this->custom_services = isset($this->settings[ 'services']) ? $this->settings[ 'services']: array();
		$this->offer_rates     = @$this->settings[ 'offer_rates'];
				
		$this->freight_shipper_person_name      = htmlspecialchars_decode( $this->settings[ 'shipper_person_name' ] );
		$this->freight_shipper_company_name     = htmlspecialchars_decode( $this->settings[ 'shipper_company_name' ] );
		$this->freight_shipper_phone_number     = $this->settings[ 'shipper_phone_number' ];
		$this->shipper_email      				=  $this->settings[ 'shipper_email' ];
		
		$this->freight_shipper_street      = htmlspecialchars_decode( $this->settings[ 'freight_shipper_street' ] );
		$this->freight_shipper_street_2    = htmlspecialchars_decode( $this->settings[ 'shipper_street_2'] );
		$this->freight_shipper_city        = $this->settings[ 'freight_shipper_city' ];
		$this->freight_shipper_state       = $this->settings[ 'freight_shipper_state' ];
		
		$this->output_format = @$this->settings['output_format'];
		$this->image_type = @$this->settings['image_type'];		
		
		$this->dutypayment_type = isset($this->settings['dutypayment_type']) ? $this->settings['dutypayment_type'] : '';
		$this->dutyaccount_number = isset($this->settings['dutyaccount_number']) ? $this->settings['dutyaccount_number'] : '';
	
		$this->dimension_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'IN' : 'CM';
		$this->weight_unit = isset($this->settings['dimension_weight_unit']) && $this->settings['dimension_weight_unit'] == 'LBS_IN' ? 'LBS' : 'KG';
		
		$this->labelapi_dimension_unit = $this->dimension_unit == 'IN' ? 'I' : 'C';
		$this->labelapi_weight_unit = $this->weight_unit == 'LBS' ? 'L' : 'K';
		
		$this->product_code			=	'V01PAK';
		$this->europaket_enabled	=	isset( $this->settings['europaket_enabled'] ) && $this->settings['europaket_enabled'] == 'yes' ? true : false;
		$this->export_doc_terms_of_trade       = 	$this->settings[ 'export_doc_terms_of_trade' ];
		$this->export_doc_desc			       = 	$this->settings[ 'export_doc_desc' ];
		
	}

	public function debug( $message, $type = 'notice' ) {
		if ( $this->debug ) {
			echo( $message);
		}
	}

	public function get_dhl_packages( $package ) {
		switch ( $this->packing_method ) {
			case 'box_packing' :
				return $this->box_shipping( $package );
			break;
			case 'weight_based' :
				return $this->weight_based_shipping($package);
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

			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $package['contents']);
			if($skip_product){
			    continue;
			}
			if(isset($values['mesured_weight']) && $values['mesured_weight'] !=0 )
			{
				$weight = $values['mesured_weight'];
			}
			else
			{
				$weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
			}
			if ( ! $weight ) {
				$this->debug( sprintf( __( 'Product # is missing weight.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				wf_admin_notice::add_notice(sprintf( __( 'Product is missing weight.', 'wf-shipping-dhl' )), 'error');
				return;
			}

			$group = array();

			$group = array(
				'GroupNumber'       => $group_id,
				'GroupPackageCount' => 1,
				'Weight' => array(
					'Value' => max( '0.5', round( $weight, 2 ) ),
					'Units' => $this->weight_unit
				),
				'packed_products' => array( $values['data'] )
			);

			if ( wf_get_product_length( $values['data'] ) && wf_get_product_height($values['data']) && wf_get_product_width( $values['data'] ) ) {

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
			for($loop = 0; $loop < $values['quantity'];$loop++){
				$to_ship[] = $group;
			}
			$group_id++;
		}
		return $to_ship;
	}

	private function box_shipping( $main_packages ) {
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

			if ( !empty( $box['id'] ) ) {
				$newbox->set_id( current( explode( ':', $box['id'] ) ) );
			}

			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
		}

		// Add items
		foreach ( $main_packages['contents'] as $item_id => $values ) {

			if ( ! $values['data']->needs_shipping() ) {
				$this->debug( sprintf( __( 'Product # is virtual. Skipping.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				continue;
			}
				
			$skip_product = apply_filters('wf_shipping_skip_product_from_dhl_label',false, $values, $main_packages['contents']);
			if($skip_product){
			    continue;
			}
			if ( $values['data']->get_length() && $values['data']->get_height() && $values['data']->get_width() && $values['data']->get_weight() ) {

				$dimensions = array( $values['data']->get_length(), $values['data']->get_height(), $values['data']->get_width() );

				for ( $i = 0; $i < $values['quantity']; $i ++ ) {
					
					if(isset($values['mesured_weight']) && $values['mesured_weight'] !=0 )
					{
						$weight = $values['mesured_weight'];
					}
					else
					{
						$weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
					}		
					$boxpack->add_item(
						wc_get_dimension( $dimensions[2], $this->dimension_unit ),
						wc_get_dimension( $dimensions[1], $this->dimension_unit ),
						wc_get_dimension( $dimensions[0], $this->dimension_unit ),
						$weight,
						$values['data']->get_price(),
						array(
							'data' => $values['data']
						)
					);
				}

			} else {
				$this->debug( sprintf( __( 'Product #%s is missing dimensions. Aborting.', 'wf-shipping-dhl' ), $item_id ), 'error' );
				wf_admin_notice::add_notice(sprintf( __( 'Product is missing dimensions. Aborting.', 'wf-shipping-dhl' ) ), 'error');
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
				return $this->per_item_shipping($main_packages);
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

			//for backward compatibility
			$skip_product = apply_filters('wf_shipping_skip_product',false, $values, $package['contents']);
			if($skip_product){
				continue;
			}
			
            if (!($values['quantity'] > 0 && $values['data']->needs_shipping())) {
                $this->debug(sprintf(__('Product #%d is virtual. Skipping.', 'wf-shipping-dhl'), $ctr));
                continue;
            }

			if(isset($values['mesured_weight']) && $values['mesured_weight'] !=0 )
			{
				$weight = $values['mesured_weight'];
			}
			else
			{
				$weight = wc_get_weight( (!$values['data']->get_weight() ? 0 :$values['data']->get_weight()), $this->weight_unit );
			}
			
            if (!$weight) {
                $this->debug(sprintf(__('Product #%d is missing weight.', 'wf-shipping-dhl'), $ctr), 'error');
                wf_admin_notice::add_notice(sprintf( __( 'Product is missing weight.', 'wf-shipping-dhl' )), 'error');
                return;
            }
			$weight_pack->add_item( $weight, $values['data'], $values['quantity'] );
        }
		
		$pack	=	$weight_pack->pack_items();		
		$errors	=	$pack->get_errors();
		if( !empty($errors) ){
			//do nothing
			return;
		} else {
			$boxes		=	$pack->get_packed_boxes();
			$unpacked_items	=	$pack->get_unpacked_items();
			
			$insured_value			=	0;
			
			$packages		=	array_merge( $boxes,	$unpacked_items ); // merge items if unpacked are allowed
			$package_count	=	sizeof($packages);
			
			// get all items to pass if item info in box is not distinguished
			$packable_items	=	$weight_pack->get_packable_items();
			$all_items		=	array();
			if(is_array($packable_items)){
				foreach($packable_items as $packable_item){
					$all_items[]	=	$packable_item['data'];
				}
			}
			//pre($packable_items);
			
			if(isset($this->order)){
				$order_total	=	$this->order->get_total();
			}
			
			$to_ship  = array();
			$group_id = 1;
			foreach($packages as $package){//pre($package);
			
				$packed_products = array();
				if(($package_count	==	1) && isset($order_total)){
					$insured_value	=	$order_total;
				}else{
					$insured_value	=	0;
					if(!empty($package['items'])){
						foreach($package['items'] as $item){						
							$insured_value			=	$insured_value+$item->get_price();
							
						}
					}else{
						if( isset($order_total) && $package_count){
							$insured_value	=	$order_total/$package_count;
						}
					}
				}
				$packed_products	=	isset($package['items']) ? $package['items'] : $all_items;
				// Creating package request
				$package_total_weight	=	$package['weight'];
				
				$group = array(
					'GroupNumber'       => $group_id,
					'GroupPackageCount' => 1,
					'Weight' => array(
						'Value' => max( '0.5', round( $package['weight'], 2 ) ),
						'Units' => $this->weight_unit
					),
					'InsuredValue'      => array(
						'Amount'   => round( $insured_value ),
						'Currency' => get_woocommerce_currency()
					),
					'packed_products' => $packed_products,
				);
				
				$to_ship[] = $group;
				$group_id++;
			}
		}
		return $to_ship;
	}
	
	private function wf_get_receiver_details($package){
		
		$destination_city = strtoupper( $package['destination']['city'] );
		$destination_postcode = str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ));
		$destination_country_name = isset( WC()->countries->countries[ $package['destination']['country'] ] ) ? WC()->countries->countries[ $package['destination']['country'] ] : $package['destination']['country']; 
		
		$receiver=array();
		
		$receiver['name1']= wf_get_order_shipping_first_name($this->order).' '.wf_get_order_shipping_last_name($this->order);
		
		$packastation_data	=	wf_packstation::get_order_packstation($this->order);		
		if(!$packastation_data)
		{	
			$shipping_company = wf_get_order_shipping_company( $this->order );
			if( isset($shipping_company) && !empty($shipping_company) ) {
				$receiver['Address']['name2']= $shipping_company;
			}

			$address_line1 = wf_get_order_shipping_address_1($this->order);
			$address_line2 = wf_get_order_shipping_address_2($this->order);
			
			if( empty($address_line2) ){
				$address_split = explode(" ", $address_line1);
				if( count( $address_split ) > 1 ) {
					$address_line2 = $address_split[ count( $address_split ) - 1 ];
					array_splice( $address_split, -1 );
					$address_line1 = implode( " ", $address_split );
				}
				else {
					$address_line1 = wf_get_order_shipping_address_1( $this->order );
					$address_line2 = '-';
				}
			}
			
			// Only 7 digit permitted and if not provided then should be populated from address line 1
			$receiver['Address']['streetName']= $address_line1;
			$receiver['Address']['streetNumber']= mb_substr( $address_line2, 0, 5 );
			// If address line 2 is bigger than 5 store them in other fields
			if( strlen($address_line2)>5 ){
				$receiver['Address']['addressAddition']= mb_substr($address_line2, 5, 35);
			}
			
			$receiver['Address']['zip']=$destination_postcode;
			
			$receiver['Address']['city']=$destination_city;
			$receiver['Address']['Origin']['countryISOCode']=$package['destination']['country'];
		}else{
			$receiver['Packstation']['packstationNumber']	=	$packastation_data['packstationId'];
			$receiver['Packstation']['postNumber']			=	wf_packstation::get_order_postnumber($this->order);
			$receiver['Packstation']['zip']					=	$packastation_data['address']->zip;
			$receiver['Packstation']['city']				=	$packastation_data['address']->city;
		}
		
		$receiver['Communication']['email']			= wf_get_order_billing_email($this->order);
		$receiver['Communication']['contactPerson'] = wf_get_order_shipping_first_name($this->order).' '.wf_get_order_shipping_last_name($this->order);
		$receiver['Communication']['phone']			= wf_get_order_billing_phone($this->order);
		
		return $receiver;
	}
	
	private function wf_get_shipper_details($package){
		$destination_city = strtoupper( $package['destination']['city'] );
		$destination_postcode = str_replace( ' ', '', strtoupper( $package['destination']['postcode'] ));
		$destination_country_name = isset( WC()->countries->countries[ $package['destination']['country'] ] ) ? WC()->countries->countries[ $package['destination']['country'] ] : $package['destination']['country']; 
		$consignee_name = wf_get_order_shipping_first_name($this->order) . ' ' . wf_get_order_shipping_last_name($this->order);
		
		$origin_country_name = isset( WC()->countries->countries[ $this->origin_country ] ) ? WC()->countries->countries[ $this->origin_country ] : $this->origin_country;
		
		$shipper=array();
		
		//if person name is emplty, take company name as name1
		if( empty($this->freight_shipper_person_name) ){
			$name1 = $this->freight_shipper_company_name;
			$name2 = '';
		}else{
			$name1 = $this->freight_shipper_person_name;
			$name2 = $this->freight_shipper_company_name;
		}

		$shipper['Name']['name1']= $name1;
		if( !empty($name2) ){
			$shipper['Name']['name2'] = $name2;
		}
		
		$shipper['Address']['streetName']= $this->freight_shipper_street;
		$shipper['Address']['streetNumber']= $this->freight_shipper_street_2;
		$shipper['Address']['zip']	=	$this->origin;		
		$shipper['Address']['city']=$this->freight_shipper_city;
		$shipper['Address']['Origin']['countryISOCode']=$this->origin_country; 
		if($this->freight_shipper_state){
			$shipper['Address']['Origin']['state']=$this->freight_shipper_state; 
		}
		
		$shipper['Communication']['email']=$this->shipper_email;
		$shipper['Communication']['contactPerson']= $this->freight_shipper_person_name;
		$shipper['Communication']['phone']=$this->freight_shipper_phone_number;
		
		return $shipper;
	}
	
	private function wf_get_return_receiver_details($package){
		//if person name is emplty, take company name as name1
		if( empty($this->freight_shipper_person_name) ){
			$name1 = $this->freight_shipper_company_name;
			$name2 = '';
		}else{
			$name1 = $this->freight_shipper_person_name;
			$name2 = $this->freight_shipper_company_name;
		}
		
		$return['Name']	=	array(
			'name1'	=> 	$name1,
		);
		if( !empty($name2) ){
			$return['Name']['name2']	=	$name2;
		}

		$return['Address']	=	array(
			'streetName'	=> 	$this->freight_shipper_street,
			'streetNumber'	=>	$this->freight_shipper_street_2,
			'zip'			=>	$this->origin,
			'city'			=>	$this->freight_shipper_city,
			'Origin'		=> 	array(
				'countryISOCode'	=>	$this->origin_country,
				'state'				=>	$this->freight_shipper_state,
			),
		);
		$return['Communication']	=	array(
			'phone'			=>	$this->freight_shipper_phone_number,
			'email'			=>	$this->shipper_email,
			'contactPerson'	=>	$this->freight_shipper_person_name,
		);
		return $return;
	}
	
	private function wf_get_export_doc($order_id, $dhl_packages){
		$order      = wc_get_order( $order_id );
		$order_total=$order->get_total();
		$oder_currency	=	wf_get_order_currency($order);
		$order_weight=$this->total_weight;
		$order_quantity=$order->get_item_count();
		$export_doc=array();
		$export_doc['exportType']='OTHER';
		$export_doc['exportTypeDescription']='goods';
		$export_doc['termsOfTrade']=$this->export_doc_terms_of_trade?$this->export_doc_terms_of_trade:'DDU';
		$export_doc['placeOfCommital']=$this->origin_country;		
		$export_doc['additionalFee']=0;
		
		//deprecated in API 2.0
		//$export_doc['Amount']=$order_quantity;
		//$export_doc['Description']=$this->export_doc_desc;
		//$export_doc['CountryCodeOrigin']=$this->origin_country;
		//$export_doc['CustomsValue']=$order_total;
		//$export_doc['CustomsCurrency']=$oder_currency;		
		
		//Take HST of the first product in the package.
		$product 	= !empty($dhl_packages['packed_products']) ? current($dhl_packages['packed_products']) : '';

		$par_id 	= wp_get_post_parent_id( wf_get_product_id($product) );
		$post_id 	= $par_id ? $par_id : wf_get_product_id($product);

		$wf_hs_code 	= get_post_meta( $post_id, '_wf_hs_code', 1); 

		$export_doc['ExportDocPosition']=array(
			'description'=>$this->export_doc_desc,
			'countryCodeOrigin'=>$this->origin_country,
			'amount'=>$order_quantity,
			'netWeightInKG'=>round($order_weight/$order_quantity,2),
			'customsValue'=>round($order_total/$order_quantity,2),
			'customsTariffNumber'=> !empty($wf_hs_code) ? $wf_hs_code : $this->order_id //
		);
		return $export_doc;
	}
	private function get_dhl_requests( $dhl_packages, $package) {

		$product 	= !empty($dhl_packages['packed_products']) ? current($dhl_packages['packed_products']) : '';
		if(empty($product))
		{
			return;
		}


		$return_label_required	=	false;
		if(isset($_GET['return_label'])	&&	$_GET['return_label']=='true'){
			$return_label_required	=	true;
		}
		
		$cod_required	=	false;
		$cod_amount		=	0;
		if(isset($_GET['cod'])	&&	$_GET['cod']=='true'){
			$cod_required	=	true;
			$cod_amount		=	$this->order->get_total();
		}
		
		
		$shipment_details_params	=	array(
			'return_label_required'	=>	$return_label_required,
			'cod_required'			=>	$cod_required,
			'cod_amount'			=>	$cod_amount,
			'visual_check_of_age'	=>	$this->get_package_visual_check_of_age($package),
		);
		$shipment_details = $this->wf_get_shipment_details($dhl_packages, $shipment_details_params);
		
		$shipper_details=$this->wf_get_shipper_details($package);
		$receiver_details=$this->wf_get_receiver_details($package);
		
		$export_doc=$this->wf_get_export_doc($this->order_id, $dhl_packages);
		
		$request=array();
		
		
		$request['Version']=array(
			'majorRelease'=>'2',
			'minorRelease'=>'2',
		);
		
		
		
		$request['ShipmentOrder']=array(
			'sequenceNumber'=>1,
			'Shipment'		=>array(
				'ShipmentDetails'=>$shipment_details,
				'Shipper'=>$shipper_details,
				'Receiver'=>$receiver_details,
			)
		);
		if( in_array($this->product_code,	array('V53WPAK','V54EPAK')) )
		{
			$request['ShipmentOrder']['Shipment']['ExportDocument']=$export_doc;
		}
		if($return_label_required){
			$request['ShipmentOrder']['Shipment']['ReturnReceiver']	=	$this->wf_get_return_receiver_details($package);
		}
		return $request;
		
	}
	
	private function wf_get_shipment_details($dhl_packages,$params	= array()){
		$pieces = "";
		$total_packages = 0;
		$total_weight   = 0;
		$total_value = 0;
		$currency = get_woocommerce_currency();
		
		
		
		if($this->origin_country=='DE' && $this->destination_country !='DE')
		{
			$this->product_code='V53WPAK';
			if($this->europaket_enabled && $this->is_european_country($this->destination_country)){ // Europaket
				$this->product_code='V54EPAK';
			}			
		}
		
		// Replace product code of account number
		$this->account_number	=	$this->account_number_with_product_code($this->account_number, $this->product_code);
		
		$shipping_date = date('Y-m-d');
				
		$items=array();
		if ( $dhl_packages ) {
			$parcel = $dhl_packages;
			$total_packages += $parcel['GroupPackageCount'];
			$total_weight   += $parcel['Weight']['Value'] * $parcel['GroupPackageCount'];
			$total_value   += $parcel['InsuredValue']['Amount'] * $parcel['GroupPackageCount'];
			
			$items['weightInKG']	=	$parcel['Weight']['Value'];
			
			if(isset($parcel['Dimensions'])){
				$items['lengthInCM']	=	$parcel['Dimensions']['Length'];
				$items['widthInCM']	=	$parcel['Dimensions']['Width'];
				$items['heightInCM']	=	$parcel['Dimensions']['Height'];
			}
		}
		
		$this->total_weight=$total_weight;
		
		$shipment_details=array();		
		$shipment_details['product']=$this->product_code;
		$shipment_details['accountNumber']	=	str_replace(" ","",$this->account_number);
		
		$shipment_details['shipmentDate']	=	date('Y-m-d');
		$shipment_details['ShipmentItem']=$items;
		
		if($params['return_label_required']){
			$shipment_details['returnShipmentAccountNumber']=str_replace(" ","",$this->return_account_number);
			$shipment_details['Service']['ReturnReceipt']=array(
				'_'=>'',
				'active'=>1,
			);
		}
		
		if($params['cod_required']){
			$shipment_details['Service']['CashOnDelivery']	=	array(
				'_'			=>	'',
				'active'	=>	1,
				'codAmount'	=>	$params['cod_amount']
			);
		}		
		
		if($params['visual_check_of_age']){
			$shipment_details['Service']['VisualCheckOfAge']=array(
				'_'=>'',
				'active'=>1,
				'type'=>'A18',
			);
		}
				
		
		if($this->product_code=='V53WPAK')
		{
			$shipment_details['Service']['Premium']=true;
		}
		
		$shipment_details = apply_filters('woocommerce_dhl_paket_request',$shipment_details,$this->order_id);
		return 	$shipment_details;
	}

	private function wf_get_package_from_order($order){
		$orderItems = $order->get_items();
		
		foreach($orderItems as $orderItem){
			$product_data   = wc_get_product( $orderItem['variation_id'] ? $orderItem['variation_id'] : $orderItem['product_id'] );
                        if(WC()->version >'2.7'){
                            $data = $orderItem->get_meta_data();
                        }else{
                            $data = $orderItem;
                        }
			$mesured_weight = 0;
			if(isset($data[1]->value['weight']['value']))
			{
				$mesured_weight = (wc_get_weight($data[1]->value['weight']['value'],$this->weight_unit,$data[1]->value['weight']['unit']));
			}
			
			$items[] = array( 'data' => $product_data , 'quantity' => $orderItem['qty'],'mesured_weight' => $mesured_weight );
			
		}
		$package['contents'] = $items;
		$package['destination']['country'] = 	wf_get_order_shipping_country($order);
		$package['destination']['first_name'] = wf_get_order_shipping_first_name($order);
		$package['destination']['last_name'] = 	wf_get_order_shipping_last_name($order);
		$package['destination']['company'] = 	wf_get_order_shipping_company($order);
		$package['destination']['address_1'] = 	wf_get_order_shipping_address_1($order);
		$package['destination']['address_2'] = 	wf_get_order_shipping_address_2($order);
		$package['destination']['city'] = 		wf_get_order_shipping_city($order);
		$package['destination']['state'] = 		wf_get_order_shipping_state($order);
		$package['destination']['postcode'] = 	wf_get_order_shipping_postcode($order);
		return $package;
	}
	
	public function print_label( $order, $service_code, $order_id ){
		$this->order          = $order; 
		$this->order_id       = $order_id;
		$this->service_code   = $service_code;
        
        $pack                 = $this->wf_get_package_from_order( $order );
        
        if( is_array( $pack ) ) {
           return $this->print_label_processor( $pack );
        }
        else {
			wf_admin_notice::add_notice(__( 'Unexpected error while get package.', 'wf-shipping-dhl' ));
            return false;
        }
	}
	
	public function print_label_processor( $package ) {		
		$this->shipmentErrorMessage = '';
		$this->master_tracking_id = '';		
		
		// Debugging
		$this->debug( __( 'dhl debug mode is on - to hide these messages, turn debug mode off in the settings.', 'wf-shipping-dhl' ) );

		$this->destination_country = $package['destination']['country'];
		
		$ctr=0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$ctr++;

			if ( !( $values['quantity'] > 0 && $values['data']->needs_shipping() ) ) {
				$this->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'wf-shipping-dhl' ), $ctr ) );
				continue;
			}

			if ( ! $values['data']->get_weight() ) {
				$this->debug( sprintf( __( 'Product #%d is missing weight.', 'wf-shipping-dhl' ), $ctr ), 'error' );
				// return;
			}
		}
		
		// Get requests
		$dhl_packages   = $this->get_dhl_packages( $package );			
		$dhl_packages	= $this->manual_packages($dhl_packages);
		if(is_array($dhl_packages)){	
			foreach ($dhl_packages as $key => $dhl_pack) {
				$dhl_requests   = $this->get_dhl_requests( $dhl_pack, $package);
				if ( $dhl_requests ) {
					$dhl_requests	=	apply_filters('wf_dhl_paket_create_shipment_request', $dhl_requests, $this->order);
					$this->run_package_request( $dhl_requests,$dhl_packages, $key );
				}
			}
		}
		if($this->shipmentErrorMessage){
			wf_admin_notice::add_notice(sprintf(__('Order #%d: %s','wf-shipping-dhl'), wf_get_order_id($this->order), $this->shipmentErrorMessage));
			return false;
		}else{
			return true;
		}
	}
	
	public function generate_packages($order, $service_code){
		$package  = $this->wf_get_package_from_order( $order );
		if( is_array( $package ) ) {
			$dhl_packages   = $this->get_dhl_packages( $package );
			if( empty($dhl_packages) ){
				$dhl_packages = array();
			}
			$orderid = wf_get_order_id($order);
			update_post_meta( $orderid, '_wf_dhl_paket_stored_packages', $dhl_packages );
			wp_redirect( admin_url( '/post.php?post='.$orderid.'&action=edit') );
			exit;
		}else {
            $error_msg = __( 'Unexpected error while get package.', 'wf-shipping-dhl' );
            $return = array(
				'ErrorMessage' => $error_msg
			);
            return $return;
        }
	}

	public function run_package_request( $request, $dhl_packages=null, $package_number=false ) {
		/* try {		 	
		 */
			$this->process_result( $this->get_result( $request, $package_number ) , $request, $dhl_packages);
			
		/*  } catch ( Exception $e ) {
			$this->debug( print_r( $e, true ), 'error' );
			return false;
		} */ 
	}

	private function get_result( $request, $package_number ) {
		$this->debug( 'DHL REQUEST for package '.$package_number.': <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' . print_r($request, true ) . '</pre>' );
		$client = $this->loginSoapClient();
		try {
			$result = $client->createShipmentOrder($request);
		}catch(Exception $e){
			return array(
				'ErrorMessage' => $e->faultstring
			);
		}
		$this->debug( 'DHL RESPONSE for package '.$package_number.': <pre class="debug_info" style="background:#EEE;border:1px solid #DDD;padding:5px;">' .  htmlspecialchars(print_r( $result, true ),ENT_SUBSTITUTE) . '</pre>' );
		
		if($result->Status->statusCode>0 || !isset($result->CreationState->LabelData->shipmentNumber)){
			//handle the error
			if(isset($result->CreationState->LabelData->Status->statusMessage)&&is_array($result->CreationState->LabelData->Status->statusMessage)){
				$error_msg='<ul>';
					foreach($result->CreationState->LabelData->Status->statusMessage as $msg){
						$error_msg.='<li>'.$msg.'</li>';
					}
				$error_msg.='</ul>';
			}
			else{
				$error_msg=$result->Status->statusMessage;
			}
			$return = array(
				'ErrorMessage' => $error_msg
			);
		}
		else{
			// response is ok
			$return = array('ShipmentID' => $result->CreationState->LabelData->shipmentNumber,
				'LabelImage' => $result->CreationState->LabelData->labelUrl,
				'PieceInformation' => '',//json_encode($result->CreationState->PieceInformation) // Deprecated in 2.0
			);
			if( in_array($this->product_code,	array('V53WPAK','V54EPAK')) )
			{
				$exportDoc=$this->getExportDoc($result->CreationState->LabelData->shipmentNumber);
				if($exportDoc->Status->statusCode===0)
				{
					if($exportDoc->ExportDocData->exportDocURL)
						$return['ExportDoc']=$exportDoc->ExportDocData->exportDocURL;
				}
			}
		}
		return $return;
	}
	private function process_result( $result = '' , $request, $dhl_packages) {
		if (!empty($result['ShipmentID']) && !empty($result['LabelImage'])){			
			$shipmentId = $result['ShipmentID'];
			$shippingLabel = $result['LabelImage'];
			$pieceInformation = $result['PieceInformation'];
			
			if(isset($request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service'])){
				$shipment_services = $request['ShipmentOrder']['Shipment']['ShipmentDetails']['Service'];
				if(is_array($shipment_services)){
					foreach($shipment_services as $service_key=>$service_name){
						if(is_array($this->custom_services)){
							if(array_key_exists($service_key,$this->custom_services)){
								add_post_meta($this->order_id,'_wf_woo_dhl_service_code',$service_key, false);
								if($service_key=='DeliveryOnTime'){
									add_post_meta($this->order_id,'_wf_woo_dhl_service_time',$shipment_services[$service_key]['time'], false);
								}
							}
						}
						
					}
				}
			}
			
			add_post_meta($this->order_id, '_wf_woo_dhl_shipmentId', $shipmentId, false);
			add_post_meta($this->order_id, '_wf_woo_dhl_shippingLabel_'.$shipmentId, $shippingLabel, true);
			add_post_meta($this->order_id,'_wf_woo_dhl_piece_information',$pieceInformation, false);
			if( !empty($result['ExportDoc']) ){
				add_post_meta($this->order_id, '_wf_woo_dhl_export_doc_'.$shipmentId, $result['ExportDoc'],true);
			}
            // Shipment Tracking (Auto)
            try {
                $shipment_id_cs = $shipmentId;
                $admin_notice = WfTrackingUtil::update_tracking_data( $this->order_id, $shipment_id_cs, 'deutsche-post-dhl', WF_Tracking_Admin_DHLPaket::SHIPMENT_SOURCE_KEY, WF_Tracking_Admin_DHLPaket::SHIPMENT_RESULT_KEY );
            } catch ( Exception $e ) {
                $admin_notice = '';
                // Do nothing.
            }

            // Shipment Tracking (Auto)
            // if( '' != $admin_notice ) {
            //     WF_Tracking_Admin_DHLPaket::display_admin_notification_message( $this->order_id, $admin_notice );
            // }
            // else {
            //     //Do your plugin's desired redirect.
            //     //exit;
            // }
			
		}
		
		if(!empty($result['ErrorMessage'])){
			$this->shipmentErrorMessage .=  $result['ErrorMessage'];		
		}				
	}
	
	private function wf_get_parcel_details($dhl_packages){
		$complete_box = array();
		if ( $dhl_packages ) {
			foreach ( $dhl_packages as $key => $parcel ) {
				$box_details = "";					
				if(!empty($parcel['package_id'])){
					$box_details .=  '<strong>BOX:  </strong>' . $parcel['package_id'] . '<br />';					
				}				
				if(isset($parcel['Weight'])){
					$box_details .=  '<strong>Weight:  </strong>' . $parcel['Weight']['Value'] . ' ' . $parcel['Weight']['Units'] . '<br />';					
				}		
				if(isset($parcel['Dimensions'])){
					$box_details .=  '<strong>Height:  </strong>' . $parcel['Dimensions']['Height'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';
					$box_details .=  '<strong>Width:  </strong>' . $parcel['Dimensions']['Width'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';
					$box_details .=  '<strong>Length:  </strong>' . $parcel['Dimensions']['Length'] . ' ' . $parcel['Dimensions']['Units'] . '<br />';					
				}
				$box_details .= '<hr>';
				$complete_box[] = $box_details;				
			}			
		}			
		return $complete_box;
	}
	
	private function getExportDoc($shipment_id){
		
		$client = $this->loginSoapClient();		
		$request=array();		
		$request['Version']=array(
			'majorRelease'=>'2',
			'minorRelease'=>'0',
		);		
		$request['shipmentNumber']=$shipment_id;
		try{
			$result = $client->getExportDoc($request);
			return $result;
		}catch(Exception $e){
			update_post_meta($this->order_id, '_wf_woo_dhl_shipmentErrorMessage', $e->faultstring);
		}	
	}
	
	public function delete_shipment($order_id){
		
		$shipment_ids = get_post_meta($order_id,'_wf_woo_dhl_shipmentId',false);
		$client = $this->loginSoapClient();		
		$request=array();
		
		$request['Version']=array(
			'majorRelease'=>'1',
			'minorRelease'=>'0',
		);
		
		foreach ($shipment_ids as $key => $shipment_id) {
			$request['shipmentNumber']=$shipment_id;
			try{
				$result = $client->deleteShipmentOrder($request);
				return true;
			}catch(Exception $e){
				wf_admin_notice::add_notice($e->faultstring);
				//update_post_meta($order_id, '_wf_woo_dhl_shipmentErrorMessage', $e->faultstring);
				return false;
			}		
		}
	}
	
	public function createManifest($order_id,$shipment_id){
				
		$client = $this->loginSoapClient();		
		$request=array();
		
		$request['Version']=array(
			'majorRelease'=>'2',
			'minorRelease'=>'0',
		);
		
		$request['shipmentNumber']=$shipment_id;
		try{
			$result = $client->doManifest($request);
			if($result->Status->statusCode>0){
				//error occured
				update_post_meta($order_id, '_wf_woo_dhl_shipmentErrorMessage', $result->Status->statusMessage);				
			}
			else{
				// successful manifest
				add_post_meta($order_id, '_wf_woo_dhl_manifest_'.$shipment_id, $shipment_id, true);
			}
		}catch(Exception $e){
			update_post_meta($order_id, '_wf_woo_dhl_shipmentErrorMessage', $e->faultstring);
		}
	}
	
	public function get_manifest($order_id,$mani_date=false){
		$client = $this->loginSoapClient();		
		$request=array();
		
		$request['Version']=array(
			'majorRelease'=>'2',
			'minorRelease'=>'0',
		);
		
		$request['manifestDate']=$mani_date?$mani_date:date('Y-m-d');
		try{
			$result = $client->getManifest($request);
			if($result->Status->statusCode>0){
				//error occured
				update_post_meta($order_id, '_wf_woo_dhl_shipmentErrorMessage', $result->Status->statusMessage);				
			}
			else{
				// successful manifest
				return $result->manifestData;
			}
		}catch(Exception $e){
			update_post_meta($order_id, '_wf_woo_dhl_shipmentErrorMessage', $e->faultstring);
		}
		return false;
	}
	
	public function validate_label_data(){
		
		$ship_pack_length	=	(float)$_REQUEST['ship_pack_length'];
		$ship_pack_width	=	(float)$_REQUEST['ship_pack_width'];
		$ship_pack_height	=	(float)$_REQUEST['ship_pack_height'];
		$ship_pack_weight	=	(float)$_REQUEST['ship_pack_weight'];
		if(($ship_pack_length||$ship_pack_width||$ship_pack_height||$ship_pack_weight)&&!($ship_pack_length&&$ship_pack_width&&$ship_pack_height&&$ship_pack_weight)){
			update_post_meta($this->order_id, '_wf_woo_dhl_shipmentErrorMessage', 'You might have missed one or more dimensions');
			return false;
		}else{
			return true;
		}
		
	}
	
	public function manual_packages($packages){
		if(!isset($_GET['weight'])){
			return $packages;
		}
		
		$length_arr		=	json_decode(stripslashes(html_entity_decode($_GET["length"])));
		$width_arr		=	json_decode(stripslashes(html_entity_decode($_GET["width"])));
		$height_arr		=	json_decode(stripslashes(html_entity_decode($_GET["height"])));
		$weight_arr		=	json_decode(stripslashes(html_entity_decode($_GET["weight"])));		
		$insurance_arr	=	json_decode(stripslashes(html_entity_decode($_GET["insurance"])));

		$no_of_package_entered	=	count($weight_arr);
		$no_of_packages			=	count($packages);
		
		// Populate extra packages, if entered manual values
		if($no_of_package_entered > $no_of_packages){ 
			$package_clone	=	is_array($packages) ? current($packages) : $this->get_dummy_dhl_paket_package(); //get first package to clone default data
			for($i=$no_of_packages; $i<$no_of_package_entered; $i++){
				
				$packages[$i]	=	$package_clone;
				
				$packages[$i]['GroupNumber']	=	$i+1;
				$packages[$i]['GroupPackageCount']	=	1;
				unset($packages[$i]['packed_products']);
			}
		}
		
		// Overridding package values
		foreach($packages as $key => $package){
			
			if(isset($weight_arr[$key])){// If not available in GET then don't overwrite.
				$packages[$key]['Weight']['Value']	=	$weight_arr[$key];
			}
			
			if(isset($length_arr[$key])){// If not available in GET then don't overwrite.
				$packages[$key]['Dimensions']['Length']	=	$length_arr[$key];
			}
			
			if(isset($width_arr[$key])){// If not available in GET then don't overwrite.
				$packages[$key]['Dimensions']['Width']	=	$width_arr[$key];
			}
			
			if(isset($height_arr[$key])){// If not available in GET then don't overwrite.
				$packages[$key]['Dimensions']['Height']	=	$height_arr[$key];
			}
			
			if( isset($insurance_arr[$key]) ){// If not available in GET then don't overwrite.
				$packages[$key]['InsuredValue']['Amount']	=	$insurance_arr[$key];
			}
		}
		
		return $packages;
	}
	private function get_dummy_dhl_paket_package(){
		return array (
			'GroupNumber' => 1,
			'GroupPackageCount' => 1,
			'Weight' => array (
				'Value' => 0,
				'Units' => $this->weight_unit,
			),
			'Dimensions' => array (
				'Length' => 0,
				'Width' => 0,
				'Height' => 0,
				'Units' => $this->dimension_unit,
			),
			'InsuredValue' => array (
				'Amount' => '0',
				'Currency' => get_woocommerce_currency(),
			),
			'packed_products' => array(),
		);
	}
	
	private function loginSoapClient(){
            try{
		$client = new SoapClient($this->api_url,
		array('login' => $this->site_id,
			'password' => $this->site_password,
			'location' => $this->service_url,
			'soap_version' => SOAP_1_1,
			'trace' => true)
		);
            }catch (Exception $e){
                $this->debug($e->getMessage());
            }
		
		$authentication=array(
            'user'=> $this->api_user,
            'signature'=> $this->api_key,
			'type'=>0
        );
        $authHeader = new SoapHeader($this->soap_header_url, 'Authentification', $authentication);
        
        $client->__setSoapHeaders($authHeader);
		
		return $client;
	}	
	
	public function get_visual_check_of_age($item){
		return get_post_meta( wf_get_product_id($item), '_wf_dhlp_age_check', true);
	}
	
	public function get_package_visual_check_of_age($package){
		foreach ( $package['contents'] as $item_id => $values ) {
			if($this->get_visual_check_of_age($values['data'])){
				return true;
			}
		}
		return false;
	}
	
	public function account_number_with_product_code($account_number, $product_code){
		
		$prd_codes	=	array(
			'V01PAK'	=>	'01',
			'V53WPAK'	=>	'53',
			'V54EPAK'	=>	'54',
			'V06TG'		=>	'01',
			'V06WZ'		=>	'01',
			'V86PARCEL'	=>	'86',
			'V87PARCEL'	=>	'87',
			'V82PARCEL'	=>	'82',
		);
		
		$account_number	=	str_replace(" ","",$account_number);
		if(strlen($account_number) != 14){ // Account number is not well formatted, 10 digit ekp, 2 digit product code, 2 digit partner id
			return $account_number;
		}
		
		if(!array_key_exists($product_code, $prd_codes)){ // Invalid product code
			return $account_number;
		}
		
		$ekp		=	substr($account_number, 0, 10);
		$prtn_id	=	substr($account_number, -2);
		
		$account_number	=	$ekp.$prd_codes[$product_code].$prtn_id;
		
		return $account_number;
	}
	
	public static function is_european_country($country_code){
		if(!$country_code){
			return false;
		}
		$euro_countries	=	array(
			'RU','UA','FR','ES','SE','NO','DE','FI','PL','IT',
			'UK','RO','BY','EL','BG','IS','HU','PT','AZ','AT',
			'CZ','RS','IE','GE','LT','LV','HR','BA','SK','EE',
			'DK','CH','NL','MD','BE','AL','MK','TR','SI','ME',
			'XK','LU','MT','LI',
		);
		
		return in_array($country_code, $euro_countries)?true:false;
	}
	
	public function wf_array_to_xml($tags,$full_xml=false){//$full_xml true will contain <?xml version
		$xml_str	=	'';
		foreach($tags as $tag_name	=> $tag){
			$out	=	'';
			try{
				$xml = new SimpleXMLElement('<'.$tag_name.'/>');
				
				if(is_array($tag)){
					$this->array2XML($xml,$tag);
					
					if(!$full_xml){
						$dom	=	dom_import_simplexml($xml);
						$out.=$dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
					}
					else{
						$out.=$xml->saveXML();
					}
				}
				else{
					$out.=$tag;
				}
				
			}catch(Exception $e){
				// Do nothing
			}
			$xml_str.=$out;
		}
		return $xml_str;
	}
	
	public function array2XML($obj, $array)
	{
		foreach ($array as $key => $value)
		{
			if(is_numeric($key))
				$key = 'item' . $key;

			if (is_array($value))
			{
				$node = $obj->addChild($key);
				$this->array2XML($node, $value);
			}
			else
			{
				$obj->addChild($key, htmlspecialchars($value));
			}
		}
	}
}