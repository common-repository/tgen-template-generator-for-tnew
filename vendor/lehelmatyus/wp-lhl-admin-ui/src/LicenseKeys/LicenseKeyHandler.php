<?php

namespace WpLHLAdminUi\LicenseKeys;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use WpLHLAdminUi\LicenseKeys\LicenseKeyDataInterface;
use WpLHLAdminUi\LicenseKeys\LicenseKeyResponseDataModel;
use WpLHLAdminUi\LicenseKeys\LicenseKeyResponseModel;
use WP_Error;

class LicenseKeyHandler {

	protected $version;
	protected $plugin_name;
	private $debug = true;

	/**
	 * For Communication with License Server
	 */
	private $host;
	private $consumer_key;
	private $consumer_secret;

	/**
	 * WP Options Table
	 * 
	 * Labels for how license Keys are stored
	 * in options table
	 * general_bundle[license key]
	 * license_bundle[licene_key_date]
	 */
	private $name_for_options_license_bundle;
	private $name_for_license_key;
	private $name_for_license_key_hash;
	private $name_for_license_key_date;

	/**
	 * Stored Values for Autorenew Licenses
	 */
	public $autorenew_date_value = 'auto_renew';
	public $autorenew_date_text = 'Auto Renew Enabled';

	/**
	 * Option Variables
	 */
	protected $license_key_option;

	/**
	 * HTTP Guzzle
	 */
	private $g_client;

	/**
	 * For Creating Purchase Links
	 */
	protected $plugin_purchase_link_url;
	protected $plugin_purchase_link_text;

	public function __construct(
		LicenseKeyDataInterface $data
	) {

		$this->host = trailingslashit($data->get_license_host());
		$this->consumer_key = $data->get_consumer_key();
		$this->consumer_secret = $data->get_consumer_secret();

		$this->name_for_options_license_bundle = $data->get_name_for_options_licensekey_bundle();
		$this->name_for_license_key = $data->get_name_for_license_key();
		$this->name_for_license_key_hash = $data->get_name_for_license_key_hash();
		$this->name_for_license_key_date = $data->get_name_for_license_key_date();

		$this->plugin_purchase_link_url = $data->get_plugin_purchase_link_url();
		$this->plugin_purchase_link_text = $data->get_plugin_purchase_link_text();

		$this->version = $data->get_version();
		$this->plugin_name = $data->get_plugin_name();

		/**
		 * Options
		 */
		$this->license_key_option = get_option($this->name_for_options_license_bundle);

		/**
		 * Guzzle
		 */

		$this->g_client = new Client([
			// Base URI is used with relative requests
			'base_uri' => $this->host,
			// You can set any number of default request options.
			'timeout'  => 5.0,
		]);
	}

	public function get_purchase_link($text = "") {
		$text = $text ?: $this->plugin_purchase_link_text;
		$output = sprintf('<a href="%s" class="%s" target="_blank" >%s</a>', $this->plugin_purchase_link_url, "lhl_amind_ui_link", $text);
		return $output;
	}

	/**
	 * Retrive the saved license Key
	 */
	public function get_license_key() {
		if (empty($this->license_key_option[$this->name_for_license_key])) {
			return false;
		}
		return $this->license_key_option[$this->name_for_license_key];
	}

	/**
	 * Check if license Key is active
	 */
	public function is_active() {

		/**
		 * Needs to have valid key and a valid date or autorenew date
		 */

		if (empty($this->license_key_option[$this->name_for_license_key])) {
			return false;
		}

		if (empty($this->license_key_option[$this->name_for_license_key_date])) {
			return false;
		}

		if ($this->is_auto_renew() && $this->has_valid_key()) {
			return true;
		}

		// If date exits it should be valid format
		if (!$this->is_auto_renew()) {
			if (
				!empty($this->license_key_option[$this->name_for_license_key_date]) &&
				!$this->__validate_date($this->license_key_option[$this->name_for_license_key_date])
			) {
				return false;
			}
		}

		/**
		 * Validate timestamp
		 * see if date is in future
		 */

		$today = date("Y-m-d H:i:s");

		if (($this->license_key_option[$this->name_for_license_key_date]) > $today) {
			return true;
		}

		return false;
	}

	/**
	 * Activate the key
	 */

	public function activate_key($key = '', $expiration_date = '') {

		if (empty($key)) {
			return false;
		}

		if (!empty($expiration_date)) {
		} else {
			$expiration_date = $this->autorenew_date_value;
		}

		$today = date("Y-m-d H:i:s");

		$license_key_option = [];
		$license_key_option[$this->name_for_license_key] = $key;
		$license_key_option[$this->name_for_license_key_date] = $expiration_date;
		$license_key_option[$this->name_for_license_key_hash] = $this->encrypt_license_key($key);

		update_option($this->name_for_options_license_bundle, $license_key_option);

		return true;
	}

	function encrypt_license_key($license_key) {
		$salt = 'this_is_a_salt_that_will_yield_$4_million_for_me'; // Replace this with your own salt
		return hash('sha256', $license_key . $salt);
	}

	public function deactivate_key() {
		$this->flush_key_related_info();
		return true;
	}

	/**
	 * Get support token
	 */

	public function get_support_token() {
		if (empty($this->license_key_option[$this->name_for_license_key])) {
			return false;
		}
		return substr($this->license_key_option[$this->name_for_license_key], 0, 12);
	}
	/**
	 * Flush Key related options
	 */

	public function flush_key_related_info() {
		$this->license_key_option[$this->name_for_license_key] = "";
		$this->license_key_option[$this->name_for_license_key_date] = "";
		update_option($this->name_for_options_license_bundle, $this->license_key_option);
	}

	/**
	 * Get Registered Expiration date
	 */
	public function check_if_a_date_is_in_past($date) {

		if (!($this->__validate_date($date))) {
			return true;
		}

		$today = date("Y-m-d H:i:s");
		if ($today <= $date) {
			return false;
		}

		return true;
	}

	/**
	 * Get License options
	 */
	public function get_license_key_options() {
		return get_option($this->name_for_options_license_bundle);
	}


	/**
	 * Get Registered Expiration date
	 */
	public function get_expiration_date() {
		return $this->license_key_option[$this->name_for_license_key_date];
	}

	/**
	 * Get Resgistered Expiration date pretty format
	 */
	public function get_expiration_date_pretty() {

		if ($this->is_auto_renew()) {
			return $this->autorenew_date_text;
		}

		if (!empty($this->get_expiration_date())) {
			$orgDate = date_create($this->get_expiration_date());
			$newDate = date_format($orgDate, "d M Y");
			return $newDate;
		}

		return false;
	}

	/**
	 * Get Expiration id Day
	 */
	function expiration_in_days() {

		if ($this->is_auto_renew()) {
			return false;
		}

		$differenceFormat = '%a';
		$exp_date = date_create($this->get_expiration_date());
		$today = date_create(date("Y-m-d H:i:s"));

		// simulate date in the future
		// $today = $today->add(new DateInterval('P30D'));

		$interval = date_diff($exp_date, $today);
		$diff_days = $interval->format($differenceFormat);

		if ($exp_date < $today) {
			$diff_days = -1 * $diff_days;
		} else {
			$diff_days = 1 * $diff_days;
		}

		return $diff_days;
	}
	/**
	 * Communicate with server to Activate Key
	 *
	 * This method is used to communicate with the server to activate a license key.
	 *
	 * @param string $key_to_activate The license key to activate.
	 * @param string $action The action to perform. Possible values are 'validate', 'activate', or 'deactivate'.
	 *
	 * @return WP_Error|LicenseKeyResponseModel Returns a WP_Error object if there is an error, or a LicenseKeyResponseModel object if the activation is successful.
	 */

	public function __comm_key_action($key_to_activate, string $action) {
		$actions = ["validate", "activate", "deactivate"];
		if (!in_array($action, $actions)) {
			$error = new WP_Error();
			$error->add("no_such_action", __($this->get_message("no_such_action")), array('status' => 404));
			return $error;
		}

		if (empty($key_to_activate)) {
			$error = new WP_Error();
			$error->add("key_is_empty", __($this->get_message("key_is_empty")), array('status' => 404));
			return $error;
		}

		try {

			$response = $this->g_client->request(
				'GET',
				'/wp-json/lmfwc/v2/licenses/' . $action . '/' . $key_to_activate,
				[
					'query' => [
						'consumer_key' => $this->consumer_key,
						'consumer_secret' => $this->consumer_secret,
					],
					'http_errors' => false // Dont throw error on 404 or 500 https://docs.guzzlephp.org/en/stable/request-options.html#http-errors
				]
			);
		} catch (ConnectException $e) {
			$response = $e->getHandlerContext();
			// error_log(print_r($response, true));
			$error = new WP_Error();
			$error->add("connection_lost", "connection_lost"($response['error']) ?? $this->get_message("connection_lost"), array('status' => 404));
			return $error;
		}

		$response_code = $response->getStatusCode();

		if (
			$response_code == 200 ||
			$response_code == 404 ||
			$response_code == 401
		) {

			$body_content = $response->getBody()->getContents();
			$body_content = json_decode($body_content);

			/**
			 * JSON Error in response
			 */
			if (!(json_last_error() === JSON_ERROR_NONE)) {
				// return new LicenseKeyHandlerError(
				// 	"json_parse_error",
				// 	$this->get_message("json_parse_error"),
				// );
				$error = new WP_Error();
				$error->add("json_parse_error", __($this->get_message("json_parse_error")), array('status' => 404));
				return $error;
			}

			/**
			 * No JSon Error but response is error
			 */
			if (
				$response_code == 404 ||
				$response_code == 401
			) {
				$error = new WP_Error();
				$error->add($body_content->code, __($body_content->message), array('status' => $body_content->data->status));
				return $error;
			}
			/**
			 * It's All Good
			 */
			// error_log(print_r($body_content, true));
			$data = new LicenseKeyResponseDataModel(
				$body_content->data->id,
				$body_content->data->productId,
				$body_content->data->licenseKey,
				$body_content->data->createdAt,
				$body_content->data->createdBy,
				$body_content->data->expiresAt,
				$body_content->data->source,
				$body_content->data->status,
				$body_content->data->timesActivated,
				$body_content->data->timesActivatedMax,
				$body_content->data->updatedAt,
				$body_content->data->updatedBy,
				$body_content->data->userId,
				$body_content->data->validFor
			);

			$LicenseKeyResponse = new LicenseKeyResponseModel($body_content->success, $data);
			// error_log(print_r($LicenseKeyResponse, true));

			return $LicenseKeyResponse;
		}

		/**
		 * Fallback error
		 */
		// error_log(print_r($response, true));
		$error = new WP_Error();
		$error->add("bad_response", __($this->get_message("bad_response")), array('status' => 404));
		return $error;
	}

	/**
	 * Communicate with server to Validate Key
	 */
	public function _comm__validate_key($key_to_activate) {
		return $this->__comm_key_action($key_to_activate, "validate");
	}

	public function _comm__validate_key_CURL($key_to_activate) {

		if (empty($key_to_activate)) {
			return false;
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->host . '/wp-json/lmfwc/v2/licenses/validate/' . $key_to_activate . '?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			// CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return json_encode(array('message' => 'cURL Error #:' . $err));
		} else {
			return $response;
		}
	}


	/**
	 * Communicate with server to Validate Key
	 */
	public function _comm__activate_key($key_to_activate) {
		return $this->__comm_key_action($key_to_activate, "activate");
	}

	/**
	 * Communicate with server to Activate Key
	 */
	public function _comm__activate_key_CURL($key_to_activate) {

		if (empty($key_to_activate)) {
			return false;
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->host . '/wp-json/lmfwc/v2/licenses/activate/' . $key_to_activate . '?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($this->debug) {
			error_log("curl");
			error_log($this->host . '/wp-json/lmfwc/v2/licenses/activate/' . $key_to_activate . '?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret);
			error_log(print_r($response, true));
			error_log(print_r($err, true));
		}

		if ($err) {
			return json_encode(array('message' => 'cURL Error #:' . $err));
		} else {
			return $response;
		}
	}

	/**
	 * Communicate with server to Activate Key
	 */
	public function _comm__deactivate_key($key_to_activate) {
		return $this->__comm_key_action($key_to_activate, "deactivate");
	}

	public function _comm__deactivate_key_CURL($key_to_activate) {

		if (empty($key_to_activate)) {
			return false;
		}

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->host . '/wp-json/lmfwc/v2/licenses/deactivate/' . $key_to_activate . '?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($this->debug) {
			error_log("curl");
			error_log($this->host . '/wp-json/lmfwc/v2/licenses/activate/' . $key_to_activate . '?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret);
			error_log(print_r($response, true));
			error_log(print_r($err, true));
		}

		if ($err) {
			return json_encode(array('message' => 'cURL Error #:' . $err));
		} else {
			return $response;
		}
	}

	public function license__key_expiring_soon_notification() {

		if (!$this->is_active()) {
			return false;
		}

		if ($this->is_auto_renew()) {
			return false;
		}

		$message = "";
		$expiration_in_days = $this->expiration_in_days();

		if ($expiration_in_days < 0) {

			$message .= "<b>";
			$message .= $this->plugin_name . __('license is <b>Expired</b>. ', 'terms-popup-on-user-login');
			$message .= "</b>";
			$message .= $this->get_purchase_link();
		} elseif ($expiration_in_days < 60) {

			$message .= $this->plugin_name . __('license key will expire in ', 'terms-popup-on-user-login');
			$message .= "<b>";
			$message .= $expiration_in_days;
			$message .= __(' days', 'terms-popup-on-user-login');
			$message .= ".</b> ";
			$message .= __('If you don\'t have an active subscription you can ', 'terms-popup-on-user-login');
			$message .= $this->get_purchase_link(__('purchase a license key.', 'terms-popup-on-user-login'));
		}

		if (!empty($message)) {
			$this->display_warning($message, false);
		}
	}

	public function display_warning($message, $is_dismissable = false) {

		$dismissable = $is_dismissable ? "is-dismissible" : "";

		echo "<div class='notice notice-warning " . esc_attr($dismissable) . "' data-dismissible='notice-two-2'>";
		echo '<p>';
		echo '<span class="dashicons dashicons-warning" style="color: #f56e28"></span> ';
		echo $message;
		echo '</p>';
		echo '</div>';
	}


	/**
	 * Return Custom Messages Based onevent
	 */
	public function get_message($code) {

		$message = '';

		switch ($code) {

			case 'activated':
				$message = 'Succesfully activated, be sure to save changes and enjoy your premium features.';
				break;

			case 'activated_already_saved':
				$message = 'Succesfully activated.';
				break;

			case 'deactivated':
				$message = 'Succesfully deactivated, be sure to save changes.';
				break;

			case 'deactivated_already_saved':
				$message = 'Succesfully deactivated.';
				break;

			case 'activated2':
				$message = 'Succesfully activated, be sure to save changes and enjoy your premium features..';
				break;

			case 'no_key_provided':
				$message = 'No key provided.';
				break;

			case 'key_expired':
				$message = 'Key is expired.';
				break;

			case 'key_not_good':
				$message = 'Key provided is not good.';
				break;

			case 'key_is_empty':
				$message = 'Empty Key provided';
				break;

			case 'connection_lost':
				$message = 'Unable to connect to License Server';
				break;

			case 'json_parse_error':
				$message = 'Invalid response from lehelmatyus.com. Please contact support.';
				break;

			case 'empty_response':
				$message = 'Empty response from lehelmatyus.com. Please contact support.';
				break;

			case 'unable_to_activate':
				$message = 'Unable to activate. Please contact support.';
				break;

			case 'unable_to_deactivate':
				$message = 'Unable to deactivate. Please contact support.';
				break;

			case 'no_such_action':
				$message = 'Bad path. No Such Action.';
				break;

			case 'bad_response':
				$message = 'License server not responding';
				break;

			default:
				# code...
				break;
		}

		return $message;
	}

	/**
	 * Check if key is present
	 */
	public function has_valid_key() {
		if (
			!empty($this->license_key_option[$this->name_for_license_key_date])
		) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if date value is autorenew
	 */
	public function is_auto_renew() {
		if ($this->license_key_option[$this->name_for_license_key_date] == $this->autorenew_date_value) {
			return true;
		}
		return false;
	}

	protected function __validate_date($date, $format = 'Y-m-d H:i:s') {
		// Create the format date
		$d = \DateTime::createFromFormat($format, $date);

		// Return the comparison    
		return $d && $d->format($format) === $date;
	}
}
