<?php
class NumericHelper {
        public static function ToWord($number) {
            $digit1 = array (
                    0 => 'zero',
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                    4 => 'four',
                    5 => 'five',
                    6 => 'six',
                    7 => 'seven',
                    8 => 'eight',
                    9 => 'nine'
            );
            $digit1_5 = array (
                    1 => 'eleven',
                    2 => 'twelve',
                    3 => 'thirteen',
                    4 => 'fourteen',
                    5 => 'fifteen',
                    6 => 'sixteen',
                    7 => 'seventeen',
                    8 => 'eighteen',
                    9 => 'nineteen'
            );
            $digit2 = array (
                    1 => 'ten',
                    2 => 'twenty',
                    3 => 'thirty',
                    4 => 'forty',
                    5 => 'fifty',
                    6 => 'sixty',
                    7 => 'seventy',
                    8 => 'eighty',
                    9 => 'ninety'
            );
            $digit3 = array (
                    1 => 'one hundred',
                    2 => 'two hundred',
                    3 => 'three hundred',
                    4 => 'four hundred',
                    5 => 'five hundred',
                    6 => 'six hundred',
                    7 => 'seven hundred',
                    8 => 'eight hundred',
                    9 => 'nine hundred'
            );
            $steps = array (
                    1 => 'thousand',
                    2 => 'million',
                    3 => 'billion',
                    4 => 'trillion',
                    5 => 'quadrillion',
                    6 => 'quintillion',
                    7 => 'sextillion',
                    8 => 'septillion',
                    9 => 'octillion',
                    10 => 'nonillion',
                    11 => 'decillion'
            );
            $t = array (
                    'and' => 'and'
            );
            
            return self::toWord2 ( $number, $digit1, $digit1_5, $digit2, $digit3, $t );
        }
        public static function ToWordFa($number) {
            $digit1 = array (
                    0 => 'صفر',
                    1 => 'یک',
                    2 => 'دو',
                    3 => 'سه',
                    4 => 'چهار',
                    5 => 'پنج',
                    6 => 'شش',
                    7 => 'هفت',
                    8 => 'هشت',
                    9 => 'نه'
            );
            $digit1_5 = array (
                    1 => 'یازده',
                    2 => 'دوازده',
                    3 => 'سیزده',
                    4 => 'چهارده',
                    5 => 'پانزده',
                    6 => 'شانزده',
                    7 => 'هفده',
                    8 => 'هجده',
                    9 => 'نوزده'
            );
            $digit2 = array (
                    1 => 'ده',
                    2 => 'بیست',
                    3 => 'سی',
                    4 => 'چهل',
                    5 => 'پنجاه',
                    6 => 'شصت',
                    7 => 'هفتاد',
                    8 => 'هشتاد',
                    9 => 'نود'
            );
            $digit3 = array (
                    1 => 'صد',
                    2 => 'دویست',
                    3 => 'سیصد',
                    4 => 'چهارصد',
                    5 => 'پانصد',
                    6 => 'ششصد',
                    7 => 'هفتصد',
                    8 => 'هشتصد',
                    9 => 'نهصد'
            );
            $steps = array (
                    1 => 'هزار',
                    2 => 'میلیون',
                    3 => 'بیلیون',
                    4 => 'تریلیون',
                    5 => 'کادریلیون',
                    6 => 'کوینتریلیون',
                    7 => 'سکستریلیون',
                    8 => 'سپتریلیون',
                    9 => 'اکتریلیون',
                    10 => 'نونیلیون',
                    11 => 'دسیلیون'
            );
            $t = array (
                    'and' => 'و'
            );
            return self::toWord2 ( $number, $digit1, $digit1_5, $digit2, $digit3, $t );
        }
        private static function toWord2($number, array $digit1, array $digit1_5, array $digit2, array $digit3, array $t) {
            $formated = self::numberFormat ( $number, 0, '.', ',' );
            $groups = explode ( ',', $formated );
            
            $steps = count ( $groups );
            
            $parts = array ();
            foreach ( $groups as $step => $group ) {
                $group_words = self::groupToWords ( $group, $digit1, $digit1_5, $digit2, $digit3 );
                if ($group_words) {
                    $part = implode ( ' ' . $t ['and'] . ' ', $group_words );
                    if (isset ( $steps [$steps - $step - 1] )) {
                        $part .= ' ' . $steps [$steps - $step - 1];
                    }
                    $parts [] = $part;
                }
            }
            return implode ( ' ' . $t ['and'] . ' ', $parts );
        }
        private static function numberFormat($number, $decimal_precision = 0, $decimals_separator = '.', $thousands_separator = ',') {
            $number = explode ( '.', str_replace ( ' ', '', $number ) );
            $number [0] = str_split ( strrev ( $number [0] ), 3 );
            $total_segments = count ( $number [0] );
            for($i = 0; $i < $total_segments; $i ++) {
                $number [0] [$i] = strrev ( $number [0] [$i] );
            }
            $number [0] = implode ( $thousands_separator, array_reverse ( $number [0] ) );
            if (! empty ( $number [1] )) {
                $number [1] = Round ( $number [1], $decimal_precision );
            }
            return implode ( $decimals_separator, $number );
        }
        private static function groupToWords($group, array $digit1, array $digit1_5, array $digit2, array $digit3) {
            $d3 = floor ( $group / 100 );
            $d2 = floor ( ($group - $d3 * 100) / 10 );
            $d1 = $group - $d3 * 100 - $d2 * 10;
            
            $group_array = array ();
            
            if ($d3 != 0) {
                $group_array [] = $digit3 [$d3];
            }
            
            if ($d2 == 1 && $d1 != 0) { // 11-19
                $group_array [] = $digit1_5 [$d1];
            } else if ($d2 != 0 && $d1 == 0) { // 10-20-...-90
                $group_array [] = $digit2 [$d2];
            } else if ($d2 == 0 && $d1 == 0) { // 00
            } else if ($d2 == 0 && $d1 != 0) { // 1-9
                $group_array [] = $digit1 [$d1];
            } else { // Others
                $group_array [] = $digit2 [$d2];
                $group_array [] = $digit1 [$d1];
            }
            
            if (! count ( $group_array )) {
                return FALSE;
            }
            
            return $group_array;
        }
    }
?>