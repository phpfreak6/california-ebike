jQuery(document).ready(function(){
	wf_dhl_paket_toggle_api_settings_fields();
	// Toggle Credential Fields
	jQuery('#woocommerce_wf_dhl_paket_shipping_production').click(function(){
		wf_dhl_paket_toggle_api_settings_fields();
	});
});

function wf_dhl_paket_toggle_api_settings_fields(){
	var live_mode	=	jQuery('#woocommerce_wf_dhl_paket_shipping_production').is(':checked');
	if(live_mode){
		
		jQuery('#woocommerce_wf_dhl_paket_shipping_site_id').closest('tr').hide();
		jQuery('#woocommerce_wf_dhl_paket_shipping_site_password').closest('tr').hide();
		jQuery('#woocommerce_wf_dhl_paket_shipping_api_user').closest('tr').show();
		jQuery('#woocommerce_wf_dhl_paket_shipping_api_key').closest('tr').show();
	}else{
		
		jQuery('#woocommerce_wf_dhl_paket_shipping_site_id').closest('tr').show();
		jQuery('#woocommerce_wf_dhl_paket_shipping_site_password').closest('tr').show();
		jQuery('#woocommerce_wf_dhl_paket_shipping_api_user').closest('tr').hide();
		jQuery('#woocommerce_wf_dhl_paket_shipping_api_key').closest('tr').hide();
	}		
}