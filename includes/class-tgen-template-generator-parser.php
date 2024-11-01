<?php

defined('ABSPATH') || exit;

use voku\helper\HtmlDomParser;
use PrettyXml\Formatter;
use Smush\Core\Modules\Helpers\Parser;

// use pear\Net_URL2;
require_once __DIR__ . '/../vendor/pear/net_url2/Net/URL2.php';

// Use composer Packages
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.lehelmatyus.com
 * @since      1.0.0
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tgen_Template_Generator
 * @subpackage Tgen_Template_Generator/public
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Tgen_Template_Generator_Parser {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;
    private $url;
    private TgenParserOptionsModel $parserOptionsModel;

    private $config = array(
        'tgen_container_wrap' => 0,
        'tgen_inline_css' => '',
        'tgen_content_region_selector' => '#TNEW-container',
    );

    private $tgen_content_region_selector = '#TNEW-container';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($version, $url, $parserOptionsModel) {

        $this->version = $version;
        $this->url = $url;
        $this->parserOptionsModel = $parserOptionsModel;

        /**
         * Load configs from Plugin
         */
        $this->config = $this->getConfigs();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */

    public function parse() {

        $generated_file_urls = [];

        $url = $this->url;
        // $url = 'http://www.minimalsites.com/';
        // $url = 'http://nww1mm.test/';
        // $url = 'https://dev-nww1mm.pantheonsite.io/explore/museum-and-memorial';
        // $url = 'https://www.theworldwar.org/';

        if (!$this->url_check($url)) {
            return false;
        }

        /**
         * Initialize document and load in html
         */
        $doc = new DomDocument;
        libxml_use_internal_errors(true);
        $doc->validateOnParse = true;
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $doc->loadHtml(file_get_contents($url));

        /**
         * Add Inline Styles
         */
        $head = $doc->getElementsByTagName('head')->item(0);

        if ($this->config['tgen_inline_css']) {
            $style = $doc->createElement('style', $this->config['tgen_inline_css']);

            if (!empty($head)) {
                $head->appendChild($style);
            }
        }

        /**
         * Add External link to style URL
         */
        if (!empty($this->parserOptionsModel->getExternalCssUrl())) {
            $external_filename = $this->config['tgen_external_css_url'];
            $external_link = $doc->createElement('link');
            $external_link->setAttribute('rel', 'stylesheet');
            $external_link->setAttribute('media', 'all');
            $external_link->setAttribute('href', $external_filename);

            if (!empty($head)) {
                $head->appendChild($external_link);
            }
        }


        /**
         * Remove Unused Comments
         */
        if (!empty($this->parserOptionsModel->getCleanHtmlComments())) {
            $doc = $this->removeDomNodes($doc, '//comment()');
        }

        /**
         * Cleanup Filters
         */

        /**
         * Remove Unused Comments
         */

        if (!empty($this->parserOptionsModel->getHeaderFilterOption())) {
            $headrFilterOption = $this->parserOptionsModel->getHeaderFilterOption();

            if ($headrFilterOption == 'do_not_use') {
            }
            if ($headrFilterOption == 'remove_filters_only') {
                $keep_filters_only = false;
                $this->useCleanupFilters($doc, $keep_filters_only);
            }
            if ($headrFilterOption == 'keep_filters_only') {
                $keep_filters_only = true;
                $this->useCleanupFilters($doc, $keep_filters_only);
            }
        }

        /**
         * Clean Body classes
         */


        /**
         * Swap Element with placeholder
         */

        // Create TNEW comment placeholder
        $placeholder_comment = $doc->createComment("TNEW CONTENT HERE");


        /**
         * Get Content Area
         */
        if ($this->tgen_content_region_selector[0] == "#") {
            // is ID selector
            // substring in use since # in front of it is not needed
            $content_area = $doc->getElementById(substr($this->tgen_content_region_selector, 1));
        }
        if ($this->tgen_content_region_selector[0] == ".") {
            // is Class selector
            // substring in use since . in front of it is not needed

            // $content_area = $doc->getElementsByClassName(substr($this->tgen_content_region_selector, 1));
            $finder = new DomXPath($doc);
            $classname = substr($this->tgen_content_region_selector, 1);
            error_log($classname);

            $content_area = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            $content_area = $content_area[0];
        }

        /**
         * Replace Content Area with 
         */
        if (!empty($content_area)) {

            // Create new div with class TNEW-container
            $content_area_new = $doc->createElement('div');
            $content_area_new->setAttribute('id', "TNEW-container"); //TNEW-container

            // Add container Wrap if needed
            if (!empty($this->config['tgen_container_wrap'])) {
                $content_area_new->setAttribute('class', "container");
            }

            // Swap old content container with new TNEW-container container 
            $content_area_new->appendChild($placeholder_comment);
            $content_area->parentNode->replaceChild($content_area_new, $content_area);
        }


        /**
         * Clean the body tag from class namess
         */
        if (!empty($this->parserOptionsModel->getCleanBodyTag())) {
            // $body = $doc->getElementsByTagName('body')->item(0);
            // $body->removeAttribute('class');

            $content_body = $doc->getElementsByTagName('body');
            foreach ($content_body as $key => $body) {
                $body->removeAttribute('class');
            }
        }


        // Removes html <element>
        // Remove <script> tags for cleanup
        if (!empty($this->parserOptionsModel->getRemoveInlineScriptTags())) {
            $doc = $this->removeElementsByTagNames($doc, array('script'));
        }


        /**
         * Add
         */
        // $root = $doc->createElement('book');
        // $root = $doc->appendChild($root);

        /**
         * Pretty Formatting
         */

        $dom_string = $doc->saveHTML();
        $formatter = new Formatter();
        $dom_string = $formatter->format($dom_string);
        $dom_string = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $dom_string);
        $doc = new DomDocument;
        $doc->loadHtml($dom_string);

        /**
         * Save file
         */
        // Check for folder or create folder
        $uploads  = wp_upload_dir(null, false);
        $logs_dir = $uploads['basedir'] . '/tgen-temp-gen';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }

        // check for folder or create folder
        $logs_dir_temp = $logs_dir . '/temp';
        if (!is_dir($logs_dir_temp)) {
            mkdir($logs_dir_temp, 0755, true);
        }

        /**
         * Write Temp file
         * Temp file is then consumed again by the other library
         */
        $temp_file_path = $logs_dir_temp . "/temp_tpl.html";
        $file_written = $doc->saveHTMLFile($temp_file_path);


        /**********************************************
         * Use the other Library to finish the file
         * ********************************************
         */


        /**
         * Consume temp file and use as input for HtmlDomParser
         * a different library
         */
        /**
         * Create new file with Absolute URLs
         * then save template file ans well as demo file
         */
        $file_path_output = $logs_dir . "/index.html";
        $generated_file_urls = $this->generate_absolute_url_tpl_and_save_files($url, $temp_file_path, $file_path_output);


        libxml_clear_errors();
        return $generated_file_urls;
    }


    public function getConfigs() {


        $options = get_option('tgentg_generator_options');

        if (!empty($options['tgen_inline_css'])) {
            $options['tgen_inline_css'] = wp_filter_nohtml_kses($options['tgen_inline_css']);
        }
        if (!empty($options['tgen_container_wrap'])) {
            $options['tgen_container_wrap'] = $options['tgen_container_wrap'];
        }

        if (!empty($options['tgen_content_region_selector'])) {
            $options['tgen_content_region_selector'] = wp_filter_nohtml_kses($options['tgen_content_region_selector']);
            $this->tgen_content_region_selector = $options['tgen_content_region_selector'];
        }

        return $options;
    }


    /**
     * Removes <element> from DOM
     */
    function removeElementsByTagNames($doc, $element_types) {

        $all_elements_by_type = [];
        foreach ($element_types as $key => $element_type_name) {
            $all_elements_by_type[$key] = $doc->getElementsByTagName($element_type_name);
        }

        /**
         * Remove These
         */
        $remove = [];

        // Collect all
        foreach ($all_elements_by_type as $key => $elementType) {
            foreach ($elementType as $item) {
                $remove[] = $item;
            }
        }

        // Remove
        foreach ($remove as $item) {
            $item->parentNode->removeChild($item);
        }

        return $doc;
    }

    function removeDomNodes($dom, $xpathString) {
        $xpath = new DOMXPath($dom);
        while ($node = $xpath->query($xpathString)->item(0)) {
            $node->parentNode->removeChild($node);
        }
        return $dom;
    }

    function useCleanupFilters($dom, $keep_filters_only = false) {

        $filters = $this->parserOptionsModel->getHeaderFiltersArr();
        $headTagRemover = new TgenHeadTagRemoverUtility();

        if ($keep_filters_only == false) {
            // remove filters only
            foreach ($filters as $key => $filter) {
                $headTagRemover->removeTags($dom, $filter->getTag(), [$filter->getSearchString()]);
            }
        } else {
            // keep filters only
            $headTagRemover->removeAllTagsExceptfilters($dom, $filters);
        }
    }

    /**
     * Creates a new file with URL's turned into absolute urls
     */
    function generate_absolute_url_tpl_and_save_files($base_url, $source_path, $destination_path) {

        $generated_file_urls = [];

        $uri = new Net_URL2($base_url);
        $baseURI = $uri;
        $site_html = HtmlDomParser::file_get_html($source_path);

        /**
         * Resolve URL
         */
        foreach ($site_html->find('base[href]') as $elem) {
            $baseURI = $uri->resolve($elem->href);
        }

        foreach ($site_html->find('*[src]') as $elem) {
            $elem->src = $baseURI->resolve($elem->src)->__toString();
        }
        foreach ($site_html->find('*[href]') as $elem) {
            if (strtoupper($elem->tag) === 'BASE') continue;
            $elem->href = $baseURI->resolve($elem->href)->__toString();
        }
        foreach ($site_html->find('form[action]') as $elem) {
            $elem->action = $baseURI->resolve($elem->action)->__toString();
        }

        $site_html->save($destination_path);

        /************************************************
         * Also save it as a WP Page template file
         ************************************************/

        $theme_page_template_path = get_stylesheet_directory() . '/page-templates/page-tgen-template.php';
        /**
         * Add a commet to the php file like so
         *  * Template Name: TGEN Template
         */
        // Get the HTML content
        $html_content = $site_html->innertext;
        // Add the PHP template comment at the beginning
        $php_content = "<?php /* Template Name: TGEN Template */ ?>\n" . $html_content;
        // Write the content to a PHP file
        file_put_contents($theme_page_template_path, $php_content);

        /***********************************************
         *  END of php template file
         ***********************************************/


        $generated_file_urls['template'] = $destination_path;


        /**
         * Create a Demo html
         * use the template but add some content 
         * for the user to see where the content will be displayed eventually on TNEW
         */

        // By now the content wrapper has been swapped with "TNEW-container"
        // We just add placeholder for demo
        $tgen_content_wrapper = $site_html->findOne("#TNEW-container");
        $tgen_content_wrapper->innertext .= '
            <div style="border:2px solid rgb(0, 116, 204); padding: 100px; min-height: 500px; background: rgb(0, 116, 204, 0.2); margin: 10px;border-radius: 5px;">
                <h1>
                ' .
            __('&lt; TNEW Placehoder &gt;', 'tgen-template-generator')
            . '
                </h1>
                <p>
                ' .
            __('TNEW content will be placed here.', 'tgen-template-generator')
            . '
                </p>
                <p>
                ' .
            __('This is the DEMO page use the other url for TNEW template.', 'tgen-template-generator')
            . '
                </p>
            </div>
        ';
        // error_log($main);

        $site_html->save($destination_path . "-demo.html");
        $generated_file_urls['demo'] = $destination_path;


        return $generated_file_urls;
    }

    /**
     * Checks if valid url
     */
    function url_check($url) {
        $headers = @get_headers($url);
        return is_array($headers) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]) : false;
    }
}
