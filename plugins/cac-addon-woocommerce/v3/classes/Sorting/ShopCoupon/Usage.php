<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_WC_Column_ShopCoupon_Usage $column
 */
class ACA_WC_Sorting_ShopCoupon_Usage extends ACP_Sorting_Model {

	public function __construct( ACA_WC_Column_ShopCoupon_Usage $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

	public function get_sorting_vars() {
		$post_ids = array();
		foreach ( $this->strategy->get_results() as $post_id ) {
			$post_ids[ $post_id ] = $this->get_usage_limit( $post_id );
		}

		return array(
			'ids' => $this->sort( $post_ids ),
		);
	}

	private function get_usage_limit( $id ) {
		$coupon = new WC_Coupon( $id );

		$limit = $coupon->get_usage_limit();

		if ( ! $limit ) {
			$limit = $coupon->get_usage_limit_per_user();
		}

		return $limit;
	}

}
