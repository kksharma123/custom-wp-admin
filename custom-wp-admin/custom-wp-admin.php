<?php
/**
 * Plugin Name: Custom wp-admin / wp-login.php
 * Plugin URI: 
 * Description: Modify wp-admin and wp-login URL or create new slug to login into the admin dashboard .
 * Version: 1.0.0
 * Author: Krishan Kant Sharma
 * Author URI: 
 * Text Domain: CTLP
 * 
 * @package  Hide/Custom wp-admin / wp-login.php
 * @category Plugin
 * @author   Krishan Kant Sharma 
 * @version  1.0.0
 */

if (!defined('ABSPATH')) {
	exit;
}
/* Constant version */
define('CTLPWP_ADMIN_CUSTOM_VERSION', '1.0.0');
/* Constant slug */
define('CTLPWP_ADMIN_CUSTOM_HIDE_SLUG', basename(plugin_dir_path(__FILE__)));
/* Constant path to the main file for activation call */
define('CTLPWP_ADMIN_CORE_FILE', __FILE__);
/* Constant path to plugin directory */
define('CTLPWP_ADMIN_CUSTOM_PATH', trailingslashit(plugin_dir_path(__FILE__)));
/* Constant uri to plugin directory */
define('CTLPWP_ADMIN_CUSTOM_URI', trailingslashit(plugin_dir_url(__FILE__)));

define( 'CTLPWP_ADMIN_HIDE_LOGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once(CTLPWP_ADMIN_CUSTOM_PATH . 'includes/class-admin.php');
/* Initialization */
if (!function_exists('CTLP_CUSTOM_init')) {
	function CTLP_CUSTOM_init() {
		return CTLP_CUSTOM_Admin::instance();
	}
}
CTLP_CUSTOM_init();
