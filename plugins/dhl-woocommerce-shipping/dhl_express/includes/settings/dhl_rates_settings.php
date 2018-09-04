<?php $this->init_settings(); 
global $woocommerce;
$wc_main_settings = array();
if(isset($_POST['wf_dhl_rates_save_changes_button']))
{
	$wc_main_settings = get_option('woocommerce_wf_dhl_shipping_settings');	
	$wc_main_settings['delivery_time'] = (isset($_POST['wf_dhl_shipping_delivery_time'])) ? 'yes' : '';
	$wc_main_settings['request_type'] = (isset($_POST['wf_dhl_shipping_request_type'])) ? 'ACCOUNT' : 'LIST';
	$wc_main_settings['show_dhl_extra_charges'] = (isset($_POST['wf_dhl_shipping_show_dhl_extra_charges'])) ? 'yes' : '';
	$wc_main_settings['offer_rates'] = (isset($_POST['wf_dhl_shipping_offer_rates'])) ? 'cheapest' : 'all';
	$wc_main_settings['title'] = (isset($_POST['wf_dhl_shipping_title'])) ? sanitize_text_field($_POST['wf_dhl_shipping_title']) :  __( 'DHL', 'wf-shipping-dhl' );
	$wc_main_settings['availability'] = (isset($_POST['wf_dhl_shipping_availability']) && $_POST['wf_dhl_shipping_availability'] ==='all') ? 'all' : 'specific';

	$wc_main_settings['services'] = $_POST['dhl_service'];

	if($wc_main_settings['availability'] === 'specific')
	{
		$wc_main_settings['countries'] = isset($_POST['wf_dhl_shipping_countries']) ? $_POST['wf_dhl_shipping_countries'] : '';
	}
	
	update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
	
}

$general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
$this->custom_services = isset($general_settings['services']) ? $general_settings['services'] : array();

?>
<table>
	<tr valign="top" ">
		<td style="width:30%;font-weight:800;">
			<label for="wf_dhl_shipping_delivery_time"><?php _e('Show/Hide','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">

				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_delivery_time" id="wf_dhl_shipping_delivery_time" style="" value="yes" <?php echo (isset($general_settings['delivery_time']) && $general_settings['delivery_time'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show Delivery Time','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('on enabling this, estimated delivery date will shown for each service of DHL.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_request_type" id="wf_dhl_shipping_request_type" style="" value="yes" <?php echo (isset($general_settings['request_type']) && $general_settings['request_type'] ==='ACCOUNT') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show DHL Account Rates','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On enabling this, the plugin will fetch the account specific rates of the shipper.','wf-shipping-dhl') ?>"></span>
			</fieldset>

			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_show_dhl_extra_charges" id="wf_dhl_shipping_show_dhl_extra_charges" style="" value="yes" <?php echo (isset($general_settings['show_dhl_extra_charges']) && $general_settings['show_dhl_extra_charges'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show Break Down Charges','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On enabling this, the customer will be shown the breakdown of shipping charges in the cart/checkout page. The breakdown includes weight charges, DHL handling charges, remote area surchage.','wf-shipping-dhl') ?>"></span>
			</fieldset>

			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_offer_rates" id="wf_dhl_shipping_offer_rates" style="" value="yes" <?php echo (isset($general_settings['offer_rates']) && $general_settings['offer_rates'] ==='cheapest') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show Cheapest Rates Only','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On enabling this, the cheapest rate will be shown in the cart/checkout page.','wf-shipping-dhl') ?>"></span>
			</fieldset>
		</td>
	</tr>
	<tr valign="top" >
		<td style="width:30%;font-weight:800;">
			<label for="wf_dhl_shipping_"><?php _e('Method Config','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<label for="wf_dhl_shipping_title"><?php _e('Method Title / Availability','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Provide the Method title which will be reflected as the service name if Show cheapest rates only is enabled.','wf-shipping-dhl') ?>"></span>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_title" id="wf_dhl_shipping_title" style="" value="<?php echo (isset($general_settings['title'])) ? $general_settings['title'] : __( 'DHL', 'wf-shipping-dhl' ); ?>" placeholder=""> 
			</fieldset>
			
			<fieldset style="padding:3px;">
				<?php if(isset($general_settings['availability']) && $general_settings['availability'] ==='specific')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_availability"  id="wf_dhl_shipping_availability1" value="all" placeholder=""> <?php _e('Supports All Countries','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio"  name="wf_dhl_shipping_availability" checked="true" id="wf_dhl_shipping_availability2"  value="specific" placeholder=""> Supports <?php _e('Specific Countries','wf-shipping-dhl') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_availability" checked="true" id="wf_dhl_shipping_availability1"  value="all" placeholder=""> <?php _e('Supports All Countries','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_availability" id="wf_dhl_shipping_availability2"  value="specific" placeholder=""> <?php _e('Supports Specific Countries','wf-shipping-dhl') ?>
				<?php } ?>
			</fieldset>
			<fieldset style="padding:3px;" id="dhl_spacific">
				<label for="wf_dhl_shipping_countries"><?php _e('Specific Countries','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('You can select the shipping method to be available for all countries or selective countries.','wf-shipping-dhl') ?>"></span><br/>

				<select class="chosen_select" multiple="true" name="wf_dhl_shipping_countries[]" >
					<?php 
					$woocommerce_countys = $woocommerce->countries->get_countries();
					$selected_country =  (isset($general_settings['countries']) && !empty($general_settings['countries']) ) ? $general_settings['countries'] : array($woocommerce->countries->get_base_country());

					
					foreach ($woocommerce_countys as $key => $value) {
						if(in_array($key, $selected_country))
						{
							echo '<option value="'.$key.'" selected>'.$value.'</option>';
						}
						echo '<option value="'.$key.'">'.$value.'</option>';
					}
					?>
				</td>
			</fieldset>
		</tr>
		<tr valign="top" ">
			<td colspan="2">
				<?php
				include( WF_DHL_PAKET_EXPRESS_ROOT_PATH.'dhl_express/includes/html-wf-services.php' );
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:right;padding-right: 10%;">
				<br/>
				<input type="submit" value="<?php _e('Save Changes','wf-shipping-dhl') ?>" class="button button-primary" name="wf_dhl_rates_save_changes_button">
				
			</td>
		</tr>

	</table>
	<script type="text/javascript">

		
		jQuery(window).load(function(){
			
			jQuery('#wf_dhl_shipping_availability1').change(function(){
				jQuery('#dhl_spacific').hide();

			}).change();

			jQuery('#wf_dhl_shipping_availability2').change(function(){
				if(jQuery('#wf_dhl_shipping_availability2').is(':checked')) {
					jQuery('#dhl_spacific').show();
				}else
				{
					jQuery('#dhl_spacific').hide();
				}
			}).change();

		});

	</script>