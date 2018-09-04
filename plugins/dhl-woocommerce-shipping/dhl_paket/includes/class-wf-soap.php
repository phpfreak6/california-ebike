<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

if(!class_exists('WF_Soap')){
	class WF_Soap{
		var $client;
		function __construct($wsdl, $params){
			if($this->is_soap_available()){
				$this->client	=	new SoapClient( $wsdl, $params );
			}
			else{
				if(!class_exists('nusoap_client')){
					require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/nusoap/lib/nusoap.php';
				}
				$this->client = new nusoap_client( $wsdl, 'wsdl' );
			}
		}
		
		public function call($method, $params){
			if($this->is_soap_available()){
				$response	=	call_user_func(array($this->client, $method), $params);
			}
			else{
				$response	=	$this->client->call($method, $params);
				$response	=	json_decode(json_encode($response), FALSE);
			}
			return $response;
		}
		
		public  static function is_soap_available(){
			if( extension_loaded( 'soap' ) ){
				return true;
			}
			return false;
		}
	}
}