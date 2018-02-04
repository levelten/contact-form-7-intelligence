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
* Plugin URI:        https://wordpress.org/plugins/cf7-intelligence
* Description:       Integrates Intelligence with Contact Form 7 enabling easy Google Analytics goal tracking and visitor intelligence gathering.
* Version:           1.0.7
* Author:            LevelTen
* Author URI:        https://intelligencewp.com
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

define('WPCF7_INTEL_VER', '1.0.7');

if (0) {
// Create a helper function for easy SDK access.
  function wpcf7_intel_fs() {
    global $wpcf7_intel_fs;

    if (!isset($wpcf7_intel_fs)) {
      // Include Freemius SDK.
      if (file_exists(dirname(dirname(__FILE__)) . '/intelligence/freemius/start.php')) {
        // Try to load SDK from parent plugin folder.
        require_once dirname(dirname(__FILE__)) . '/intelligence/freemius/start.php';
      }
      else {
        if (file_exists(dirname(dirname(__FILE__)) . '/intelligence-premium/freemius/start.php')) {
          // Try to load SDK from premium parent plugin folder.
          require_once dirname(dirname(__FILE__)) . '/intelligence-premium/freemius/start.php';
        }
        else {
          require_once dirname(__FILE__) . '/freemius/start.php';
        }
      }

      $wpcf7_intel_fs = fs_dynamic_init(array(
        'id' => '1676',
        'slug' => 'cf7-intelligence',
        'type' => 'plugin',
        'public_key' => 'pk_24a97d212435dfaf0323e63dc851d',
        'is_premium' => FALSE,
        'has_paid_plans' => FALSE,
        'parent' => array(
          'id' => '1675',
          'slug' => 'intelligence',
          'public_key' => 'pk_cd3b6d95db54c50e50ccbf77112de',
          'name' => 'Intelligence for WordPress',
        ),
        'menu' => array(
          'slug' => 'intel_config',
          'first-path' => 'admin.php?page=intel_config&plugin=wpcf7_intel&q=admin/config/intel/settings/setup/wpcf7_intel',
          'account' => FALSE,
          'support' => FALSE,
        ),
      ));
    }

    return $wpcf7_intel_fs;
  }

  function wpcf7_intel_fs_is_parent_active_and_loaded() {
    // Check if the parent's init SDK method exists.
    return function_exists('intel_fs');
  }

  function wpcf7_intel_fs_is_parent_active() {
    $active_plugins_basenames = get_option('active_plugins');

    foreach ($active_plugins_basenames as $plugin_basename) {
      if (0 === strpos($plugin_basename, 'intelligence/') ||
        0 === strpos($plugin_basename, 'intelligence-premium/')
      ) {
        return TRUE;
      }
    }

    return FALSE;
  }

  function wpcf7_intel_fs_init() {
    if (wpcf7_intel_fs_is_parent_active_and_loaded()) {
      // Init Freemius.
      wpcf7_intel_fs();

      // Parent is active, add your init code here.
    }
    else {
      // Parent is inactive, add your error handling here.
    }
  }

  if (wpcf7_intel_fs_is_parent_active_and_loaded()) {
    // If parent already included, init add-on.
    wpcf7_intel_fs_init();
  }
  else {
    if (wpcf7_intel_fs_is_parent_active()) {
      // Init add-on only after the parent is loaded.
      add_action('_loaded', 'wpcf7_intel_fs_init');
    }
    else {
      // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
      wpcf7_intel_fs_init();
    }
  }
}

/**
 * Class NF_Intel
 */
final class WPCF7_Intel {

  protected $version = WPCF7_INTEL_VER;

  public static  $plugin_un = 'wpcf7_intel';

  /**
   * @var NF_MailChimp
   * @since 3.0
   */
  private static $instance;

  /**
   * Plugin Directory
   *
   * @since 3.0
   * @var string $dir
   */
  public static $dir = '';

  /**
   * Plugin URL
   *
   * @since 3.0
   * @var string $url
   */
  public static $url = '';

  /**
   * Main Plugin Instance
   *
   * Insures that only one instance of a plugin class exists in memory at any one
   * time. Also prevents needing to define globals all over the place.
   *
   * @since 3.0
   * @static
   * @static var array $instance
   * @return NF_Intel Instance
   */
  public static function getInstance() {

    if (!isset(self::$instance) && !(self::$instance instanceof WPCF7_Intel)) {
      self::$instance = new WPCF7_Intel();

      self::$dir = plugin_dir_path(__FILE__);

      self::$url = plugin_dir_url(__FILE__);
    }

    return self::$instance;
  }

  /**
   * constructor.
   *
   */
  public function __construct() {

    add_action( 'admin_menu', array( $this, 'admin_menu' ));

    // plugin setup hooks
    if (!is_callable('intel')) {
      // Add pages for plugin setup
      add_action( 'admin_menu', array( $this, 'intel_setup_menu' ));
    }

    global $pagenow;

    if ( 'plugins.php' == $pagenow ) {
      add_action( 'admin_notices', array( $this, 'plugin_setup_notice') );
    }

    add_filter('intel_menu_info', array( $this, 'intel_menu' ));
  }

  public function admin_menu() {
    //$plugin_un = 'wpcf7_intel';
    add_submenu_page('wpcf7', esc_html__("Setup", self::$plugin_un), esc_html__("Intelligence", self::$plugin_un), 'manage_options', 'wpcf7_intel', array($this, 'wpcf7_settings_page'));

  }

  public function get_title() {
    return __( 'reCAPTCHA', 'contact-form-7' );
  }

  public function wpcf7_settings_page() {
    $items = array();

    $items[] = '<div class="wrap">';
    $items[] = '<h1>' . esc_html__( 'Intelligence Settings', self::$plugin_un ) . '</h1>';
    $items[] = '</div>';

    $connect_desc = __('Not connected.', self::$plugin_un);
    $connect_desc .= ' ' . sprintf(
        __( ' %sSetup Intelligence%s', self::$plugin_un ),
        '<a href="/wp-admin/admin.php?page=intel_config&plugin=' . self::$plugin_un . '&q=admin/config/intel/settings/setup/' . self::$plugin_un . '" class="button">', '</a>'
      );
    if($this->is_intel_installed()) {
      $connect_desc = __('Connected');
    }

    $items[] = '<table class="form-table">';
    $items[] = '<tbody>';
    $items[] = '<tr>';
    $items[] = '<th>' . esc_html__( 'Intelligence API', self::$plugin_un ) . '</th>';
    $items[] = '<td>' . $connect_desc . '</td>';
    $items[] = '</tr>';


    if ($this->is_intel_installed()) {
      $eventgoal_options = intel_get_form_submission_eventgoal_options();
      $default_name = get_option('intel_form_track_submission_default', 'form_submission');
      $value = !empty($eventgoal_options[$default_name]) ? $eventgoal_options[$default_name] : Intel_Df::t('(not set)');
      $l_options = Intel_Df::l_options_add_destination('wp-admin/admin.php?page=wpcf7_intel');
      $l_options['attributes'] = array(
        'class' => array('button'),
      );
      $value .= ' ' . Intel_Df::l(esc_html__('Change', self::$plugin_un), 'admin/config/intel/settings/form/default_tracking', $l_options);
      $items[] = '<tr>';
      $items[] = '<th>' . esc_html__( 'Default submission event/goal', self::$plugin_un ) . '</th>';
      $items[] = '<td>' . $value . '</td>';
      $items[] = '</tr>';

      $default_value = get_option('intel_form_track_submission_value_default', '');
      $items[] = '<tr>';
      $items[] = '<th>' . esc_html__( 'Default submission value', self::$plugin_un ) . '</th>';
      $items[] = '<td>' . (!empty($default_value) ? $default_value : Intel_Df::t('(default)')) . '</td>';
      $items[] = '</tr>';
    }
    $items[] = '</tbody>';
    $items[] = '</table>';

    $output = implode("\n", $items);
    echo $output;
  }

  /*************************************
   * Plugin setup functions
   */

  public function is_intel_installed($level = 'min') {
    if (!is_callable('intel_is_installed')) {
      return FALSE;
    }
    return intel_is_installed($level);
  }

  public function plugin_setup_notice() {

    // check dependencies
    if (!function_exists('intel_is_plugin_active')) {
      echo '<div class="error">';
      echo '<p>';
      echo '<strong>' . __('Notice:') . '</strong> ';

      _e('Contact Form 7 Intelligence plugin needs to be setup:', self::$plugin_un);
      echo ' ' . sprintf(
          __( ' %sSetup plugin%s', self::$plugin_un ),
          '<a href="/wp-admin/admin.php?page=intel_config&plugin=' . self::$plugin_un . '" class="button">', '</a>'
        );

      echo '</p>';
      echo '</div>';
      return;
    }

    if (!intel_is_plugin_active('wpcf7')) {
      echo '<div class="error">';
      echo '<p>';
      echo '<strong>' . __('Notice:') . '</strong> ';
      _e('The Contact Form 7 Intelligence plugin requires the Contact Form 7 plugin to be installed and active.', self::$plugin_un);
      echo '</p>';
      echo '</div>';
      return;
    }
  }

  /**
   * Implements hook_activated_plugin()
   *
   * Used to redirect back to wizard after intel is activated
   *
   * @param $plugin
   */
  public function intel_setup_activated_plugin($plugin) {
    require_once( self::$dir . 'intel_com/intel.setup.inc' );
    intel_setup_activated_plugin($plugin);
  }

  public function intel_setup_menu() {
    global $wp_version;

    $plugin_un = 'wpcf7_intel';

    // check if intel is installed, if so exit
    if (is_callable('intel')) {
      return;
    }

    add_menu_page(esc_html__("Intelligence", $plugin_un), esc_html__("Intelligence", $plugin_un), 'manage_options', 'intel_admin', array($this, 'intel_setup_page'), version_compare($wp_version, '3.8.0', '>=') ? 'dashicons-analytics' : '');
    add_submenu_page('intel_admin', esc_html__("Setup", $plugin_un), esc_html__("Setup", $plugin_un), 'manage_options', 'intel_config', array($this, 'intel_setup_page'));

    add_action('activated_plugin', array( $this, 'intel_setup_activated_plugin' ));
  }

  public function intel_setup_page() {
    if (!empty($_GET['plugin']) && $_GET['plugin'] != 'wpcf7_intel') {
      return;
    }
    $output = $this->intel_setup_plugin_instructions();

    print $output;
  }

  public function intel_setup_plugin_instructions($options = array()) {

    require_once( self::$dir . 'intel_com/intel.setup.inc' );

    // initialize setup state option
    $intel_setup = get_option('intel_setup', array());
    $intel_setup['active_path'] = 'admin/config/intel/settings/setup/' . self::$plugin_un;
    update_option('intel_setup', $intel_setup);

    intel_setup_set_activated_option('intelligence', array('destination' => $intel_setup['active_path']));

    $items = array();

    $items[] = '<h1>' . __('Contact Form 7 Intelligence Setup', self::$plugin_un) . '</h1>';
    $items[] = __('To continue with the setup please install the Intelligence plugin.', self::$plugin_un);

    $items[] = "<br>\n<br>\n";

    $vars = array(
      'plugin_slug' => 'intelligence',
      'card_class' => array(
        'action-buttons-only'
      ),
      //'activate_url' => $activate_url,
    );
    $vars = intel_setup_process_install_plugin_card($vars);

    $items[] = '<div class="intel-setup">';
    $items[] = intel_setup_theme_install_plugin_card($vars);
    $items[] = '</div>';

    return implode(' ', $items);
  }

  public function intel_menu($items = array()) {
    $items['admin/config/intel/settings/setup/' . self::$plugin_un] = array(
      'title' => 'Setup',
      'description' => Intel_Df::t('Contact Form 7 Intelligence initial plugin setup'),
      'page callback' => 'wpcf7_intel_admin_setup_page',
      'access callback' => 'user_access',
      'access arguments' => array('admin intel'),
      'type' => Intel_Df::MENU_LOCAL_ACTION,
      //'weight' => $w++,
      'file' => 'admin/wpcf7_intel.admin_setup.inc',
      'file path' => self::$dir,
    );
    return $items;
  }
}

function wpcf7_intel() {
  return WPCF7_Intel::getInstance();
}
wpcf7_intel();

/*
function wpcf7_intel_install() {
  require_once plugin_dir_path( __FILE__ ) . 'wpcf7-intel.install';
  wpcf7_intel_install();
}
register_uninstall_hook( __FILE__, 'wpcf7_intel_install' );
*/

function wpcf7_intel_activation() {
  if (is_callable('intel_activate_plugin')) {
    intel_activate_plugin('wpcf7_intel');
  }
}
register_activation_hook( __FILE__, 'wpcf7_intel_activation' );

function _wpcf7_intel_uninstall() {
  require_once plugin_dir_path( __FILE__ ) . 'wpcf7-intel.install';
  wpcf7_intel_uninstall();
}
register_uninstall_hook( __FILE__, '_wpcf7_intel_uninstall' );

/**
 * Implements hook_intel_system_info()
 *
 * Registers plugin with intel_system
 *
 * @param array $info
 * @return array
 */
function wpcf7_intel_intel_system_info($info = array()) {
  $info['wpcf7_intel'] = array(
    'plugin_file' => 'wpcf7-intel.php', // Main plugin file
    'plugin_path' => WPCF7_Intel::$dir, // The path to the directory containing file
    'update_file' => 'wpcf7-intel.install', // default [plugin_un].install
  );
  return $info;
}
add_filter('intel_system_info', 'wpcf7_intel_intel_system_info');



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
  $markup .= '<h3>' . esc_html(esc_html__('Tracking', 'wpcf7_intel')) . '</h3>';
  $markup .= '<p>';
  $markup .= esc_html(esc_html__('Intelligence can automate triggering a goal or event upon form submission.', 'wpcf7_intel'));
  $markup .= ' ' . esc_html(esc_html__('Please use the below fields to configure how you want to track form submission in analytics.', 'wpcf7_intel'));
  $markup .= '</p>';
  $form['wpcf7_intel']['tracking_header'] = array(
    '#type' => 'markup',
    '#markup' => $markup,
  );

  // create add goal link
  $id = !empty($_GET['post']) ? $_GET['post'] : '';
  $l_options = array(
    'attributes' => array(
      'class' => array('button', 'intel-add-goal'),
    )
  );
  $l_options = Intel_Df::l_options_add_destination('wp-admin/admin.php?page=wpcf7&action=edit&post=' . $id . '#intel', $l_options);
  $add_goal = Intel_Df::l( '+' . Intel_Df::t('Add goal'), 'admin/config/intel/settings/goal/add', $l_options);

  $form['wpcf7_intel']['inline_wrapper_0'] = array(
    '#type' => 'markup',
    //'#markup' => '<style>.form-item-wpcf7-intel-tracking-event-name {display: inline;} .intel-add-goal-link {display: inline;} </style>',
    '#markup' => '<style>.form-item-wpcf7-intel-track-submission {display: inline-block;} div.intel-display-inline {display: inline-block;}</style><div class="intel-display-inline">',
  );

  $options = intel_get_form_submission_eventgoal_options();
  $form['wpcf7_intel']['track_submission'] = array(
    '#type' => 'select',
    '#title' => esc_html__('Submission event/goal', 'wpcf7_intel'),
    '#options' => $options,
    '#default_value' => !empty($settings['track_submission']) ? $settings['track_submission'] : '',
    '#description' => esc_html__('Select the goal or event you would like to trigger to be tracked in analytics when a form is submitted.', 'wpcf7_intel'),
    '#suffix' => '<div class="intel-display-inline" style="vertical-align: bottom; margin-bottom: 15px;">' . $add_goal . '</div>',
  );
  $form['wpcf7_intel']['inline_wrapper_1'] = array(
    '#type' => 'markup',
    //'#markup' => '<style>.form-item-wpcf7-intel-tracking-event-name {display: inline;} .intel-add-goal-link {display: inline;} </style>',
    '#markup' => '</div>',
  );

  /*
  $form['wpcf7_intel']['inline_wrapper_1'] = array(
    '#type' => 'markup',
    '#markup' => '<div>' . $add_goal . '</div></div>',
  );
  */

  $desc = esc_html__('Each goal has a default site wide value set in the Intelligence goal settings, but you can override that value per form.', 'wpcf7_intel');
  $desc .= esc_html__('If you would like to use a custom goal/event value, enter it here otherwise leave the field blank to use the site defaults.', 'wpcf7_intel');
  $form['wpcf7_intel']['track_submission_value'] = array(
    '#type' => 'textfield',
    '#title' => esc_html__('Submission value', 'wpcf7_intel'),
    '#default_value' => !empty($settings['track_submission_value']) ? $settings['track_submission_value'] : '',
    '#description' => $desc,
    '#size' => 8,
  );

  /*
  $desc = esc_html__('Will trigger a "Form view" event when a page is hit that includes the form.', 'wpcf7_intel');
  //$desc .= esc_html__('If you would like to use a custom goal/event value, enter it here otherwise leave the field blank to use the site defaults.', 'wpcf7_intel');
  $options = intel_get_form_view_options();
  $form['wpcf7_intel']['track_view'] = array(
    '#type' => 'select',
    '#title' => esc_html__('Track form views', 'wpcf7_intel'),
    '#options' => $options,
    '#default_value' => !empty($settings['track_view']) ? $settings['track_view'] : '',
    '#description' => $desc,
  );
  */

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
  $_POST['wpcf7_intel']['track_submission'] = sanitize_text_field($_POST['wpcf7_intel']['track_submission']);

  $_POST['wpcf7_intel']['track_submission_value'] = trim(sanitize_text_field($_POST['wpcf7_intel']['track_submission_value']));
  if ($_POST['wpcf7_intel']['track_submission_value'] !== '') {
    if (!is_numeric($_POST['wpcf7_intel']['track_submission_value'])) {
      $_POST['wpcf7_intel']['track_submission_value'] = '';
    }
    else {
      $_POST['wpcf7_intel']['track_submission_value'] = intval($_POST['wpcf7_intel']['track_submission_value']);
    }
  }

  //$_POST['wpcf7_intel']['track_view'] = sanitize_text_field($_POST['wpcf7_intel']['track_view']);

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

  // if tracking event/value settings are empty, use defaults
  if (empty($settings['track_submission'])) {
    $settings['track_submission'] = get_option('intel_form_track_submission_default', 'form_submission');
  }
  if (!empty($settings['track_submission_value'])) {
    $settings['track_submission_value'] = get_option('intel_form_track_submission_value_default', '');
  }

  if (!empty($settings['track_submission'])) {
    $track['name'] = $settings['track_submission'];
    if (substr($track['name'], -1) == '-') {
      $track['name'] = substr($track['name'], 0, -1);
      $track['valued_event'] = 0;
    }
    if (!empty($settings['track_submission_value'])) {
      $track['value'] = $settings['track_submission_value'];
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



/**
 * Implements hook_intel_form_type_info()
 */
function wpcf7_intel_form_type_info($info = array()) {
  $info['wpcf7'] = array(
    'title' => __( 'Contact Form 7', 'contact-form-7' ),
    'plugin' => array(
      'name' => __( 'Contact Form 7', 'contact-form-7' ),
      'slug' => 'contact-form-7',
      'text_domain' => 'contact-form-7',
    ),
    //'submission_data_callback' => 'wpcf7_intel_form_type_submission_data',
  );
  return $info;
}
// Register hook_intel_form_type_forms_info()
add_filter('intel_form_type_info', 'wpcf7_intel_form_type_info');

/**
 * Implements hook_intel_form_type_FORM_TYPE_UN_form_data()
 */
function wpcf7_intel_form_type_form_info($data = NULL, $options = array()) {
  $info = &Intel_Df::drupal_static( __FUNCTION__, array());
  if (!empty($data) && empty($options['refresh'])) {
    return $data;
  }

  $args = array(
    'post_type'   => 'wpcf7_contact_form'
  );

  $posts = get_posts( $args );

  foreach ($posts as $k => $post) {
    $row = array(
      'settings' => array(),
    );
    $row['id'] = $post->ID;
    $row['title'] = $post->post_title;
    $options = get_option('wpcf7_intel_form_settings_' . $post->ID, array());

    if (!empty($options)) {

      if (!empty($options['track_submission'])) {
        $labels = intel_get_form_submission_eventgoal_options();
        $row['settings']['track_submission'] = $options['track_submission'];
        $row['settings']['track_submission__title'] = !empty($labels[$options['track_submission']]) ? $labels[$options['track_submission']] : $options['track_submission'];
      }

      if (!empty($options['track_submission_value'])) {
        $row['settings']['track_submission_value'] = $options['track_submission_value'];
      }

      $row['settings']['field_map'] = array();
      if (!empty($options['field_map']) && is_array($options['field_map'])) {
        foreach ($options['field_map'] as $k => $v) {
          if (!empty($v)) {
            $row['settings']['field_map'][] = $v;
          }
        }
      }

    }

    $row['settings_url'] = '/wp-admin/admin.php?page=wpcf7&action=edit&post=' . $row['id'] . '#intel';
    $info[$post->ID] = $row;
  }

  return $info;

  foreach ($ninja_forms as $k => $form) {
    $row = array();
    $row['id'] = $form->get_id();
    $row['title'] = $form->get_setting('title', Intel_Df::t('(not set)'));
    $row['settings_url'] = '/wp-admin/admin.php?page=ninja-forms&form_id=' . $row['id'];
    $row['settings'] = array();

    $actions = Ninja_Forms()->form( $row['id'] )->get_actions();

    foreach ($actions as $action) {
      $action_settings = $action->get_settings();

      if ($action_settings['type'] == 'intel') {
        if (!empty($action_settings['intel_track_submission'])) {
          $name = $action_settings['intel_track_submission'];
          $row['settings']['track_submission'] = !empty($intel_eventgoal_options[$name]) ? $intel_eventgoal_options[$name] : $name;
        }
        if (!empty($action_settings['intel_track_submission_value'])) {
          $row['settings']['track_submission_value'] = $action_settings['intel_track_submission_value'];
        }
      }
    }

    $data[$row['id']] = $row;
  }

  return $data;
}
// Register hook_intel_form_type_form_info()
add_filter('intel_form_type_wpcf7_form_info', 'wpcf7_intel_form_type_form_info');

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
      $labels = intel_get_form_submission_eventgoal_options();
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
/*
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
*/