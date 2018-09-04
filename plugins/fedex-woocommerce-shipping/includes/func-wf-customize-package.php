<?php
/*
 * Handle bundled products and external products on cart and order page while calculate shipping and generate label 
 */

// Skip the bundled products and external products while generating the packages on order page

if( !function_exists('wf_skip_bundled_external_package_generate_label') )
{
	function wf_skip_bundled_external_package_generate_label( $package )
	{
		foreach( $package['contents'] as $key => $pack)
		{
			if( $pack['data']->get_type() == 'bundle' || $pack['data']->get_type() == 'external' )
			{
				unset($package['contents'][$key]);
			}
		}
		return $package;
	}
}

add_filter( 'wf_customize_package_on_generate_label', 'wf_skip_bundled_external_package_generate_label');

//End of skip bundled products and external products while generating the packages


// Customize or break package if package contains bundled product on cart and checkout page
if( !function_exists('wf_break_bundled_products_to_individual_product_packages') )
{
	function wf_break_bundled_products_to_individual_product_packages( $package )
	{
		foreach($package['contents'] as $key => $product)
		{
			if( isset($product['stamp']) )	    //Set only in bundled product
			{
				foreach( $product['stamp'] as $stamps)
				{
					$quantity[ $stamps['product_id'] ] = $stamps['quantity'];
				}
				$stamps					= $product['stamp'];
				$quantity[ $product['product_id'] ]	= $product['quantity'];

				//Create packages for bundled products by seperating to individual products
				foreach( $stamps as $stamp )
				{
					$package['contents'][ $stamp['product_id'] ] = array(
								'key'		=>	$stamp['product_id'],
								'product_id'	=>	$stamp['product_id'],
								'variation_id'	=>	isset($stamp['variation_id']) ? $stamp['variation_id'] : 0,
								'quantity'	=>	$quantity[ $stamp['product_id'] ] * $quantity[ $product['product_id'] ],
								'data'		=>	wc_get_product($stamp['product_id'])
					);
				}
				unset( $package['contents'][$key]);
			}
		}
		return $package;
	}
}

add_filter( 'wf_customize_package_on_cart_and_checkout', 'wf_break_bundled_products_to_individual_product_packages' );

// End of customize or break package if package contains bundled product on cart and checkout page