<?php $this->init_settings(); 
global $woocommerce;
$wc_main_settings = array();
$package_type = array('BOX'=>__('DHL Box', 'wf-shipping-dhl'),'FLY'=>__('Flyer', 'wf-shipping-dhl'),'YP'=>__('Your Pack', 'wf-shipping-dhl'));
$weight_type =  array('pack_descending'=>__('Pack heavier items first', 'wf-shipping-dhl'),'pack_ascending'=>__('Pack lighter items first', 'wf-shipping-dhl'),'pack_simple'=>__('Pack purely divided by weight', 'wf-shipping-dhl'));
if(isset($_POST['wf_dhl_packing_save_changes_button']))
{
	$wc_main_settings = get_option('woocommerce_wf_dhl_shipping_settings');	
	$wc_main_settings['packing_method'] = sanitize_text_field($_POST['wf_dhl_shipping_packing_method']);
	$wc_main_settings['dimension_weight_unit'] = (isset($_POST['wf_dhl_shipping_dimension_weight_unit']) && $_POST['wf_dhl_shipping_dimension_weight_unit'] ==='KG_CM') ? 'KG_CM' : 'LBS_IN';
	if($wc_main_settings['packing_method'] === 'per_item')
	{
		$wc_main_settings['shp_pack_type'] = sanitize_text_field($_POST['wf_dhl_shipping_shp_pack_type']);
	}
	if($wc_main_settings['packing_method'] === 'box_packing')
	{
		

		$box_data = $_POST['boxes_name'];

	$box = array();
	foreach ( $box_data as $key => $value) {
		$box_id = $_POST['boxes_id'][$key];
		
		if(!empty($_POST['boxes_name'][$key]))
		{

		
		$box_name = empty($_POST['boxes_name'][$key]) ? 'New Box' : sanitize_text_field($_POST['boxes_name'][$key]);
		$box_length = empty($_POST['boxes_length'][$key]) ? 0 : sanitize_text_field($_POST['boxes_length'][$key]); 
		$boxes_width = empty($_POST['boxes_width'][$key]) ? 0 : sanitize_text_field($_POST['boxes_width'][$key]); 
		$boxes_height = empty($_POST['boxes_height'][$key]) ? 0 : sanitize_text_field($_POST['boxes_height'][$key]); 
		$boxes_inner_length = empty($_POST['boxes_inner_length'][$key]) ? 0 : sanitize_text_field($_POST['boxes_inner_length'][$key]); 
		$boxes_inner_width = empty($_POST['boxes_inner_width'][$key]) ? 0 : sanitize_text_field($_POST['boxes_inner_width'][$key]); 
		$boxes_inner_height = empty($_POST['boxes_inner_height'][$key]) ? 0 : sanitize_text_field($_POST['boxes_inner_height'][$key]); 
		$boxes_box_weight = empty($_POST['boxes_box_weight'][$key]) ? 0 : sanitize_text_field($_POST['boxes_box_weight'][$key]); 
		$boxes_max_weight = empty($_POST['boxes_max_weight'][$key]) ? 0 : sanitize_text_field($_POST['boxes_max_weight'][$key]);
		$box_enabled = isset($_POST['boxes_enabled'][$key]) ? true : false; 
		$box[$key] = array(
				'id' => $box_id,
				'name' => $box_name,
				'length' => $box_length,
				'width' => $boxes_width,
				'height' => $boxes_height,
				'inner_length' => $boxes_inner_length,
				'inner_width' => $boxes_inner_width,
				'inner_height' => $boxes_inner_height,
				'box_weight' => $boxes_box_weight,
				'max_weight' => $boxes_max_weight,
				'enabled' => $box_enabled,
				'pack_type' => $_POST['boxes_pack_type'][$key]
				);
	}
	}
	
		$wc_main_settings['boxes'] = $box;
	}
	if($wc_main_settings['packing_method'] === 'weight_based')
	{
		$wc_main_settings['box_max_weight'] = !empty($_POST['wf_dhl_shipping_box_max_weight']) ?sanitize_text_field($_POST['wf_dhl_shipping_box_max_weight']) : '';
		$wc_main_settings['weight_packing_process'] = sanitize_text_field($_POST['wf_dhl_shipping_weight_packing_process']);

	}

	update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
	
	
}

$general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
$this->boxes = isset($general_settings['boxes']) ? $general_settings['boxes'] : include(  WF_DHL_PAKET_EXPRESS_ROOT_PATH.'dhl_express/includes/data-wf-box-sizes.php' );

?>
<table>
	<tr valign="top" ">
		<td style="width:35%;font-weight:800;">
			<label for="wf_dhl_shipping_"><?php _e('Packing Options','wf-shipping-dhl') ?></label>
		</td><td scope="row" class="titledesc" style="display: block;width:100%;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
			 <label for="wf_dhl_shipping_"><?php _e('Parcel Packing Method','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Select the Packing method using which you want to pack your products.  Pack items individually - This option allows you to pack each item separately in a box. Hence, multiple items will go in multiple boxes. Pack into boxes with weights and dimensions - This option allows you to pack items into boxes of various sizes. Weight based packing - This option allows you to pack your products based on weight of the package.','wf-shipping-dhl') ?>"></span>	<br>
				<select name="wf_dhl_shipping_packing_method" id="wf_dhl_shipping_packing_method" default="per_item">
					<?php 
						$selected_packing_method = isset($general_settings['packing_method']) ? $general_settings['packing_method'] : 'per_item';
					?>
					<option value="per_item" <?php echo ($selected_packing_method === 'per_item') ? 'selected="true"': '' ?> ><?php _e('Default: Pack items individually','wf-shipping-dhl') ?></option>
					<option value="box_packing" <?php echo ($selected_packing_method === 'box_packing') ? 'selected="true"': '' ?> ><?php _e('Recommended: Pack into boxes with weights and dimensions','wf-shipping-dhl') ?></option>
					<option value="weight_based" <?php echo ($selected_packing_method === 'weight_based') ? 'selected="true"': '' ?> ><?php _e('Weight based: Calculate shipping on the basis of order total weight','wf-shipping-dhl') ?></option>
				</select>
			</fieldset>
			<fieldset style="padding:3px;">
				<?php if(isset($general_settings['dimension_weight_unit']) && $general_settings['dimension_weight_unit'] ==='KG_CM')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_dimension_weight_unit"  id="wf_dhl_shipping_dimension_weight_unit"  value="LBS_IN" placeholder=""> <?php _e('Use Pounds,Inches (lbs,in) ','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio"  name="wf_dhl_shipping_dimension_weight_unit" checked="true" id="wf_dhl_shipping_dimension_weight_unit"  value="KG_CM" placeholder=""> Use <?php _e('Kilograms,Centimeters (Kg,cm)','wf-shipping-dhl') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_dimension_weight_unit" checked="true" id="wf_dhl_shipping_dimension_weight_unit"  value="LBS_IN" placeholder=""> <?php _e('Use Pounds,Inches (lbs,in) ','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_dimension_weight_unit" id="wf_dhl_shipping_dimension_weight_unit"  value="KG_CM" placeholder=""> <?php _e('Use Kilograms,Centimeters (Kg,cm)','wf-shipping-dhl') ?>
				<?php } ?>
			</fieldset>
		</td>
	</tr>
	<tr>
		<tr id="packing_options">
			<td colspan="2">
			<?php include( WF_DHL_PAKET_EXPRESS_ROOT_PATH.'dhl_express/includes/html-wf-box-packing.php') ?>
			</td>
		</tr>
		<tr id="packing_options_shp_pack_type">
			<td style="width:35%;font-weight:800;">
			<label for="wf_dhl_shipping_shp_pack_type"><?php _e('Pack items individually <br/>(Package Type)','wf-shipping-dhl') ?></label> 
			<span class="woocommerce-help-tip" data-tip="DHL Box: There are the most commonly used boxes for packing. These are the boxes which get populated when you install the plugin.<br/>Flyer: This option is suitable for Binded documents and Flat materials.<br/> Your Box: With this option, your item gets packed into customized box.<br/> For example, the shipping cost of Item X is £10. If the customer adds two quantities of Item X to the Cart, then the total shipping cost is £10 x 2, which is £20."></span>
			
		</td><td scope="row" class="titledesc" style="display: block;width:100%;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
			
				<?php 
					$slected_pack_type = isset($general_settings['shp_pack_type']) ? $general_settings['shp_pack_type'] : 'BOX';
					foreach ($package_type as $key => $value) {
						if($key === $slected_pack_type)
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_shp_pack_type" id="wf_dhl_shipping_shp_pack_type" style="" value="'.$key.'" checked="true" placeholder=""> '.$value.' ';
						}
						else
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_shp_pack_type" id="wf_dhl_shipping_shp_pack_type" style="" value="'.$key.'"  placeholder=""> '.$value.' ';
						}
					}
				?>
			</td>
		<tr id="packing_options_weight_packing_process">
			<td style="width:35%;font-weight:800;">
			<label for="wf_dhl_shipping_shp_pack_type"><?php _e('Pack items individually <br/>(Package Type)','wf-shipping-dhl') ?></label> 
		</td><td scope="row" class="titledesc" style="display: block;width:100%;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				 <label for="wf_dhl_shipping_box_max_weight"><?php _e('Maximum Weight / Packing','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('This option will allow each box to hold the maximum value provided in the field. 
Box Sizes - This section allows you to create your own box size(dimensions) and provide the box weight.','wf-shipping-dhl') ?>" ></span><br>
				 <input class="input-text regular-input " type="text" name="wf_dhl_shipping_box_max_weight" id="wf_dhl_shipping_box_max_weight" style="" value="<?php echo (isset($general_settings['box_max_weight'])) ? $general_settings['box_max_weight'] : ''; ?>" placeholder="">
				</fieldset>
			<fieldset style="padding:3px;">
				<?php 
					$slected_weight_type = isset($general_settings['weight_packing_process']) ? $general_settings['weight_packing_process'] : 'pack_descending';
					foreach ($weight_type as $key => $value) {
						if($key === $slected_weight_type)
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_weight_packing_process" id="wf_dhl_shipping_weight_packing_process" style="" value="'.$key.'" checked="true" placeholder=""> '.$value.' ';
						}
						else
						{
							echo '<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_weight_packing_process" id="wf_dhl_shipping_weight_packing_process" style="" value="'.$key.'"  placeholder=""> '.$value.' ';
						}
					}
				?>
			</td>
			
		</tr>
	<tr>
		<td colspan="2" style="text-align:right;padding-right: 10%;">
			<br/>
			<input type="submit" value="<?php _e('Save Changes','wf-shipping-dhl') ?>" class="button button-primary" name="wf_dhl_packing_save_changes_button">
		</td>
	</tr>
</table>