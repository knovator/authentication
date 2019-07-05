<?php

return array(
    'pdf'   => [
        'enabled' => true,
        'binary'  => '"' .config('app.snappy_pdf'). '"',
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],
    'image' => [
        'enabled' => true,
        'binary'  => '"' .config('app.snappy_image'). '"',
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],


);
