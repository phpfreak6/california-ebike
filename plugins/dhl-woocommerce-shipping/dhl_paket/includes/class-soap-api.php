<?php
class soapAPI{
	var $api_url;
	var $api_url_params;
	var $authentication;
	var $service_url;
	var $site_id;
	var $site_password;
	
	function __construct(){
		$this->api_url='https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/geschaeftskundenversand-api/1.0/geschaeftskundenversand-api-1.0.wsdl';
		
									
		$this->authentication=array(
            'user'=>'geschaeftskunden_api',
            'signature'=>'Dhl_ep_test1',
			'type'=>0
        );
		$this->settings = get_option( 'woocommerce_'.WF_DHL_PAKET_ID.'_settings', null );
		$this->production      = ( $bool = $this->settings[ 'production' ] ) && $bool == 'yes' ? true : false;
		
		$_stagingUrl = 'https://cig.dhl.de/services/sandbox/soap';
		$_productionUrl = 'https://cig.dhl.de/services/production/soap';
		
		$this->service_url     = ($this->production == true) ? $_productionUrl  : $_stagingUrl ;
		
		$this->site_id         = $this->settings[ 'site_id' ];
		$this->site_password	= $this->settings[ 'site_password' ];
		
		$this->api_user         = $this->settings[ 'api_user' ];
		$this->api_key			= $this->settings[ 'api_key' ];
						
		$this->service_url     = ($this->production == true) ? $_productionUrl  : $_stagingUrl ;
		
		$this->client = 	new SoapClient($this->api_url,
					array('login' => $this->site_id,
						'password' => $this->site_password,
						'location' => $this->service_url,
						'soap_version' => SOAP_1_1)
					);
		$authentication=array(
            'user'=>$this->api_user,
            'signature'=>$this->api_key,
			'type'=>0
        );
		
		$authHeader = new SoapHeader('http://dhl.de/webservice/cisbase', 'Authentification', $authentication);
        //int_r($authHeader);
        $this->client->__setSoapHeaders($authHeader);
	}
	
	function createShipmentDD($request){
		$response = $this->createShipmentDD($request);pre($response);exit;
		return $response;
	}
}