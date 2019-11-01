<?php

namespace App\Contract;

interface ConfigContract
{
    public function get(string $key);
    public function set(string $key, string $value);
    public function has(string $key);
    public function save(): ConfigContract;
    public function matches(string $key, string $value);
}
