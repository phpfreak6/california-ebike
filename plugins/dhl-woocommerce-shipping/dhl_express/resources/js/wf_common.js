jQuery(document).ready(function(){
	wf_dhl_eprs_load_packing_method_options();
	jQuery('.packing_method').change(function(){
		wf_dhl_eprs_load_packing_method_options();
	});

	jQuery("#woocommerce_wf_dhl_shipping_advanced_settings").next('table').hide();
	jQuery(document).on("click", "#woocommerce_wf_dhl_shipping_advanced_settings", function(event){
		event.stopImmediatePropagation();
		jQuery(this).next('table').toggle(100);
	});
	
	airway_bill_check	=	jQuery('#woocommerce_wf_dhl_shipping_request_archive_airway_label').is(":checked");
	if(airway_bill_check)
	{
		jQuery('#woocommerce_wf_dhl_shipping_no_of_archive_bills').closest('tr').show();
	}else{
		jQuery('#woocommerce_wf_dhl_shipping_no_of_archive_bills').closest('tr').hide();
	}

	jQuery(document).on("change", "#woocommerce_wf_dhl_shipping_request_archive_airway_label", function(event){
		event.stopImmediatePropagation();
		if (this.checked) 
            jQuery('#woocommerce_wf_dhl_shipping_no_of_archive_bills').closest('tr').show();
        else 
            jQuery('#woocommerce_wf_dhl_shipping_no_of_archive_bills').closest('tr').hide();
		
	}).change();


	dhl_email_check	=	jQuery('#woocommerce_wf_dhl_shipping_dhl_email_notification_service').is(":checked");
	if(dhl_email_check)
	{
		jQuery('#woocommerce_wf_dhl_shipping_dhl_email_notification_message').closest('tr').show();
	}else{
		jQuery('#woocommerce_wf_dhl_shipping_dhl_email_notification_message').closest('tr').hide();
	}

	jQuery(document).on("change", "#woocommerce_wf_dhl_shipping_dhl_email_notification_service", function(event){
		event.stopImmediatePropagation();
		if (this.checked) 
            jQuery('#woocommerce_wf_dhl_shipping_dhl_email_notification_message').closest('tr').show();
        else 
            jQuery('#woocommerce_wf_dhl_shipping_dhl_email_notification_message').closest('tr').hide();
		
	}).change();

	return_label_check	=	jQuery('#woocommerce_wf_dhl_shipping_return_label_key').is(":checked");
	if(return_label_check)
	{
		jQuery('#woocommerce_wf_dhl_shipping_return_label_acc_number').closest('tr').show();
	}else{
		jQuery('#woocommerce_wf_dhl_shipping_return_label_acc_number').closest('tr').hide();
	}

	jQuery(document).on("change", "#woocommerce_wf_dhl_shipping_return_label_key", function(event){
		event.stopImmediatePropagation();
		if (this.checked) 
            jQuery('#woocommerce_wf_dhl_shipping_return_label_acc_number').closest('tr').show();
        else 
            jQuery('#woocommerce_wf_dhl_shipping_return_label_acc_number').closest('tr').hide();
		
	}).change();

});


function wf_dhl_eprs_load_packing_method_options(){
	pack_method	=	jQuery('.packing_method').val();
	jQuery('#packing_options').hide();
	jQuery('.weight_based_option').closest('tr').hide();
	jQuery('#woocommerce_wf_dhl_shipping_shp_pack_type').closest('tr').hide();
	switch(pack_method){
		case 'per_item':
			jQuery('#woocommerce_wf_dhl_shipping_shp_pack_type').closest('tr').show();
		default:
			break;
			
		case 'box_packing':
			jQuery('#packing_options').show();
			break;
			
		case 'weight_based':
			jQuery('.weight_based_option').closest('tr').show();
			break;
	}
}


jQuery(document).ready(function($) {
	"use strict";

	// Handling uploading of the logo on shipment label printing settings form.
	// Adapted from Mike Jolley
	// http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
	var file_frame;

	$('#dhl_media_upload_image_button').click(function( event ){

		event.preventDefault();
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			// Set to true to allow multiple files to be selected
			multiple: false
		});
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			var attachment = file_frame.state().get('selection').first().toJSON();
			// Send the value of attachment.url back to shipment label printing settings form
			jQuery('#wf_dhl_shipping_customer_logo_url').val(attachment.url);
		});

		// Finally, open the modal
		file_frame.open();
	});
	
	
});



