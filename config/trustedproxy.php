<?php

$proxies = array_values(array_filter(
    explode(',', (string) env('TRUSTED_PROXIES', ''))
));

return [
    'proxies' => $proxies !== [] ? $proxies : null,
];
