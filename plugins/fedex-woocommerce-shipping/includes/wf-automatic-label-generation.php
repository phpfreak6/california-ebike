<?php
	$shipping_setting =get_option('woocommerce_wf_fedex_woocommerce_shipping_settings');

	if(isset($shipping_setting['automate_package_generation']) && $shipping_setting['automate_package_generation']=='yes' )
	{
		add_action( 'woocommerce_order_status_changed', 'wf_automatic_package_and_label_generation_fedex', 100, 4 );
	}
	
	function wf_automatic_package_and_label_generation_fedex( $order_id, $order_status_old, $order_status_new, $order )
	{	
		$shipping_setting_fedex =get_option('woocommerce_wf_fedex_woocommerce_shipping_settings');
		$allowed_order_status = apply_filters( 'xa_automatic_label_generation_allowed_order_status', array('processing'), $order_status_new, $order_id );	// Allowed order status for automatic label generation
		
		// Stop automatic package generation when order status is changed and order status not found in allowed order status
		if( ! in_array($order_status_new, $allowed_order_status) ) {
			if( $shipping_setting_fedex['debug'] == 'yes' ) {
				WC_Admin_Meta_Boxes::add_error( __( "Since Order Status is ", 'wf-shipping-fedex' ).$order_status_new.__( ". Automatic label generation has been suspended (Fedex).", 'wf-shipping-fedex' ) );
			}
			return;
		}
		if( ! ($order instanceof WC_Order) ) {
			$order = new WC_Order($order_id);
		}
		
		$order_items = $order->get_items();
		if( empty($order_items) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Fedex - No product Found. Please check the products in order.', 'wf-shipping-fedex' ) );
			return;
		}
		
		//  Automatically Generate Packages
		$current_minute=(integer)date('i');
		$package_url=admin_url( '/post.php?wf_fedex_generate_packages='.base64_encode($order_id).'&auto_generate='.md5($current_minute) );
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$package_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$output=curl_exec($ch);
		if( ! $output && curl_errno($ch) ) {
			WC_Admin_Meta_Boxes::add_error( __( 'Fedex - Curl error while automatic package generation. Error number - ', 'wf-shipping-fedex' ). curl_errno($ch) );
		}
		curl_close($ch);
	}
	
	function wf_get_shipping_service($order,$retrive_from_order = false, $shipment_id=false){
		
		if($retrive_from_order == true){
			$service_code = get_post_meta($order->id, 'wf_woo_fedex_service_code'.$shipment_id, true);
			if(!empty($service_code)) return $service_code;
		}
		
		if(!empty($_GET['service'])){			
		    $service_arr    =   json_decode(stripslashes(html_entity_decode($_GET["service"])));  
			return $service_arr[0];
		}

		$settings = get_option( 'woocommerce_'.WF_Fedex_ID.'_settings', null );

		//Origin coutry without state
		$origin_country = current( explode( ':', $settings['origin_country'] ) ) ;
		
		if( $origin_country == $order->get_shipping_country() ){
			if( !empty($settings['default_dom_service']) ){
				return $settings['default_dom_service'];
			}
		}else{
			if( !empty($settings['default_int_service']) ){
				return $settings['default_int_service'];
			}
		}

		//TODO: Take the first shipping method. It doesnt work if you have item wise shipping method
		$shipping_methods = $order->get_shipping_methods();
		
		if ( ! $shipping_methods ) {
			return '';
		}
	
		$shipping_method = array_shift($shipping_methods);

		return str_replace(WF_Fedex_ID.':', '', $shipping_method['method_id']);
	}
	
	if(isset($shipping_setting['automate_label_generation']) && $shipping_setting['automate_label_generation']=='yes' ){	
		add_action('wf_after_package_generation','wf_auto_genarate_label_fedex',2,2);
	}

	function wf_auto_genarate_label_fedex($order_id,$package_data){
		if( empty($package_data[key($package_data)]) ){
			WC_Admin_Meta_Boxes::add_error('Fedex Automatic label generation Failed. Please check product weight and Dimension.');
		}
		else {
			/// Automatically Generate Labels
			$current_minute=(integer)date('i');
			$package_url=admin_url( '/post.php?wf_fedex_createshipment='.$order_id.'&auto_generate='.md5($current_minute) );

			$service_code=wf_get_shipping_service( new WC_Order($order_id) );
			$weight=array();
			$length=array();
			$width=array();
			$height=array();
			$services=array();
			foreach($package_data as $key=>$val)
			{	
				foreach($val as $key2=>$package)
				{	//error_log('weight='.$package['Weight']['Value']);
					if(isset($package['Weight'])) $weight[]=$package['Weight']['Value'];
					if(isset($package['Dimensions']))
					{
						$length[]=$package['Dimensions']['Length'];
						$width[]=$package['Dimensions']['Width'];
						$height[]=$package['Dimensions']['Height'];
					}
					$services[]=$service_code;
				}
			}
			$package_url.='&weight=["'.implode('","',$weight).'"]';
			$package_url.='&length=["'.implode('","',$length).'"]';
			$package_url.='&width=["'.implode('","',$width).'"]';
			$package_url.='&height=["'.implode('","',$height).'"]';
			$package_url.='&service=["'.implode('","',$services).'"]';
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$package_url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			@$output=curl_exec($ch);
			if( ! $output && curl_errno($ch) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Fedex - Curl error while automatic label generation. Error number - ', 'wf-shipping-fedex' ). curl_errno($ch) );
			}
			curl_close($ch);
		}
	}
	if(isset($shipping_setting['auto_email_label']) && $shipping_setting['auto_email_label']=='yes' )
	{		
		add_action('wf_label_generated_successfully','wf_after_label_generation_fedex',3,3);
	}


	function wf_after_label_generation_fedex($shipment_id,$encoded_label_image,$order_id)
	{	
		$shipping_setting2 =get_option('woocommerce_wf_fedex_woocommerce_shipping_settings');
		if(isset($shipping_setting2['email_content']) && !empty($shipping_setting2['email_content']))
		{
			$emailcontent=$shipping_setting2['email_content'];
		}
		else
		{
			$emailcontent= ' ';
		}
		unset($shipping_setting2);
		if(!empty($shipment_id))
		{
			$order = new WC_Order( $order_id );
			$to_emails = apply_filters( 'xa_add_email_addresses_to_send_label', array( $order->get_billing_email() ), $shipment_id, $order);

			$subject = 'Shipment Label For Your Order';
			$img_url=admin_url('/post.php?wf_fedex_viewlabel='.base64_encode($shipment_id.'|'.$order_id));
			$body = "Please Download the label
			<html>
			<body>	<div>".$emailcontent."</div> </br>
					<a href='".$img_url."' ><input type='button' value='Download the label here' /> </a>
			</body>
			</html>
					";
			$headers = array('Content-Type: text/html; charset=UTF-8');
			foreach($to_emails as $to)
			{
				wp_mail( $to, $subject, $body, $headers );
			}
		}
	
	}

	if(isset($shipping_setting['allow_label_btn_on_myaccount']) && $shipping_setting['allow_label_btn_on_myaccount']=='yes' )
	{	
		add_action('woocommerce_view_order','wf_add_view_shippinglabel_button_on_myaccount_order_page_fedex');
	}
	function wf_add_view_shippinglabel_button_on_myaccount_order_page_fedex($order_id)
	{
		$shipment_id= get_post_meta($order_id,'wf_woo_fedex_shipmentId',true);
		if(!empty($shipment_id))
		{
			$img_url=admin_url('/post.php?wf_fedex_viewlabel='.base64_encode($shipment_id.'|'.$order_id));
			echo ' </br><a href="'.$img_url.'" ><input type="button" value="Download Shipping Label here" class="button" /> </a> </br></br>';			
		}

	}
	unset($shipping_setting);