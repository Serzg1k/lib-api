<?php
return [
    'jwt' => [
        'key' => 'REPLACE_ME_WITH_LONG_RANDOM_SECRET',
        'issuer' => 'library-api',
        'audience' => 'library-clients',
        'ttl' => 3600 * 24,
    ],
];