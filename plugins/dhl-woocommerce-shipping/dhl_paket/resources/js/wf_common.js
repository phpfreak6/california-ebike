jQuery(document).ready(function(){
	
	// Toggle Packing Methods
	wf_dhl_pkt_load_packing_method_options();
	jQuery('.packing_method').change(function(){
		wf_dhl_pkt_load_packing_method_options();
		
	});
	
	// Advance settings tab
	jQuery('.woocommerce_wf_dhl_paket_shipping_advanced_settings').next('table').hide();
	jQuery('.woocommerce_wf_dhl_paket_shipping_advanced_settings').click(function(){
		jQuery(this).next('table').toggle();
	});
});

function wf_dhl_pkt_load_packing_method_options(){
	pack_method	=	jQuery('.packing_method').val();
	
	jQuery('#packing_options').hide();
	jQuery('.weight_based_option').closest('tr').hide();
	switch(pack_method){
		
		case 'box_packing':
			jQuery('#packing_options').show();
			break;
			
		case 'weight_based':
			jQuery('.weight_based_option').closest('tr').show();
			break;
			
		case 'per_item':
			jQuery('#packing_options').hide();
			break;
			
		default:
			break;
			
		
	}
}