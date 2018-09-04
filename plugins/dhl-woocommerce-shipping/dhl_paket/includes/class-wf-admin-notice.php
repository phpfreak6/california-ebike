<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('wf_admin_notice')){
	define('WF_NOTICE_OPTION','wf_admin_notice_option');
	class wf_admin_notice{		
		
		public function __construct(){
			//nothing right now
		}
		
		public static function add_notice($message, $type='error'){
			$notices	=	get_option(WF_NOTICE_OPTION);
			if(!$notices){
				add_option(WF_NOTICE_OPTION);
				$notices	=	array(
					'error' => array(),
					'success' => array(),
					'warning' => array(),
					'info' => array(),
				);
			}
			switch($type){
				case 'error':
					$notices['error'][]	=	$message;
					break;
					
				case 'warning':
					$notices['warning'][]=	$message;
					break;
				
				case 'notice': case 'success':
					$notices['success'][]=	$message;
					break;					
					
				case 'info':
					$notices['info'][]=	$message;
					break;
			}
			update_option(WF_NOTICE_OPTION,$notices);
		}
		
		public static function throw_notices(){
			$notices	=	get_option(WF_NOTICE_OPTION);
			delete_option(WF_NOTICE_OPTION);
			if(!$notices){
				return;
			}
			if(isset($notices) && is_array($notices)){
				foreach($notices as $notice_type => $notice_list){
					if(is_array($notice_list) && count($notice_list)){
						$notice_class	=	'notice-'.$notice_type;
						echo '<div class="notice  '.$notice_class.' is-dismissible" >';
						foreach($notice_list as $notice){
							echo '<p>'.__($notice,'').'</p>';
						}
						echo '</div>';
					}
				}
			}		
		}
	}
}