<?php

return [
    'nav' => [
        'about' => 'About Us',
        'products' => 'Products',
        'csr' => 'Sustainability',
        // Hover menus in the top nav (desktop only). An item resolves to
        // nav[base ?? section] . suffix, so `base` lets an item point at another
        // section's page — the Investor documents live under CSR, for instance.
        // `head` renders the item as a bold group heading.
        // A `#hash` suffix needs a matching id on that page; a `/slug` suffix
        // needs a matching CsrProgram slug; `?tab=` needs a tab key the page knows.
        'menus' => [
            'about' => [
                ['label' => 'Combiphar at a Glance', 'suffix' => '#sekilas'],
                ['label' => 'Our History & Purpose', 'suffix' => '#sejarah'],
                ['label' => 'Vision & Mission', 'suffix' => '#visi-misi'],
                ['label' => 'Leadership', 'suffix' => '#kepemimpinan'],
                ['label' => 'Awards', 'suffix' => '#penghargaan'],
                ['label' => 'Our Presence', 'suffix' => '#kehadiran'],
                ['label' => 'Manufacturing Solutions', 'suffix' => '#manufacturing'],
                ['label' => 'International Business', 'suffix' => '#international'],
            ],
            'products' => [
                ['label' => 'Consumer Health', 'suffix' => '?cat=consumer-health', 'head' => true],
                ['label' => 'Pharmaceutical', 'suffix' => '?cat=pharmaceutical', 'head' => true],
                ['label' => 'Nutritions & Herbal Care', 'suffix' => '?cat=nutrition-herbal', 'head' => true, 'children' => [
                    ['label' => 'Cereal & Snack', 'suffix' => '?cat=nutrition-herbal&sub=nutrition-herbal-serealsnack'],
                    ['label' => 'Honey', 'suffix' => '?cat=nutrition-herbal&sub=nutrition-herbal-honey'],
                    ['label' => 'Herbal', 'suffix' => '?cat=nutrition-herbal&sub=nutrition-herbal-herbal'],
                ]],
            ],
            'csr' => [
                ['label' => 'ESG', 'suffix' => '#esg', 'head' => true, 'children' => [
                    ['label' => 'Environmental', 'suffix' => '/environmental'],
                    ['label' => 'Social', 'suffix' => '/social'],
                    ['label' => 'Governance', 'suffix' => '/governance'],
                ]],
                ['label' => 'Championing a Healthy Tomorrow', 'suffix' => '#health', 'head' => true, 'children' => [
                    ['label' => 'Empowerment', 'suffix' => '/combi-hope-youth-empowerment'],
                    ['label' => 'Education', 'suffix' => '/education'],
                    // Jumps to the Sports section, not a detail page.
                    ['label' => 'Sport', 'suffix' => '#sport'],
                ]],
            ],
            'investor' => [
                ['label' => 'Whistleblowing System', 'base' => 'csr', 'suffix' => '/whistleblowing-system'],
                ['label' => 'Kebijakan Corporate Governansi', 'base' => 'csr', 'suffix' => '/kebijakan-corporate-governansi'],
                ['label' => 'Etika dan Kepatuhan', 'base' => 'csr', 'suffix' => '/etika-dan-kepatuhan'],
                ['label' => 'Piagam Internal Audit', 'base' => 'csr', 'suffix' => '/piagam-internal-audit'],
                ['label' => 'Nominasi dan Remunerasi', 'base' => 'csr', 'suffix' => '/nominasi-dan-remunerasi'],
                ['label' => 'Komite Audit', 'base' => 'csr', 'suffix' => '/komite-audit'],
            ],
            'news' => [
                ['label' => 'Health Information', 'suffix' => '?tab=health'],
                ['label' => 'Product Info', 'suffix' => '?tab=product'],
                ['label' => 'Investor Update', 'suffix' => '?tab=investor'],
                ['label' => 'Others', 'suffix' => '?tab=others'],
            ],
            'contact' => [
                ['label' => 'Careers', 'suffix' => '?tab=karir'],
                ['label' => 'Contact', 'suffix' => '?tab=kontak'],
            ],
        ],
        'investor' => 'Investor',
        'news' => 'News',
        'contact' => 'Careers & Contact',
    ],
    'search' => 'Search',
    'menu' => 'Menu',
    'close' => 'Close',
    'read_more' => 'Read More',
    'learn_more' => 'Learn More',
    'follow_us' => 'Follow us on social media:',
    'rights' => 'All Rights Reserved to Combiphar',
    'terms' => 'Terms of Use',
    'privacy' => 'Privacy Notice',
    'quick_links' => 'Quick Links',
    'scroll' => 'Scroll to Explore',
    'notfound' => [
        'code' => '404 Not Found',
        'title' => 'Sorry, this page could not be found',
        'text' => 'The page you are looking for may have been removed, had its name changed, or is temporarily unavailable',
        'home' => 'Back to Homepage',
    ],
];
