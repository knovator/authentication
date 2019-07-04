<?php


namespace App\Constants;


/**
 * Class Master
 * @package App\Constants
 */
class Master
{

    const COLOR = 'COLOR';

    // Purchase Order Statuses
    const PURCHASE_STATUS = 'PURCHASE_STATUS'; // parent
    const PO_PENDING = 'PO_PENDING';
    const PO_DELIVERED = 'PO_DELIVERED';
    const PO_CANCELED = 'PO_CANCELED';

    // Sales Order Statuses
    const SALES_STATUS = 'SALES_STATUS'; // parent
    const SO_PENDING = 'SO_PENDING';
    const SO_MANUFACTURING = 'SO_MANUFACTURING';
    const SO_CANCELED = 'SO_CANCELED';
    const SO_DELIVERED = 'SO_DELIVERED';
}
