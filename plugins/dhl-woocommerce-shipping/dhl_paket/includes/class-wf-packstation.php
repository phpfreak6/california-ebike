<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('wf_packstation')){
	class wf_packstation{
		
		public function __construct(){
			$this->id	=	WF_DHL_PAKET_ID;			
			$this->init();
			
			if($this->packstation_enabled){			
				// filters
				add_filter( 'woocommerce_checkout_fields' , array($this,'checkout_packstation_field'));
				add_filter( 'woocommerce_update_order_review_fragments', array($this,'update_packstation_field'), 90, 1);
				
				add_filter('woocommerce_localisation_address_formats', array($this,'order_address_formats'), 10 );
				add_filter( 'woocommerce_formatted_address_replacements',  array($this,'order_address_replacements'),10,3  );
				
				add_filter( 'woocommerce_order_formatted_shipping_address', array($this,'order_shipping_address'), 10, 2 );
			}
			
		}
		
		function order_address_formats($formats){
			foreach ($formats as $key => $format) {
				$formats[$key]=$format."\n{packstation}"."\n{postnumber}";
			}
			return $formats;
		}
		
		function order_address_replacements( $formats, $values ){
			if(isset($values['packstation'])){
				$formats['{packstation}'] = $values['packstation'];
			}else{
				$formats['{packstation}']	=	'';
			}

			if(isset($values['postnumber'])){
				$formats['{postnumber}'] = $values['postnumber'];
			}else{
				$formats['{postnumber}']	=	'';
			}	
			return $formats;
		}
		
		private function init(){
			$this->settings 				=	get_option( 'woocommerce_'.$this->id.'_settings', null );
			$this->endpoint					=	'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/standortsuche-api/1.1/standortsuche-api-1.1.wsdl';
			
			$_stagingUrl 					=	'https://cig.dhl.de/services/sandbox/soap';
			$_productionUrl 				=	'https://cig.dhl.de/services/production/soap';
		
			$this->production      			=	( $bool = $this->settings[ 'production' ] ) && $bool == 'yes' ? true : false;
			$this->packstation_enabled      = 	( isset($this->settings[ 'packstation_enabled' ]) ) && $this->settings[ 'packstation_enabled' ] == 'yes' ? true : false;
			
			$this->service_url     			= 	($this->production == true) ? $_productionUrl  : $_stagingUrl ;
			
			
			$this->site_id         			= 	$this->production?'WooForceDHLPaket_v2_1':$this->settings[ 'site_id' ];	// Site ID and Pass static for live mode 
			$this->site_password			= 	$this->production?'uAPpRrvKCefDto0GesZeee498Tg9U4':$this->settings[ 'site_password' ];
			$this->api_user         		= 	$this->production?$this->settings[ 'api_user' ]:'2222222222_01'; // API user info is static for test mode
			$this->api_key					= 	$this->production?$this->settings[ 'api_key' ]:'pass';
			
			$this->location_key		= ""; // Key for location request, It needs to be passed even if it is blank
		}
		
		public function checkout_packstation_field($fields){
			$options	=	$this->get_packstations();			
			$fields['shipping']['shipping_packstation'] = array(		
				'label'       => __('Select Packstation', 'wf-shipping-dhl'),
				'placeholder' => '',
				'required'    => false,
				'clear'       => false,
				'type'        => 'select',
				//'class' 	  => array ('address-field', 'update_totals_on_change' ),
				'options'     => $options			
			);
			
			$fields['shipping']['shipping_packstation_postnumber'] = array(		
				'label'       => __('Post Number', 'wf-shipping-dhl'),
				'placeholder' => __('Post number for delivery to the packingstations', 'wf-shipping-dhl'),
				'required'    => false,
				'clear'       => false,
				'type'        => 'text'
			);
			
			return $fields;
		}
		
		public function update_packstation_field($fields){			
			$options	=	$this->get_packstations();
			$packstation_field	=	'<select id="shipping_packstation" name="shipping_packstation" class="select">';
				foreach($options as $val => $label){
					$packstation_field .= '<option value=\''.$val.'\'>'.$label.'</option>';
				}
			$packstation_field	.=	'</select>';
			$fields['#shipping_packstation']	=	$packstation_field;
			return $fields;
		}
		
		private function get_packstations(){
			$shipping_address 	= WC()->customer->get_shipping_address();
			$shipping_address_2 = WC()->customer->get_shipping_address_2();
			$shipping_city 		= WC()->customer->get_shipping_city();
			$shipping_postcode	= WC()->customer->get_shipping_postcode();
			$shipping_state 	= WC()->customer->get_shipping_state();
			
			$packstations	=	array('' => __('--Select Packstation--', 'wf-shipping-dhl' ));
			$request	=	array(
				'address' 	=> 	array(
					'zip'		=>	$shipping_postcode,
					'city'		=>	$shipping_city,
				),
				'key'		=>	$this->location_key,
			);
			$result		=	$this->do_request('getPackstationsByAddress', $request);
			if(isset($result->error)){
				$packstations[]= $result->error_msg; 
			}else{
				if(isset($result->packstation) && is_array($result->packstation)){
					foreach($result->packstation as $station){
						$station_values	=	base64_encode(serialize(array(
							'address'		=>	$station->address,
							'id'			=>	$station->id,
							'packstationId'	=>	$station->packstationId
						)));
						$station_label	=	$station->address->street;
						$station_label	.=	isset($station->address->streetNo) ? ' '.$station->address->streetNo : '';
						$station_label	.=	', '.$station->address->zip;
						$station_label	.=	', '.$station->address->city;
						$packstations[$station_values]	=	$station_label;
					}
				}
			}
			return $packstations;
		}
		
		private function do_request($operation, $request){
			$error	= new stdClass();
			try {
				
				$client = new WF_Soap( $this->endpoint, array('login' => $this->site_id,
							'password' => $this->site_password,
							'location' => $this->service_url,
							'soap_version' => SOAP_1_1
				));
				$result	=	$client->call($operation, $request);
				return $result;
		
			} catch ( Exception $e ) {
				$error->error	=	1;
				$error->error_msg	=	$e->getMessage();
				return $error;
			}
		}
		
		public function order_shipping_address($fields, $order){
			$packstation_info	=	self::get_order_packstation($order);
			if(!empty($packstation_info)){
				$packstation_text	=	sprintf(__('Packstation (%s): ', 'wf-shipping-dhl'),$packstation_info['packstationId']);
				$packstation_text	.=	$packstation_info['address']->street.' '.$packstation_info['address']->streetNo;
				$packstation_text	.=	', '.$packstation_info['address']->zip;
				$packstation_text	.=	', '.$packstation_info['address']->city;
				if( property_exists($packstation_info['address'],'district') ){
					$packstation_text	.=	', '.$packstation_info['address']->district;
				}
				
				$fields['packstation']	=	$packstation_text;
				$fields['postnumber']	=	'Post Number: '. self::get_order_postnumber($order);
			}
			return $fields;
		}
		
		public static function get_order_packstation($order){
			$packstation_info	=	false;
			$packstation_data	=	get_post_meta( wf_get_order_id($order),'_shipping_packstation',1);
			if(!empty($packstation_data)){
				$packstation_data_decoded	=	base64_decode($packstation_data);
				if(is_serialized($packstation_data_decoded)){
					$packstation_info	=	unserialize($packstation_data_decoded);
				}
			}
			return $packstation_info;
		}
		
		public static function get_order_postnumber($order){
			return get_post_meta( wf_get_order_id($order),'_shipping_packstation_postnumber',1);
		}
	}
}
new wf_packstation();