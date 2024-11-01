<?php

// Define the TGEN_HeadTagRemover class
class TgenHeadTagRemoverUtility {
    private $allowed_head_tags = ['title', 'meta', 'link', 'style', 'script', 'base'];
    private $debug = false;

    public function __construct() {
    }

    public function removeTags(DOMDocument $dom, $tag, $keywords) {

        // Check if allowed keywords are empty or if the tag is not in the allowed head tags
        if (empty($keywords) || !in_array($tag, $this->allowed_head_tags)) {
            return $dom;
        }

        // Find all specified tags
        $tags = $dom->getElementsByTagName($tag);

        foreach ($tags as $tag_element) {
            $is_in_filters = false;
            foreach ($keywords as $keyword) {
                // Check if the keyword is present in the tag's node value or attribute values
                if (stripos($tag_element->nodeValue, $keyword) !== false || $this->hasAttributeWithValue($tag_element, $keyword)) {
                    $is_in_filters = true;
                    break;
                }
            }
            // error_log("tag elements: " . $tag_element->nodeName);
            // error_log("tag ID: " . $tag_element->nodeId);
            // error_log("is_in_filters: " . $is_in_filters);
            // Determine whether to remove or keep the tag based on the $keep_filters_only parameter

            // Remove the tag if it is in the filters
            if ($is_in_filters) {
                $tag_element->parentNode->removeChild($tag_element);
                // error_log("IN filter tag removed: " . $tag_element->nodeName);
            }
        }

        // Save the modified HTML
        $html = $dom->saveHTML();

        return $html;
    }

    public function removeAllTagsExceptfilters(DOMDocument $dom, $filters) {

        /**
         * Get all unique tags in the filters
         */
        $uniqueTagsinFilters = array_unique(array_map(function ($filter) {
            return $filter->getTag();
        }, $filters));

        /**
         * Remove all tags from filters that are not present in the allowed head tags
         * cleanup junk input
         */
        $allowed_head_tags = $this->allowed_head_tags;
        $filters = array_filter($filters, function ($filter) use ($allowed_head_tags) {
            return in_array($filter->getTag(), $allowed_head_tags);
        });


        // Find all specified tags
        foreach ($this->allowed_head_tags as $current_allowed_tag) {

            // $head = $dom->getElementsByTagName('head')->item(0);
            $tags = $dom->getElementsByTagName($current_allowed_tag);
            // $head = $dom->getElementsByTagName('head')->item(0);
            // $tags = $head->getElementsByTagName($current_allowed_tag);

            if ($tags->length == 0) {
                continue;
            }

            foreach ($tags as $tag_element) {

                if ($this->debug) error_log("------- current tag element: " . $current_allowed_tag);

                /**
                 * Remove all tags that are not present in the filters
                 */
                if (!in_array($tag_element->nodeName, $uniqueTagsinFilters)) {
                    $tag_element->parentNode->removeChild($tag_element);
                    if ($this->debug) error_log("not present -> tag removed: " . $tag_element->nodeName);
                } else {

                    /**
                     * Check if the tag is in the filters
                     */
                    $shouldRemove = true;
                    foreach ($filters as $filter) {
                        if ($filter->getTag() == $current_allowed_tag) {
                            if ($this->debug)  error_log("filter tag: " . $filter->getTag() . " : " . $filter->getSearchString());

                            // Check if the keyword is present in the tag's node value or attribute values
                            if (stripos($tag_element->nodeValue, $filter->getSearchString()) !== false || $this->hasAttributeWithValue($tag_element, $filter->getSearchString())) {
                                $shouldRemove = true;
                                break;
                            } else {
                                $shouldRemove = true;
                            }
                        }
                    }
                    if ($shouldRemove) {
                        $tag_element->parentNode->removeChild($tag_element);
                        if ($this->debug) error_log("Removed: " . $tag_element->nodeName);
                        // error_log($filter->getTag() . ": " . $filter->getSearchString());
                    } else {
                        if ($this->debug) error_log("Not Removed: " . $tag_element->nodeName);
                        if ($this->debug) error_log($filter->getTag() . ": " . $filter->getSearchString());
                    }
                }
            }
        }


        // Save the modified HTML
        $html = $dom->saveHTML();

        return $html;
    }


    private function hasAttributeWithValue($element, $value) {
        foreach ($element->attributes as $attribute) {
            if (stripos($attribute->nodeValue, $value) !== false) {
                return true;
            }
        }
        return false;
    }
}
