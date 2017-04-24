<?php
    /**
     * Цена за 1 предмет
     */
    class Discount {
        public static function get()
        {
            return [4, 10, 20, 30, 40, 50, 70, 98];
        }
        public static function json()
        {
            return json_encode(self::get());
        }
    }
