<?php

namespace App\Classes\ObjectNotations;


class ObjectNotation {

    public function __construct(array $data) {
        self::set( $data);
    }


    protected function set(array $data):void {
        foreach ($data AS $key => $value) {
            $this->{$key} = $value;
        }
    }

}
