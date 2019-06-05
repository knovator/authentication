<?php

namespace App\Support;


use App\Modules\Thread\Constants\ThreadType;

/**
 * Class Formula
 * @package App\Support
 */
class Formula
{

    private static $instance = null;

    /**
     * prevent creating multiple instances due to "private" constructor
     * Formula constructor.
     */
    private function __construct() {

    }

    /**
     * @return Formula
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $type
     * @param $quantityDetails
     * @param $items
     * @param $designDetail
     * @return
     */
    public function getTotalKgQty($type, $quantityDetails, $items) {

        switch ($type) {
            case ThreadType::WEFT:

                $value = ($quantityDetails['denier'] * ($items['designDetail']->panno +
                            $items['designDetail']->additional_panno) * $quantityDetails['pick'] * $items['total_meters']) / (9000 * 1000);

                return $this->convertRoundValue($value);
            case ThreadType::WARP:

                $value = ($quantityDetails['denier'] * ($items['designDetail']->panno) *
                        $items['total_meters']) / (9000 * 1000);

                return $this->convertRoundValue($value);

            default:
                return 0;
        }


    }

    /**
     * @param $value
     * @return string
     */
    private function convertRoundValue($value) {
        return (float) number_format($value,
            2, '.', ',');
    }


    // prevent from being un-serialized

    private function __clone() {

    }

    private function __wakeup() {

    }
}
