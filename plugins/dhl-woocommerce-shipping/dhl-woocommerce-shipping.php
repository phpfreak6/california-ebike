<?php
/*
	Plugin Name: DHL Express/DHL Paket WooCommerce Shipping with Print Label
	Plugin URI: https://www.xadapter.com/product/woocommerce-dhl-shipping-plugin-with-print-label/
	Description: Obtain real time shipping rates and Print shipping labels and Print shipping labels via DHL Paket Shipping API.
	Version: 3.4.17
    WC requires at least: 2.6
    WC tested up to: 3.3.3
	Author: XAdapter
	Author URI: https://www.xadapter.com/shop/
	Copyright: 2016-2017 XAdapter.
	Text Domain: wf-shipping-dhl
*/
	
	if( !defined('WF_DHL_PAKET_PATH') ){
		define("WF_DHL_PAKET_PATH", plugins_url('',__FILE__));
	}
	if( !defined('WF_DHL_PAKET_EXPRESS_ROOT_PATH') ){
		define("WF_DHL_PAKET_EXPRESS_ROOT_PATH", plugin_dir_path(__FILE__ ));
	}
	define("WF_DHL_PAKET_ID", "wf_dhl_paket_shipping");
	define("EXPRESS_FPDF_FONTPATH",plugin_dir_path(__FILE__ ).'dhl_express/includes/fpdf/font/');
	if( !defined('WF_DHL_ID') ){
		define("WF_DHL_ID", "wf_dhl_shipping");
	}
	if( !defined('WF_DHL_ECOMMERCE_ID') ){
			define("WF_DHL_ECOMMERCE_ID", "wf_dhl_ecommerce_shipping");
		}

	function wf_merge_pre_activation(){
		
	//check if basic version is there
		if ( is_plugin_active('dhl-woocommerce-shipping-method/dhl-woocommerce-shipping.php') ){
			deactivate_plugins( basename( __FILE__ ) );
			wp_die( __("Oops! You tried installing the premium version without deactivating and deleting the basic version. Kindly deactivate and delete DHL(Basic) Woocommerce Extension and then try again", "wf-shipping-dhl" ), "", array('back_link' => 1 ));
		}
	}

	register_activation_hook( __FILE__, 'wf_merge_pre_activation' );

/**
 * Check if WooCommerce is active
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {	
	
	include_once ( 'dhl-deprecated-functions.php' );
	
	if (!function_exists('wf_dhl_paket_is_eu_country')){
		function wf_dhl_paket_is_eu_country ($countrycode, $destinationcode) {
			$eu_countrycodes = array(
				'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 
				'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
				'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
				'HR', 'GR'

				);
			return(in_array($countrycode, $eu_countrycodes) && in_array($destinationcode, $eu_countrycodes));
		}
	}
	
	
if (!function_exists('wf_get_settings_url')){
	function wf_get_settings_url(){
		return version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
	}
}

if (!function_exists('wf_dhl_is_eu_country')){
	function wf_dhl_is_eu_country ($countrycode, $destinationcode) {
		$eu_countrycodes = array(
			'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 
			'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
			'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
			'HR', 'GR'

			);
		return(in_array($countrycode, $eu_countrycodes) && in_array($destinationcode, $eu_countrycodes));
	}
}
if( !class_exists('wf_dhl_wooCommerce_shipping_setup') ){
	class wf_dhl_wooCommerce_shipping_setup {
		
		public function __construct() {
			$this->wf_init();
			//	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'wf_dhl_wooCommerce_shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_dhl_wooCommerce_shipping_methods' ) );		
			add_filter( 'admin_enqueue_scripts', array( $this, 'wf_dhl_scripts' ) );	
			add_filter( 'admin_notices', array($this,'wf_dhl_key_check') ,99);
			
			
				add_action('admin_footer', array( $this,'wf_add_bulk_action_links') , 10); //to add bulk option to orders page
				add_action('woocommerce_admin_order_actions_end', array( $this, 'wf_add_print_label_buttons' )); //to add print option at the end of each orders in orders page
				if( is_admin() ){
					add_action( 'woocommerce_product_options_shipping', array($this,'wf_additional_product_shipping_options')  );
					add_action( 'woocommerce_process_product_meta', array( $this, 'wf_save_additional_product_shipping_options' ) );
				}
				include_once('dhl_express/includes/dhl-extra-fields-show.php');
			}
			function wf_dhl_key_check()
			{
				$activation_check = get_option('dhl_activation_status','no_key');
				if(empty($activation_check) || $activation_check != 'active')
				{
					echo sprintf( '<div id="message" class="error"><p>' . __( '%s - Your license is expired/not activated. Please <a href="%s">update your License</a> to avail latest updates and stability improvements.', 'wf-woocommerce-packing-list' ) . '</p></div>', '<b>DHL Express / eCommerce / Paket Shipping Plugin with Print Label </b>', admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_shipping&subtab=licence' ) ) ;
				}
			}
			function wf_additional_product_shipping_options() {
			    //HS code field
				woocommerce_wp_text_input( array(
					'id' => '_wf_hs_code',
					'label' => __('HS Tariff Number (DHL)', 'wf-shipping-dhl'),
					'description' => __('The Harmonized Commodity Description and Coding System, also known as the Harmonized System (HS) of tariff nomenclature is an internationally standardized system of names and numbers to classify traded products.'),
					'desc_tip' => 'true',
					'placeholder' => ''
					) );

			    //Country of manufacture
				woocommerce_wp_text_input( array(
					'id' => '_wf_manufacture_country',
					'label' => __('Country of manufacture (DHL)', 'wf-shipping-dhl'),
					'description' => __('A note on the country of manufacture can be updated here. This will be part of the commercial invoice. ', 'wf-shipping-dhl' ),
					'desc_tip' => 'true',
					'placeholder' => ''
					) );

				woocommerce_wp_select( array(
					'id' => '_wf_dhl_signature',
					'label' => __('Signature options (DHL)', 'wf-shipping-dhl'),
					'options'         => array( //if change here, change the 'admin-helper.php' also.
						'0'       			=> __( 'No Signature Required', 'wf-shipping-dhl' ),
						'1'       			=> __( 'Delivery Signature', 'wf-shipping-dhl' ),
						'2'       			=> __( 'Content Signature', 'wf-shipping-dhl' ),
						'3'       			=> __( 'Named Signature', 'wf-shipping-dhl' ),
						'4'       			=> __( 'Adult Signature', 'wf-shipping-dhl' ),
						'5'       			=> __( 'Contract Signature', 'wf-shipping-dhl' ),
						'6'       			=> __( 'Alternative Signature', 'wf-shipping-dhl' ),
						),
					'description' => __('All international shipments require a signature for delivery. Please choose the signature service required for this product.', 'wf-shipping-dhl' ),
					'desc_tip' => 'true',
					) );
			}

			function wf_save_additional_product_shipping_options( $post_id ) {
			    //HS code value
				if ( isset( $_POST['_wf_hs_code'] ) ) {
					update_post_meta( $post_id, '_wf_hs_code', esc_attr( $_POST['_wf_hs_code'] ) );
				}
			    //Country of manufacture
				if ( isset( $_POST['_wf_manufacture_country'] ) ) {
					update_post_meta( $post_id, '_wf_manufacture_country', esc_attr( $_POST['_wf_manufacture_country'] ) );
				}
			    //Signature option
				if ( isset( $_POST['_wf_dhl_signature'] ) ) {
					update_post_meta( $post_id, '_wf_dhl_signature', esc_attr( $_POST['_wf_dhl_signature'] ) );
				}
			}

			function wf_add_print_label_buttons( $order ){
				global $post;
				$shipmentIds = get_post_meta(wf_get_order_id($order), 'wf_woo_dhl_shipmentId', false);
				if( !empty($shipmentIds) ){ 
					$i=0;
					foreach($shipmentIds as $shipmentId) {
						$i++;
						$shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_shippingLabel_'.$shipmentId, true);
						$download_url = admin_url('/post.php?wf_dhl_viewlabel='.base64_encode($shipmentId.'|'.$post->ID));
						?>
						<a disabled class="button tips " 
						target="_blank" 
						data-tip="<?php esc_attr_e('Download DHL Express Label', 'wf-woocommerce-packing-list'); ?>" 
						href="<?php echo $download_url ?>">
						<img src="<?php echo untrailingslashit(plugins_url('/', __FILE__)) . '/dhl_express/resources/images/label-icon.png'; ?>" 
						alt="<?php esc_attr_e('Print Shipping Label', 'wf-woocommerce-packing-list'); ?>" width="14"/>
					</a><?php
				}
			}
				$return_shipmentIds = get_post_meta(wf_get_order_id($order), 'wf_woo_dhl_return_shipmentId', false);
				if( !empty($return_shipmentIds) ){ 
					$i=0;
					foreach($return_shipmentIds as $shipmentId) {
						$i++;
						$shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_shippingLabel_'.$shipmentId, true);
						$download_url = admin_url('/post.php?wf_dhl_viewreturnlabel='.base64_encode($shipmentId.'|'.$post->ID));
						?>
						<a disabled class="button tips " 
						target="_blank" 
						data-tip="<?php esc_attr_e('Download DHL Express Return Label', 'wf-woocommerce-packing-list'); ?>" 
						href="<?php echo $download_url ?>">
						<img src="<?php echo untrailingslashit(plugins_url('/', __FILE__)) . '/dhl_express/resources/images/label-icon.png'; ?>" 
						alt="<?php esc_attr_e('Print Shipping Label', 'wf-woocommerce-packing-list'); ?>" width="14"/>
					</a><?php
				}
			}
		}
		function wf_add_bulk_action_links()
		{
			global $post_type;
			if ('shop_order' == $post_type) {
				$settings 					 = get_option( 'woocommerce_'.WF_DHL_ID.'_settings', null );

				if(!empty($settings ))
				{
					$enable_shipping_label 		 = isset( $settings['enabled_label'] ) ? $settings['enabled_label'] : 'yes';
					if($enable_shipping_label  ==='yes')
					{
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('create_shipment_dhl').text('<?php _e('Create DHL Express Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action']");

						jQuery('<option>').val('create_shipment_dhl').text('<?php _e('Create DHL Express Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action2']");

						jQuery('<option>').val('create_shipment_return_dhl').text('<?php _e('Create DHL Express Return Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action']");

						jQuery('<option>').val('create_shipment_return_dhl').text('<?php _e('Create DHL Express Return Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action2']");
					});
				</script>
				<?php
			}
			 }
			}
		}

		public function wf_init() {
			include_once ( 'dhl_express/includes/class-wf-tracking-admin.php' );
			if ( is_admin() ) {				
				include_once ( 'dhl_express/includes/class-wf-dhl-woocommerce-shipping-admin.php' );
					//include api manager
				include_once ( 'wf_api_manager/wf-api-manager-config.php' );
			}	            
		}
		
		public function wf_dhl_scripts() {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'common-script', plugins_url( '/dhl_express/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'dhl-style', plugins_url( '/dhl_express/resources/css/wf_common_style.css', __FILE__ ) );
	
		}
			
			public function wf_dhl_wooCommerce_shipping_init() {
				
				include_once( 'dhl_express/includes/class-wf-dhl-woocommerce-shipping.php' );
				
			}

			
			public function wf_dhl_wooCommerce_shipping_methods( $methods ) {
				$methods[] = 'wf_dhl_woocommerce_shipping_method';
				return $methods;
			}

		}
		new wf_dhl_wooCommerce_shipping_setup();	
	}
	if( !class_exists('wf_dhl_paket_wooCommerce_shipping_setup') ){
		class wf_dhl_paket_wooCommerce_shipping_setup {
			
			public function __construct() {
				add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
				
				$this->wf_init();
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				add_action( 'woocommerce_shipping_init', array( $this, 'wf_dhl_paket_wooCommerce_shipping_init' ) );
				add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_dhl_paket_wooCommerce_shipping_methods' ) );		
				add_filter( 'admin_enqueue_scripts', array( $this, 'wf_dhl_paket_scripts' ) );		
			}
			
			public function wf_init() {
				include_once ( 'dhl_paket/includes/class-wf-tracking-admin.php' );
				include_once ( 'dhl_paket/includes/class-wf-packstation.php' );
				include_once ( 'dhl_paket/includes/class-wf-soap.php' );
				if ( is_admin() ) {
				// Add Notice Class
					include_once ( 'dhl_paket/includes/class-wf-admin-notice.php' );
					
				// Admin functionality				
					include_once ( 'dhl_paket/includes/class-wf-dhl-paket-woocommerce-shipping-admin.php' );
					
				// Include api manager
					include_once ( 'wf_api_manager/wf-api-manager-config.php' );
					
					include_once ( 'dhl_paket/includes/class-wf-admin-options.php' );
				}
				
			}
			
			public function wf_dhl_paket_scripts() {
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'wf-dhl_pak-script', plugins_url( '/dhl_paket/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_style( 'wf-dhl-pak-style', plugins_url( '/dhl_paket/resources/css/wf_common_style.css', __FILE__ ));
			}
			
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_shipping' ) . '">' . __( 'DHL Express', 'wf-shipping-dhl' ) . '</a>',
					
					'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_paket_woocommerce_shipping_method' ) . '">' . __( 'DHL Paket', 'wf-shipping-dhl' ) . '</a>',

					'<a href="' . admin_url( 'admin.php?page=' . wf_get_settings_url() . '&tab=shipping&section=wf_dhl_ecommerce_shipping_method' ) . '">' . __( 'DHL Ecommerce', 'wf-shipping-dhl' ) . '</a>',
					
					'<a href="https://www.xadapter.com/category/product/woocommerce-dhl-express-shipping-plugin-with-print-label/" target="_blank">' . __('Documentation', 'wf-shipping-dhl') . '</a>',
					'<a href="https://www.xadapter.com/online-support/" target="_blank">' . __('Support', 'wf-shipping-dhl') . '</a>'
					);
				return array_merge( $plugin_links, $links );
			}			
			
			public function wf_dhl_paket_wooCommerce_shipping_init() {
				include_once( 'dhl_paket/includes/class-wf-dhl-paket-woocommerce-shipping.php' );
			}

			
			public function wf_dhl_paket_wooCommerce_shipping_methods( $methods ) {
				$methods[] = 'wf_dhl_paket_woocommerce_shipping_method';
				return $methods;
			}

		/**
         * Handle localisation
         */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'wf-shipping-dhl', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
		}
	}
	new wf_dhl_paket_wooCommerce_shipping_setup();	
}

	if( !class_exists('WF_DHL_Ecommerce_Shipping_Setup') ){
	class WF_DHL_Ecommerce_Shipping_Setup {
		
		public function __construct() {
			$this->wf_init();
			//	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_action( 'woocommerce_shipping_init', array( $this, 'wf_dhl_eCommerce_shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'wf_dhl_eCommerce_shipping_methods' ) );		
			add_filter( 'admin_enqueue_scripts', array( $this, 'wf_dhl_ecommerce_scripts' ) );		
			
				add_action('admin_footer', array( $this,'wf_add_bulk_ecommerce_action_links') , 10); //to add bulk option to orders page
				add_action('woocommerce_admin_order_actions_end', array( $this, 'wf_add_ecommerce_print_label_buttons' )); //to add print option at the end of 
			}

		
			function wf_add_ecommerce_print_label_buttons( $order ){
				global $post;
				$shipmentIds = get_post_meta(wf_get_order_id($order), 'wf_woo_dhl_ecommerce_shipmentId', false);
				if( !empty($shipmentIds) ){ 
					$i=0;
					foreach($shipmentIds as $shipmentId) {
						$i++;
						$shipping_label = get_post_meta($post->ID, 'wf_woo_dhl_eccommerce_shippingLabel_'.$shipmentId, true);
						$download_url = admin_url('/post.php?wf_dhl_eccommerce_viewlabel='.base64_encode($shipmentId.'|'.$post->ID));
						?>
						<a disabled class="button tips " 
						target="_blank" 
						data-tip="<?php esc_attr_e('Download DHL Ecommerce Label', 'wf-woocommerce-packing-list'); ?>" 
						href="<?php echo $download_url ?>">
						<img src="<?php echo untrailingslashit(plugins_url('/', __FILE__)) . '/dhl_eccommerce/resources/images/label-icon.png'; ?>" 
						alt="<?php esc_attr_e('Print Shipping Label', 'wf-woocommerce-packing-list'); ?>" width="14"/>
					</a><?php
				}
			}
		}

		function wf_add_bulk_ecommerce_action_links()
		{
			global $post_type;
			if ('shop_order' == $post_type) {
				$settings 					 = get_option( 'woocommerce_'.WF_DHL_ECOMMERCE_ID.'_settings', null );
				if(!empty($settings ) && isset($settings['enabled']) && $settings['enabled'] ==='yes' )
				{
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('<option>').val('create_ecommerce_shipment_dhl').text('<?php
						_e('Create DHL Ecommerce Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action']");

						jQuery('<option>').val('create_ecommerce_shipment_dhl').text('<?php
						_e('Create DHL Ecommerce Shipment', 'wf-shipping-dhl') ?>'
						).appendTo("select[name='action2']");
					});
				</script>
				<?php
				}
			}
		}

		public function wf_init() {
			if ( is_admin() ) {				
				include_once ( 'dhl_eccommerce/includes/class-wf-dhl-woocommerce-shipping-admin.php' );
					//include api manager
				include_once ( 'wf_api_manager/wf-api-manager-config.php' );
			}	            
		}
		
		public function wf_dhl_ecommerce_scripts() {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'common-script', plugins_url( '/dhl_eccommerce/resources/js/wf_common.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'dhl-style', plugins_url( '/dhl_eccommerce/resources/css/wf_common_style.css', __FILE__ ) );
		}
			
			public function wf_dhl_eCommerce_shipping_init() {
				include_once( 'dhl_eccommerce/includes/class-wf-dhl-woocommerce-shipping.php' );
			}

			
			public function wf_dhl_eCommerce_shipping_methods( $methods ) {
				$methods[] = 'wf_dhl_ecommerce_shipping_method';
				return $methods;
			}

		}
		new WF_DHL_Ecommerce_Shipping_Setup();	
	}
}