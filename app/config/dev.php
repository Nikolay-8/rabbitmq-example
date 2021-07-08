<?php
return [
    'rabbit_mq' =>  [
        'host' => 'rabbit-mq',
        'port' => 5672,
        'user' => 'user',
        'password' => 'password',
        'vhost' => 'my_vhost'
    ],
    'queue_example' => [
        'queue' => 'queue_example',
        'exchange'  =>  'exchange',
        'routing_key'   =>  'rout'
    ]
];