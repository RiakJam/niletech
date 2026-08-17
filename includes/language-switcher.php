<?php
$languages = getLanguages();
$current_lang = getCurrentLanguage();
?>
<div class="relative inline-block text-left z-[60]">
    <button type="button" id="language-dropdown" class="inline-flex items-center justify-center gap-2 px-3 py-1 text-sm font-medium text-white hover:text-blue-200 transition-colors focus:outline-none" aria-expanded="true">
        <span class="text-base"><?php echo getLanguageFlag($current_lang); ?></span>
        <span class="hidden sm:inline"><?php echo $languages[$current_lang]; ?></span>
        <svg class="w-4 h-4 transition-transform duration-300" id="lang-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    
    <!-- Dropdown Menu - Click only -->
    <div id="language-menu" class="absolute <?php echo getDirection() === 'rtl' ? 'left-0' : 'right-0'; ?> mt-2 w-48 rounded-lg shadow-2xl bg-white ring-1 ring-black ring-opacity-5 z-[60] transition-all duration-300 ease-out origin-top-right" style="transform: scale(0.95) translateY(-8px); opacity: 0; pointer-events: none; visibility: hidden;">
        <div class="py-1" role="menu" aria-orientation="vertical">
            <?php foreach ($languages as $lang_code => $lang_name): ?>
                <a href="#" data-lang="<?php echo $lang_code; ?>" class="language-option block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-nile-primary transition-all duration-300 ease-out <?php echo $current_lang === $lang_code ? 'bg-blue-50 text-nile-primary font-semibold' : ''; ?>" role="menuitem">
                    <span class="mr-2"><?php echo getLanguageFlag($lang_code); ?></span>
                    <?php echo $lang_name; ?>
                    <?php if ($current_lang === $lang_code): ?>
                        <span class="float-right text-nile-primary">✓</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
    /* Language dropdown styles */
    .language-option {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    /* Subtle shimmer effect on hover */
    .language-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(0, 68, 185, 0.08), transparent);
        transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .language-option:hover::before {
        left: 100%;
    }
    
    /* Active language styling */
    .language-option.active-lang {
        background: linear-gradient(90deg, #0044B9, #0087F2);
        color: white !important;
    }
    
    .language-option.active-lang::before {
        display: none;
    }
    
    /* Chevron - NO hover rotation, ONLY rotates when open */
    #lang-chevron {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: rotate(0deg);
    }
    
    #lang-chevron.rotated {
        transform: rotate(180deg);
    }
    
    /* Dropdown animation */
    #language-menu {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const langButton = document.getElementById('language-dropdown');
        const langMenu = document.getElementById('language-menu');
        const langChevron = document.getElementById('lang-chevron');
        const languageOptions = document.querySelectorAll('.language-option');
        let isLangOpen = false;

        // Click to toggle - works on both desktop and mobile
        langButton?.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isLangOpen) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        function openDropdown() {
            isLangOpen = true;
            langMenu.style.pointerEvents = 'auto';
            langMenu.style.visibility = 'visible';
            requestAnimationFrame(() => {
                langMenu.style.transform = 'scale(1) translateY(0)';
                langMenu.style.opacity = '1';
            });
            // ONLY rotate chevron when dropdown is OPEN
            if (langChevron) {
                langChevron.classList.add('rotated');
            }
        }

        function closeDropdown() {
            isLangOpen = false;
            langMenu.style.transform = 'scale(0.95) translateY(-8px)';
            langMenu.style.opacity = '0';
            setTimeout(() => {
                langMenu.style.pointerEvents = 'none';
                langMenu.style.visibility = 'hidden';
            }, 300);
            // ONLY reset chevron when dropdown is CLOSED
            if (langChevron) {
                langChevron.classList.remove('rotated');
            }
        }

        // Handle language selection
        languageOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const langCode = this.dataset.lang;
                if (langCode !== '<?php echo getCurrentLanguage(); ?>') {
                    // Add a small delay for visual feedback
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        window.location.href = '?lang=' + langCode;
                    }, 150);
                } else {
                    closeDropdown();
                }
            });
            
            // Hover effect with smooth transition
            option.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px) scale(1.02)';
                this.style.boxShadow = '0 2px 8px rgba(0,68,185,0.1)';
            });
            
            option.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0) scale(1)';
                this.style.boxShadow = 'none';
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (isLangOpen && !langMenu.contains(e.target) && !langButton.contains(e.target)) {
                closeDropdown();
            }
        });

        // Prevent closing when clicking inside the menu
        langMenu?.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Close dropdown on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isLangOpen) {
                closeDropdown();
            }
        });
    });
</script>