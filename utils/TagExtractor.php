<?php

class TgenFilterTagExtractor {

    /**
     * Extracts filters from the input string.
     *
     * @param string $input The input string to extract filters from.
     * @return array An array of FilterModel objects representing the extracted filters.
     */
    public static function extractFilters($input) {
        $filters = [];

        // Split input by new lines
        $lines = explode("\n", $input);

        foreach ($lines as $line) {
            // Remove leading and trailing whitespace
            $line = trim($line);

            // Split line by ':'
            $parts = explode(':', $line, 2);

            // If the line contains ':' delimiter
            if (count($parts) === 2) {
                // Extract tag and search string
                $tag = trim(str_replace(['<', '>'], '', $parts[0]));
                $search_string = trim($parts[1]);

                // Add to filters array                

                $filter = new FilterModel();
                $filter->setTag($tag);
                $filter->setSearchString($search_string);
                $filters[] = $filter;
            }
        }

        return $filters;
    }
}
