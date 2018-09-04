<tr valign="top" id="ec_packing_options">
	<td class="titledesc" colspan="2" style="padding-left:0px">
	<strong><?php _e( 'Box Sizes', 'wf_dhl_wooCommerce_shipping' ); ?></strong><br><br>
		<style type="text/css">
			.ec_dhl_boxes td, .dhl_ec_services td {
				vertical-align: middle;
				padding: 4px 7px;
			}
			.dhl_ec_services th, .ec_dhl_boxes th {
				padding: 9px 7px;
			}
			.ec_dhl_boxes td input {
				margin-right: 4px;
			}
			.ec_dhl_boxes .ec_check-column {
				vertical-align: middle;
				text-align: left;
				padding: 0 7px;
			}
			.dhl_ec_services th.sort {
				width: 16px;
				padding: 0 16px;
			}
			.dhl_ec_services td.sort {
				cursor: move;
				width: 16px;
				padding: 0 16px;
				cursor: move;
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;
			}
		</style>
                <?php
                $pack_type = array(
			'BOX' => __('DHL Box ', 'wf-shipping-dhl'),
			'FLY' => __('Flyer', 'wf-shipping-dhl'),
                        'YP' => __('Your Pack', 'wf-shipping-dhl'),
		);
                $option_string = '';
                foreach ($pack_type as $k => $v) {
                    $selected = ($k == 'YP')? 'selected="selected"' : '';
                    $option_string.='<option value="'.$k.'"'.$selected.' >'.$v.'</option>';
                }
                ?>
		
		<script type="text/javascript">

			jQuery(window).load(function(){

                                var pack_type_options = '<?php echo $option_string; ?>';
				jQuery('#woocommerce_dhl_packing_method_ec').change(function(){

					if ( jQuery(this).val() == 'box_packing' )
						jQuery('#ec_packing_options').show();
					else
						jQuery('#ec_packing_options').hide();

				}).change();

				jQuery('.ec_dhl_boxes .insert').click( function() {
					var $tbody = jQuery('.ec_dhl_boxes').find('tbody');
					var size = $tbody.find('tr').size();
					var code = '<tr class="new">\
							<td class="ec_check-column"><input type="checkbox" /></td>\
							<input type="hidden" size="5" name="boxes_id[' + size + ']" />\
							<td><input type="text" size="20" name="boxes_name[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_length[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_width[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_height[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_length[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_width[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_inner_height[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_box_weight[' + size + ']" /></td>\
							<td><input type="text" size="5" name="boxes_max_weight[' + size + ']" /></td>\
							<td><input type="checkbox" name="boxes_enabled[' + size + ']" /></td>\
						        <td><select name="boxes_pack_type[' + size + ']" >' + pack_type_options + '</select></td>\
                                                     </tr>';

					$tbody.append( code );

					return false;
				} );

				jQuery('.ec_dhl_boxes .remove').click(function() {
					var $tbody = jQuery('.ec_dhl_boxes').find('tbody');

					$tbody.find('.ec_check-column input:checked').each(function() {
						jQuery(this).closest('tr').hide().find('input').val('');
					});

					return false;
				});

				// Ordering
				jQuery('.dhl_ec_services tbody').sortable({
					items:'tr',
					cursor:'move',
					axis:'y',
					handle: '.sort',
					scrollSensitivity:40,
					forcePlaceholderSize: true,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'wc-metabox-sortable-placeholder',
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
						dhl_ec_services_row_indexes();
					}
				});

				function dhl_ec_services_row_indexes() {
					jQuery('.dhl_ec_services tbody tr').each(function(index, el){
						jQuery('input.order', el).val( parseInt( jQuery(el).index('.dhl_ec_services tr') ) );
					});
				};

			});

		</script>
	</td>
</tr>