<?php
    /**
     * Цена за 1 предмет
     */
    class Prices {
        public static function get()
        {
            $prices = [];
            foreach(Grades::$all as $grade => $label) {
                switch($grade) {
                    case 11:
                        $prices[$grade] = 1700;
                        break;
                    default:
                        $prices[$grade] = 1550;
                        break;
                }
            }
            return $prices;
        }
    }
