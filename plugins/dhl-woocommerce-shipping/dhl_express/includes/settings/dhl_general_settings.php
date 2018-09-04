<?php $this->init_settings(); 
global $woocommerce;
$wc_main_settings = array();
if(isset($_POST['wf_dhl_validate_credentials']))
{
	$site_id		= $_POST['wf_dhl_shipping_site_id'];
	$site_pwd		= $_POST['wf_dhl_shipping_site_pwd'];
	$site_test_mode = $_POST['wf_dhl_shipping_production'];
	$site_county	= $_POST['wf_dhl_shipping_base_country'];
	
	$wc_main_settings					= get_option('woocommerce_wf_dhl_shipping_settings');	
	$wc_main_settings['production']		= (isset($_POST['wf_dhl_shipping_production']) && $_POST['wf_dhl_shipping_production'] ==='yes') ? 'yes' : '';
	$wc_main_settings['account_number'] = (isset($_POST['wf_dhl_shipping_ac_num'])) ? sanitize_text_field($_POST['wf_dhl_shipping_ac_num']) : '130000279';
	$wc_main_settings['site_id']		= (isset($_POST['wf_dhl_shipping_site_id'])) ? sanitize_text_field($_POST['wf_dhl_shipping_site_id']) : 'CIMGBTest';
	$wc_main_settings['site_password']	= (isset($_POST['wf_dhl_shipping_site_pwd'])) ? sanitize_text_field($_POST['wf_dhl_shipping_site_pwd']) : 'DLUntOcJma';
	

	update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
	
	$validate = wf_validate_crendials($site_test_mode,$site_id,$site_pwd,$site_county);
	//update_option('wf_creadentials_validate','done');
}

if(isset($_POST['wf_dhl_validate_credentials_edit']))
{
	update_option('wf_dhl_shipping_validation_data','');
}

function wf_validate_crendials($site_test_mode,$site_id,$site_pwd,$site_county)
{
	if(strlen($site_pwd) < 8)
	{
		update_option('wf_dhl_validation_error','<small style="color:red">The Password field should have a minimum of 8 characters.</small>');
		update_option('wf_dhl_shipping_validation_data','undone');
		return false;
	}
	global $woocommerce;

	$url = ($site_test_mode === 'yes')? 'https://xmlpi-ea.dhl.com/XMLShippingServlet' : 'https://xmlpitest-ea.dhl.com/XMLShippingServlet';

	$mailingDate = date('Y-m-d', time());
$xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
	<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
		<GetQuote>
			<Request>
				<ServiceHeader>
					<SiteID>{$site_id}</SiteID>
					<Password>{$site_pwd}</Password>
				</ServiceHeader>
			</Request>
			<From>
				<CountryCode>{$site_county}</CountryCode>
			</From>
			<BkgDetails>
				<PaymentCountryCode>{$site_county}</PaymentCountryCode>
				<Date>{$mailingDate}</Date>
				<ReadyTime>PT10H21M</ReadyTime>
				<DimensionUnit>IN</DimensionUnit>
				<WeightUnit>LB</WeightUnit>
				<IsDutiable>N</IsDutiable>
			</BkgDetails>
			<To>
				<CountryCode>{$site_county}</CountryCode>
			</To>
		</GetQuote>
	</p:DCTRequest>
XML;

	$result = wp_remote_post($url, array(
		'method' => 'POST',
		'timeout' => 70,
		'sslverify' => 0,
        'body' => $xmlRequest
		)
	);

	if ( is_wp_error( $result ) ) {
            $error_message = $result->get_error_message();
            update_option('wf_dhl_validation_error','<small style="color:red">'.$error_message.'</small>');
			update_option('wf_dhl_shipping_validation_data','undone');
			return false;
        }
        elseif (!isset($result['body']))
	{
		update_option('wf_dhl_validation_error','<small style="color:red">API Informations Invalid</small>');
		update_option('wf_dhl_shipping_validation_data','undone');
		return false;
	}
	libxml_use_internal_errors(true);
	$xml = simplexml_load_string(utf8_encode($result['body']));
	if(isset($xml->Response->Status->Condition->ConditionData))
	{
update_option('wf_dhl_validation_error','<small style="color:red">'.$xml->Response->Status->Condition->ConditionData.'</small>');
update_option('wf_dhl_shipping_validation_data','undone');
return false;

}else
{
	update_option('wf_dhl_shipping_validation_data','done');
	update_option('wf_dhl_validation_error','');
	return true;
}
	
}
if(isset($_POST['wf_dhl_genaral_save_changes_button']))
{
	$site_id = isset($_POST['wf_dhl_shipping_site_id']) ? $_POST['wf_dhl_shipping_site_id'] : false;
	$site_pwd = isset($_POST['wf_dhl_shipping_site_pwd']) ? $_POST['wf_dhl_shipping_site_pwd'] : false;
	$site_test_mode = isset($_POST['wf_dhl_shipping_production']) ? $_POST['wf_dhl_shipping_production'] : false;
	$site_county = isset($_POST['wf_dhl_shipping_base_country']) ? $_POST['wf_dhl_shipping_base_country'] : false;
    if($site_id && $site_pwd && $site_test_mode && $site_county)
    {
    $validate = wf_validate_crendials($site_test_mode,$site_id,$site_pwd,$site_county);
    }
    else
    {
    	$validate=true;
    }
    if($validate)
    {
	$wc_main_settings = get_option('woocommerce_wf_dhl_shipping_settings');	
	$my_account_number = (isset($wc_main_settings['account_number'])) ? $wc_main_settings['account_number'] : '';
	$my_site_id = (isset($wc_main_settings['site_id'])) ? $wc_main_settings['site_id'] : '';
	$my_site_pwd = (isset($wc_main_settings['site_password'])) ? $wc_main_settings['site_password'] : '';
	$my_site_mode = (isset($wc_main_settings['production'])) ? $wc_main_settings['production'] : '';

	$wc_main_settings['production'] = (isset($_POST['wf_dhl_shipping_production']) && $_POST['wf_dhl_shipping_production'] ==='yes') ? 'yes' : $my_site_mode;

	$wc_main_settings['account_number'] = (isset($_POST['wf_dhl_shipping_ac_num'])) ? sanitize_text_field($_POST['wf_dhl_shipping_ac_num']) : $my_account_number;
	$wc_main_settings['site_id'] = (isset($_POST['wf_dhl_shipping_site_id'])) ? sanitize_text_field($_POST['wf_dhl_shipping_site_id']) : $my_site_id;
	$wc_main_settings['site_password'] = (isset($_POST['wf_dhl_shipping_site_pwd'])) ? sanitize_text_field($_POST['wf_dhl_shipping_site_pwd']) : $my_site_pwd;
	$wc_main_settings['enabled'] = (isset($_POST['wf_dhl_shipping_rates'])) ? 'yes' : 'no';
	$wc_main_settings['enabled_label'] = (isset($_POST['wf_dhl_shipping_enabled_label'])) ? 'yes' : '';
	$wc_main_settings['insure_contents'] = (isset($_POST['wf_dhl_shipping_insure_contents'])) ? 'yes' : '';
	$wc_main_settings['insure_contents_chk'] = (isset($_POST['wf_dhl_shipping_insure_contents_chk'])) ? 'yes' : '';
	$wc_main_settings['debug'] = (isset($_POST['wf_dhl_shipping_debug'])) ? 'yes' : '';
	$wc_main_settings['shipper_person_name'] = (isset($_POST['wf_dhl_shipping_shipper_person_name'])) ? sanitize_text_field($_POST['wf_dhl_shipping_shipper_person_name']) : '';
	$wc_main_settings['shipper_company_name'] = (isset($_POST['wf_dhl_shipping_shipper_company_name'])) ? sanitize_text_field($_POST['wf_dhl_shipping_shipper_company_name']) : '';
	$wc_main_settings['shipper_phone_number'] = (isset($_POST['wf_dhl_shipping_shipper_phone_number'])) ? sanitize_text_field($_POST['wf_dhl_shipping_shipper_phone_number']) : '';
	$wc_main_settings['shipper_email'] = (isset($_POST['wf_dhl_shipping_shipper_email'])) ? sanitize_text_field($_POST['wf_dhl_shipping_shipper_email']) : '';
	$wc_main_settings['freight_shipper_street'] = (isset($_POST['wf_dhl_shipping_freight_shipper_street'])) ? sanitize_text_field($_POST['wf_dhl_shipping_freight_shipper_street']) : '';
	$wc_main_settings['shipper_street_2'] = (isset($_POST['wf_dhl_shipping_shipper_street_2'])) ? sanitize_text_field($_POST['wf_dhl_shipping_shipper_street_2']) : '';
	$wc_main_settings['freight_shipper_city'] = (isset($_POST['wf_dhl_shipping_freight_shipper_city'])) ? sanitize_text_field($_POST['wf_dhl_shipping_freight_shipper_city']) : '';
	$wc_main_settings['freight_shipper_state'] = (isset($_POST['wf_dhl_shipping_freight_shipper_state'])) ? sanitize_text_field($_POST['wf_dhl_shipping_freight_shipper_state']) : '';
	$wc_main_settings['insure_currency'] = (isset($_POST['wf_dhl_shipping_insure_currency'])) ? sanitize_text_field($_POST['wf_dhl_shipping_insure_currency']) : '';
	$wc_main_settings['insure_converstion_rate'] = (isset($_POST['wf_dhl_shipping_insure_converstion_rate']) && !empty($_POST['wf_dhl_shipping_insure_converstion_rate'])) ? sanitize_text_field($_POST['wf_dhl_shipping_insure_converstion_rate']) : 1;
	$wc_main_settings['origin'] = (isset($_POST['wf_dhl_shipping_origin'])) ? ($_POST['wf_dhl_shipping_origin']) : '';
	$wc_main_settings['base_country'] = $_POST['wf_dhl_shipping_base_country'];
	$wc_main_settings['conversion_rate'] = isset($_POST['wf_dhl_shipping_conversion_rate']) ? sanitize_text_field($_POST['wf_dhl_shipping_conversion_rate']) : '';
	$country_based_data = include_once(WF_DHL_PAKET_EXPRESS_ROOT_PATH.'dhl_express/includes/data-wf-country-details.php');
	$wc_main_settings['dhl_currency_type'] = isset($country_based_data[$wc_main_settings['base_country']]['currency']) ? $country_based_data[$wc_main_settings['base_country']]['currency'] : '';
	$wc_main_settings['region_code'] = isset($country_based_data[$wc_main_settings['base_country']]['region']) ? $country_based_data[$wc_main_settings['base_country']]['region'] : '';
	if(class_exists('wf_vendor_addon_setup'))
	{
		$wc_main_settings['vendor_check'] = (isset($_POST['wf_dhl_shipping_vendor_check'])) ? 'yes' : '';	
	}
	update_option('woocommerce_wf_dhl_shipping_settings',$wc_main_settings);
}
}

$general_settings = get_option('woocommerce_wf_dhl_shipping_settings');
$general_settings = empty($general_settings) ? array() : $general_settings;
$validation = get_option('wf_dhl_shipping_validation_data');
?>
<img style="float:right;" src="<?php echo WF_DHL_PAKET_PATH.'/dhl_express/resources/images/dhl.png'; ?>" width="180" height="60" />

<table>
	<tr valign="top">
		<td style="width:40%;font-weight:800;">
			<label for="wf_dhl_shipping_production"><?php _e('DHL Account Information','wf-shipping-dhl') ?> </label> <span class="woocommerce-help-tip" data-tip="<?php _e('For getting SiteID and password for countries other than the United States (UK and Rest of the World), the customer should contact DHL account manager. The account manager must request integration with WooForce/XAdapter via DHL Presales Department. For getting SiteID and Password for the United States, you need to write to xmlrequests@dhl.com along with your full Account details like account number, region, address, etc. to get API Access. For getting SiteID and Password for Australia, you are prompted to contact DHL Express by e-mailing onlineshipping.au@dhl.com.','wf-shipping-dhl') ?>"></span>
		</td>
		<?php echo $this->get_option('woocommerce_wf_dhl_shipping_production'); ?>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<?php if(isset($general_settings['production']) && $general_settings['production'] ==='yes')
				{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_production"  id="wf_dhl_shipping_production" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="no" placeholder=""> <?php _e('Test Mode','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio"  name="wf_dhl_shipping_production" checked="true" id="wf_dhl_shipping_production" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="yes" placeholder=""> <?php _e('Live Mode','wf-shipping-dhl') ?>
				<?php }else{ ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_production" checked="true" id="wf_dhl_shipping_production" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="no" placeholder=""> <?php _e('Test Mode','wf-shipping-dhl') ?>
				<input class="input-text regular-input " type="radio" name="wf_dhl_shipping_production" id="wf_dhl_shipping_production" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="yes" placeholder=""> <?php _e('Live Mode','wf-shipping-dhl') ?>
				<?php } ?>
				<br>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="text" name="wf_dhl_shipping_ac_num" id="wf_dhl_shipping_ac_num" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?>  value="<?php echo (isset($general_settings['account_number'])) ? $general_settings['account_number'] : '130000279'; ?>" placeholder="130000279"> <label for="wf_dhl_shipping_ac_num"><?php _e('Account Number','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Enter your DHL online account number as obtained from DHL. You can contact your DHL sales representative for this.','wf-shipping-dhl') ?>"></span>

			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " required type="text" name="wf_dhl_shipping_site_id" id="wf_dhl_shipping_site_id" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="<?php echo (isset($general_settings['site_id'])) ? $general_settings['site_id'] : 'CIMGBTest'; ?>" placeholder="CIMGBTest"><?php echo ($validation === 'done') ? '<span style="vertical-align: bottom;color:green" class="dashicons dashicons-yes"></span>' : ''; ?> 
				<label for="wf_dhl_shipping_"><?php _e('Site ID','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('You can get the SITE ID from the DHL integration team.','wf-shipping-dhl') ?>"></span>	
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " required type="Password" name="wf_dhl_shipping_site_pwd" id="wf_dhl_shipping_site_pwd" <?php echo ($validation === 'done') ? 'disabled="true"' : ''; ?> value="<?php echo (isset($general_settings['site_password'])) ? $general_settings['site_password'] : 'DLUntOcJma'; ?>" placeholder="**************"><?php echo ($validation === 'done') ? '<span style="vertical-align: bottom;color:green" class="dashicons dashicons-yes"></span>' : ''; ?> 
				<label for="wf_dhl_shipping_site_pwd"><?php _e('Site Password','wf-shipping-dhl') ?><font style="color:red;">*</font></label>
				<span class="woocommerce-help-tip" data-tip="<?php _e('You can get the PASSWORD from the DHL integration team.','wf-shipping-dhl') ?>"></span>				

			</fieldset>
			<?php echo get_option('wf_dhl_validation_error'); ?>
			<fieldset style="padding:3px;">
				<?php
				if($validation === 'done')
				{
					echo '<input type="submit" value="Edit Credentials" class="button button-secondary" name="wf_dhl_validate_credentials_edit" >';
				}else
				{
					echo '<input type="submit" value=" Validate Credentials" class="button button-secondary" name="wf_dhl_validate_credentials" >';
				}
				?>
			</fieldset>
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:800;">
			<label for="wf_dhl_shipping_rates"><?php _e('Enable/Disable','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">

				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_rates" id="wf_dhl_shipping_rates" style="" value="yes" <?php echo (!isset($general_settings['enabled']) || isset($general_settings['enabled']) && $general_settings['enabled'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Real time Rates','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this to fetch the rates from DHL in cart/checkout page.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_enabled_label" id="wf_dhl_shipping_enabled_label" style="" value="yes" <?php echo (!isset($general_settings['enabled_label']) || isset($general_settings['enabled_label']) && $general_settings['enabled_label'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Shipping Label','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('This option allows the user to create a label in the order admin page. Disabling it will hide the label creation option.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_insure_contents" id="wf_dhl_shipping_insure_contents" style="" <?php echo (!isset($general_settings['insure_contents']) || isset($general_settings['insure_contents']) && $general_settings['insure_contents'] ==='yes') ? 'checked' : ''; ?> value="yes" placeholder=""> <?php _e('Enable Insurance','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this to insure your products. The insured value will be the total cart value.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<?php $default_currency = get_woocommerce_currency(); ?>
			<fieldset style="padding:3px;"id="wf_dhl_insurance_related">
				Select Insurance Currency </br> <select name="wf_dhl_shipping_insure_currency" class="wc-enhanced-select">
				<?php 
					$selected_currency = isset($general_settings['insure_currency']) ? $general_settings['insure_currency'] : get_woocommerce_currency();
					$currency_arr = get_woocommerce_currencies();
					foreach($currency_arr as $key => $value)
					{
						echo '<option value="'.$key.'" '.(($key === $selected_currency ) ? 'selected' : '').'>'.$value.' ('.$key.') </option>';
					}
				?>
				</select><br/>
				<label for="wf_dhl_shipping_conversion_rate"><?php _e('Converstion Rate','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="Use this field to set the conversion rate of the Store currency <?php echo $default_currency; ?> to the Insurance currency choosen from the dropdown above."></span> <br/>	 
				<input type="number" min="0" step="0.00001" name="wf_dhl_shipping_insure_converstion_rate" value="<?php echo isset($general_settings['insure_converstion_rate']) ? $general_settings['insure_converstion_rate'] : ''; ?>"/><br/>
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_insure_contents_chk" id="wf_dhl_shipping_insure_contents_chk" style="" <?php echo (!isset($general_settings['insure_contents_chk']) || isset($general_settings['insure_contents_chk']) && $general_settings['insure_contents_chk'] ==='yes') ? 'checked' : ''; ?> value="yes" placeholder=""> <?php _e('DHL Insurance Checkout Field','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this to insure your products. The insured value will be the total cart value.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_debug" id="wf_dhl_shipping_debug" style="" value="yes" <?php echo (isset($general_settings['debug']) && $general_settings['debug'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Developer Mode','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('Enable this option to troubleshoot the plugin. On enabling this, request and response information will be shown in the cart/checkout page.','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<?php
			if(class_exists('wf_vendor_addon_setup'))
			{
				?>
			<fieldset style="padding:3px;">
				<input class="input-text regular-input " type="checkbox" name="wf_dhl_shipping_vendor_check" id="wf_dhl_shipping_vendor_check" style="" value="yes" <?php echo (isset($general_settings['vendor_check']) && $general_settings['vendor_check'] ==='yes') ? 'checked' : ''; ?> placeholder=""> <?php _e('Enable Multi-Vendor Shipper Address','wf-shipping-dhl') ?> <span class="woocommerce-help-tip" data-tip="<?php _e('You are seeing this option becuase XA Multi-Vendor Shipping Add-On is installed. By enabling this option, Shipper Adress set in multi-vendor plugin settings will be overriden by the below Shipper Address settings..','wf-shipping-dhl') ?>"></span>
			</fieldset>
			<?php
			}
			?>
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:800;">
			<label for="wf_dhl_shipping_"><?php _e('Default Currency','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">
			<fieldset style="padding:3px;">
				<?php $selected_currency = isset($general_settings['dhl_currency_type']) ? $general_settings['dhl_currency_type'] : get_woocommerce_currency(); ?>
				<label for="wf_dhl_shipping_"><?php echo '<b>'.$selected_currency.' ('. get_woocommerce_currency_symbol($selected_currency).')</b>'; ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('This field picks the default currency of the country provided in Shipper Address Section.','wf-shipping-dhl') ?>"></span><br/>
			</fieldset>
			<?php 
			$selected_currency = isset($general_settings['dhl_currency_type']) ? $general_settings['dhl_currency_type'] : get_woocommerce_currency();
			if($selected_currency != $default_currency)
			{
				?>
				<fieldset style="padding:3px;">
				<label for="wf_dhl_shipping_conversion_rate"><?php _e('Converstion Rate','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="Use this field to set the conversion rate of the  DHL currency <?php echo $selected_currency; ?> to the Store’s currency <?php echo $default_currency; ?>. "></span> <br/>	 
				<input class="input-text regular-input " type="number" min="0" step="0.00001" name="wf_dhl_shipping_conversion_rate" id="wf_dhl_shipping_conversion_rate" style="" value="<?php echo (isset($general_settings['conversion_rate'])) ? $general_settings['conversion_rate'] : ''; ?>" placeholder=""><b> <?php echo $default_currency; ?></b>
				</fieldset>
				<?php
			}
			 ?>
			
			
		</td>
	</tr>
	<tr valign="top">
		<td style="width:40%;font-weight:800;">
			<label for="wf_dhl_shipping_"><?php _e('Shipper Address','wf-shipping-dhl') ?></label>
		</td>
		<td scope="row" class="titledesc" style="display: block;margin-bottom: 20px;margin-top: 3px;">

			<table>
				<tr>
					<td>
						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Shipper Name','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Name of the person responsible for shipping.','wf-shipping-dhl') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_shipper_person_name" id="wf_dhl_shipping_shipper_person_name" style="" value="<?php echo (isset($general_settings['shipper_person_name'])) ? $general_settings['shipper_person_name'] : ''; ?>" placeholder=""> 	
						</fieldset>
					</td>
					<td>
						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Company Name','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Company name of the shipper.','wf-shipping-dhl') ?>"></span>	 <br/>
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_shipper_company_name" id="wf_dhl_shipping_shipper_company_name" style="" value="<?php echo (isset($general_settings['shipper_company_name'])) ? $general_settings['shipper_company_name'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
				</tr>
				<tr>
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Phone Number','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Phone number of the shipper.','wf-shipping-dhl') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_shipper_phone_number" id="wf_dhl_shipping_shipper_phone_number" style="" value="<?php echo (isset($general_settings['shipper_phone_number'])) ? $general_settings['shipper_phone_number'] : ''; ?>" placeholder=""> 	
						</fieldset>
					</td>
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Email Address','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Email address of the shipper.','wf-shipping-dhl') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_shipper_email" id="wf_dhl_shipping_shipper_email" style="" value="<?php echo (isset($general_settings['shipper_email'])) ? $general_settings['shipper_email'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
				</tr>
				<tr>
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Address Line 1','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Official address line 1 of the shipper.','wf-shipping-dhl') ?>"></span>	<br> 
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_freight_shipper_street" id="wf_dhl_shipping_freight_shipper_street" style="" value="<?php echo (isset($general_settings['freight_shipper_street'])) ? $general_settings['freight_shipper_street'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
					<td>

						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_"><?php _e('Address Line 2','wf-shipping-dhl') ?></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Official address line 2 of the shipper.','wf-shipping-dhl') ?>"></span>	<br/> 
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_shipper_street_2" id="wf_dhl_shipping_shipper_street_2" style="" value="<?php echo (isset($general_settings['shipper_street_2'])) ? $general_settings['shipper_street_2'] : ''; ?>" placeholder=""> 	
						</fieldset>

					</td>
				</tr>
				<tr>
					<td>
						<fieldset style="padding-left:3px;">
							<label for="wf_dhl_shipping_freight_shipper_city"><?php _e('City','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('City of the shipper.','wf-shipping-dhl') ?>"></span>	 <br/>

							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_freight_shipper_city" id="wf_dhl_shipping_freight_shipper_city" style="" value="<?php echo (isset($general_settings['freight_shipper_city'])) ? $general_settings['freight_shipper_city'] : ''; ?>" placeholder="">
						</fieldset>
					</td>
					<td>
						<fieldset style="padding-left:3px;">

							<label for="wf_dhl_shipping_freight_shipper_state"><?php _e('State','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('State of the shipper.','wf-shipping-dhl') ?>"></span>	<br/>
							<input class="input-text regular-input " type="text" name="wf_dhl_shipping_freight_shipper_state" id="wf_dhl_shipping_freight_shipper_state" style="" value="<?php echo (isset($general_settings['freight_shipper_state'])) ? $general_settings['freight_shipper_state'] : ''; ?>" placeholder="">
						</fieldset>
					</td>
				</tr>
				<tr>
					<td>

					<fieldset style="padding-left:3px;">

					<label for="wf_dhl_shipping_base_country"><?php _e('Country','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Country of the shipper(Used for fetching rates and label generation).','wf-shipping-dhl') ?>"></span><br/>

						<select style="width:75%;" name="wf_dhl_shipping_base_country" >
							<?php 
							$woocommerce_countys = $woocommerce->countries->get_countries();
							$selected_country =  (isset($general_settings['base_country']) && $general_settings['base_country'] !='') ? $general_settings['base_country'] : $woocommerce->countries->get_base_country();

							foreach ($woocommerce_countys as $key => $value) {
								if($key === $selected_country)
								{
									echo '<option value="'.$key.'" selected>'.$value.'</option>';
								}
								echo '<option value="'.$key.'">'.$value.'</option>';
							}
							?>
						</select>

					</fieldset>
				</td>
				<td>	
					<fieldset style="padding-left:3px;">

						<label for="wf_dhl_shipping_origin"><?php _e('Postal Code','wf-shipping-dhl') ?><font style="color:red;">*</font></label> <span class="woocommerce-help-tip" data-tip="<?php _e('Postal code of the shipper(Used for fetching rates and label generation).','wf-shipping-dhl') ?>"></span><br/>
						<input class="input-text regular-input " type="text" name="wf_dhl_shipping_origin" id="wf_dhl_shipping_origin" style="" value="<?php echo (isset($general_settings['origin'])) ? $general_settings['origin'] : ''; ?>" placeholder="">
					</fieldset>
				</td>
			</tr>
		</table>

	</td>
</tr>
<tr>
	<td colspan="2" style="text-align:right;">

		<button type="submit" class="button button-primary" name="wf_dhl_genaral_save_changes_button"> <?php _e('Save Changes','wf-shipping-dhl') ?> </button>
		
	</td>
</tr>
</table>

<script type="text/javascript">

		
		jQuery(window).load(function(){
			
			

			jQuery('#wf_dhl_shipping_insure_contents').change(function(){
				if(jQuery('#wf_dhl_shipping_insure_contents').is(':checked')) {
					jQuery('#wf_dhl_insurance_related').show();
				}else
				{
					jQuery('#wf_dhl_insurance_related').hide();
				}
			}).change();
		});

	</script>
