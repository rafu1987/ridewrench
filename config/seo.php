<?php

return [
    'production_host' => env('SEO_PRODUCTION_HOST', 'ridewrench.de'),

    'indexing_enabled' => env('SEO_INDEXING_ENABLED', false),

    'sitemap_urls' => ['/', '/faq', '/privacy', '/legal-notice', '/feedback'],
];
