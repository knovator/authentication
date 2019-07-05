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








