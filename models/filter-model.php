<?php

class FilterModel {
    private $tag;
    private $search_string;

    public function getTag() {
        return $this->tag;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }

    public function getSearchString() {
        return $this->search_string;
    }

    public function setSearchString($search_string) {
        $this->search_string = $search_string;
    }
}
