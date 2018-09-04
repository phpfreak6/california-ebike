<?php 
	if(!class_exists('WF_Dhl_Extra_Meta_Fields_Class'))
	{
		class WF_Dhl_Extra_Meta_Fields_Class
		{
			public function __construct()
			{
				$settings 				= get_option( 'woocommerce_'.WF_DHL_ID.'_settings', null );
				$delivery_time			= false;
				$show_dhl_extra_charges = '';
				if(!empty($settings) && isset($settings))
				{
					$show_dhl_extra_charges = isset($settings['show_dhl_extra_charges']) ? $settings['show_dhl_extra_charges'] : '' ;
					$del_bool         =  isset($settings['delivery_time']) ? $settings['delivery_time'] : 'no' ;
					$delivery_time   = ($del_bool == 'yes') ? true : false;

				}

				if(!empty($show_dhl_extra_charges) && $show_dhl_extra_charges === 'yes')
				{
					add_filter( 'woocommerce_cart_shipping_method_full_label', array($this, 'wf_add_extra_charges'), 10, 2 );
				}
				
				// Disply estimate delivery time
				if($delivery_time) {
					add_filter( 'woocommerce_cart_shipping_method_full_label', array($this, 'wf_add_delivery_time'), 10, 2 );
				}
				
				$this->insurance_content = isset($settings['insure_contents']) ? $settings['insure_contents'] : '' ;
				$this->insurance_content_chk = isset($settings['insure_contents_chk']) ? $settings['insure_contents_chk'] : '' ;
				
				if(!empty($this->insurance_content) && $this->insurance_content === 'yes' && !empty($this->insurance_content_chk) && $this->insurance_content_chk === 'yes')
				{
					add_filter( 'woocommerce_checkout_fields' ,array($this, 'wf_dhl_custom_override_checkout_fields') );
				}
				
				add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'wf_dhl_woocommerce_cart_shipping_packages' ));
              
			}
			
			function wf_dhl_woocommerce_cart_shipping_packages($shipping = array())
			{
				
			   foreach($shipping as $key=>$val)
				{
					$str = "";
					if(isset($_POST['post_data']))
					{
						parse_str($_POST['post_data'],$str);
					}
					if(isset($str['dhl_insurance']))
					{
						$shipping[$key]['dhl_insurance']=true;
					}
					else if(!empty($this->insurance_content) && $this->insurance_content === 'yes' && !empty($this->insurance_content_chk) && $this->insurance_content_chk === 'yes')
					{
						$shipping[$key]['dhl_insurance']=false;
					}
					else if(!empty($this->insurance_content) && $this->insurance_content == 'yes')
					{
						$shipping[$key]['dhl_insurance']=true;
					}else
					{
						$shipping[$key]['dhl_insurance']=false;
					}
					
					if(isset($_POST['dhl_insurance']))
					{
						$shipping[$key]['dhl_insurance']=true;
					}
					
					
				}
                return $shipping;
			}
			
			function wf_dhl_custom_override_checkout_fields( $fields ) {

				$fields['billing']['dhl_insurance'] = array(
					'label' => 'Enable DHL Shipping Insurance',
					'type'  => 'checkbox',
					'required' => 0,
					'default'   => true,
					'class' => array ( 'update_totals_on_change' )
				);
				return $fields;
			} 
			
			public function wf_add_delivery_time( $label, $method ) {
				if( !is_object($method) ){
					return $label;
				}
				$est_delivery = $method->get_meta_data();
				if( isset($est_delivery['dhl_delivery_time']) ){
					$est_delivery_html = "<br /><small>".__('Est delivery: ', 'wf-shipping-dhl'). $est_delivery['dhl_delivery_time'].'</small>';
					$est_delivery_html = apply_filters( 'wf_dhl_estimated_delivery', $est_delivery_html, $est_delivery );
					$label .= $est_delivery_html;
				}
				return $label;
			}

			public function wf_add_extra_charges( $label, $method ) {
				if( !is_object($method) ){
					return $label;
				}
				$extra_charges = $method->get_meta_data();
				$tax_calculation_amount = 0;
				foreach ($method->taxes as $value) {
				   $tax_calculation_amount += $value;
				}
					if( isset($extra_charges['weight_charge']) ){
						$tax_calculation_html = '';
						if($tax_calculation_amount > 0)
						{
							$check_tax_type = get_option('woocommerce_tax_display_cart');
							if($check_tax_type != 'excl')
							{
								$tax_calculation_html = '<small>+ '.__('Taxes: ', 'wf-shipping-dhl').wc_price($tax_calculation_amount).'</small>';
							}
						}
					$extra_charges_html = "<br /><small>".__('Weight Charges: ', 'wf-shipping-dhl'). wc_price($extra_charges['weight_charge']). ' + '.__('DHL Handling Charges: ', 'wf-shipping-dhl'). wc_price($extra_charges['extra_charge']).' (Inc Tax,Ship,etc.) </small>'.$tax_calculation_html;
				   // $est_delivery_html = apply_filters( 'wf_dhl_estimated_delivery', $est_delivery_html, $est_delivery );
					$label .= $extra_charges_html;
					}

				return $label;
			}
		}
	}
	new WF_Dhl_Extra_Meta_Fields_Class();