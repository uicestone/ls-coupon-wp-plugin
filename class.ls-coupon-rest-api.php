<?php

require_once(LS_Coupon__PLUGIN_DIR . 'rest-controllers/class.ls-coupon-rest-coupon-controller.php');

class LS_Coupon_REST_API {

	public static function init() {
		(new LS_Coupon_REST_Coupon_Controller())->register_routes();
	}

}