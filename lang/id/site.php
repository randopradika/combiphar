<?php

return [
    'nav' => [
        'about' => 'Tentang Kami',
        'products' => 'Produk',
        'csr' => 'Keberlanjutan',
        // Hover menus in the top nav (desktop only). An item resolves to
        // nav[base ?? section] . suffix, so `base` lets an item point at another
        // section's page — the Investor documents live under CSR, for instance.
        // `head` renders the item as a bold group heading.
        // A `#hash` suffix needs a matching id on that page; a `/slug` suffix
        // needs a matching CsrProgram slug; `?tab=` needs a tab key the page knows.
        'menus' => [
            'about' => [
                ['label' => 'Sekilas Combiphar', 'suffix' => '#sekilas'],
                ['label' => 'Sejarah & Tujuan Kami', 'suffix' => '#sejarah'],
                ['label' => 'Visi & Misi', 'suffix' => '#visi-misi'],
                ['label' => 'Kepemimpinan', 'suffix' => '#kepemimpinan'],
                ['label' => 'Penghargaan', 'suffix' => '#penghargaan'],
                ['label' => 'Kehadiran Kami', 'suffix' => '#kehadiran'],
                ['label' => 'Manufacturing Solutions', 'suffix' => '#manufacturing'],
                ['label' => 'International Business', 'suffix' => '#international'],
            ],
            'products' => [
                ['label' => 'Consumer Health', 'suffix' => '?cat=consumer-health', 'head' => true],
                ['label' => 'Pharmaceutical', 'suffix' => '?cat=pharmaceutical', 'head' => true],
                ['label' => 'Nutritions & Herbal Care', 'suffix' => '?cat=nutrition-herbal', 'head' => true, 'children' => [
                    ['label' => 'Sereal & Snack', 'suffix' => '?cat=nutrition-herbal&sub=nutrition-herbal-serealsnack'],
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
                ['label' => 'Informasi Kesehatan', 'suffix' => '?tab=health'],
                ['label' => 'Info Produk', 'suffix' => '?tab=product'],
                ['label' => 'Investor Update', 'suffix' => '?tab=investor'],
                ['label' => 'Lainnya', 'suffix' => '?tab=others'],
            ],
            'contact' => [
                ['label' => 'Karir', 'suffix' => '?tab=karir'],
                ['label' => 'Kontak', 'suffix' => '?tab=kontak'],
            ],
        ],
        'investor' => 'Investor',
        'news' => 'Berita',
        'contact' => 'Karir & Kontak',
    ],
    'search' => 'Cari',
    'menu' => 'Menu',
    'close' => 'Tutup',
    'back' => 'Kembali',
    'read_more' => 'Selengkapnya',
    'learn_more' => 'Pelajari Lebih Lanjut',
    'follow_us' => 'Ikuti kami di media sosial:',
    'terms' => 'Ketentuan Penggunaan',
    'privacy' => 'Kebijakan Privasi',
    'quick_links' => 'Tautan Cepat',
    'scroll' => 'Gulir untuk Menjelajah',
    'notfound' => [
        'code' => '404 Not Found',
        'title' => 'Maaf, halaman ini tidak ditemukan',
        'text' => 'Halaman yang Anda cari mungkin telah dihapus, telah diubah namanya atau sedang tidak tersedia saat ini',
        'home' => 'Kembali ke Beranda',
    ],
];
