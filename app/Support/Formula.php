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
     * @param $threadDetail
     * @param $designDetail
     * @param $totalMeters
     * @return int|string
     */
    public function getTotalKgQty($type, $threadDetail, $designDetail, $totalMeters) {
        switch ($type) {
            case ThreadType::WEFT:

                $value = ($threadDetail['denier'] * ($designDetail->panno +
                            $designDetail->additional_panno) * $threadDetail['pick'] * $totalMeters) / (9000 * 1000);

                return $this->convertRoundValue($value);
            case ThreadType::WARP:

                $value = ($threadDetail['denier'] * ($designDetail->panno + $designDetail->reed) *
                        $totalMeters) / (9000 * 1000);

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
