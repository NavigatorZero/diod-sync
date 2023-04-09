<?php
namespace App\Classes\ObjectNotations;


class Sync extends ObjectNotation {

    public int $first_sync;
    public int $second_sync;
    public bool $is_sync_in_progress;
    public bool $is_second_sync;
    public string $last_sync;

    public function __construct(array $data) {
        parent::__construct($data);
    }
}
