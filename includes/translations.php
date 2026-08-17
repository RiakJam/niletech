<?php
// Main translation system - Loads all translation files

// Available languages
$languages = [
    'en' => 'English',
    'sw' => 'Kiswahili',
    'ar' => 'العربية'
];

// Get current language from session or default to English
session_start();
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Change language if requested
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $_SESSION['language'] = $_GET['lang'];
}

$current_lang = $_SESSION['language'];

// Load translation files
$translations = [];

// Load header translations
if (file_exists(__DIR__ . '/translations-header.php')) {
    include_once __DIR__ . '/translations-header.php';
}

// Load footer translations
if (file_exists(__DIR__ . '/translations-footer.php')) {
    include_once __DIR__ . '/translations-footer.php';
}

// Load index translations
if (file_exists(__DIR__ . '/translations-index.php')) {
    include_once __DIR__ . '/translations-index.php';
}

// Convert Western numerals to Eastern Arabic numerals
function toArabicNumerals($number) {
    $western = ['0','1','2','3','4','5','6','7','8','9'];
    $eastern = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    return str_replace($western, $eastern, $number);
}

// Format phone number based on language
function formatPhoneNumber($number) {
    global $current_lang;
    if ($current_lang === 'ar') {
        return toArabicNumerals($number);
    }
    return $number;
}

// Translation function
function translate($key) {
    global $translations, $current_lang;
    if (isset($translations[$current_lang][$key])) {
        return $translations[$current_lang][$key];
    }
    // Fallback to English if translation not found
    return isset($translations['en'][$key]) ? $translations['en'][$key] : $key;
}

// Get current language
function getCurrentLanguage() {
    global $current_lang;
    return $current_lang;
}

// Get language code
function getLanguageCode() {
    global $current_lang;
    return $current_lang;
}

// Get language direction (RTL for Arabic)
function getDirection() {
    global $current_lang;
    return $current_lang === 'ar' ? 'rtl' : 'ltr';
}

// Get language flag
function getLanguageFlag($lang) {
    $flags = [
        'en' => '🇬🇧',
        'sw' => '🇹🇿',
        'ar' => '🇸🇦'
    ];
    return isset($flags[$lang]) ? $flags[$lang] : '🌐';
}

// Get all languages
function getLanguages() {
    global $languages;
    return $languages;
}
?>