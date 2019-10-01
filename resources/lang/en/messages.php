<?php

return [

    // Module messages
    'created'                       => ':module created successfully.',
    'updated'                       => ':module updated successfully.',
    'deleted'                       => ':module deleted successfully.',
    'retrieved'                     => ':module retrieved successfully.',
    'not_found'                     => ':module not found.',
    'added'                         => ':module successfully added.',
    'sent'                          => ':module sent successfully.',
    'associated'                    => 'Please remove all :related of this :module.',
    'can_not_export'                => ':module are not available, so you can not export it.',


    // Design Messages
    'can_not_edit_design'           => 'This design is approved by admin, so you can not update it.',
    'in_active_design'              => 'This design is in active, so you can not update it.',


    // Purchase order Messages
    'can_not_edit_order'            => 'This Order is completed, so you can not update.',
    'can_not_delete_complete_order' => 'This Order is completed, so you can not delete.',


    'order_has_deliveries_not_delete'   => 'This Order has deliveries, so you can not delete.',
    'order_has_deliveries_not_cancel'   => 'This Order has deliveries, so you can not cancel this order.',
    'purchase_deliveries_must_complete' => 'To complete this order,deliveries must be delivered.',


    // Sales order Messages
    'not_delete_order'                  => 'This Order is in :status state, so you can not delete it.',
    'delivery_can_not_delete'           => 'This Delivery is in :status state, so you can not delete it.',
    'complete_order'                    => 'To complete this order, all partial order must be delivered or canceled .',
    'must_partial_delivery'             => 'To Complete this order, you have to create partial delivery.',

    // password messages
    'current_password_wrong'            => 'Current password is incorrect.',
    'password_changed'                  => 'Password changed successfully.',

    'something_wrong'           => 'Oops! something went wrong ,please try again later.',

    // partial order messages
    'quantity_not_exists'       => 'Quantity you are trying to do should be less than or equal to the remaining quantity.',

    // Delivery messages
    'partial_order_not_present' => "This delivery doesn't have any partial orders, so you can not export it.",
];
