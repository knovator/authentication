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
    'can_not_export'                => ':module are not available, so you can not export.',


    // Design Messages
    'can_not_edit_design'           => 'This design is approved by admin, so you can not update it.',
    'in_active_design'              => 'This design is in active, so you can not update it.',


    // Purchase order Messages
    'can_not_edit_purchase_order'   => 'This Order is completed, so you can not update it.',
    'can_not_delete_purchase_order' => 'This Order is completed, so you can not delete it.',

    // Sales order Messages
    'not_delete_sales_order'        => 'This Order is in :status state, so you can not delete it.',
    'delivery_can_not_delete'       => 'This Delivery is in :status state, so you can not delete it.',
    'complete_order'                => 'To complete this order, all partial order must be completed or canceled .',


    // password messages
    'current_password_wrong'        => 'Current password is incorrect.',
    'password_changed'              => 'Password changed successfully.',

    'something_wrong'     => 'Oops! something went wrong ,please try again later.',

    // partial order messages
    'quantity_not_exists' => 'Quantity you are trying to do should be less than or equal to the remaining quantity.'
];
