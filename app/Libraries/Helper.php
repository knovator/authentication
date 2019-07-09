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
        $wordMessage = '';
        $words = [
            '0'  => '',
            '1'  => 'one',
            '2'  => 'two',
            '3'  => 'three',
            '4'  => 'four',
            '5'  => 'five',
            '6'  => 'six',
            '7'  => 'seven',
            '8'  => 'eight',
            '9'  => 'nine',
            '10' => 'ten',
            '11' => 'eleven',
            '12' => 'twelve',
            '13' => 'thirteen',
            '14' => 'fourteen',
            '15' => 'fifteen',
            '16' => 'sixteen',
            '17' => 'seventeen',
            '18' => 'eighteen',
            '19' => 'nineteen',
            '20' => 'twenty',
            '30' => 'thirty',
            '40' => 'forty',
            '50' => 'fifty',
            '60' => 'sixty',
            '70' => 'seventy',
            '80' => 'eighty',
            '90' => 'ninety'
        ];
        $digits = ['', '', 'hundred', 'thousand', 'lakh', 'crore'];

        $number = explode(".", $number);
        $result = ["", ""];
        $index = 0;
        foreach ($number as $val) {
            // loop each part of number, right and left of dot
            for ($row = 0; $row < strlen($val); $row++) {
                // look at each part of the number separately  [1] [5] [4] [2]  and  [5] [8]
                $numberPart = str_pad($val[$row], strlen($val) - $row, "0",
                    STR_PAD_RIGHT); // make 1 => 1000, 5 => 500, 4 => 40 etc.
                if ($numberPart <= 20) { // if it's below 20 the number should be one word
                    $numberPart = 1 * substr($val, $row, 2); // use two digits as the word
                    $row++; // increment i since we used two digits
                    $result[$index] .= $words[$numberPart] . " ";
                } else {
                    //echo $numberPart . "<br>\n"; //debug
                    if ($numberPart > 90) {  // more than 90 and it needs a $digit.
                        $result[$index] .= $words[$val[$row]] . " " . $digits[strlen($numberPart) -
                            1] .
                            " ";
                    } else {
                        if ($numberPart != 0) { // don't print zero
                            $result[$index] .= $words[str_pad($val[$row], strlen($val) - $row, "0",
                                    STR_PAD_RIGHT)] . " ";
                        }
                    }
                }
            }
            $index++;
        }
        if (trim($result[0]) != "") {
            $wordMessage .= $result[0] . "Rupees ";
        }
        if ($result[1] != "") {
            $wordMessage .= $result[1] . "Paise";
        }

        return strtoupper(str_replace('   ', ' ', $wordMessage));
    }
}








