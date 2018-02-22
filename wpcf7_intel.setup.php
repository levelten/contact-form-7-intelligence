<?php

/**
 * Included to assist in initial setup of plugin
 *
 * @link       getlevelten.com/blog/tom
 * @since      1.0.8
 *
 * @package    Intel
 */

if (!is_callable('intel_setup')) {
	include_once wpcf7_intel()->dir . 'intel_com/intel.setup.php';
}

class WPCF7_Intel_Setup extends Intel_Setup {

	public $plugin_un = 'wpcf7_intel';

}

function wpcf7_intel_setup() {
	return WPCF7_Intel_Setup::instance();
}
wpcf7_intel_setup();
