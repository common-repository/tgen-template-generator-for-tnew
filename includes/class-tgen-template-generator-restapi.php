<?php

defined('ABSPATH') || exit;

use WpLHLAdminUi\LicenseKeys\LicenseKeyHandler;

/**
 * Extend the main WP_REST_Posts_Controller to a private endpoint controller.
 */

class TGEN_template_Generator_Rest_API extends WP_REST_Posts_Controller {

    /**
     * The namespace.
     *
     * @var string
     */
    protected $namespace = 'tgen-template-generator/v1';

    /**
     * Rest base for the current object.
     *
     * @var string
     */
    protected $rest_base = 'action';
    protected $crawl_url = '';

    protected $error_code = "def_err_code";
    protected $error_message = "Err Message: No options set.";

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
    }


    /**
     * Register the routes for the objects of the controller.
     *
     * Nearly the same as WP_REST_Posts_Controller::register_routes(), but all of these
     * endpoints are hidden from the index.
     */
    public function register_routes() {

        /* Generate
         * wp-json/tgen-template-generator/v1/action/generate
         */
        register_rest_route($this->namespace, '/' . $this->rest_base . '/generate', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'generate_template'),
                'permission_callback' => array($this, 'generate_template_permission_check'),
                'show_in_index'       => false,
            ),
        ));
        /* Activate Key
         * wp-json/tgen-template-generator/v1/action/activatekey
         */
        register_rest_route($this->namespace, '/' . $this->rest_base . '/activatekey', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'activate_key'),
                'permission_callback' => array($this, 'activate_key_permission_check'),
                'show_in_index'       => false,
            ),
        ));
        /* Activate Key
         * wp-json/tgen-template-generator/v1/action/deactivatekey
         */
        register_rest_route($this->namespace, '/' . $this->rest_base . '/deactivatekey', array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'deactivate_key'),
                'permission_callback' => array($this, 'deactivate_key_permission_check'),
                'show_in_index'       => false,
            ),
        ));
    }


    /**
     * Activate Key
     */
    public function activate_key($request) {
        $response = array();
        $error = new WP_Error();
        $parameters = $request->get_json_params();
        $key_handler = new LicenseKeyHandler(new TGEN_LicenseKeyDataProvider());

        /**
         * Check License Server
         */
        $_com_response = $key_handler->_comm__activate_key($parameters["license_key"]);
        if (is_wp_error($_com_response)) {
            return $_com_response;
        }
        if ($_com_response->getSuccess() == false) {
            $error->add("unable_to_activate", __($key_handler->get_message('unable_to_activate')), array('status' => 404));
            $key_handler->flush_key_related_info();
            return $error;
        }

        /**
         * Save License Key
         */
        $key_handler->activate_key(
            $_com_response->getData()->getLicenseKey(),
            $_com_response->getData()->getExpiresAt()
        );

        $response['_com_response'] = $_com_response;
        $response['code'] = "key_activated";
        $response['message'] = __($key_handler->get_message('activated_already_saved'));
        return new WP_REST_Response($response, 200);
    }

    public function activate_key_permission_check($request) {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }
    /**
     * DeActivate Key
     */
    public function deactivate_key($request) {
        error_log("deactivate_key");
        $response = array();
        $error = new WP_Error();
        $parameters = $request->get_json_params();
        $key_handler = new LicenseKeyHandler(new TGEN_LicenseKeyDataProvider());

        /**
         * Check License Server
         */
        $_com_response = $key_handler->_comm__deactivate_key("THIS_IS_TGEN_LICENSE_MONEY");
        if (is_wp_error($_com_response)) {
            return $_com_response;
        }

        if ($_com_response->getSuccess() == false) {
            $error->add("unable_to_deactivate", __($key_handler->get_message('unable_to_deactivate')), array('status' => 404));
            $key_handler->flush_key_related_info();
            return $error;
        }

        /**
         * Save License Key
         */
        $key_handler->deactivate_key();

        // $response['_com_response'] = $_com_response;
        $response['code'] = "key_deactivated";
        $response['message'] = __($key_handler->get_message('deactivated_already_saved'));
        return new WP_REST_Response($response, 200);
    }
    public function deactivate_key_permission_check($request) {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }

    /**
     * Generate Template
     */

    public function generate_template_permission_check($request) {
        if (current_user_can('manage_options')) {
            return true;
        }
        return false;
    }

    public function generate_template($request) {



        $response = array();
        $error = new WP_Error();

        $response['code'] = "generate_template";
        $response['message'] = __("Generating tempalte.", "tgen-template-generator");
        $response['data'] = array();

        $options = get_option('tgentg_generator_options');

        /**
         * Validate
         */
        if (!$this->__validate($options, $request)) {
            $error->add($this->error_code, $this->error_message, array('status' => 401));
            return $error;
        }

        /**
         * Parse
         */
        $parseOptions = new TgenParserOptionsModel();
        $plugin_parser = new Tgen_Template_Generator_Parser(1, $this->crawl_url, $parseOptions);
        $parse_responce = $plugin_parser->parse();


        /**
         * Build Response
         */
        $response['data']['crawl_url'] = $this->crawl_url;
        $response['data']['parse_response'] = $parse_responce;
        $site_url = get_site_url();

        $upload_dir = wp_upload_dir();
        $response['data']['urls']['template'] = $upload_dir["baseurl"] . "/tgen-temp-gen/";
        $response['data']['urls']['demo'] = $upload_dir["baseurl"] . "/tgen-temp-gen/index.html-demo.html";

        /**
         * Responce
         */
        return new WP_REST_Response($response, 200);
    }

    private function __validate($options, $request) {

        $template_page_id = $options['tgen_teamplate_page_id'];

        /**
         * 1. Check if user is not logged in
         */
        // $user_id = get_current_user_id();
        $user  = wp_get_current_user();
        $user_id   = (int) $user->ID;
        // $user_id = $user->ID;
        if ($user_id == 0) {
            $this->error_code = "no_such_user";
            $this->error_message = __('No such user code: 0', 'tgen-template-generator');
            return 0;
        }

        /**
         * 2. Check if nonce is bad
         */
        if (rest_cookie_check_errors($request)) {
            // Nonce is correct!
            // $response['data'] = array('nonce'=> rest_cookie_check_errors($request));
        } else {
            $this->error_code = "no_such_user";
            $this->error_message = __('No such user code: 1', 'tgen-template-generator');
            return 0;
        }


        /**
         * Check if mandaroy fields are good
         */
        if (empty($template_page_id)) {
            $this->error_code = "missing_tgen_teamplate_page_id";
            $this->error_message = __('Template Source Page was not set', 'tgen-template-generator');
            return 0;
        }


        // Get URL for the page that was set as Template source
        $url = get_permalink($template_page_id);
        if ($url == false) {
            $this->error_code = "bad_tgen_teamplate_page_id";
            $this->error_message = __('Non Existent Source Page', 'tgen-template-generator');
            return 0;
        }

        /**
         * Check if page is public
         */

        if (get_post_status($template_page_id) !== 'publish') {
            $this->error_code = "tgen_teamplate_page_id_not_public";
            $this->error_message = __('Template Source Page Not Public', 'tgen-template-generator');
            return 0;
        }
        $this->crawl_url = $url;


        return 1;
    }
}
