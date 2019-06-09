<?php

require_once(LS_Coupon__PLUGIN_DIR . 'rest-controllers/class.ls-coupon-rest-shop-controller.php');
require_once(LS_Coupon__PLUGIN_DIR . 'rest-controllers/class.ls-coupon-rest-coupon-controller.php');
require_once(LS_Coupon__PLUGIN_DIR . 'rest-controllers/class.ls-coupon-rest-code-controller.php');

class LS_Coupon_REST_API {

	public static function init() {
		(new LS_Coupon_REST_Shop_Controller())->register_routes();
		(new LS_Coupon_REST_Coupon_Controller())->register_routes();
		(new LS_Coupon_REST_Code_Controller())->register_routes();
	}

}