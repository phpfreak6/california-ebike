jQuery(document).ready(function(){
	wf_dhl_ecm_load_ec_packing_method_options();
	jQuery('.ec_packing_method').change(function(){
		wf_dhl_ecm_load_ec_packing_method_options();
	});

	jQuery("#woocommerce_wf_dhl_shipping_advanced_settings_ec").next('table').hide();
	jQuery(document).on("click", "#woocommerce_wf_dhl_shipping_advanced_settings_ec", function(){
		jQuery(this).next('table').toggle(100);
	})
});

function wf_dhl_ecm_load_ec_packing_method_options(){
	pack_method	=	jQuery('.ec_packing_method').val();
	jQuery('#ec_packing_options').hide();
	jQuery('.weight_based_option_ec').closest('tr').hide();
	jQuery('#woocommerce_wf_dhl_shipping_shp_pack_type_ec').closest('tr').hide();
	switch(pack_method){
		case 'per_item':
			jQuery('#woocommerce_wf_dhl_shipping_shp_pack_type_ec').closest('tr').show();
		default:
			break;
			
		case 'box_packing':
			jQuery('#ec_packing_options').show();
			break;
			
		case 'weight_based':
			jQuery('.weight_based_option_ec').closest('tr').show();
			break;
	}
}