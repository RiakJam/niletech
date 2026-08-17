<?php
// Include translation system
include_once 'includes/translations.php';
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguageCode(); ?>" dir="<?php echo getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niletech - <?php echo translate('home'); ?></title>
    
    <!-- Favicon - ROUND -->
    <link rel="icon" href="images/Logo.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="images/Logo.png">
    
    <!-- Google Fonts - Sans Serif -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <style>
        /* Global font */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Make favicon round */
        link[rel="icon"] {
            border-radius: 50% !important;
        }
        
        /* Top nav contact icons */
        .contact-icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }
        
        /* Top nav contact wrapper - fixes alignment */
        .contact-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        /* RTL specific styles */
        <?php if (getDirection() === 'rtl'): ?>
        .flex-row { flex-direction: row-reverse; }
        .space-x-3 > :not([hidden]) ~ :not([hidden]) { --tw-space-x-reverse: 1; }
        .ml-2 { margin-right: 0.5rem; margin-left: 0; }
        .mr-2 { margin-left: 0.5rem; margin-right: 0; }
        /* Fix phone number in RTL */
        .phone-number {
            direction: ltr !important;
            display: inline-block;
        }
        /* Fix icon alignment in RTL */
        .contact-wrapper {
            flex-direction: row-reverse;
        }
        .contact-icon {
            margin-left: 2px;
            margin-right: 0;
        }
        <?php endif; ?>
    </style>
    
    <link rel="stylesheet" href="dist/output.css">
</head>
<body>

<!-- TOP NAVIGATION BAR (Utility Bar) - Clean & Professional -->
<div class="bg-nile-primary border-b border-blue-800 hidden md:block">
    <div class="container-custom">
        <div class="flex justify-between items-center py-2">
            <!-- Left: Contact Info - Email & Phone only -->
            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                <!-- Email -->
                <div class="contact-wrapper text-white/90 hover:text-white transition-colors duration-200">
                    <svg class="contact-icon text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <a href="mailto:<?php echo translate('email'); ?>" class="text-xs font-medium hover:text-white transition-colors duration-200">
                        <?php echo translate('email'); ?>
                    </a>
                </div>
                
                <!-- Phone - Uses translated number with proper numerals -->
                <div class="contact-wrapper text-white/90 hover:text-white transition-colors duration-200">
                    <svg class="contact-icon text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <a href="tel:+254712345678" class="text-xs font-medium hover:text-white transition-colors duration-200 phone-number">
                        <?php echo formatPhoneNumber(translate('phone')); ?>
                    </a>
                </div>
            </div>
            
            <!-- Right: Language Switcher Only -->
            <div class="flex items-center">
                <?php include_once 'includes/language-switcher.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- MAIN NAVIGATION BAR - Reduced Height -->
<nav class="bg-nile-white shadow-md sticky top-0 z-50">
    <div class="container-custom">
        <div class="flex justify-between items-center py-2 md:py-3">
            <!-- Logo -->
            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                <div class="w-14 h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 flex-shrink-0 bg-white rounded-lg shadow-sm p-1">
                    <img src="images/Logo.png" alt="<?php echo translate('company_name'); ?>" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-xl md:text-2xl lg:text-3xl font-extrabold text-nile-primary tracking-tight"><?php echo translate('company_name'); ?></span>
                    <span class="hidden lg:block text-[9px] text-gray-400 font-semibold tracking-[0.15em] uppercase"><?php echo translate('tagline'); ?></span>
                </div>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center space-x-7 rtl:space-x-reverse">
                <a href="#" class="text-sm font-medium text-nile-dark hover:text-nile-primary transition-colors border-b-2 border-transparent hover:border-nile-primary pb-1"><?php echo translate('home'); ?></a>
                <a href="#services" class="text-sm font-medium text-nile-dark hover:text-nile-primary transition-colors border-b-2 border-transparent hover:border-nile-primary pb-1"><?php echo translate('services'); ?></a>
                <a href="#about" class="text-sm font-medium text-nile-dark hover:text-nile-primary transition-colors border-b-2 border-transparent hover:border-nile-primary pb-1"><?php echo translate('about'); ?></a>
                <a href="#portfolio" class="text-sm font-medium text-nile-dark hover:text-nile-primary transition-colors border-b-2 border-transparent hover:border-nile-primary pb-1"><?php echo translate('portfolio'); ?></a>
                <a href="#contact" class="text-sm font-medium text-nile-dark hover:text-nile-primary transition-colors border-b-2 border-transparent hover:border-nile-primary pb-1"><?php echo translate('contact'); ?></a>
                <a href="#contact" class="bg-nile-primary hover:bg-nile-secondary text-white font-semibold text-sm py-2 px-6 rounded-lg transition duration-300 shadow-md hover:shadow-lg">
                    <?php echo translate('get_started'); ?>
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="menu-toggle" class="lg:hidden text-nile-primary focus:outline-none relative w-9 h-9 flex items-center justify-center" aria-label="Toggle menu">
                <div class="relative w-7 h-7">
                    <span id="menu-bar-1" class="absolute block w-full h-0.5 bg-nile-primary transition-all duration-300 ease-in-out" style="top: 5px; transform-origin: center;"></span>
                    <span id="menu-bar-2" class="absolute block w-full h-0.5 bg-nile-primary transition-all duration-300 ease-in-out" style="top: 13px; transform-origin: center;"></span>
                    <span id="menu-bar-3" class="absolute block w-full h-0.5 bg-nile-primary transition-all duration-300 ease-in-out" style="bottom: 5px; transform-origin: center;"></span>
                </div>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Menu -->
<div id="mobile-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300" style="opacity: 0;"></div>

<div id="mobile-menu-container" class="fixed top-0 <?php echo getDirection() === 'rtl' ? 'right-0' : 'left-0'; ?> h-auto max-h-screen w-80 bg-white shadow-2xl z-50 transform transition-transform duration-400 ease-in-out hidden" style="transform: <?php echo getDirection() === 'rtl' ? 'translateX(100%)' : 'translateX(-100%)'; ?>; max-height: 90vh; overflow-y: auto; border-radius: 0 0 20px 20px;">
    <div class="overflow-y-auto">
        <!-- Mobile Menu Header -->
        <div class="flex items-center p-4 border-b border-gray-200 sticky top-0 bg-white z-10" style="border-radius: 0 0 20px 20px;">
            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                <div class="w-12 h-12 flex-shrink-0 bg-white rounded-lg shadow-sm p-1">
                    <img src="images/Logo.png" alt="<?php echo translate('company_name'); ?>" class="w-full h-full object-contain">
                </div>
                <span class="text-lg font-bold text-nile-primary"><?php echo translate('company_name'); ?></span>
            </div>
        </div>
        
        <!-- Mobile Menu Links -->
        <div class="py-2">
            <?php 
            $menu_items = [
                'home' => '#',
                'services' => '#services',
                'about' => '#about',
                'portfolio' => '#portfolio',
                'contact' => '#contact'
            ];
            foreach ($menu_items as $key => $link): 
            ?>
            <a href="<?php echo $link; ?>" class="mobile-menu-item block text-nile-dark hover:text-nile-primary hover:bg-blue-50 py-2.5 px-6 transition-all duration-300 border-l-4 border-transparent hover:border-nile-primary" style="transform: translateX(-10px); opacity: 0; animation: slideInMobile 0.4s ease forwards;">
                <?php echo translate($key); ?>
            </a>
            <?php endforeach; ?>
            
            <!-- Get Started Button -->
            <div class="px-6 mt-3">
                <a href="#contact" class="inline-block w-full bg-nile-primary hover:bg-nile-secondary text-white font-semibold py-2.5 px-8 rounded-lg transition duration-300 shadow-md hover:shadow-lg text-center transform hover:scale-105 transition-all duration-300">
                    <?php echo translate('get_started'); ?>
                </a>
            </div>
            
            <!-- Mobile Language Switcher -->
            <div class="mt-4 px-6 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2"><?php echo translate('language'); ?>:</p>
                <div class="flex flex-nowrap gap-1.5 overflow-x-auto pb-1">
                    <?php foreach (['en', 'sw', 'ar'] as $lang_code): ?>
                        <a href="?lang=<?php echo $lang_code; ?>" class="mobile-lang-btn flex-shrink-0 px-3 py-1.5 text-xs rounded-lg <?php echo getCurrentLanguage() === $lang_code ? 'bg-nile-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition-all duration-300 whitespace-nowrap transform hover:scale-105">
                            <?php echo getLanguageFlag($lang_code); ?> <?php echo $lang_code === 'en' ? 'EN' : ($lang_code === 'sw' ? 'SW' : 'AR'); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mobile menu animations */
    @keyframes slideInMobile {
        0% {
            transform: translateX(-15px);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .mobile-menu-item {
        animation: slideInMobile 0.4s ease forwards;
    }
    
    .mobile-menu-item:nth-child(1) { animation-delay: 0.05s; }
    .mobile-menu-item:nth-child(2) { animation-delay: 0.10s; }
    .mobile-menu-item:nth-child(3) { animation-delay: 0.15s; }
    .mobile-menu-item:nth-child(4) { animation-delay: 0.20s; }
    .mobile-menu-item:nth-child(5) { animation-delay: 0.25s; }
    
    .mobile-lang-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .mobile-lang-btn:hover {
        transform: scale(1.05);
    }
    
    #mobile-menu-container {
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    #mobile-overlay {
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenuContainer = document.getElementById('mobile-menu-container');
    const mobileOverlay = document.getElementById('mobile-overlay');
    const bar1 = document.getElementById('menu-bar-1');
    const bar2 = document.getElementById('menu-bar-2');
    const bar3 = document.getElementById('menu-bar-3');
    let isMenuOpen = false;
    
    const isRTL = '<?php echo getDirection(); ?>' === 'rtl';
    const slideOut = isRTL ? 'translateX(100%)' : 'translateX(-100%)';
    const slideIn = 'translateX(0)';

    function openMenu() {
        isMenuOpen = true;
        mobileMenuContainer.classList.remove('hidden');
        mobileOverlay.classList.remove('hidden');
        
        // Reset animations for menu items
        document.querySelectorAll('.mobile-menu-item').forEach((item, index) => {
            item.style.animation = 'none';
            item.offsetHeight;
            item.style.animation = `slideInMobile 0.4s ease forwards ${index * 0.05 + 0.05}s`;
        });
        
        requestAnimationFrame(() => {
            mobileMenuContainer.style.transform = slideIn;
            mobileOverlay.style.opacity = '1';
        });
        
        // Animate hamburger to X
        bar1.style.transform = 'rotate(45deg)';
        bar1.style.top = '13px';
        bar2.style.opacity = '0';
        bar2.style.transform = 'scaleX(0)';
        bar3.style.transform = 'rotate(-45deg)';
        bar3.style.bottom = '13px';
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        isMenuOpen = false;
        mobileMenuContainer.style.transform = slideOut;
        mobileOverlay.style.opacity = '0';
        
        setTimeout(() => {
            mobileMenuContainer.classList.add('hidden');
            mobileOverlay.classList.add('hidden');
        }, 400);
        
        // Reset hamburger
        bar1.style.transform = 'rotate(0deg)';
        bar1.style.top = '5px';
        bar2.style.opacity = '1';
        bar2.style.transform = 'scaleX(1)';
        bar3.style.transform = 'rotate(0deg)';
        bar3.style.bottom = '5px';
        document.body.style.overflow = '';
    }

    // Toggle menu with animation
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        if (isMenuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Close menu on overlay click
    mobileOverlay?.addEventListener('click', closeMenu);

    // Close menu on link click (smooth)
    document.querySelectorAll('#mobile-menu-container a:not(.mobile-lang-btn)').forEach(link => {
        link.addEventListener('click', function(e) {
            // Check if it's a section link
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    closeMenu();
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 400);
                }
            } else {
                closeMenu();
            }
        });
    });

    // Close menu on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isMenuOpen) closeMenu();
    });

    // Handle resize - close menu on desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024 && isMenuOpen) closeMenu();
    });
</script>