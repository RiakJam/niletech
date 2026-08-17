<?php
// FOOTER TRANSLATIONS ONLY
// This file handles all footer-specific translations

$footer_translations = [
    'en' => [
        // Footer Company
        'footer_tagline' => 'Innovate. Develop. Solve. We provide cutting-edge web development and technology solutions to help your business thrive.',
        
        // Footer Services
        'footer_services' => 'Our Services',
        'footer_web_dev' => 'Web Development',
        'footer_software' => 'Software Solutions',
        'footer_cloud' => 'Cloud Services',
        'footer_security' => 'IT Security',
        'footer_support' => 'Tech Support',
        
        // Footer Quick Links
        'footer_quick_links' => 'Quick Links',
        'footer_about' => 'About Us',
        'footer_portfolio' => 'Portfolio',
        'footer_blog' => 'Blog',
        'footer_careers' => 'Careers',
        'footer_contact' => 'Contact',
        
        // Footer Contact
        'footer_get_in_touch' => 'Get In Touch',
        'footer_address' => '123 Tech Street, Silicon Valley, CA 94025',
        'footer_email' => 'info@niletech.com',
        'footer_phone' => '+1 (555) 123-4567',
        
        // Footer Copyright
        'footer_copyright' => 'All rights reserved.',
        'footer_tagline_short' => 'Innovate. Develop. Solve.',
    ],
    'sw' => [
        // Footer Company
        'footer_tagline' => 'Buni. Tengeneza. Suluhisha. Tunatoa suluhisho za kisasa za maendeleo ya wavuti na teknolojia kusaidia biashara yako kustawi.',
        
        // Footer Services
        'footer_services' => 'Huduma Zetu',
        'footer_web_dev' => 'Maendeleo ya Wavuti',
        'footer_software' => 'Suluhisho za Programu',
        'footer_cloud' => 'Huduma za Wingu',
        'footer_security' => 'Usalama wa IT',
        'footer_support' => 'Msaada wa Teknolojia',
        
        // Footer Quick Links
        'footer_quick_links' => 'Viungo vya Haraka',
        'footer_about' => 'Kuhusu Sisi',
        'footer_portfolio' => 'Kazi Zetu',
        'footer_blog' => 'Blogi',
        'footer_careers' => 'Kazi',
        'footer_contact' => 'Wasiliana Nasi',
        
        // Footer Contact
        'footer_get_in_touch' => 'Wasiliana Nasi',
        'footer_address' => '123 Tech Street, Silicon Valley, CA 94025',
        'footer_email' => 'info@niletech.com',
        'footer_phone' => '+1 (555) 123-4567',
        
        // Footer Copyright
        'footer_copyright' => 'Haki zote zimehifadhiwa.',
        'footer_tagline_short' => 'Buni. Tengeneza. Suluhisha.',
    ],
    'ar' => [
        // Footer Company
        'footer_tagline' => 'ابتكر. طور. حل. نقدم حلولاً متطورة لتطوير الويب والتكنولوجيا لمساعدة عملك على الازدهار.',
        
        // Footer Services
        'footer_services' => 'خدماتنا',
        'footer_web_dev' => 'تطوير الويب',
        'footer_software' => 'حلول البرمجيات',
        'footer_cloud' => 'خدمات السحابة',
        'footer_security' => 'أمن المعلومات',
        'footer_support' => 'الدعم التقني',
        
        // Footer Quick Links
        'footer_quick_links' => 'روابط سريعة',
        'footer_about' => 'معلومات عنا',
        'footer_portfolio' => 'أعمالنا',
        'footer_blog' => 'المدونة',
        'footer_careers' => 'وظائف',
        'footer_contact' => 'اتصل بنا',
        
        // Footer Contact
        'footer_get_in_touch' => 'تواصل معنا',
        'footer_address' => '123 شارع التكنولوجيا، وادي السيليكون، كاليفورنيا 94025',
        'footer_email' => 'info@niletech.com',
        'footer_phone' => '+1 (555) 123-4567',
        
        // Footer Copyright
        'footer_copyright' => 'جميع الحقوق محفوظة.',
        'footer_tagline_short' => 'ابتكر. طور. حل.',
    ]
];

// Merge with main translations
foreach ($footer_translations as $lang => $translations_array) {
    foreach ($translations_array as $key => $value) {
        $translations[$lang][$key] = $value;
    }
}
?>