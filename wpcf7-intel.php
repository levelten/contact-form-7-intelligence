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
* Plugin URI:        intl.getlevelten.com
* Description:       Adds behavior and visitor intelligence to contact form 7.
* Version:           1.0.0
* Author:            Tom McCracken
* Author URI:        getlevelten.com/blog/tom
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       intel
* Domain Path:       /languages
*/


add_filter('wpcf7_editor_panels', 'wpcf7_intel_wpcf7_editor_panels');
function wpcf7_intel_wpcf7_editor_panels($panels) {

  $new_page = array(
    'intel' => array(
      'title' => __( 'Intelligence', 'contact-form-7' ),
      'callback' => 'wpcf7_intel_form_edit_page'
    )
  );

  $panels = array_merge($panels, $new_page);

  return $panels;
}

function wpcf7_intel_form_edit_page($args) {
  require_once INTEL_DIR . 'includes/class-intel-form.php';

  $out = Intel_Form::drupal_get_form('wpcf7_intel_form_edit_form', $args);

  print $out;
}

function wpcf7_intel_form_edit_form($form, $form_state, $args) {

  $submission_goals = intel_get_event_goal_info('submission');

  $default_settings = array();
  $settings = get_option('wpcf7_intel_form_settings_' . $args->id(), array());

  $options = array();
  $options[''] = esc_html__( '-- None --', 'wpcf7_intel' );
  $options['form_submission-'] = esc_html__( 'Event: Form submission', 'wpcf7_intel' );
  $options['form_submission'] = esc_html__( 'Valued Event: Form submission!', 'wpcf7_intel' );

  foreach ($submission_goals AS $key => $goal) {
    $options[$key] = esc_html__( 'Goal: ', 'intel') . $goal['goal_title'];
  }
  // Set #tree to group all cf7_intel data in POST
  $form['wpcf7_intel'] = array(
    '#tree' => TRUE,
  );
  $form['wpcf7_intel']['tracking_event_name'] = array(
    '#type' => 'select',
    '#title' => Intel_Df::t('Tracking event'),
    '#options' => $options,
    '#default_value' => !empty($settings['tracking_event_name']) ? $settings['tracking_event_name'] : '',
    //'#description' => $desc,
    //'#size' => 32,
  );

  $form['wpcf7_intel']['tracking_event_value'] = array(
    '#type' => 'textfield',
    '#title' => Intel_Df::t('Tracking value'),
    '#default_value' => !empty($settings['tracking_event_value']) ? $settings['tracking_event_value'] : '',
    //'#description' => $desc,
    '#size' => 8,
  );

  $form['wpcf7_intel']['markup_1'] = array(
    '#type' => 'markup',
    '#markup' => '<h3>' . esc_html__( 'Field Map', 'cf7_intel' ) . '</h3>',
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
    '#title' => esc_html__( 'Field map', 'cf7_intel' ),
    '#collapsible' => FALSE,
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
      $f[$key] = array(
        '#type' => 'textfield',
        '#title' => $title ,
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
            '#default_value' => !empty($settings['field_map'][$key2]) ? $settings['field_map'][$key2] : '',
          );
        }
      }
    }

  }

  $form['wpcf7_intel']['field_map'] = Intel_Df::drupal_array_merge_deep($form['wpcf7_intel']['field_map'], $fp);
  $form['wpcf7_intel']['field_map'] = Intel_Df::drupal_array_merge_deep($form['wpcf7_intel']['field_map'], $fa);

  return $form;
}

add_filter('wpcf7_after_save', 'wpcf7_intel_wpcf7_form_edit_form_submit');
function wpcf7_intel_wpcf7_form_edit_form_submit($args) {
  //Intel_Df::watchdog('cf7 form submit POST', print_r($args, 1));
  //Intel_Df::watchdog('cf7 form submit POST', print_r($_POST, 1));
  update_option('wpcf7_intel_form_settings_' . $args->id(), $_POST['wpcf7_intel']);
}

add_filter('wpcf7_form_response_output', 'wpcf7_intel_wpcf7_form_response_output', 10, 4);
function wpcf7_intel_wpcf7_form_response_output($output, $class, $content, $instance) {
  return;
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

add_action('wpcf7_before_send_mail', 'wpcf7_intel_wpcf7_before_send_mail');
function wpcf7_intel_wpcf7_before_send_mail($obj) {
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

  Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail submission_values', print_r($vars['submission_values'], 1));

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

  Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail settings', print_r($settings, 1));
  // process visitor_properties
  if (!empty($settings['field_map']) && is_array($settings['field_map'])) {
    foreach ($settings['field_map'] as $prop_name => $field_name) {
      // strip [] brackets around $field_name
      $fn = substr(substr($field_name, 1), 0, -1);
      if (!empty($field_name) && !empty($posted_data[$fn])) {
        $visitor_properties[$prop_name] = $posted_data[$fn];
      }
    }
  }

  Intel_Df::watchdog('wpcf7_intel_wpcf7_before_send_mail visitor_properties', print_r($visitor_properties, 1));

  intel_process_form_submission($vars);
}

//$message = apply_filters( 'wpcf7_display_message', $message, $status );
add_filter('wpcf7_display_message', 'wpcf7_intel_wpcf7_display_message', 10, 2);
function wpcf7_intel_wpcf7_display_message($message, $status) {
  //$args = func_get_args();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_display_message args', print_r($args, 1));

  //$submission = WPCF7_Submission::get_instance();
  //Intel_Df::watchdog('wpcf7_intel_wpcf7_display_message submission', print_r($submission, 1));

  $script = intel()->tracker->get_pushes_script();
  $message .= "\n$script";
  //$message .= 'wpcf7_intel_wpcf7_display_message was here';

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
      $labels = gf_intel_intl_eventgoal_labels();
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

  $data['settings_url'] = '/wp-admin/admin.php?page=wpcf7&action=edit&post=' . $data['id'];

  return $data;
}