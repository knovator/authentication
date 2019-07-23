<?php


if (!function_exists('getFontColor')) {

    /** Return notification type
     * @param $hexColor
     * @return string
     */
    function getFontColor($hexColor) {
        list($red, $green, $blue) = sscanf($hexColor, "#%02x%02x%02x");
        if (($red * 0.299 + $green * 0.587 + $blue * 0.114) > 186) {
            return "#000000";
        }

        return "#ffffff";
    }
}

if (!function_exists('getPercentage')) {
    /**
     * @param $total
     * @param $number
     * @return float|int
     */
    function getPercentageValue($total, $number) {
        if ($total > 0) {
            return round($number / ($total / 100), 2);
        } else {
            return 0;
        }
    }
}

if (!function_exists('displayWords')) {

    /**
     * @param $number
     * @return string
     */
    function displayWords($number) {
        $no = round($number);
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $digitsLength = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0  => '',
            1  => 'One',
            2  => 'Two',
            3  => 'Three',
            4  => 'Four',
            5  => 'Five',
            6  => 'Six',
            7  => 'Seven',
            8  => 'Eight',
            9  => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($i < $digitsLength) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
            } else {
                $str [] = null;
            }
        }

        $rupees = implode(' ', array_reverse($str));

        $paise = ($decimal) ? "And " . (!empty($words[$decimal - $decimal % 10]) ? $words[$decimal
                - $decimal % 10] . ' ' : '') .
            (!empty($words[$decimal % 10]) ? $words[$decimal % 10] . ' ' : '') . 'Paise ' : '';

        $string =  ($rupees ? $rupees.'Rupees ' : '') . $paise . "Only";
        return strtoupper($string);
    }
}








