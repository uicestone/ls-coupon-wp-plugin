<?php

WP_CLI::add_command('ls-coupon', 'LS_Coupon_CLI');

/**
 * Filter spam comments.
 */
class LS_Coupon_CLI extends WP_CLI_Command {

	/**
	 * Download all cpc history reviews.
	 *
	 * ## EXAMPLES
	 *
	 *     wp ls-coupon test
	 *
	 */
	public function test() {
		WP_CLI::line('Test.');
	}

}