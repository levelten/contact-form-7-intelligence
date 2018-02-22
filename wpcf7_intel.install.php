<?php

/**
 * Fired when the intel plugin is installed and contains schema info and updates.
 *
 * @link       getlevelten.com/blog/tom
 * @since      1.2.7
 *
 * @package    Intel
 */


function wpcf7_intel_install() {

}

/**
 * Implements hook_uninstall();
 *
 * Delete plugin settings
 *
 */
function wpcf7_intel_uninstall() {
	global $wpdb;

	// delete options
	$sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpcf7_intel_%'";
	$wpdb->query( $sql );

	// uninstall plugin related intel data
	if (is_callable('intel_uninstall_plugin')) {
		intel_uninstall_plugin('wpcf7_intel');
	}

}

/**
 * Migrate submission tracking setting properties
 */
function wpcf7_intel_update_1001() {
	global $wpdb;

	$sql = "
		  SELECT *
		  FROM {$wpdb->prefix}options
		  WHERE option_name LIKE 'wpcf7_intel_form_settings_%'
		";

	$data = array();

	$results = $wpdb->get_results( $wpdb->prepare($sql, $data) );

	foreach ($results as $row) {
		$value = unserialize($row->option_value);

		if (isset($value['tracking_event_name'])) {
			$value['track_submission'] = $value['tracking_event_name'];
			unset($value['tracking_event_name']);
		}
		if (isset($value['tracking_event_value'])) {
			$value['track_submission_value'] = $value['tracking_event_value'];
			unset($value['tracking_event_value']);
		}

		update_option($row->option_name, $value);
	}

}