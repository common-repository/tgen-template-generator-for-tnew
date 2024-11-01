<?php

class TgenContentTagRemoverUtility {
    private $allowed_keywords;

    public function __construct($allowed_keywords) {
        $this->allowed_keywords = $allowed_keywords;
    }

    public function removeTags($html, $selector) {
        if (empty($this->allowed_keywords)) {
            return $html;
        }

        // Use DOMDocument to parse HTML
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Use DOMXPath to query HTML using XPath expressions
        $xpath = new DOMXPath($dom);

        // Find elements based on the CSS selector
        $elements = $xpath->query($selector);

        foreach ($elements as $element) {
            $is_allowed = false;
            foreach ($this->allowed_keywords as $keyword) {
                if (stripos($element->nodeValue, $keyword) !== false) {
                    $is_allowed = true;
                    break;
                }
            }
            if (!$is_allowed) {
                $element->parentNode->removeChild($element);
            }
        }

        // Save the modified HTML
        $html = $dom->saveHTML();

        return $html;
    }
}
