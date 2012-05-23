<?php

abstract class ResponseStore {

    /**
     * @param string $key
     */
    abstract public function FindMatch($key);
}
