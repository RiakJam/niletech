<?php
declare(strict_types=1);
require_once __DIR__ . '/marketplace-icons.php';
require_once __DIR__ . '/geo.php';

function marketplace_category_groups(): array {
    return [
        'vehicles' => ['Vehicles', '🚗', [
            'cars' => 'Cars', 'automotive' => 'Vehicle Parts & Accessories', 'motorcycles' => 'Motorcycles & Scooters',
            'buses' => 'Buses & Microbuses', 'trucks' => 'Trucks & Trailers', 'heavy_machinery' => 'Construction & Heavy Machinery',
            'boats' => 'Watercraft & Boats', 'personal_mobility' => 'Personal Mobility',
        ]],
        'property' => ['Property', '⌂', [
            'houses_sale' => 'Houses & Apartments for Sale', 'houses_rent' => 'Houses & Apartments for Rent',
            'short_let' => 'Short Let', 'new_builds' => 'New Builds', 'land_sale' => 'Land & Plots for Sale',
            'land_rent' => 'Land & Plots for Rent', 'commercial_sale' => 'Commercial Property for Sale',
            'commercial_rent' => 'Commercial Property for Rent', 'event_spaces' => 'Event Centres & Workstations',
        ]],
        'phones_tablets' => ['Phones & Tablets', '▯', [
            'phones' => 'Mobile Phones', 'phone_accessories' => 'Phone & Tablet Accessories', 'tablets' => 'Tablets',
            'smart_watches' => 'Smart Watches', 'headphones' => 'Headphones',
        ]],
        'electronics' => ['Electronics', '▣', [
            'computers' => 'Laptops & Computers', 'audio' => 'TV, Audio & Video', 'gaming' => 'Games & Consoles',
            'cameras' => 'Photo & Video Cameras', 'security_electronics' => 'Security & Surveillance',
            'networking' => 'Networking Products', 'printers' => 'Printers & Scanners', 'monitors' => 'Computer Monitors',
            'computer_parts' => 'Computer Hardware & Accessories', 'electronic_accessories' => 'Electronics Accessories',
            'software' => 'Software',
        ]],
        'home' => ['Home, Furniture & Appliances', '⌂', [
            'furniture' => 'Furniture', 'lighting' => 'Lighting', 'storage' => 'Storage & Organization',
            'home_decor' => 'Home Accessories', 'appliances' => 'Home Appliances', 'kitchen_appliances' => 'Kitchen Appliances',
            'cookware' => 'Kitchenware & Cookware', 'household' => 'Household Supplies', 'garden' => 'Garden Supplies',
        ]],
        'fashion' => ['Fashion', '◇', [
            'womens_fashion' => "Women's Fashion", 'mens_fashion' => "Men's Fashion", 'shoes' => 'Shoes',
            'bags' => 'Bags', 'watches' => 'Watches & Jewellery', 'fashion_accessories' => 'Fashion Accessories',
        ]],
        'beauty' => ['Beauty & Personal Care', '✧', [
            'hair_beauty' => 'Hair Beauty', 'face_care' => 'Face Care', 'body_care' => 'Body Care',
            'oral_care' => 'Oral Care', 'fragrance' => 'Fragrance', 'makeup' => 'Makeup',
            'beauty_tools' => 'Beauty Tools & Accessories', 'supplements' => 'Vitamins & Supplements',
            'wellness' => 'Wellness & Massagers',
        ]],
        'services' => ['Services', '✦', [
            'building_services' => 'Building & Trades Services', 'car_services' => 'Car Services',
            'it_services' => 'Computer & IT Services', 'repair_services' => 'Repair Services',
            'cleaning_services' => 'Cleaning Services', 'printing_services' => 'Printing Services',
            'logistics_services' => 'Logistics & Delivery', 'legal_services' => 'Legal Services',
            'financial_services' => 'Tax & Financial Services', 'rental_services' => 'Rental Services',
            'travel_services' => 'Travel & Tours', 'classes' => 'Classes & Courses',
            'childcare_services' => 'Child Care & Education', 'health_services' => 'Health & Beauty Services',
            'event_services' => 'Party, Catering & Events', 'photography_services' => 'Photography & Video',
            'pet_services' => 'Pet Services',
        ]],
        'construction' => ['Repair & Construction', '⚒', [
            'building_materials' => 'Building Materials & Supplies', 'plumbing' => 'Plumbing & Water Systems',
            'electrical' => 'Electrical Equipment', 'power_tools' => 'Electrical Hand Tools',
            'hand_tools' => 'Hand Tools', 'doors_security' => 'Doors & Security',
            'flooring' => 'Flooring & Tiles', 'paint' => 'Paint & Finishes', 'construction_other' => 'Other Repair & Construction',
        ]],
        'commercial_tools' => ['Commercial Equipment & Tools', '▤', [
            'medical_equipment' => 'Medical Equipment & Supplies', 'safety_gear' => 'Safety Equipment & Protective Gear',
            'manufacturing_equipment' => 'Manufacturing Equipment', 'manufacturing_supplies' => 'Manufacturing Materials',
            'store_equipment' => 'Retail & Store Equipment', 'office_equipment' => 'Office Equipment',
            'catering_equipment' => 'Restaurant & Catering Equipment', 'commercial_other' => 'Other Commercial Equipment',
        ]],
        'leisure' => ['Leisure & Activities', '♫', [
            'sports' => 'Sports Equipment', 'bicycles' => 'Bicycles & Cycling', 'musical_instruments' => 'Musical Instruments & Gear',
            'books' => 'Books', 'arts_crafts' => 'Arts & Crafts', 'collectibles' => 'Collectibles',
            'outdoors' => 'Outdoor & Camping', 'fitness' => 'Fitness Equipment',
        ]],
        'kids' => ['Babies & Kids', '★', [
            'toys' => 'Toys & Games', 'kids_furniture' => "Children's Furniture", 'kids_clothes' => "Children's Clothing",
            'kids_shoes' => "Children's Shoes", 'baby_gear' => 'Strollers & Baby Gear',
            'baby_care' => 'Baby Care', 'school_supplies' => 'School Supplies',
        ]],
        'agriculture' => ['Food, Agriculture & Farming', '❀', [
            'food' => 'Food & Beverages', 'farm_animals' => 'Farm Animals', 'seeds' => 'Seeds & Fertilizers',
            'farm_machinery' => 'Farm Machinery & Equipment', 'farm_supplies' => 'Farm Supplies',
        ]],
        'pets' => ['Animals & Pets', '♧', [
            'dogs' => 'Dogs & Puppies', 'cats' => 'Cats & Kittens', 'fish' => 'Fish & Aquariums',
            'birds' => 'Birds', 'pet_accessories' => 'Pet Accessories', 'other_pets' => 'Other Pets',
        ]],
        'jobs' => ['Jobs', '▦', [
            'marketing_jobs' => 'Advertising & Marketing Jobs', 'finance_jobs' => 'Accounting & Finance Jobs',
            'it_jobs' => 'Computer & IT Jobs', 'sales_jobs' => 'Sales Jobs', 'hospitality_jobs' => 'Hotel & Hospitality Jobs',
            'trades_jobs' => 'Construction & Trades Jobs', 'education_jobs' => 'Education Jobs',
            'healthcare_jobs' => 'Healthcare Jobs', 'other_jobs' => 'Other Jobs',
        ]],
        'seeking_work' => ['Seeking Work - CVs', '▧', [
            'professional_cvs' => 'Professional CVs', 'technical_cvs' => 'Technical CVs',
            'service_cvs' => 'Service & Hospitality CVs', 'entry_cvs' => 'Entry-Level CVs',
        ]],
        'business_industry' => ['Business & Industry', '▥', [
            'business_sale' => 'Business for Sale & Investment', 'import_export' => 'Import, Export & Logistics',
            'wholesale' => 'Wholesale & Bulk', 'business_services' => 'Business Services',
        ]],
        'other' => ['Other', '＋', []],
    ];
}

function marketplace_categories(): array {
    $categories = [];
    foreach (marketplace_category_groups() as $key => [$label, $icon, $children]) {
        $categories[$key] = [$label, $icon];
        foreach ($children as $childKey => $childLabel) $categories[$childKey] = [$childLabel, $icon];
    }
    return $categories;
}

function marketplace_category_parent(string $key): ?string {
    foreach (marketplace_category_groups() as $parent => $details) {
        if ($key === $parent || isset($details[2][$key])) return $parent;
    }
    return null;
}

function marketplace_category_filter_keys(string $key): array {
    $group = marketplace_category_groups()[$key] ?? null;
    return $group ? array_merge([$key], array_keys($group[2])) : [$key];
}

function marketplace_price(?string $value, ?string $currency = 'KES'): string {
    if ($value === null || $value === '') return 'Ask for price';
    $code = isset(marketplace_currencies()[$currency ?? '']) ? $currency : 'KES';
    return ($code === 'KES' ? 'KSh' : $code) . ' ' . number_format((float)$value, ((float)$value == (int)(float)$value ? 0 : marketplace_currency_decimals($code)));
}

function marketplace_product_url(array $product, string $mainSite): string {
    if (!empty($product['domain_slug']) && in_array(parse_url($mainSite, PHP_URL_HOST), ['nileteck.com','www.nileteck.com'], true)) return 'https://' . $product['domain_slug'] . '.nileteck.com/' . rawurlencode($product['slug']);
    return $mainSite . '/p/marketplace/' . rawurlencode($product['business_slug']) . '/' . rawurlencode($product['slug']);
}

function marketplace_image_url(?string $path, string $assetPrefix): ?string {
    if (!$path || !preg_match('~^uploads/products/[a-f0-9]{32}\.(?:jpe?g|png|webp)$~', $path)) return null;
    return $assetPrefix . $path;
}

function marketplace_demo_cell(?string $path): ?int {
    if ($path !== null && preg_match('/^demo:([1-6])$/', $path, $match)) return (int)$match[1];
    return null;
}
