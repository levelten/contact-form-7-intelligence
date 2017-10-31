<?php
/**
* Contact Form 7 Intelligence bootstrap file
*
* This file is read by WordPress to generate the plugin information in the plugin
* admin area. This file also includes all of the dependencies used by the plugin,
* registers the activation and deactivation functions, and defines a function
* that starts the plugin.
*
* @link              getlevelten.com/blog/tom
* @since             1.0.0
* @package           Intelligence
*
* @wordpress-plugin
* Plugin Name:       Contact Form 7 Intelligence
* Plugin URI:        http://intelligencewp.com/plugin/cf7-intelligence/
* Description:       Integrates Intelligence with Contact Form 7 enabling easy Google Analytics goal tracking and visitor intelligence gathering.
* Version:           1.0.2
* Author:            Tom McCracken
* Author URI:        getlevelten.com/blog/tom
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       wpcf7_intel
* Domain Path:       /languages
* GitHub Plugin URI: https://github.com/levelten/wp-cf7-intelligence
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

define('WPCF7_INTEL_VER', '1.0.3');

add_filter('wpcf7_editor_panels', 'wpcf7_intel_wpcf7_editor_panels');
function wpcf7_intel_wpcf7_editor_panels($panels) {

  $new_page = array(
    'intel' => array(
      'title' => __( 'Intelligence', 'wpcf7_intel' ),
      'callback' => 'wpcf7_intel_form_edit_page'
    )
  );

  $panels = array_merge($panels, $new_page);

  return $panels;
}

function wpcf7_intel_form_edit_page($contact_form) {
  if (!defined('INTEL_VER')) {
    echo wpcf7_intel_error_msg_missing_intel();
    return;
  }

  require_once INTEL_DIR . 'includes/class-intel-form.php';

  $out = Intel_Form::drupal_get_form('wpcf7_intel_form_edit_form', $contact_form);
  if (is_array($out)) {
    $out = Intel_Df::render($out);
  }

  print $out;
}

function wpcf7_intel_intl_eventgoal_labels() {

  $submission_goals = intel_get_event_goal_info('submission');

  $data = array();

  $data[''] = '-- ' . esc_html__( 'None', 'wpcf7_intel' ) . ' --';
  $data['form_submission-'] = esc_html__( 'Event: Form submission', 'wpcf7_intel' );
  $data['form_submission'] = esc_html__( 'Valued event: Form submission!', 'wpcf7_intel' );


  foreach ($submission_goals AS $key => $goal) {
    $data[$key] = esc_html__( 'Goal: ', 'intel') . $goal['goal_title'];
  }

  return $data;
}

function wpcf7_intel_form_edit_form($form, $form_state, $contact_form) {

  //$submission_goals = intel_get_event_goal_info('submission');

  $default_settings = array();
  $settings = get_option('wpcf7_intel_form_settings_' . $contact_form->id(), array());

  intel()->admin->enqueue_scripts();
  intel()->admin->enqueue_styles();

  // Set #tree to group all cf7_intel data in POST
  $form['wpcf7_intel'] = array(
    '#tree' => TRUE,
  );

  $form['wpcf7_intel']['wrapper_0'] = array(
    '#type' => 'markup',
    '#markup' => '<div class="bootstrap-wrapper">',
  );

  $markup = '';
  $markup .= '<h3>' . esc_html(esc_html__('Submission tracking', 'wpcf7_intel')) . '</h3>';
  $markup .= '<p>';
  $markup .= esc_html(esc_html__('Intelligence can automate triggering a goal or event upon form submission.', 'wpcf7_intel'));
  $markup .= ' ' . esc_html(esc_html__('Please use the below fields to configure how you want to track form submission in analytics.', 'wpcf7_intel'));
  $markup .= '</p>';
  $form['wpcf7_intel']['tracking_header'] = array(
    '#type' => 'markup',
    '#markup' => $markup,
  );

  $options = wpcf7_intel_intl_eventgoal_labels();
  $form['wpcf7_intel']['tracking_event_name'] = array(
    '#type' => 'select',
    '#title' => esc_html__('Tracking event', 'wpcf7_intel'),
    '#options' => $options,
    '#default_value' => !empty($settings['tracking_event_name']) ? $settings['tracking_event_name'] : '',
    '#description' => esc_html__('Select the goal or event you would like to trigger to be tracked in analytics when a form is submitted.', 'wpcf7_intel'),
    //'#size' => 32,
  );

  $desc = esc_html__('Each goal has a default site wide value set in the Intelligence goal settings, but you can override that value per form.');
  $desc .= esc_html__('If you would like to use a custom goal/event value, enter it here otherwise leave the field blank to use the site defaults.', 'wpcf7_intel');
  $form['wpcf7_intel']['tracking_event_value'] = array(
    '#type' => 'textfield',
    '#title' => esc_html__('Tracking value override', 'wpcf7_intel'),
    '#default_value' => !empty($settings['tracking_event_value']) ? $settings['tracking_event_value'] : '',
    '#description' => $desc,
    '#size' => 8,
  );

  $markup = '';
  $markup .= '<h3>' . esc_html__( 'Contact profile', 'wpcf7_intel' ) . '</h3>';
  $markup .= '<p>';
  $markup .= esc_html__( 'Intelligence Pro can build a contact profile based on form submissions and other data integrations.', 'wpcf7_intel' );
  $markup .= ' ' . esc_html__( 'Use the fields below to map Intelligence Contact properties to Contact Form 7 fields.', 'wpcf7_intel' );
  $markup .= '</p>';
  $markup .= '<p>';
  $markup .= esc_html__( 'You can use the following field-tags as inputs for the field map.', 'wpcf7_intel' );
  $markup .= '<br>';
  $markup .= wpcf7_intel_field_map_tags($contact_form, 'mail', array('string' => 1));
  $markup .= '</p>';
  $form['wpcf7_intel']['markup_1'] = array(
    '#type' => 'markup',
    '#markup' => $markup,
  );

  $prop_info = intel()->visitor_property_info();

  $prop_wf_info = intel()->visitor_property_webform_info();

  $priority = array(
    'data.name' => 1,
    'data.givenName' => 1,
    'data.familyName' => 1,
    'data.email' => 1,
  );
  $form['wpcf7_intel']['field_map'] = array(
    '#type' => 'fieldset',
    '#title' => esc_html__( 'Field map', 'wpcf7_intel' ),
    '#collapsible' => FALSE,
    //'#description' => esc_html__( '', 'wpcf7_intel' ),
  );
  $fp = array();
  $fa = array();
  //foreach ($prop_info as $k => $v) {
  foreach ($prop_wf_info as $k => $v) {
    $pi = $prop_info[$k];

    if (!empty($priority[$k])) {
      $f = &$fp;
    }
    else {
      $f = &$fa;
    }

    if (array_key_exists('@value', $pi['variables'])) {
      $key = $k;
      $title = !empty($v['title']) ? $v['title'] : $pi['title'];
      $desc = !empty($pi['description']) ? $pi['description'] : '';
      $f[$key] = array(
        '#type' => 'textfield',
        '#title' => $title ,
        '#description' => $desc,
        '#default_value' => !empty($settings['field_map'][$key]) ? $settings['field_map'][$key] : '',
      );
    }

    if (!empty($v['variables'])) {
      foreach ($v['variables'] as $kk => $vv) {
        if ($pi['variables'][$kk] != '@value') {
          $key2 = $key . "__$kk";
          $f[$key2] = array(
            '#type' => 'textfield',
            '#title' => $title  . ': ' . (!empty($vv['title']) ? $vv['title'] : $kk),
            '#description' => $desc,
            '#default_value' => !empty($settings['field_map'][$key2]) ? $settings['field_map'][$key2] : '',
          );
        }
      }
    }

  }

  $form['wpcf7_intel']['field_map'] = Intel_Df::drupal_array_merge_deep($form['wpcf7_intel']['field_map'], $fp);
  $form['wpcf7_intel']['field_map'] = Intel_Df::drupal_array_merge_deep($form['wpcf7_intel']['field_map'], $fa);

  $form['wpcf7_intel']['wrapper_1'] = array(
    '#type' => 'markup',
    '#markup' => '</div>',
  );

  return $form;
}

function wpcf7_intel_field_map_tags($contact_form, $for = 'mail', $options = array()) {
  $mail = wp_parse_args( $contact_form->prop( $for ),
    array(
      'active' => false,
      'recipient' => '',
      'sender' => '',
      'subject' => '',
      'body' => '',
      'additional_headers' => '',
      'attachments' => '',
      'use_html' => false,
      'exclude_blank' => false,
    )
  );

  $mail = array_filter( $mail );

  $ret = array();

  foreach ( (array) $contact_form->collect_mail_tags() as $mail_tag ) {
    $pattern = sprintf( '/\[(_[a-z]+_)?%s([ \t]+[^]]+)?\]/',
      preg_quote( $mail_tag, '/' ) );
    $used = preg_grep( $pattern, $mail );
    if (!empty($options['string'])) {
      $ret[] = sprintf(
        '<span class="%1$s">[%2$s]</span>',
        'mailtag code ' . ( $used ? 'used' : 'unused' ),
        esc_html( $mail_tag ) );
    }
    else {
      $ret[] = $mail_tag;
    }

  }
  if (!empty($options['string'])) {
    return implode(' ', $ret);
  }
  else {
    return $ret;
  }
}

add_filter('wpcf7_after_save', 'wpcf7_intel_wpcf7_form_edit_form_submit');
function wpcf7_intel_wpcf7_form_edit_form_submit($args) {
  if (!defined('INTEL_VER')) {
    return;
  }
  //Intel_Df::watchdog('cf7 form submit POST', print_r($args, 1));
  //Intel_Df::watchdog('cf7 form submit POST', print_r($_POST, 1));

  // sanitize inputs
  $_POST['wpcf7_intel']['tracking_event_name'] = sanitize_text_field($_POST['wpcf7_intel']['tracking_event_name']);
  $_POST['wpcf7_intel']['tracking_event_value'] = trim(sanitize_text_field($_POST['wpcf7_intel']['tracking_event_value']));

  if ($_POST['wpcf7_intel']['tracking_event_value'] !== '') {
    if (!is_numeric($_POST['wpcf7_intel']['tracking_event_value'])) {
      $_POST['wpcf7_intel']['tracking_event_value'] = '';
    }
    else {
      $_POST['wpcf7_intel']['tracking_event_value'] = intval($_POST['wpcf7_intel']['tracking_event_value']);
    }
  }

  foreach ($_POST['wpcf7_intel']['field_map'] as $k => $v) {
    $_POST['wpcf7_intel']['field_map'][$k] = sanitize_text_field($v);
  }

  // save option
  update_option('wpcf7_intel_form_settings_' . $args->id(), $_POST['wpcf7_intel']);
}

/*
add_filter('wpcf7_form_response_output', 'wpcf7_intel_wpcf7_form_response_output', 10, 4);
function wpcf7_intel_wpcf7_form_response_output($output, $class, $content, $instance) {
  return $output;
  $args = func_get_args();
  Intel_Df::watchdog('wpcf7_intel_wpcf7_form_response_output args', print_r($args, 1));

  //$submission = WPCF7_Submission::get_instance();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_form_response_output submission', print_r($submission, 1));

  //$posted_data = $submission->get_posted_data();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_form_response_output posted_data', print_r($posted_data, 1));

  //$output .= 'INTEL Rules!';

  //$unit_tag = $instance->get_unit_tag();
  $data = array(
    'intel' => array(
      'config' => array(
        'trackForms' => array(
          "form.wpcf7-form",
        )
      )
    ),
  );
  intel()->add_js($data, array('type' => 'settings'));


  //Intel_Df::watchdog('wpcf7_intel_wpcf7_form_response_output unit_tag', print_r($unit_tag, 1));

  return $output;
}
*/

add_action('wpcf7_before_send_mail', 'wpcf7_intel_wpcf7_before_send_mail');
function wpcf7_intel_wpcf7_before_send_mail($obj) {
  // check if intel is installed
  if (!defined('INTEL_VER')) {
    return;
  }
  //$args = func_get_args();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail()', '');
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail args', print_r($args, 1));

  $settings = get_option('wpcf7_intel_form_settings_' . $obj->id(), array());

  $submission = WPCF7_Submission::get_instance();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail submission', print_r($submission, 1));

  $posted_data = $submission->get_posted_data();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail posted_data', print_r($posted_data, 1));

  $vars = intel_form_submission_vars_default();

  $submission = &$vars['submission'];
  $track = &$vars['track'];
  $visitor_properties = &$vars['visitor_properties'];

  $submission->type = 'wpcf7';
  $submission->fid = $obj->id();
  //$submission->fsid = $entry['id'];
  //$submission->submission_uri = "/wp-admin/admin.php?page=gf_entries&view=entry&id={$submission->fid}&lid={$submission->fsid}";
  $submission->form_title = $obj->title();

  $vars['submission_values'] = array();
  //$submission->data['submission_post'] = array();
  foreach ($posted_data as $k => $v) {
    if (substr($k, 0, 1) != '_') {
      $vars['submission_values'][$k] = $v;
    }
  }

  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail submission_values', print_r($vars['submission_values'], 1));

  if (!empty($settings['tracking_event_name'])) {
    $track['name'] = $settings['tracking_event_name'];
    if (substr($track['name'], -1) == '-') {
      $track['name'] = substr($track['name'], 0, -1);
      $track['valued_event'] = 0;
    }
    if (!empty($settings['tracking_event_value'])) {
      $track['value'] = $settings['tracking_event_value'];
    }
  }

  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail settings', print_r($settings, 1));
  // process visitor_properties
  if (!empty($settings['field_map']) && is_array($settings['field_map'])) {
    foreach ($settings['field_map'] as $prop_name => $field_name) {
      // strip [] brackets around $field_name
      $fn = substr(substr($field_name, 1), 0, -1);
      if (!empty($field_name) && !empty($posted_data[$fn])) {
        $visitor_properties[$prop_name] = sanitize_text_field($posted_data[$fn]);
      }
    }
  }

  //Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail visitor_properties', print_r($visitor_properties, 1));

  intel_process_form_submission($vars);
}

add_filter('wpcf7_display_message', 'wpcf7_intel_wpcf7_display_message', 10, 2);
function wpcf7_intel_wpcf7_display_message($message, $status) {
  // check if intel is installed
  if (!defined('INTEL_VER')) {
    return $message;
  }

  // if there was an error, no intel pushes should exists.
  $pushes = intel()->tracker->get_intel_pushes();

  if (empty($pushes)) {
    return $message;
  }

  // cf7 places the message on the page twice, once for standard display and a
  // another for screen readers which will trigger the event twice. The following
  // js will only push the events once.
  $message .= "\n<script>
    var _wpcf7_intel_goal_cnt = _wpcf7_intel_goal_cnt || 0;
    if (_wpcf7_intel_goal_cnt == 0) {
      _wpcf7_intel_goal_cnt++;
";
  foreach ($pushes as $key => $value) {
    $message .= "  io('$key', " . json_encode($value) . ");\n";
  }
  $message .= "}\n</script>";

  return $message;
}

add_filter('intel_form_type_forms_info', 'wpcf7_intel_form_type_forms_info');
function wpcf7_intel_form_type_forms_info($info) {
  $args = array(
    'post_type'   => 'wpcf7_contact_form'
  );

  $info['wpcf7'] = get_posts( $args );

  return $info;
}

add_filter('intel_form_type_wpcf7_form_setup', 'wpcf7_intel_form_type_form_setup', 0, 2);
function wpcf7_intel_form_type_form_setup($data, $info) {

  if (empty($info->ID)) {
    return $info;
  }

  $data['id'] = $info->ID;
  $data['title'] = $info->post_title;
  $options = get_option('wpcf7_intel_form_settings_' . $info->ID, array());

  if (!empty($options)) {
    if (!empty($options['tracking_event_name'])) {
      $labels = wpcf7_intel_intl_eventgoal_labels();
      $data['tracking_event'] = !empty($labels[$options['tracking_event_name']]) ? $labels[$options['tracking_event_name']] : $options['tracking_event_name'];
    }

    $data['field_map'] = array();
    if (!empty($options['field_map']) && is_array($options['field_map'])) {
      foreach ($options['field_map'] as $k => $v) {
        if (!empty($v)) {
          $data['field_map'][] = $v;
        }
      }
    }

  }

  $data['settings_url'] = '/wp-admin/admin.php?page=wpcf7&action=edit&post=' . $data['id'] . '#intel';

  return $data;
}

/*
 *
 */
// dependencies notices
add_action( 'admin_notices', 'wpcf7_intel_plugin_dependency_notice' );
function wpcf7_intel_plugin_dependency_notice() {
  global $pagenow;
  // Short-circuit it.
  if ( 'plugins.php' != $pagenow ) {
    return;
  }

  // check dependencies
  if (!function_exists('intel_is_plugin_active')) {
    echo wpcf7_intel_error_msg_missing_intel(array('notice' => 1));
    return;
  }

  if (!intel_is_plugin_active('wpcf7')) {
    echo '<div class="error">';
    echo '<p>';
    echo '<strong>' . __('Notice:') . '</strong> ';
    _e('The Contact Form 7 Intelligence plugin requires the Contact Form 7 plugin to be installed and active.');
    echo '</p>';
    echo '</div>';
    return;
  }
}

function wpcf7_intel_error_msg_missing_intel($options = array()) {
  $msg = '';

  if (!empty($options['notice'])) {
    $msg .=  '<div class="error">';
  }
  $msg .=  '<p>';
  $msg .=  '<strong>' . __('Notice:') . '</strong> ';
  $msg .=  __('The Contact Form 7 Intelligence plugin requires the Intelligence plugin to be installed and active.');
  $msg .=  '</p>';
  if (!empty($options['notice'])) {
    $msg .=  '</div>';
  }
  return $msg;
}