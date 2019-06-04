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
    public function getTotalKgQty($type, $quantityDetails, $items, $designDetail) {

        switch ($type) {
            case ThreadType::WEFT:
                return ($quantityDetails['denier'] * ($designDetail->panno +
                            $designDetail->additional_panno) * $quantityDetails['pick'] * $items['total_meters']) / (9000 * 1000);
            case ThreadType::WARP:
                return ($quantityDetails['denier'] * ($designDetail->panno) * $items['total_meters']) / (9000 * 1000);

            default:
                return 0;
        }


    }


    // prevent from being un-serialized

    private function __clone() {

    }

    private function __wakeup() {

    }
}
