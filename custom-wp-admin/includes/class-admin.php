<?php
/**
 * Plugin class CTLP_CUSTOM_Admin
 *
 * @since   1.0.0
 * @package CTLP_CUSTOM_Admin
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CTLP_CUSTOM_Admin')) :
    final class CTLP_CUSTOM_Admin {
        private static $instance = null;
        private $slug;
        private $page_url;
        private function __construct() {
            /* Nothing here! */
        }

        public function __clone() {
            _doing_it_wrong(__FUNCTION__, __("Please don't hack me!", 'CTLP'), '1.0.1');
        }

        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __("Please don't hack me!", 'CTLP'), '1.0.1');
        }

        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->setup();
                self::$instance->actions();
            }
            return self::$instance;
        }

        private function setup() {
            /* Setup variables */
            $this->data        = new stdClass();
            $this->version     = CTLPWP_ADMIN_CUSTOM_VERSION;
            $this->slug        = CTLPWP_ADMIN_CUSTOM_HIDE_SLUG;
            $this->page_url    = admin_url('admin.php?page=' . $this->slug);
        }

        public function get_slug() {
            return $this->slug;
        }

        public function get_page_url() {
            return $this->page_url;
        }

        public function actions() {
            add_action('plugins_loaded', array($this, 'ctlp_plugins_loaded'), 99999);
            add_action('wp_loaded', array($this, 'ctlp_plugin_loaded'));
            add_filter('site_url', array($this, 'ctlp_site_url'), 10, 4);
            add_filter('wp_redirect', array($this, 'ctlp_wp_redirect'), 10, 2);
            remove_action('template_redirect', 'wp_redirect_admin_locations', 999999);

            add_action('admin_menu', array($this, 'ctlp_hide_login_menu_page'));
            add_action('admin_init', array($this, 'ctlp_settings'));
            add_filter('login_url', array($this, 'ctlp_login_url'), 10, 3);
            add_filter( 'plugin_action_links_' . CTLPWP_ADMIN_HIDE_LOGIN_BASENAME, array( $this, 'ctlp_plugin_action_setting_links' ) );
        }

        private function ctlp_use_trailing_slashes() {
            return ('/' === substr(get_option('permalink_structure'), -1, 1));
        }
    
        private function ctlp_user_trailingslashit($string) {
            return $this->ctlp_use_trailing_slashes() ? trailingslashit($string) : untrailingslashit($string);
        }
    
        private function ctlp_template_loader() {
            global $ctlp_currentPage;
            $ctlp_currentPage = 'index.php';
            if (!defined('WP_USE_THEMES')) {
                define('WP_USE_THEMES', true);
            }
            wp();
            require_once(ABSPATH . WPINC . '/template-loader.php');
            die;
        }
    
        private function ctlp_new_login_slug($blog_id = '') {
            $options = get_option('ctlp_login_form_settings');
            $url = $options['ctlp_url'];
            if ($blog_id) {
                if ($slug = $url) {
                    return $slug;
                }
            } else {
                if ($slug = $url) {
                    return $slug;
                }  else if ($slug = 'admin-modified-custom') {
                    return $slug;
                }
            }
        }
        /**
         * 
         * Redirect to Error page Or specific page
         * 
         **/
        private function ctlp_new_redirect_slug() {
            $options = get_option('ctlp_login_form_settings');
            $url = $options['ctlp_redirection'];
            if ($slug = $url) {
                return $slug;
            } else if ($slug = '404') {
                return $slug;
            }
        }
        /**
         * 
         * New Login URL
         * 
         **/
        public function ctlp_new_login_url($scheme = null) {
            $url = home_url('/');
            if (get_option('permalink_structure')) {
                return $this->ctlp_user_trailingslashit($url . $this->ctlp_new_login_slug());
            } else {
                return $url . '?' . $this->ctlp_new_login_slug();
            }
        }
        /**
         * 
         * Redirect URL
         * 
         **/
        public function ctlp_redirect_url($scheme = null) {
            if (get_option('permalink_structure')) {
                return $this->ctlp_user_trailingslashit(home_url('/', $scheme) . $this->ctlp_new_redirect_slug());
            } else {
                return home_url('/', $scheme) . '?' . $this->ctlp_new_redirect_slug();
            }
        }
        /**
         * 
         * Plugin activation
         * 
         **/
        public static function activate() {
            add_option( 'ctlp_redirect', '1' );
        }
        /**
         * 
         * Register Settings
         * 
         **/
         public function ctlp_settings() {
            add_settings_section('ctlp', __('Form Settings', 'CTLP'), array($this, 'ctlp_options_settings_callback'), 'ctlp');
            $cs_2 = array(
                'ctlp_url' => 'Login url    ',
                'ctlp_redirection'        => 'Redirection url',
            );
            add_settings_field(
                    'ctlp_url',
                    __('Login Url </br> <code>' . trailingslashit( home_url() ) . '</code> ', 'CTLP'),
                    array($this, 'ctlp_options_settings_callback'),
                    'ctlp',
                    'ctlp',
                    array('type' => 'text', 'option_name' => 'ctlp_url', 'label_for' => 'ctlp_url')
                );
            add_settings_field(
                    'ctlp_redirection',
                    __('Redirection Url </br><code>' . trailingslashit( home_url() ) . '</code> ', 'CTLP'),
                    array($this, 'ctlp_options_settings_callback'),
                    'ctlp',
                    'ctlp',
                    array('type' => 'text', 'option_name' => 'ctlp_redirection', 'label_for' => 'ctlp_redirection')
                );
            register_setting('ctlp_login_form_settings', 'ctlp_login_form_settings');
        }
        /**
         * 
         * Menu Pages
         * 
         **/
        public function ctlp_hide_login_menu_page() {
            add_menu_page(
                __('Custom Login Setting', 'CTLP'),
                __('Custom Login Setting', 'CTLP'),
                'manage_options',
                $this->slug,
                false,
                'dashicons-admin-network'
            );
            add_submenu_page(
              $this->slug,
                __('Custom Login Setting', 'CTLP'),
                __('Custom Login Setting', 'CTLP'),
                'manage_options',
                $this->slug,
                array($this, 'ctlp_render_form_setting'),
                'dashicons-admin-network'
            );
             add_submenu_page(
                 $this->slug,
                __('Setting', 'CTLP'),
                __('Setting', 'CTLP'),
                'manage_options',
                  $this->slug . '-settings',
                array($this, 'ctlp_render_form_setting'),
                'dashicons-admin-network'
            );            
        }
        /**
         * 
         * Added Plugin action link next to the Deactivate link
         * 
         **/
        public function ctlp_plugin_action_setting_links( $links ) {
          array_unshift( $links, '<a href="' . admin_url( 'admin.php?page='.$this->slug .'-settings') . '">' . __( 'Settings', 'CTLP' ) . '</a>' );
          return $links; 
        }
        /**
         * 
         * Settings Callback
         * 
         **/
        public function ctlp_options_settings_callback($args) { 
             if ($args['type'] == 'text') { ?>
                <input type="text" name="<?php echo esc_attr('ctlp_login_form_settings'); ?>[<?php echo $args['option_name'] ?>]" class="widefat"
                 value="<?php echo esc_attr(get_option('ctlp_login_form_settings')[$args['option_name']]) ?>" id="<?php echo $args['option_name']; ?>">
            <?php } 
             }
        /**
         * 
         * Plugin Loaded
         * 
         **/
        public function ctlp_plugins_loaded() {
            global $ctlp_currentPage;
            $request = parse_url(rawurldecode($_SERVER['REQUEST_URI']));
            if ((strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-login.php') !== false || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-login', 'relative')) ||  untrailingslashit($request['path']) === site_url('login', 'relative') ) && !is_admin()) {
                $this->wp_login_php = true;
                $_SERVER['REQUEST_URI'] = $this->ctlp_user_trailingslashit('/' . str_repeat('-/', 10));
                $ctlp_currentPage = 'index.php';
            } elseif ((isset($request['path']) && untrailingslashit($request['path']) === home_url($this->ctlp_new_login_slug(), 'relative')) || (!get_option('permalink_structure') && isset($_GET[$this->ctlp_new_login_slug()]) && empty($_GET[$this->ctlp_new_login_slug()]))) {
                $ctlp_currentPage = 'wp-login.php';
            } elseif ((strpos(rawurldecode($_SERVER['REQUEST_URI']), 'wp-register.php') !== false || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-register', 'relative'))) && !is_admin()) {
                $this->wp_login_php = true;
                $_SERVER['REQUEST_URI'] = $this->ctlp_user_trailingslashit('/' . str_repeat('-/', 10));
                $ctlp_currentPage = 'index.php';
            }
        }
        /**
         * 
         * WP Loaded
         * 
         **/
        public function ctlp_plugin_loaded() {
            global $ctlp_currentPage;
            $request = parse_url(rawurldecode($_SERVER['REQUEST_URI']));
            if (!(isset($_GET['action']) && $_GET['action'] === 'postpass' && isset($_POST['post_password']))) {
                if (is_admin() && !is_user_logged_in()  && !defined('DOING_AJAX')) {
                    wp_safe_redirect($this->ctlp_redirect_url());
                    die();
                }
                if ($ctlp_currentPage === 'wp-login.php' && isset($request['path']) && $request['path'] !== $this->ctlp_user_trailingslashit($request['path']) && get_option('permalink_structure')) {
                    wp_safe_redirect($this->ctlp_user_trailingslashit($this->ctlp_new_login_url())
                        . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
                    die;
                } elseif ($this->wp_login_php) {
                  $this->ctlp_template_loader();
                } elseif ($ctlp_currentPage === 'wp-login.php') {
                    global $error, $interim_login, $action, $user_login;
                    $redirect_to = admin_url();
                    $requested_redirect_to = '';
                    if (isset($_REQUEST['redirect_to'])) {
                        $requested_redirect_to = sanitize_url($_REQUEST['redirect_to']);
                    }
                    if (is_user_logged_in()) {
                        $user = wp_get_current_user();
                        if (!isset($_REQUEST['action'])) {
                            wp_safe_redirect($requested_redirect_to);
                            die();
                        }
                    }
                    @require_once ABSPATH . 'wp-login.php';
                    die;
                }
            }
        }
        /**
         * 
         * Site URL
         * 
         **/
        public function ctlp_site_url($url, $path, $scheme, $blog_id) {
            return $this->ctlp_filter_login_php($url, $scheme);
        }
        /**
         * 
         * WP Redirect
         * 
         **/
        public function ctlp_wp_redirect($location, $status) {
            if (strpos($location, 'https://wordpress.com/wp-login.php') !== false) {
                return $location;          
            }
            return $this->ctlp_filter_login_php($location);
        }
        /**
         * 
         * WP Login php
         * 
         **/
        public function ctlp_filter_login_php($url, $scheme = null) {
            if (strpos($url, 'wp-login.php') !== false && strpos(wp_get_referer(), 'wp-login.php') === false) {
                if (is_ssl()) {
                    $scheme = 'https';
                }
                $args = explode('?', $url);
                if (isset($args[1])) {
                    parse_str($args[1], $args);
                    if (isset($args['login'])) {
                        $args['login'] = rawurlencode($args['login']);
                    }
                    $url = add_query_arg($args, $this->ctlp_new_login_url($scheme));
                } else {
                    $url = $this->ctlp_new_login_url($scheme);
                }
            }
            return $url;
        }
        /**
         * 
         * Render URL Settingss
         * 
         **/
        public function ctlp_render_form_setting() { ?>
            <div class="ctlp-wrapper ctlp-formWrapper">
                <div class="ctlp-header">
                </div>
                    <div class="ctlp-content">
                        <div id="ctlp-list" class="ctlp-item">
                            <div class="ctlp-section">
                                <div class="ctlp-wrapper">
                                    <form method="post" action="<?php echo admin_url('options.php'); ?>">
                                        <?php settings_fields('ctlp_login_form_settings'); ?>
                                        <?php do_settings_sections('ctlp'); ?>
                                        <?php submit_button('Save Form'); ?>
                                    </form>
                                </div>
                           </div>
                        </div>
                    </div>
            </div>
        <?php }
        /**
         *
         * Update url redirect : wp-admin/options.php
         *
         */
        public function ctlp_login_url($login_url, $redirect, $force_reauth) {
            if (is_404()) {
                return '#';
            }
            if ($force_reauth === false) {
                return $login_url;
            }
            if (empty($redirect)) {
                return $login_url;
            }
            $redirect = explode('?', $redirect);
            if ($redirect[0] === admin_url('options.php')) {
                $login_url = admin_url();
            }
            return $login_url;
        }
    }
endif;
