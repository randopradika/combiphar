<?php

return [

    /*
     * Server-side rendering. The Node daemon (php artisan inertia:start-ssr,
     * started by the Docker CMD) renders bootstrap/ssr/ssr.js; the gateway
     * appends /render to this URL. Rendering failures fall back to
     * client-side rendering, so a dead daemon never breaks the site.
     */
    'ssr' => [
        'enabled' => true,
        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),
    ],

];
