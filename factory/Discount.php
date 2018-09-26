<?php

class Discount {
    public static function get()
    {
        return [4, 6, 8, 10, 15, 20, 30, 40, 50, 70, 90];
    }
    public static function json()
    {
        return json_encode(self::get());
    }
}
