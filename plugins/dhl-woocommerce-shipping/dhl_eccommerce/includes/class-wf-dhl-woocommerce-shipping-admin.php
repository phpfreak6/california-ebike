<?php
if(!class_exists('wf_dhl_ecommerce_shipping_admin'))
{
class wf_dhl_ecommerce_shipping_admin{
	
	public function __construct(){
		$this->settings 					 = get_option( 'woocommerce_'.WF_DHL_ECOMMERCE_ID.'_settings', null );
		$this->custom_services 				 = isset( $this->settings['services'] ) ? $this->settings['services'] : '';
		$this->enabled 				 = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : '';
		$this->image_type 					 = isset( $this->settings['image_type'] ) ? $this->settings['image_type'] : '';
		$this->services 					 = include( 'data-wf-service-codes.php' );
		$this->debug 						 = ( $bool = $this->settings[ 'debug' ] ) && $bool == 'yes' ? true : false;
		$this->default_domestic_service		 = isset( $this->settings['default_domestic_service'] ) ? $this->settings['default_domestic_service'] : '';
		$this->default_international_service = isset( $this->settings['default_international_service'] ) ? $this->settings['default_international_service'] : '';
		if ( $this->settings['dimension_weight_unit'] == 'KG_CM' ) {
			$this->weight_unit = 'KGS';
			$this->dim_unit    = 'CM';
		} else {
			$this->weight_unit = 'LBS';
			$this->dim_unit    = 'IN';
		}

		add_action('load-edit.php', array( $this, 'wf_orders_bulk_action' ) ); //to handle post id for bulk actions		
		add_action('admin_notices', array( $this,'bulk_label_admin_notices') );
		 

		if (is_admin() && $this->enabled === 'yes') {
			add_action('add_meta_boxes', array($this, 'wf_add_dhl_metabox'));
		}

		if ( isset( $_GET['wf_dhl_ecommerce_generate_packages'] ) ) {
			add_action( 'init', array( $this, 'wf_dhl_generate_packages_ec' ), 15 );
		}
		if (isset($_GET['wf_dhl_ecommerce_createshipment'])) {
			add_action('init', array($this, 'wf_dhl_ecommerce_createshipment'));
		}
		
		if (isset($_GET['wf_dhl_viewlabel_ec'])) {
			add_action('init', array($this, 'wf_dhl_viewlabel_ec'));
		}
		
		if (isset($_GET['wf_dhl_ec_view_commercial_invoice'])) {
			add_action('init', array($this, 'wf_dhl_ec_view_commercial_invoice'));
		}
		
	}	

	function wf_dhl_generate_packages_ec(){

		if( !$this->wf_user_permission() ) {
			echo "You don't have admin privileges to view this page.";
			exit;
		}
		
		$wfdhlmsg = '';
		$post_id	=	base64_decode($_GET['wf_dhl_ecommerce_generate_packages']);
		
		$order = $this->wf_load_order( $post_id );
		if ( !$order ) return;
		
		if ( ! class_exists( 'wf_dhl_ecommerce_shipping_admin_helper' ) )
			include_once 'class-wf-dhl-woocommerce-shipping-admin-helper.php';
		
		$woodhlwrapper = new wf_dhl_ecommerce_shipping_admin_helper();
		$packages	=	$woodhlwrapper->wf_get_package_from_order($order);
		
		foreach ($packages as $key => $package) {
			$package_data[] = $woodhlwrapper->get_dhl_packages($package);
		}
		update_post_meta( $post_id, '_wf_dhl_stored_packages_ec', $package_data );
		
		wp_redirect( admin_url( '/post.php?post='.$post_id.'&action=edit') );
		exit;
	}

	function bulk_label_admin_notices() {	 
	  global $post_type, $pagenow;

	  if( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset($_REQUEST['bulk_label']) ) {
	  	$order_ids = explode( ",", $_REQUEST['ids'] );
	  	
	  	$faild_ids_str = '';
	  	$success_ids_str = '';
	  	$already_exist_arr = explode( ',', $_REQUEST['already_exist'] );
	  	foreach ($order_ids as $key => $id) {
			$dhl_shipment_err	=	get_post_meta( $id, 'wf_woo_dhl_ecommerceshipmentErrorMessage',true );
			if( !empty($dhl_shipment_err) ){
				$faild_ids_str .= $id.', ';
			}
			elseif( !in_array( $id, $already_exist_arr ) ){
				$success_ids_str .= $id.', '; 
			}
	  	}

	  	$faild_ids_str = rtrim($faild_ids_str,', ');
	  	$success_ids_str = rtrim($success_ids_str,', ');

	  	if( $faild_ids_str != '' ){
		    echo '<div class="error"><p>' . __('Create shipment is failed for following order(s) '.$faild_ids_str, 'wf-shipping-dhl') . '</p></div>';
	  	}
		
		if( $success_ids_str != '' ){
		    echo '<div class="updated"><p>' . __('Successfully created shipment for following order(s) '.$success_ids_str, 'wf-shipping-dhl') . '</p></div>';
	  	}

	  	if( isset( $_REQUEST['already_exist'] ) && $_REQUEST['already_exist'] != '' ){
		    echo '<div class="notice notice-success"><p>' . __('Shipment already exist for following order(s) '.$_REQUEST['already_exist'] , 'wf-shipping-dhl') . '</p></div>';
	  	}

	  }
	}

	public function wf_orders_bulk_action()
	{
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$action = $wp_list_table->current_action();
		
		if ($action == 'create_ecommerce_shipment_dhl') {
			//forcefully turn off debug mode, otherwise it will die and cause to break the loop.
			$this->debug = false;
			$label_exist_for = '';
			foreach($_REQUEST['post'] as $post_id) {
				$order = $this->wf_load_order( $post_id );
				if (!$order) 
					return;
				$orderid = wf_get_order_id($order);
				
				$shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_ecommerceshipmentId', false);
				if ( !empty($shipmentIds) ) {
					$label_exist_for .= $orderid.', ';
				}
				else{
					$this->wf_create_shipment($order);
				}
			}
			
			$sendback = add_query_arg( array(
				'bulk_label' => 1, 
				'ids' => join(',', $_REQUEST['post'] ),
				'already_exist' =>rtrim( $label_exist_for, ', ' )
			), admin_url( 'edit.php?post_type=shop_order' ) );
			
			wp_redirect($sendback);
			exit();
		}
	}
	private function wf_load_order($orderId){
		if (!class_exists('WC_Order')) {
			return false;
		}
		return new WC_Order($orderId);      
	}
	
	private function wf_user_permission(){
		// Check if user has rights to generate invoices
		$current_user = wp_get_current_user();
		$user_ok = false;
		if ($current_user instanceof WP_User) {
			if (in_array('administrator', $current_user->roles) || in_array('shop_manager', $current_user->roles)) {
				$user_ok = true;
			}
		}
		return $user_ok;
	}
	
	public function wf_dhl_ecommerce_createshipment(){
		$user_ok = $this->wf_user_permission();
		if (!$user_ok) 			
			return;
		
		$order = $this->wf_load_order($_GET['wf_dhl_ecommerce_createshipment']);
		if (!$order) 
			return;
		
		
		$this->wf_create_shipment($order);
		
		if ( $this->debug ) {
            //dont redirect when debug is printed
            die();
		}
        else{			
			wp_redirect(admin_url('/post.php?post='.$_GET['wf_dhl_ecommerce_createshipment'].'&action=edit'));
			exit;
		}
		
	}
	
	public function wf_dhl_viewlabel_ec(){
		$shipmentDetails = explode('|', base64_decode($_GET['wf_dhl_viewlabel_ec']));

		if (count($shipmentDetails) != 2) {
			exit;
		}
		$shipmentId = $shipmentDetails[0]; 
		$post_id = $shipmentDetails[1]; 
		$shipping_label = get_post_meta($post_id, 'wf_woo_dhl_ecommerceshippingLabel_'.$shipmentId, true);
		header('Content-Type: application/'.$this->image_type);
		header('Content-disposition: attachment; filename="ShipmentArtifact-' . $shipmentId . '.'.$this->image_type.'"');
		print(base64_decode($shipping_label)); 
		exit;
	}
	
	public function wf_dhl_ec_view_commercial_invoice(){
		$invoiceDetails = explode('|', base64_decode($_GET['wf_dhl_ec_view_commercial_invoice']));

		if (count($invoiceDetails) != 2) {
			exit;
		}
		$image_type	=	'pdf'; //commercial invoice generated in pdf only
		$shipmentId = $invoiceDetails[0]; 
		$post_id = $invoiceDetails[1]; 
		$commercial_invoice = get_post_meta($post_id, 'wf_woo_dhl_ecommerceshipping_commercialInvoice_'.$shipmentId, true);
		header('Content-Type: application/'.$image_type);
		header('Content-disposition: attachment; filename="CommercialInvoice-' . $shipmentId . '.'.$image_type.'"');
		print(base64_decode($commercial_invoice)); 
		exit;
	}
	
	private function wf_is_service_valid_for_country($order,$service_code){
		return true; 
	}
	private function wf_get_mail_type()
	{
		if(!empty($_GET['dhl_eccommerce_mail_type'])){			
			return $_GET['dhl_eccommerce_mail_type'];			
		}
		else
		{
			return '2';
		}
	}
	private function wf_get_expacted_delivery()
	{
		if(!empty($_GET['dhl_eccommerce_delivery_date'])){			
			return $_GET['dhl_eccommerce_delivery_date'];			
		}
		else
		{
			return '1';
		}
	}

	
	private function wf_get_shipping_service($order,$retrive_from_order = false){
		
		if($retrive_from_order == true){
			$orderid = wf_get_order_id($order);
			$service_code = get_post_meta( $orderid, 'wf_woo_dhl_ecommerceservice_code', true);
			if(!empty($service_code)) 
				return $service_code;
		}

		if(!empty($_GET['dhl_ecommerce_shipping_service'])){			
			return $_GET['dhl_ecommerce_shipping_service'];			
		}

			
		$is_international = ( wf_get_order_shipping_country($order) == WC()->countries->get_base_country() ) ? false : true;
		if( $is_international ){
			if(!empty( $this->default_international_service) )
				return $this->default_international_service;
		}
		elseif( !empty($this->default_domestic_service) ){
			return $this->default_domestic_service;
		}

		//TODO: Take the first shipping method. It doesnt work if you have item wise shipping method
		$shipping_methods = $order->get_shipping_methods();
		
		if ( ! $shipping_methods ) {
			return '';
		}
	
		$shipping_method = array_shift($shipping_methods);

		return str_replace(WF_DHL_ECOMMERCE_ID.':', '', $shipping_method['method_id']);
	}
	
	public function wf_create_shipment($order){		
		 if ( ! class_exists( 'wf_dhl_ecommerce_shipping_admin_helper' ) )
			include_once 'class-wf-dhl-woocommerce-shipping-admin-helper.php';
		
		$woodhlwrapper = new wf_dhl_ecommerce_shipping_admin_helper();
		$serviceCode = $this->wf_get_shipping_service($order,false);
		$mail_type = $this->wf_get_mail_type();
		$expected_delivery = $this->wf_get_expacted_delivery();
		
		$orderid = wf_get_order_id($order);
		$woodhlwrapper->print_label($order,$serviceCode,$orderid,$mail_type,$expected_delivery);

	}
	
	public function wf_add_dhl_metabox(){
		global $post;
		if (!$post) {
			return;
		}
		
		if ( in_array( $post->post_type, array('shop_order') )) {
			$order = $this->wf_load_order($post->ID);
			if (!$order) 
				return;
		
			add_meta_box('wf_dhl_ecommerce_metabox', __('DHL Ecommerce', 'wf-shipping-dhl'), array($this, 'wf_dhl_emetabox_content'), 'shop_order', 'side', 'default');
		}
	}

	public function wf_dhl_emetabox_content(){
		global $post;
		
		if (!$post) {
			return;
		}

		$order = $this->wf_load_order($post->ID);
		if (!$order) 
			return;			
		
		$orderid = wf_get_order_id($order);

		$shipmentIds = get_post_meta($orderid, 'wf_woo_dhl_ecommerceshipmentId', false);
		
		$shipmentErrorMessage = get_post_meta($orderid, 'wf_woo_dhl_ecommerceshipmentErrorMessage',true);
		
		//Only Display error message if the process is not complete. If the Invoice link available then Error Message is unnecessary
		if(!empty($shipmentErrorMessage))
		{
			echo '<div class="error"><p>' . sprintf( __( 'DHL Ecommerce Create Shipment Error:%s', 'wf-shipping-dhl' ), $shipmentErrorMessage) . '</p></div>';
		}

		echo '<ul>';
		$selected_sevice = $this->wf_get_shipping_service($order,true);	
		if (!empty($shipmentIds)) {
			if(!empty($selected_sevice) && !empty($this->services[$selected_sevice]) )
				echo "<li>Shipping service: <strong>".$this->services[$selected_sevice]."</strong></li>";		
			
			foreach($shipmentIds as $shipmentId) {
				echo '<li><strong>Shipment #:</strong> '.$shipmentId;
				echo '<hr>';
				$packageDetailForTheshipment = get_post_meta($orderid, 'wf_woo_dhl_ecommercepackageDetails_'.$shipmentId, true);
				if(!empty($packageDetailForTheshipment)){
					foreach($packageDetailForTheshipment as $dimentionValue){
						echo $dimentionValue;
					}
				}
				$shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_ecommerceshippingLabel_'.$shipmentId, true);
				if(!empty($shipping_label)){
					$download_url = admin_url('/post.php?wf_dhl_viewlabel_ec='.base64_encode($shipmentId.'|'.$post->ID));?>
					<a class="button tips" href="<?php echo $download_url; ?>" data-tip="<?php _e('Print Label', 'wf-shipping-dhl'); ?>"><?php _e('Print Label', 'wf-shipping-dhl'); ?></a>
					<?php 
				}
				$commercial_invoice = get_post_meta($post->ID, 'wf_woo_dhl_ecommerceshipping_commercialInvoice_'.$shipmentId, true);
				if(!empty($commercial_invoice)){
					$commercial_invoice_download_url = admin_url('/post.php?wf_dhl_ec_view_commercial_invoice='.base64_encode($shipmentId.'|'.$post->ID));?>
					<a class="button tips" href="<?php echo $commercial_invoice_download_url; ?>" data-tip="<?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?>"><?php _e('Commercial Invoice', 'wf-shipping-dhl'); ?></a>
					<?php 
				}
				echo '<hr style="border-color:#0074a2"></li>';
			} ?>		
			<?php								
		}
		else {
			$stored_packages	=	get_post_meta( $post->ID, '_wf_dhl_stored_packages_ec', true );
			if(empty($stored_packages)	&&	!is_array($stored_packages)){
				echo '<strong>'.__( 'Auto generate packages.', 'wf-shipping-dhl' ).'</strong></br>';
			}else{
				$generate_url = admin_url('/post.php?wf_dhl_ecommerce_createshipment='.$post->ID);
				echo '<li>choose service:<select class="select" id="dhl_ecommerce_manual_service">';
				if($this->custom_services)
				{
				foreach($this->custom_services as $service_code => $service){
					if($service['enabled'] == true && $this->wf_is_service_valid_for_country($order,$service_code) == true){
						echo '<option value="'.$service_code.'" ' . selected($selected_sevice,$service_code,false) . ' >'.$this->services[$service_code].'</option>';
					}	
				}
			}
				echo '</select></li>';
				echo '<li>choose Mail Type:<select class="select" id="dhl_ecommerce_manual_mail_type">';
				echo '<option value="2">Irregular Parcel</option>';
				echo '<option value="3">Machinable Parcel</option>';
				echo '<option value="6">BPM Machinable</option>';
				echo '<option value="7">Parcel Select Mach</option>';
				echo '<option value="8">Parcel Select NonMach</option>';
				echo '<option value="9">Media Mail</option>';
				echo '<option value="20">Marketing Parcel < 6oz</option>';
				echo '<option value="30">Marketing Parcel >= 6oz</option>';
				
				echo '</select></li>';
				echo '<li> Expected Delivery In Days<input type="number" min="0" style="padding:5px;" id="ma_expected_delivey" value="1"></li>';
				echo '<li>';
					echo '<h4>'.__( 'Package(s)' , 'wf-shipping-dhl').': </h4>';
					echo '<table id="wf_dhl_package_list_ec" class="wf-shipment-package-table">';					
						echo '<tr>';
							echo '<th>'.__('Wt.', 'wf-shipping-dhl').'</br>('.$this->weight_unit.')</th>';
							echo '<th>'.__('L', 'wf-shipping-dhl').'</br>('.$this->dim_unit.')</th>';
							echo '<th>'.__('W', 'wf-shipping-dhl').'</br>('.$this->dim_unit.')</th>';
							echo '<th>'.__('H', 'wf-shipping-dhl').'</br>('.$this->dim_unit.')</th>';
							// echo '<th>'.__('Insur.', 'wf-shipping-dhl').'</th>';
							echo '<th>&nbsp;</th>';
						echo '</tr>';
						if( empty($stored_packages[0]) ){
							$stored_packages[0][0] = $this->get_dhl_dummy_package();
						}
						foreach($stored_packages as $package_group_key	=>	$package_group){
							if( !empty($package_group) && is_array($package_group) ){ //package group may empty if boxpacking and product have no dimensions 
								foreach($package_group as $stored_package_key	=>	$stored_package){
									$dimensions	=	$this->get_dimension_from_package($stored_package);
									if(is_array($dimensions)){
										?>
										<tr>
											<td><input type="text" id="dhl_manual_weight_ec" name="dhl_manual_weight_ec[]" size="2" value="<?php echo $dimensions['Weight'];?>" /></td>     
											<td><input type="text" id="dhl_manual_length_ec" name="dhl_manual_length_ec[]" size="2" value="<?php echo $dimensions['Length'];?>" /></td>
											<td><input type="text" id="dhl_manual_width_ec" name="dhl_manual_width_ec[]" size="2" value="<?php echo $dimensions['Width'];?>" /></td>
											<td><input type="text" id="dhl_manual_height_ec" name="dhl_manual_height_ec[]" size="2" value="<?php echo $dimensions['Height'];?>" /></td>
											<td>&nbsp;</td>
										</tr>
										<?php
									}
								}
							}
						}
					echo '</table>';
					echo '<a class="wf-action-button wf-add-button" style="font-size: 12px;" id="wf_dhl_add_package_ec">Add Package</a>';
				
				echo '</li>';
				?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('#wf_dhl_add_package_ec').on("click", function(){
							var new_row = '<tr>';
								new_row 	+= '<td><input type="text" id="dhl_manual_weight_ec" name="dhl_manual_weight_ec[]" size="2" value="0"></td>';
								new_row 	+= '<td><input type="text" id="dhl_manual_length_ec" name="dhl_manual_length_ec[]" size="2" value="0"></td>';								
								new_row 	+= '<td><input type="text" id="dhl_manual_width_ec" name="dhl_manual_width_ec[]" size="2" value="0"></td>';
								new_row 	+= '<td><input type="text" id="dhl_manual_height_ec" name="dhl_manual_height_ec[]" size="2" value="0"></td>';
								// new_row 	+= '<td><input type="text" id="dhl_manual_insurance" name="dhl_manual_insurance[]" size="2" value="0"></td>';
								new_row 	+= '<td><a class="wf_dhl_package_line_remove_ec">&#x26D4;</a></td>';
							new_row 	+= '</tr>';
							
							jQuery('#wf_dhl_package_list_ec tr:last').after(new_row);
						});
						
						jQuery(document).on('click', '.wf_dhl_package_line_remove_ec', function(){
							jQuery(this).closest('tr').remove();
						});
					});
				</script>
				<li style="display:none;">
					<label for="wf_dhl_sat_delivery">
						<input type="checkbox" style="" id="wf_dhl_sat_delivery" name="wf_dhl_sat_delivery" class=""><?php _e('Saturday Delivery', 'wf-shipping-dhl') ?>
					</label>
				</li>
				<li>
					<a class="button tips onclickdisable dhl_create_shipment_ec" href="<?php echo $generate_url; ?>" data-tip="<?php _e('Create Shipment', 'wf-shipping-dhl'); ?>"><?php _e('Create Shipment', 'wf-shipping-dhl'); ?></a>
				</li><?php
			} ?>
			<a class="button button-primary tips dhl_generate_packages_ec" href="<?php echo admin_url( '/?wf_dhl_ecommerce_generate_packages='.base64_encode($post->ID) ); ?>" data-tip="<?php _e( 'Generate Packages', 'wf-shipping-dhl' ); ?>"><?php _e( 'Generate Packages', 'wf-shipping-dhl' ); ?></a><hr style="border-color:#0074a2">
			<script type="text/javascript">
				jQuery("a.dhl_generate_packages_ec").on("click", function() {
					location.href = this.href;
				});
			</script>
			<?php

		}
		echo '</ul>';?>
		<script>
		jQuery("a.dhl_create_shipment_ec").one("click", function() {
			
			jQuery(this).click(function () { return false; });
				var manual_weight_arr 	= 	jQuery("input[id='dhl_manual_weight_ec']").map(function(){return jQuery(this).val();}).get();
				var manual_weight 		=	JSON.stringify(manual_weight_arr);
				
				var manual_height_arr 	= 	jQuery("input[id='dhl_manual_height_ec']").map(function(){return jQuery(this).val();}).get();
				var manual_height 		=	JSON.stringify(manual_height_arr);
				
				var manual_width_arr 	= 	jQuery("input[id='dhl_manual_width_ec']").map(function(){return jQuery(this).val();}).get();
				var manual_width 		=	JSON.stringify(manual_width_arr);
				
				var manual_length_arr 	= 	jQuery("input[id='dhl_manual_length_ec']").map(function(){return jQuery(this).val();}).get();
				var manual_length 		=	JSON.stringify(manual_length_arr);
				
				// var manual_insurance_arr 	= 	jQuery("input[id='dhl_manual_insurance']").map(function(){return jQuery(this).val();}).get();
				// var manual_insurance 		=	JSON.stringify(manual_insurance_arr);
				
				
			   location.href = this.href + '&weight=' + manual_weight +
				'&length=' + manual_length
				+ '&width=' + manual_width
				+ '&height=' + manual_height
				+ '&dhl_ecommerce_shipping_service=' + jQuery('#dhl_ecommerce_manual_service').val()
				+ '&dhl_eccommerce_mail_type=' + jQuery('#dhl_ecommerce_manual_mail_type').val()
				+ '&dhl_eccommerce_delivery_date=' + jQuery('#ma_expected_delivey').val();
			return false;			
		});
		</script>		
		<?php
	}

	private function get_dhl_dummy_package(){
		return array(
			'Dimensions' => array(
				'Length' => 0,
				'Width' => 0,
				'Height' => 0,
				'Units' => $this->dim_unit
			),
			'Weight' => array(
				'Value' => 0,
				'Units' => $this->weight_unit
			)
		);
	}

	public function get_dimension_from_package($package){
		$dimensions	=	array(
			'Length'	=>	0,
			'Width'		=>	0,
			'Height'	=>	0,
			'Weight'	=>	0,
		);
		
		if(!is_array($package)){ // Package is not valid
			return $dimensions;
		}
		if(isset($package['Dimensions'])){
			$dimensions['Length']	=	$package['Dimensions']['Length'];
			$dimensions['Width']	=	$package['Dimensions']['Width'];
			$dimensions['Height']	=	$package['Dimensions']['Height'];
			$dimensions['dim_unit']	=	$package['Dimensions']['Units'];
		}
		
		$dimensions['Weight']	=	$package['Weight']['Value'];
		$dimensions['weight_unit']	=	$package['Weight']['Units'];
		return $dimensions;
	}	
}
}
new wf_dhl_ecommerce_shipping_admin();
?>
