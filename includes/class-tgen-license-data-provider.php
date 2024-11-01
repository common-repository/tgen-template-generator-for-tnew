<?php

use WpLHLAdminUi\LicenseKeys\LicenseKeyDataInterface;

class TGEN_LicenseKeyDataProvider implements LicenseKeyDataInterface {

    public function __construct() {
    }

    /**
     * Host URL where the licanse needs to be verifies
     */
    public function get_license_host() {
        return 'https://www.lehelmatyus.com';
    }
    /**
     * Consumer Key for API call
     * to where the license can be verfied
     */
    public function get_consumer_key() {
        return 'ck_019c9a0410beebb0a16b851bbe1cd4def1c1715d';
    }
    /**
     * Consumer Key for Api call
     * To where the licsense can be verfied
     */
    public function get_consumer_secret() {
        return 'cs_6f151c5a6ef6d2251213d42ccc3c1a3fcb364b4d';
    }

    /**
     * Name of the option where license key is stored
     */
    public function get_name_for_options_licensekey_bundle() {
        return 'tgen_license_key_bundle';
    }
    /**
     * Name of array key in the option where license key is stored
     */
    public function get_name_for_license_key() {
        return 'tgen_license_key';
    }
    /**
     * Name of array key in the option where license key hash is stored
     */
    public function get_name_for_license_key_hash() {
        return 'tgen_license_key_hash';
    }
    /**
     * Name of array key in the option where license date is stored
     */
    public function get_name_for_license_key_date() {
        return 'tgen_license_key_valid_until';
    }
    /**
     * Url where the license can be purchased
     */
    public function get_plugin_purchase_link_url() {
        return 'https://lehelmatyus.com/tgen-template-generator-for-tnew';
    }
    /**
     * Link text for Link where the license can be purchased
     */
    public function get_plugin_purchase_link_text() {
        return 'Purchase a license key';
    }

    /**
     * NAme of the plugin
     */
    public function get_plugin_name() {
        return 'Tgen Template Generator for TNEW';
    }
    /**
     * Version of the plugin
     */
    public function get_version() {
        return '1';
    }
}
