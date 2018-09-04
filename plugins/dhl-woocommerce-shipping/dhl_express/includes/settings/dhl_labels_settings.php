<?php $this->init_settings(); 
global $woocommerce;
$wc_main_settings = array();
$print_size = array('8X4_A4_PDF'=>'8X4_A4_PDF','8X4_thermal'=>'8X4_thermal','8X4_A4_TC_PDF'=>'8X4_A4_TC_PDF','8X4_CI_PDF'=>'8X4_CI_PDF','8X4_CI_thermal'=>'8X4_CI_thermal','8X4_RU_A4_PDF'=>'8X4_RU_A4_PDF','8X4_PDF'=>'8X4_PDF','8X4_CustBarCode_PDF'=>'8X4_CustBarCode_PDF','8X4_CustBarCode_thermal'=>'8X4_CustBarCode_thermal','6X4_A4_PDF'=>'6X4_A4_PDF','6X4_thermal'=>'6X4_thermal','6X4_PDF'=>'6X4_PDF');
$printer_doc_type = array('PDF'=>'PDF Output','ZPL2'=>'ZPL2 Output','EPL2'=>'EPL2 Output');
$duty_payment_type = array(''=>'None','S' =>__('Shipper','wf-shipping-dhl'),'R' =>__('Recipient','wf-shipping-dhl'),'T' =>__('Third Party/Other','wf-shipping-dhl'));
if(isset($_POST['wf_dhl_label_save_changes_button']))
{
	$wc_main_settings = get_option('woocommerce_wf_dhl_shipping_settings');	
	$wc_main_settings['plt'] = (isset($_POST['wf_dhl_shipping_plt'])) ? 'yes' : '';
	$wc_main_settings['enable_saturday_delivery'] = (isset($_POST['wf_dhl_shipping_enable_saturday_delivery'])) ? 'yes' : '';
	$wc_main_settings['cash_on_delivery'] = (isset($_POST['wf_dhl_shipping_cash_on_delivery'])) ? 'yes' : '';
	$wc_main_settings['services_select'] = (isset($_POST['wf_dhl_shipping_services_select'])) ? 'yes' : '';
	$wc_main_settings['show_front_end_shipping_method'] = (isset($_POST['wf_dhl_shipping_show_front_end_shipping_method'])) ? 'yes' : '';
	$wc_main_settings['output_format'] = $_POST['wf_dhl_shipping_output_format'];
	$wc_main_settings['image_type'] = $_POST['wf_dhl_shipping_image_type'];
	$wc_main_settings['return_label_key'] = (isset($_POST['wf_dhl_shipping_return_label_key'])) ? 'yes' : '';
	$wc_main_settings['return_label_acc_number'] = (isset($_POST['wf_dhl_shipping_return_label_acc_number'])) ? sanitize_text_field($_POST['wf_dhl_shipping_return_label_acc_number']) : '';
	$wc_main_settings['default_domestic_service'] = isset($_POST['wf_dhl_shipping_default_domestic_service']) ? $_POST['wf_dhl_shipping_default_domestic_service'] : 'none';
	$wc_main_settings['default_international_service'] = isset($_POST['wf_dhl_shipping_default_international_service']) ? $_POST['wf_dhl_shipping_default_international_service'] : 'none';
	$wc_main_settings['add_trackingpin_shipmentid'] = (isset($_POST['wf_dhl_shipping_add_trackingpin_shipmentid'])) ? 'yes' : '';
	$wc_main_settings['custom_message'] = '';
	$wc_main_settings['customer_logo_url'] =  sanitize_text_field($_POST['wf_dhl_shipping_customer_logo_url']);
	$wc_main_settings['request_archive_airway_label'] = (isset($_POST['wf_dhl_shipping_request_archive_airway_label'])) ? 'yes' : '';
	$wc_main_settings['no_of_archive_bills'] = (isset($_POST['wf_dhl_shipping_no_of_archive_bills'])) ? $_POST['wf_dhl_shipping_no_of_archive_bills'] : '1';
	$wc_main_settings['dhl_email_notification_service'] = (isset($_POST['wf_dhl_shipping_dhl_email_notification_service'])) ? 'yes' : '';
	$wc_main_settings['dhl_email_notification_message'] = (isset($_POST['wf_dhl_shipping_dhl_email_notification_message'])) ? sanitize_text_field($_POST['wf_dhl_shipping_dhl_email_notification_message']) : '';
	$wc_main_settings['latin_encoding'] = (isset($_POST['wf_dhl_shipping_latin_encoding'])) ? 'yes' : '';
	$wc_main_settings['dir_download'] = (isset($_POST['wf_dhl_shipping_dir_download'])) ? 'yes' : '';
	$wc_main_settings['dutypayment_type'] = $_POST['wf_dhl_shipping_dutypayment_type'];
	$wc_main_settings['dutyaccount_number'] = isset($_POST['wf_dhl_shipping_dutyaccount_number']) ? $_POST['wf_dhl_shipping_dutyaccount_number'] : '';
	$wc_main_settings['label_contents_text'] = sanitize_text_field($_POST['wf_dhl_shipping_label_contents_text']);
	$wc_main_settings['add_picup'] = (isset($_POST['wf_dhl_shipping_add_picup'])) ? 'yes' : '';
	$wc_main_settings['pickup_date'] = (isset($_POST['wf_dhl_shipping_pickup_date'])) ? $_POST['wf_dhl_shipping_pickup_date'] : '0';
	$wc_main_settings['pickup_time_from'] = (isset($_POST['wf_dhl_shipping_pickup_time_from'])) ? sanitize_text_field($_POST['wf_dhl_shipping_pickup_time_from']) : '';
	$wc_main_settings['pickup_time_to'] = (isset($_POST['wf_dhl_shipping_pickup_time_to'])) ? sanitize_text_field($_POST['wf_dhl_shipping_pickup_time_to']) : '';
	$wc_main_settings['pickup_person'] = (isset($_POST['wf_dhl_shipping_pickup_person'])) ? sanitize_text_field($_POST['wf_dhl_shipping_pickup_person']) : '';
	$wc_main_settings['pickup_contact'] = (isset($_POST['wf_dhl_shipping_pickup_contact'])) ? sanitize_text_field($_POST['wf_dhl_shipping_pickup_contact']) : '';
	
	update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
	
}

$general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
?>

<table>
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_"><?php _e('Enable/Disable','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_plt" id="wf_dhl_shipping_plt" style="" value="yes" <?php echo (isset($general_settings['plt']) && $general_settings['plt'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable PaperLess Trade (PLT)','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e("DHL’s Paperless Trade service allows you to electronically transmit Commercial and Proforma Invoices, eliminating the need to print and physically attach them to your shipments. With Paperless Trade, you have the option to generate Commercial or Proforma invoices in the DHL shipping solutions or to upload invoices created separately. On enabling this, DHL's paperless trade feature will be activated and a receipt will be generated as a commercial invoice.",'wf-shipping-dhl') ?>" ></span>
		</fieldset>
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_enable_saturday_delivery" id="wf_dhl_shipping_enable_saturday_delivery" style="" value="yes" <?php echo (isset($general_settings['enable_saturday_delivery']) && $general_settings['enable_saturday_delivery'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Saturday Delivery (SD)','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Special service. On activating this feature, the shipment can be delivered on Saturdays.','wf-shipping-dhl') ?> " ></span>
		</fieldset>
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_cash_on_delivery" id="wf_dhl_shipping_cash_on_delivery" style="" value="yes" <?php echo (isset($general_settings['cash_on_delivery']) && $general_settings['cash_on_delivery'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Cash On Delivery (COD)','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Special service. On activating this option, the shipment is created with Cash on delivery option.','wf-shipping-dhl') ?>" ></span>
		</fieldset>
		
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_show_front_end_shipping_method" id="wf_dhl_shipping_show_front_end_shipping_method" style="" value="yes" <?php echo (isset($general_settings['show_front_end_shipping_method']) && $general_settings['show_front_end_shipping_method'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Default Service for Label Generation','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('On enabling this option, the service selected in the cart/checkout page will only be reflected while creating shipment.','wf-shipping-dhl') ?>" ></span>
		</fieldset>
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_services_select" id="wf_dhl_shipping_services_select" style="" value="yes" <?php echo (isset($general_settings['services_select']) && $general_settings['services_select'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Show only chosen services on <a href="'.admin_url('admin.php?page=wc-settings&tab=shipping&section=wf_dhl_shipping&subtab=rates').'">Rates & Services</a> section.','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enabling this option will display only those selected services from Rates & Services section while printing the label from Order Admin page.','wf-shipping-dhl') ?>" ></span>
		</fieldset>
		
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_latin_encoding" id="wf_dhl_shipping_latin_encoding" style="" value="yes" <?php echo (isset($general_settings['latin_encoding']) && $general_settings['latin_encoding'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('UTF-8 Support','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enables UTF-8 character set support. This settings will be useful while printing labels for UTF-8 characters from languages like Chinese, Japanese, etc.','wf-shipping-dhl') ?>" ></span>
		</fieldset>
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_dir_download" id="wf_dhl_shipping_dir_download" style="" value="yes" <?php echo (isset($general_settings['dir_download']) && $general_settings['dir_download'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Direct Download','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('By choosing this option, label and invoice will be downloaded instead of opening in a new browser window.','wf-shipping-dhl') ?>" ></span>
		</fieldset>
		</td>
	</tr>

	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_">Shipping Label</label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			
			<fieldset style="padding:3px;">
				 <label for="wf_dhl_shipping_"><?php _e('Printing Size','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('This option allows you to choose the size of the label among various options. Three file formats are supported for the labels - PDF, ZPL2, EPL2.','wf-shipping-dhl') ?>" ></span><br>
				<select name="wf_dhl_shipping_output_format">
				<?php 
					$selected_value = isset($general_settings['output_format']) ? $general_settings['output_format'] : '6X4_A4_PDF';
					foreach ($print_size as $key => $value) {
						if($key == $selected_value)
						{
							echo '<option value="'.$key.'" selected="true">'.$value.'</option>';
						}
						else
						{
							echo '<option value="'.$key.'">'.$value.'</option>';
						}
					}
				?>
				</select>
			</fieldset>
			<fieldset style="padding:3px;">
				<?php 
					$slected_doc_type = isset($general_settings['image_type']) ? $general_settings['image_type'] : 'PDF';
					foreach ($printer_doc_type as $key => $value) {
						if($key === $slected_doc_type)
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_image_type" id="wf_dhl_shipping_image_type" style="" value="'.$key.'" checked="true" placeholder=""> '.$value.' ';
						}
						else
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_image_type" id="wf_dhl_shipping_image_type" style="" value="'.$key.'"  placeholder=""> '.$value.' ';
						}
					}
				?>
				
			</fieldset>
			
		</td>
	</tr>
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_label_contents_text"><?php _e('Shipping Content','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
			<label for="wf_dhl_shipping_label_contents_text"><?php _e('Shipping Content Description','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="Provide here a description about shipment contents." ></span><br>
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_label_contents_text" id="wf_dhl_shipping_label_contents_text" style="" value="<?php echo (isset($general_settings['label_contents_text'])) ? $general_settings['label_contents_text'] : ''; ?>" placeholder=""> 
			</fieldset>
			
		</td>
	</tr>
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_customer_logo_url"><?php _e('Company Logo','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;" id="">
				 <label for="wf_dhl_shipping_customer_logo_url"><?php _e('Select Company Logo','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('This option allows you to upload your own company logo which will be visible in shipping labels and return labels.','wf-shipping-dhl') ?>" ></span><br>
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_customer_logo_url" id="wf_dhl_shipping_customer_logo_url" style="" value="<?php echo (isset($general_settings['customer_logo_url'])) ? $general_settings['customer_logo_url'] : ''; ?>" placeholder=""><br><a href="#" id="dhl_media_upload_image_button" class="button-secondary"><?php _e('Choose Image','wf-shipping-dhl') ?></a>
			</fieldset>
			
		</td>
	</tr>
		<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_dutypayment_type"><?php _e('Duty Payment','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;" id="">
				 <label for="wf_dhl_shipping_dutypayment_type"><?php _e('Payment on','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Duty and tax charge payment type. It is required for non-doc or dutiable products.','wf-shipping-dhl') ?>" ></span><br>
				
					
				<select name="wf_dhl_shipping_dutypayment_type" id="wf_dhl_shipping_dutypayment_type" style="width:65%;">
					<?php 
					$selected_pay_type = isset($general_settings['dutypayment_type']) ? $general_settings['dutypayment_type'] : '';
					foreach ($duty_payment_type as $key => $value) {
						
						if($selected_pay_type === $key)
						{
							echo '<option value="'.$key.'" selected="true">'.$value . '</option>';
						}else{
							echo '<option value="'.$key.'">'.$value . '</option>';
						}	
					}
				
					 ?>
					
				</select><br>
				</fieldset>
				<fieldset style="padding:3px;" id="wf_t_acc_number">
				<label for="wf_dhl_shipping_dutyaccount_number"><?php _e('Duty Account Number','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Duty Billing account number. Required if the DutyPaymentType is Third Party.','wf-shipping-dhl') ?>" ></span><br>
				
				 <input class="input-text regular-input " type="text" name="wf_dhl_shipping_dutyaccount_number" id="wf_dhl_shipping_dutyaccount_number" style="" value="<?php echo (isset($general_settings['dutyaccount_number'])) ? $general_settings['dutyaccount_number'] : ''; ?>" placeholder="">
			</fieldset>
			
		</td>
	</tr>
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_request_archive_airway_label"><?php _e('Archive Air Waybill','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_request_archive_airway_label" id="wf_dhl_shipping_request_archive_airway_label" style="" value="yes" <?php echo (isset($general_settings['request_archive_airway_label']) && $general_settings['request_archive_airway_label'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Request Archive Air Waybill','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('For downloading archive airway bill Documents.','wf-shipping-dhl') ?>" ></span>
		</fieldset>

			<fieldset style="padding:3px;" id="wf_no_of_archive_bills">
				<?php if(isset($general_settings['no_of_archive_bills']) && $general_settings['no_of_archive_bills'] ==='2')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_no_of_archive_bills"  id="wf_dhl_shipping_no_of_archive_bills"  value="1" placeholder=""> <?php _e('One Document','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio"  name="wf_dhl_shipping_no_of_archive_bills" checked="true" id="wf_dhl_shipping_no_of_archive_bills"  value="2" placeholder=""> <?php _e('Two Documents','wf-shipping-dhl') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_no_of_archive_bills" checked="true" id="wf_dhl_shipping_no_of_archive_bills"  value="1" placeholder=""> <?php _e('One Document','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_no_of_archive_bills" id="wf_dhl_shipping_no_of_archive_bills"  value="2" placeholder=""> <?php _e('Two Documents','wf-shipping-dhl') ?>
				<?php } ?> 
			</fieldset>
			
		</td>
	</tr>
		<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_default_domestic_service"><?php _e('Bulk Shipment','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;" id="">
				 <label for="wf_dhl_shipping_default_domestic_service"><?php _e('Default Domestic Service','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Choose the default service for domestic shipment which will be set while generating bulk shipment label from order admin page. The default service will be applicable if there is no DHL service chosen during the checkout process. ','wf-shipping-dhl') ?>" ></span><br>
				
					
				<select name="wf_dhl_shipping_default_domestic_service" id="wf_dhl_shipping_default_domestic_service" style="width:65%;">
					<?php 
					$selected_pay_type = isset($general_settings['default_domestic_service']) ? $general_settings['default_domestic_service'] : '';
					echo '<option value="none" >None</option>';
					foreach ($this->services as $key => $value) {
						
						if($selected_pay_type == $key)
						{
							echo '<option value="'.$key.'" selected="true">['.$key.'] '.$value . '</option>';
						}else{
							echo '<option value="'.$key.'">['.$key.'] '.$value . '</option>';
						}	
					}
				
					 ?>
					
				</select><br>
				</fieldset>
				<fieldset style="padding:3px;" id="">
				 <label for="wf_dhl_shipping_default_international_service"><?php _e('Default International Service','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Choose the default service for international shipment which will be set while generating bulk shipment label from order admin page. The default service will be applicable if there is no DHL service chosen during the checkout process. ','wf-shipping-dhl') ?>" ></span><br>
				
					
				<select name="wf_dhl_shipping_default_international_service" id="wf_dhl_shipping_default_international_service" style="width:65%;">
					<?php 
					$selected_pay_type = isset($general_settings['default_international_service']) ? $general_settings['default_international_service'] : '';
					echo '<option value="none" >None</option>';
					foreach ($this->services as $key => $value) {
						
						if($selected_pay_type == $key)
						{
							echo '<option value="'.$key.'" selected="true">['.$key.'] '.$value . '</option>';
						}else{
							echo '<option value="'.$key.'">['.$key.'] '.$value . '</option>';
						}	
					}
				
					 ?>
					
				</select><br>
				</fieldset>
		</td>
	</tr>
	
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_return_label_key"><?php _e('Return Label','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_return_label_key" id="wf_dhl_shipping_return_label_key" style="" value="yes" <?php echo (isset($general_settings['return_label_key']) && $general_settings['return_label_key'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Return Label','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('This option allows the plugin to provide the return label feature in the order page.','wf-shipping-dhl') ?>" ></span>
		</fieldset>

			<fieldset style="padding:3px;" id="wf_return_label_acc_number">
				 <label for="wf_dhl_shipping_return_label_acc_number"><?php _e('Return Label Account Number','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Fill in the import account number provided by DHL for return labels.','wf-shipping-dhl') ?>" ></span><br>
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_return_label_acc_number" id="wf_dhl_shipping_return_label_acc_number" style="" value="<?php echo (isset($general_settings['return_label_acc_number'])) ? $general_settings['return_label_acc_number'] : ''; ?>" placeholder="">
			</fieldset>
			
		</td>
	</tr>
	<tr valign="top">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_add_trackingpin_shipmentid"><?php _e('Tracking','wf-shipping-dhl') ?></label>

		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_add_trackingpin_shipmentid" id="wf_dhl_shipping_add_trackingpin_shipmentid" style="" value="yes" <?php echo (isset($general_settings['add_trackingpin_shipmentid']) && $general_settings['add_trackingpin_shipmentid'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Tracking','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this to activate the tracking feature of the plugin. Custom tracking message - Provide your own tracking message which will be displayed in the order completion email. ','wf-shipping-dhl') ?>" ></span>
		</fieldset> 
			
		</td>
	</tr>
	<tr valign="top" id="dhl_email_service">
		<td style="width:50%;font-weight:800;">
			<label for="wf_dhl_shipping_dhl_email_notification_service"><?php _e('DHL Email Service','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_dhl_email_notification_service" id="wf_dhl_shipping_dhl_email_notification_service" style="" value="yes" <?php echo (isset($general_settings['dhl_email_notification_service']) && $general_settings['dhl_email_notification_service'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('DHL Tracking Message to Customers','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('DHL sent the Shipment details to customers.','wf-shipping-dhl') ?>" ></span>
		</fieldset>

			<fieldset style="padding:3px;" id="wf_dhl_email_notification_message">
				 <label for="wf_dhl_shipping_dhl_email_notification_message"><?php _e('Shipper Message','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipper Message to customers.','wf-shipping-dhl') ?>" ></span><br>
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_dhl_email_notification_message" id="wf_dhl_shipping_dhl_email_notification_message" style="" value="<?php echo (isset($general_settings['dhl_email_notification_message'])) ? $general_settings['dhl_email_notification_message'] : ''; ?>" placeholder="">
			</fieldset>
			
		</td>
	</tr>
	<tr valign="top" ">
		<td style="width:50%;font-weight:800;">
		<label for="wf_dhl_shipping_add_picup"><?php _e('Pickup','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
		<fieldset style="padding:3px;">
		<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_add_picup" id="wf_dhl_shipping_add_picup" style="" value="yes" <?php echo (isset($general_settings['add_picup']) && $general_settings['add_picup'] ==='yes') ? 'checked' : ''; ?> placeholder="">  <?php _e('Enable Pickup','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this if you want DHL to be able to pickup the shipment from your store. ','wf-shipping-dhl') ?>" ></span>
		</fieldset> 
			
			<fieldset style="padding:3px;" id="wf_pickup_date">
				 <label for="wf_dhl_shipping_pickup_date"><?php _e('Schedule Pickup After','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('How many days after the order has been placed, do you want the pickup to arrive at your store.','wf-shipping-dhl') ?>" ></span><br>
				<input class="input-text regular-input " min="0" max="7" type="number" name="wf_dhl_shipping_pickup_date" id="wf_dhl_shipping_pickup_date" style="" value="<?php echo (isset($general_settings['pickup_date'])) ? $general_settings['pickup_date'] : ''; ?>" placeholder="0"> <?php _e('Day(s).','wf-shipping-dhl') ?>
			</fieldset>
			
			<fieldset style="padding:3px;" id="wf_pickup_from_to">
				 <label for="wf_dhl_shipping_pickup_time_from"><?php _e('Pickup Availbility Time (24 hours Format)','wf-shipping-dhl') ?></label> <span style="color:red;"> *</span> <span class="woocommerce-help-tip" data-tip="<?php _e('Give a definite range of time within which you can allow pickup in order to avoid conflict.','wf-shipping-dhl') ?>" ></span><br>
				<b><?php _e('From','wf-shipping-dhl') ?>:</b> <input class="input-text regular-input " size="7"  type="text" name="wf_dhl_shipping_pickup_time_from" id="wf_dhl_shipping_pickup_time_from" style="" value="<?php echo (isset($general_settings['pickup_time_from'])) ? $general_settings['pickup_time_from'] : ''; ?>" placeholder="From">
				<b><?php _e('To','wf-shipping-dhl') ?>:</b> <input class="input-text regular-input "  size="7" type="text" name="wf_dhl_shipping_pickup_time_to" id="wf_dhl_shipping_pickup_time_to" style="" value="<?php echo (isset($general_settings['pickup_time_to'])) ? $general_settings['pickup_time_to'] : ''; ?>" placeholder="To">
			</fieldset>
			<fieldset style="padding:3px;" id="wf_pickup_details">
				 <label for="wf_dhl_shipping_pickup_person"><?php _e('Pickup Person Name','wf-shipping-dhl') ?></label> <span style="color:red;"> *</span> <span class="woocommerce-help-tip" data-tip="<?php _e('Give a contact person’s name and contact no. who can be contacted in case of any convenience..','wf-shipping-dhl') ?>" ></span><br>
				<input class="input-text regular-input "  type="text" name="wf_dhl_shipping_pickup_person" id="wf_dhl_shipping_pickup_person" style="" value="<?php echo (isset($general_settings['pickup_person'])) ? $general_settings['pickup_person'] : ''; ?>" placeholder="Person Name">
				<input class="input-text regular-input "  type="text" name="wf_dhl_shipping_pickup_contact" id="wf_dhl_shipping_pickup_contact" style="" value="<?php echo (isset($general_settings['pickup_contact'])) ? $general_settings['pickup_contact'] : ''; ?>" placeholder="Contact Number">
			</fieldset>
		
		
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;">
			<input type="submit" value="<?php _e('Save Changes','wf-shipping-dhl') ?>" class="button button-primary" name="wf_dhl_label_save_changes_button">
		</td>
	</tr>

</table>
<script type="text/javascript">

		
		jQuery(window).load(function(){
			
			

			jQuery('#wf_dhl_shipping_add_picup').change(function(){
				if(jQuery('#wf_dhl_shipping_add_picup').is(':checked')) {
					jQuery('#wf_pickup_date').show();
					jQuery('#wf_pickup_from_to').show();
					jQuery('#wf_pickup_details').show();
				}else
				{
					jQuery('#wf_pickup_date').hide();
					jQuery('#wf_pickup_from_to').hide();
					jQuery('#wf_pickup_details').hide();
				}
			}).change();
			
			jQuery('#wf_dhl_shipping_add_trackingpin_shipmentid').change(function(){
				if(jQuery(wf_dhl_shipping_add_trackingpin_shipmentid).is(':checked')) {
					jQuery('#dhl_email_service').show();
				}else
				{
					jQuery('#dhl_email_service').hide();
				}
			}).change();
			
			jQuery('#wf_dhl_shipping_return_label_key').change(function(){
				if(jQuery('#wf_dhl_shipping_return_label_key').is(':checked')) {
					jQuery('#wf_return_label_acc_number').show();
				}else
				{
					jQuery('#wf_return_label_acc_number').hide();
				}
			}).change();
			
			jQuery('#wf_dhl_shipping_request_archive_airway_label').change(function(){
				if(jQuery('#wf_dhl_shipping_request_archive_airway_label').is(':checked')) {
					jQuery('#wf_no_of_archive_bills').show();
				}else
				{
					jQuery('#wf_no_of_archive_bills').hide();
				}
			}).change();
			jQuery('#wf_dhl_shipping_dhl_email_notification_service').change(function(){
				if(jQuery('#wf_dhl_shipping_dhl_email_notification_service').is(':checked')) {
					jQuery('#wf_dhl_email_notification_message').show();
				}else
				{
					jQuery('#wf_dhl_email_notification_message').hide();
				}
			}).change();
			jQuery('#wf_dhl_shipping_dutypayment_type').change(function(){
				if(jQuery(this).val() == 'T') {
					jQuery('#wf_t_acc_number').show();
				}else
				{
					jQuery('#wf_t_acc_number').hide();
				}
			}).change();

			

		});

	</script>