<?php
    /**
     * Цена за 1 предмет
     */
    class Prices {
        public static function getRecommended()
        {
            $prices = [];
            foreach(Grades::$all as $grade => $label) {
                switch($grade) {
                    case 11:
                        $prices[$grade] = 1900;
                        break;
                    default:
                        $prices[$grade] = 1700;
                        break;
                }
            }
            return $prices;
        }
		
		public static function get()
        {
            $prices = [];
            foreach(Grades::$all as $grade => $label) {
                switch($grade) {
                    case 11:
                        $prices[$grade] = 1700;
                        break;
                    case Grades::EXTERNAL:
                        $prices[$grade] = 1600;
                        break;
                    default:
                        $prices[$grade] = 1550;
                        break;
                }
            }
            return $prices;
        }
    }
