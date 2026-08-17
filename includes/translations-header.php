<?php
// HEADER TRANSLATIONS ONLY
// This file handles all header-specific translations

$header_translations = [
    'en' => [
        // Top Nav
        'email' => 'info@niletech.com',
        'phone' => '+254 712 345 678',
        'language' => 'Language',
        
        // Main Nav
        'home' => 'Home',
        'services' => 'Services',
        'about' => 'About',
        'portfolio' => 'Portfolio',
        'contact' => 'Contact',
        'get_started' => 'Get Started',
        
        // Header Tagline
        'tagline' => 'WEB DEVELOPMENT & TECH SOLUTIONS',
        'company_name' => 'Niletech',
    ],
    'sw' => [
        // Top Nav
        'email' => 'info@niletech.com',
        'phone' => '+254 712 345 678',
        'language' => 'Lugha',
        
        // Main Nav
        'home' => 'Nyumbani',
        'services' => 'Huduma',
        'about' => 'Kuhusu Sisi',
        'portfolio' => 'Kazi Zetu',
        'contact' => 'Wasiliana Nasi',
        'get_started' => 'Anza Sasa',
        
        // Header Tagline
        'tagline' => 'MAENDELEO YA WAVUTI & SULUHISHO ZA TEKNOLOJIA',
        'company_name' => 'Niletech',
    ],
    'ar' => [
        // Top Nav - Using Eastern Arabic numerals
        'email' => 'info@niletech.com',
        'phone' => '+٢٥٤ ٧١٢ ٣٤٥ ٦٧٨', // Eastern Arabic numerals
        'language' => 'اللغة',
        
        // Main Nav
        'home' => 'الرئيسية',
        'services' => 'الخدمات',
        'about' => 'معلومات عنا',
        'portfolio' => 'أعمالنا',
        'contact' => 'اتصل بنا',
        'get_started' => 'ابدأ الآن',
        
        // Header Tagline
        'tagline' => 'تطوير الويب وحلول التكنولوجيا',
        'company_name' => 'Niletech',
    ]
];

// Merge with main translations
foreach ($header_translations as $lang => $translations_array) {
    foreach ($translations_array as $key => $value) {
        $translations[$lang][$key] = $value;
    }
}
?>