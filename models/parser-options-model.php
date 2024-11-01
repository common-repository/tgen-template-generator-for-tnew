<?php
class TgenParserOptionsModel {

    public $tgen_clean_body_tag = false;
    public $tgen_remove_html_comments = false;
    public $tgen_remove_inline_script_tags = false;

    public $tgen_teamplate_page_id = '';
    public $tgen_container_wrap = '';
    public $tgen_inline_css = '';
    public $tgen_external_css_url = '';

    public $tgen_header_filter_option = '';
    public $tgen_header_filters = '';


    public $error_code = '';
    public $error_message = '';

    public function __construct() {

        $option = get_option('tgentg_generator_options');
        $filterOptions = get_option('tgentg_cleanupfilter_options');

        $this->tgen_teamplate_page_id = $option['tgen_teamplate_page_id'];

        $this->tgen_clean_body_tag = $option['tgen_clean_body_tag'];
        $this->tgen_remove_html_comments = $option['tgen_remove_html_comments'];
        $this->tgen_remove_inline_script_tags = $option['tgen_remove_inline_script_tags'];

        $this->tgen_container_wrap = $option['tgen_container_wrap'];
        $this->tgen_inline_css = $option['tgen_inline_css'];
        $this->tgen_external_css_url = $option['tgen_external_css_url'];

        $this->tgen_header_filter_option = $filterOptions['tgen_header_filter_option'];
        $this->tgen_header_filters = $filterOptions['tgen_header_filters'];
    }

    /**
     * Getters
     */
    public function getCleanBodyTag() {
        return $this->tgen_clean_body_tag;
    }
    public function getCleanHtmlComments() {
        return $this->tgen_remove_html_comments;
    }
    public function getRemoveInlineScriptTags() {
        return $this->tgen_remove_inline_script_tags;
    }
    public function getExternalCssUrl() {
        return $this->tgen_external_css_url;
    }
    public function getContainerWrap() {
        return $this->tgen_container_wrap;
    }
    public function getInlineCss() {
        return $this->tgen_inline_css;
    }
    public function getTemplatePageId() {
        return $this->tgen_teamplate_page_id;
    }
    public function getHeaderFilterOption() {
        return $this->tgen_header_filter_option;
    }
    public function getHeaderFilters() {
        return $this->tgen_header_filters;
    }
    public function getHeaderFiltersArr() {
        $filter_arr = TgenFilterTagExtractor::extractFilters($this->tgen_header_filters);
        return $filter_arr;
    }


    /**
     * Setters
     */

    public function setCleanBodyTag($tgen_clean_body_tag) {
        $this->tgen_clean_body_tag = $tgen_clean_body_tag;
    }
    public function setCleanHtmlComments($tgen_remove_html_comments) {
        $this->tgen_remove_html_comments = $tgen_remove_html_comments;
    }
    public function setRemoveInlineScriptTags($tgen_remove_inline_script_tags) {
        $this->tgen_remove_inline_script_tags = $tgen_remove_inline_script_tags;
    }
    public function setExternalCssUrl($tgen_external_css_url) {
        $this->tgen_external_css_url = $tgen_external_css_url;
    }
    public function setContainerWrap($tgen_container_wrap) {
        $this->tgen_container_wrap = $tgen_container_wrap;
    }
    public function setInlineCss($tgen_inline_css) {
        $this->tgen_inline_css = $tgen_inline_css;
    }
    public function setTemplatePageId($tgen_teamplate_page_id) {
        $this->tgen_teamplate_page_id = $tgen_teamplate_page_id;
    }
    public function setHeaderFilterOption($tgen_header_filter_option) {
        $this->tgen_header_filter_option = $tgen_header_filter_option;
    }
    public function setHeaderFilters($tgen_header_filters) {
        $this->tgen_header_filters = $tgen_header_filters;
    }

    public function getOptions() {
        return array(
            'tgen_clean_body_tag' => $this->tgen_clean_body_tag,
            'tgen_remove_html_comments' => $this->tgen_remove_html_comments,
            'tgen_remove_inline_script_tags' => $this->tgen_remove_inline_script_tags,
            'tgen_external_css_url' => $this->tgen_external_css_url,
            'tgen_container_wrap' => $this->tgen_container_wrap,
            'tgen_inline_css' => $this->tgen_inline_css,
            'tgen_teamplate_page_id' => $this->tgen_teamplate_page_id
        );
    }


    /**
     * Error message
     */
    public function get_error() {
        return array(
            'code' => $this->error_code,
            'message' => $this->error_message
        );
    }

    public function __validate($options, $request) {
        $this->error_code = 'invalid_options';
        $this->error_message = __("Invalid options.", "tgen-template-generator");

        if (!isset($options['tgen_clean_body_tag'])) {
            return false;
        }

        if (!isset($options['tgen_remove_html_comments'])) {
            return false;
        }

        if (!isset($options['tgen_remove_inline_script_tags'])) {
            return false;
        }

        return true;
    }

    /**
     * Extra
     */

    // public function get_options() {
    //     return array(
    //         'tgen_clean_body_tag' => $this->tgen_clean_body_tag,
    //         'tgen_remove_html_comments' => $this->tgen_remove_html_comments,
    //         'tgen_remove_inline_script_tags' => $this->tgen_remove_inline_script_tags
    //     );
    // }

    // public function set_options($options) {
    //     $this->tgen_clean_body_tag = $options['tgen_clean_body_tag'];
    //     $this->tgen_remove_html_comments = $options['tgen_remove_html_comments'];
    //     $this->tgen_remove_inline_script_tags = $options['tgen_remove_inline_script_tags'];
    // }

    // public function save_options() {
    //     update_option('tgen_tgen_clean_body_tag', $this->tgen_clean_body_tag);
    //     update_option('tgen_tgen_remove_html_comments', $this->tgen_remove_html_comments);
    //     update_option('tgen_tgen_remove_inline_script_tags', $this->tgen_remove_inline_script_tags);
    // }
}
