<?php


class LHL_Admin_UI_TGEN {


    static $active_license_key_text = "You have an active License Key. This feature is avaialble for you.";
    static $inactive_license_key_text = "This is a Premium Feature. You need a valid license key to activate this feature.";
    static $inactive_license_key_partial_options = "Limited options without an active license key. You need a valid license key.";

    public function __construct() {
    }

    /****************************************************************************************************************************
     * Select dropdown
     ****************************************************************************************************************************/

    /**
     * 
     * Generate a Select box that is only available if license key has been purchased
     *  // Example Usage
     *  // Select Box input
     *      $options = get_option( 'tpul_settings_term_modal_woo_options' );
     *      $select_options_array = [
     *          'anonymous_only' => [
     *              'value' => 'anonymous_only',
     *              'label' => __( 'Anonymous visitors only', 'tgen-template-generator' ),
     *              'with_license_key_only' => false
     *          ],
     *          'anonymous_and_logged_in' => [
     *              'value' => 'anonymous_and_logged_in',
     *              'label' => __( 'Anonymous visitors and logged in users', 'tgen-template-generator' ),
     *              'with_license_key_only' => true
     *          ],
     *          'logged_in_only' => [
     *              'value' => 'logged_in_only',
     *              'label' => __( 'Logged in users only', 'tgen-template-generator' ),
     *              'with_license_key_only' => true
     *          ]
     *      ];
     * 
     * // Render Select box
     *          LHL_Admin_UI::form_select__active_key_required(
     *              $this->license_key_valid,
     *              $options,
     *              'tpul_settings_term_modal_woo_options',
     *              'terms_modal_woo_display_user_type',
     *              $select_options_array,
     *              true
     *          );
     */
    public static function select__active_key_required($license_key_valid, array $options, string $option_name, string $options_id, array $select_options_array, $sub_options_disabled_only = false) {

        if ($license_key_valid) {
            echo '<div class="tpul__alowed_box">';
            echo '<p class="tpul__license_notify_text">' . esc_html(self::$active_license_key_text) . '</p>';
        } else {
            echo '<div class="tpul__restricted_box">';
            if ($sub_options_disabled_only) {
                echo '<p class="tpul__license_notify_text">' . esc_html(self::$inactive_license_key_partial_options) . '</p>';
            } else {
                echo '<p class="tpul__license_notify_text">' . esc_html(self::$inactive_license_key_text) . '</p>';
            }
        }
        echo '<br/>';

        // only the options inside the select are disabled for non active license holders
        if ($sub_options_disabled_only) {
            $is_select_disabled = false;
        } else {
            $is_select_disabled = !$license_key_valid;
        }

        self::select($options, $option_name, $options_id, $select_options_array, $is_select_disabled, $license_key_valid);

        echo '</div>';
    }

    /**
     * Generate a select from an array
     * 
     * // Example Usage
     *  // Select Box input
     *      $options = get_option( 'tpul_settings_term_modal_woo_options' );
     * 
     *      $select_options_array = [
     *          'anonymous_only' => [
     *              'value' => 'anonymous_only',
     *              'label' => __( 'Anonymous visitors only', 'tgen-template-generator' ),
     *          ],
     *          'anonymous_and_logged_in' => [
     *              'value' => 'anonymous_and_logged_in',
     *              'label' => __( 'Anonymous visitors and logged in users', 'tgen-template-generator' ),
     *          ],
     *          'logged_in_only' => [
     *              'value' => 'logged_in_only',
     *              'label' => __( 'Logged in users only', 'tgen-template-generator' ),
     *          ]
     *      ];
     * 
     * // Render Select box
     *          LHL_Admin_UI::form_select(
     *              $this->license_key_valid,
     *              $options,
     *              'tpul_settings_term_modal_woo_options',
     *              'terms_modal_woo_display_user_type',
     *              $select_options_array,
     *              true
     *          );
     */
    public static function select(array $options, string $option_name, string $options_id, array $select_options_array, bool $is_disabled = false, bool $license_key_valid = false) {
        $selectbox_name = "{$option_name}[{$options_id}]";
        $disabled_attribute = $is_disabled ? " disabled='disabled'" : "";

        echo "<select name='" . esc_attr($selectbox_name) . "'" . esc_attr($disabled_attribute) . ">";
        foreach ($select_options_array as $option_item) {

            $is_disabled_sub_option = "";
            if (!empty($option_item['with_license_key_only'])) {
                $is_disabled_sub_option = ($option_item['with_license_key_only'] && !$license_key_valid) ? "disabled" : "";
            }

            echo '<option value="' . esc_attr($option_item['value']) . '" ' . esc_attr(selected($option_item['value'], $options[$options_id])) . " {esc_attr($is_disabled_sub_option)}>" . esc_attr($option_item['label']) . '</option>';
        }
        echo "</select>";
    }


    /****************************************************************************************************************************
     * Text Inmput
     ****************************************************************************************************************************/

    /**
     * Example usage
     *  $options = get_option( 'tgentg_generator_options' );
     *	$option_name = "tgentg_generator_options";
     *	$option_id = "tgen_content_region_selector";
     *
     *	LHL_Admin_UI_TGEN::form_text_input__active_key_required(
     *      true,
     *		$options,
     *		$option_name,
     *		$option_id
     *	);
     */
    public static function text_input__active_key_required($license_key_valid, array $options, string $option_name, string $options_id, bool $is_disabled = false) {

        if ($license_key_valid) {
            echo '<div class="tpul__alowed_box">';
            echo '<p class="tpul__license_notify_text">' . esc_html(self::$active_license_key_text) . '</p>';
        } else {
            echo '<div class="tpul__restricted_box">';
            echo '<p class="tpul__license_notify_text">' . esc_html(self::$inactive_license_key_text) . '</p>';
        }
        echo '<br/>';

        $is_disabled = !$license_key_valid;
        self::text_input($options, $option_name, $options_id, $is_disabled);

        echo '</div>';
    }

    /**
     * Example usage
     *  $options = get_option( 'tgentg_generator_options' );
     *	$option_name = "tgentg_generator_options";
     *	$option_id = "tgen_content_region_selector";
     *
     *	LHL_Admin_UI_TGEN::admin_text_input(
     *		$options,
     *		$option_name,
     *		$option_id
     *	);
     */
    public static function text_input(array $options, string $option_name, string $options_id, bool $is_disabled = false) {

        $input_name = "{$option_name}[{$options_id}]";
        $disabled_attribute = $is_disabled ? " disabled='disabled'" : "";
        $value = (isset($options[$options_id])) ? $options[$options_id] : '';

        echo '<input type="text" name="' . esc_attr($input_name) . '" value="' . esc_attr($value) . '" class="regular-text"' . esc_attr($disabled_attribute) . '>';
    }


    /****************************************************************************************************************************
     * Textarea
     ****************************************************************************************************************************/

    public static function textarea(array $options, string $option_name, string $options_id, bool $is_disabled = false, $rows = 15, $cols = 100) {

        $input_name = "{$option_name}[{$options_id}]";
        $disabled_attribute = $is_disabled ? " disabled='disabled'" : "";
        $value = (isset($options[$options_id])) ? $options[$options_id] : '';

        // Render the output
        echo '<textarea id="' . esc_attr($options_id) . '" name="' . esc_attr($input_name) . '" rows="' . esc_attr($rows) . '" cols="' . esc_attr($cols) . '"' . esc_attr($disabled_attribute) . '>' . esc_html($value) . '</textarea>';
    }

    /****************************************************************************************************************************
     * Cehckbox
     ****************************************************************************************************************************/

    public static function checkbox_single(array $options, string $option_name, string $options_id, $label, bool $is_disabled = false) {

        $input_name = "{$option_name}[{$options_id}]";
        $disabled_attribute = $is_disabled ? " disabled='disabled'" : "";
        $value = (isset($options[$options_id])) ? $options[$options_id] : 0;

        echo '<input type="checkbox" id="' . esc_attr($options_id) . '" name="' . esc_attr($input_name) . '" value="1"' . esc_attr(checked(1, $value, false)) .  esc_attr($disabled_attribute) . '/>';
        echo '<label for="' . esc_attr($options_id) . '">' . esc_html($label) . '</label>';
    }

    /****************************************************************************************************************************
     * Button
     ****************************************************************************************************************************/

    /**
     * Generate a Button
     * that has a message field next to it to display message responsens returned by ajax
     */
    public static function button($classes = [], $title = "Empty", $id = '', $repsoneHTML = '') {

        $output = "";
        $attr = [];
        $def_classes = [
            'button',
            'button-default'
        ];

        /**
         * Classes
         * Merge incoming classes with default classes
         */

        if (is_array($classes)) {
            // sanitize if array
            $classes = array_map(function ($a) {
                return sanitize_html_class($a);
            }, $classes);
            // merge with defaults
            $classes = array_merge($classes, $def_classes);
            $classes_string = join(' ', $classes);
        } elseif (is_string($classes)) {
            // as is string
            $classes_string = join(' ', $classes);
            $classes_string = $classes_string . " " . $classes;
        }
        $classes_string = join(' ', $classes);


        /**
         * Add Clicke Event
         */
        // if(!empty($eventname)){
        //     $evet = $eventname . "(event)";
        //     $attr[] = 'onclick="' . $evet . '"';
        // }

        /**
         * Get
         */
        $attr_string = join(' ', $attr);

        echo "<div class='lhl-admin-button__container " . esc_attr($classes[0]) . "__container'>";
        echo sprintf('<button id="%s" class="%s" %s>%s</button>', esc_attr($id), esc_attr($classes_string), esc_attr($attr_string), esc_attr($title));
        echo "<span class='lhl-admin-button__message " . esc_attr($classes[0]) . "__message'>";
        echo "</span>";
        echo "<div class='lhl-admin-button__response " . esc_attr($classes[0]) . "__response empty'>";


        echo "<p>";
        echo __('Direct your TNEW website to grab the Template file from here:', 'tgen-template-generator');
        echo "</p>";

        echo "<p class='lhl_p'>";
        echo "<input type='text' name='' value='' class='regular-text tgentg_generate_button__template' readonly>";
        echo "</p>";

        echo "<p>";
        echo __('Check out the generated template:', 'tgen-template-generator');
        echo "</p>";

        echo "<p>";
        echo "<a href='#' target='_blank' class='tgentg_generate_button__preview'>";
        echo __('Preview Template Here', 'tgen-template-generator');
        echo "</a>";
        echo "</p>";


        echo "</div>";
        echo "</div>";
    }
}
