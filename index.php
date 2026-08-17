<?php 
// Include translation system
include_once 'includes/translations.php';
// Include header
include 'layout/header.php'; 
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-nile-primary to-nile-secondary text-white py-20 md:py-32 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-300 rounded-full translate-x-1/2 translate-y-1/2"></div>
    </div>
    <div class="container-custom relative z-10">
        <div class="max-w-3xl">
            <div class="flex items-center space-x-2 mb-4">
                <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-1 rounded-full text-sm font-semibold">
                    <?php echo translate('hero_badge'); ?>
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                <?php echo translate('hero_title'); ?><br>
                <span class="text-blue-200"><?php echo translate('hero_title_highlight'); ?></span>
            </h1>
            <p class="text-lg md:text-xl text-blue-100 mb-8 leading-relaxed max-w-2xl">
                <?php echo translate('hero_subtitle'); ?>
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="#contact" class="bg-white text-nile-primary hover:bg-blue-50 font-bold py-3 px-8 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                    <?php echo translate('hero_cta_primary'); ?>
                </a>
                <a href="#services" class="border-2 border-white text-white hover:bg-white hover:text-nile-primary font-semibold py-3 px-8 rounded-lg transition duration-300">
                    <?php echo translate('hero_cta_secondary'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-16 md:py-24 bg-white">
    <div class="container-custom">
        <div class="text-center mb-12">
            <span class="text-nile-secondary font-semibold text-sm uppercase tracking-wider"><?php echo translate('services_expertise'); ?></span>
            <h2 class="text-3xl md:text-4xl font-bold text-nile-primary mb-4"><?php echo translate('services_title'); ?></h2>
            <p class="text-gray-600 max-w-2xl mx-auto"><?php echo translate('services_subtitle'); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 1. Web Development -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('web_dev_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('web_dev_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('web_dev_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('web_dev_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('web_dev_feature_3'); ?>
                    </li>
                </ul>
            </div>

            <!-- 2. Software Solutions -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('software_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('software_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('software_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('software_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('software_feature_3'); ?>
                    </li>
                </ul>
            </div>

            <!-- 3. Cloud Services -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('cloud_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('cloud_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('cloud_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('cloud_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('cloud_feature_3'); ?>
                    </li>
                </ul>
            </div>

            <!-- 4. IT Security -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('security_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('security_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('security_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('security_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('security_feature_3'); ?>
                    </li>
                </ul>
            </div>

            <!-- 5. Tech Support -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L16.95 7.05m4.95 4.95l-1.414 1.414M6.343 6.343L4.93 7.757M7.757 4.93L6.343 6.343M12 6a6 6 0 016 6 6 6 0 01-6 6 6 6 0 01-6-6 6 6 0 016-6z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('support_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('support_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('support_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('support_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('support_feature_3'); ?>
                    </li>
                </ul>
            </div>

            <!-- 6. Digital Transformation -->
            <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border-t-4 border-nile-primary group">
                <div class="w-16 h-16 bg-nile-light rounded-lg flex items-center justify-center mb-4 group-hover:bg-nile-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-nile-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-nile-primary mb-3"><?php echo translate('digital_title'); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo translate('digital_desc'); ?></p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('digital_feature_1'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('digital_feature_2'); ?>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-nile-secondary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo translate('digital_feature_3'); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-16 md:py-24 bg-nile-light">
    <div class="container-custom">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-1/2">
                <span class="text-nile-secondary font-semibold text-sm uppercase tracking-wider"><?php echo translate('about_badge'); ?></span>
                <h2 class="text-3xl md:text-4xl font-bold text-nile-primary mb-4"><?php echo translate('about_title'); ?></h2>
                <p class="text-gray-700 mb-4 leading-relaxed">
                    <?php echo translate('about_text_1'); ?>
                </p>
                <p class="text-gray-700 mb-6 leading-relaxed">
                    <?php echo translate('about_text_2'); ?>
                </p>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <span class="block text-3xl font-bold text-nile-primary">50+</span>
                        <span class="text-gray-600 text-sm"><?php echo translate('about_stat_1'); ?></span>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <span class="block text-3xl font-bold text-nile-primary">98%</span>
                        <span class="text-gray-600 text-sm"><?php echo translate('about_stat_2'); ?></span>
                    </div>
                </div>
                <a href="#" class="bg-nile-primary hover:bg-nile-secondary text-white font-semibold py-3 px-8 rounded-lg inline-block transition duration-300 shadow-md hover:shadow-lg">
                    <?php echo translate('about_cta'); ?>
                </a>
            </div>
            <div class="lg:w-1/2">
                <div class="bg-white p-8 rounded-2xl shadow-2xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-nile-primary p-4 rounded-lg text-white text-center">
                            <div class="text-4xl mb-2">⚡</div>
                            <h4 class="font-semibold"><?php echo translate('about_feature_1'); ?></h4>
                        </div>
                        <div class="bg-nile-secondary p-4 rounded-lg text-white text-center">
                            <div class="text-4xl mb-2">🔒</div>
                            <h4 class="font-semibold"><?php echo translate('about_feature_2'); ?></h4>
                        </div>
                        <div class="bg-blue-400 p-4 rounded-lg text-white text-center">
                            <div class="text-4xl mb-2">📈</div>
                            <h4 class="font-semibold"><?php echo translate('about_feature_3'); ?></h4>
                        </div>
                        <div class="bg-nile-dark p-4 rounded-lg text-white text-center">
                            <div class="text-4xl mb-2">💡</div>
                            <h4 class="font-semibold"><?php echo translate('about_feature_4'); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-nile-primary to-nile-secondary text-white">
    <div class="container-custom text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4"><?php echo translate('cta_title'); ?></h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            <?php echo translate('cta_subtitle'); ?>
        </p>
        <a href="#contact" class="bg-white text-nile-primary hover:bg-blue-50 font-bold py-4 px-12 rounded-lg inline-block transition duration-300 shadow-lg hover:shadow-xl text-lg">
            <?php echo translate('cta_button'); ?>
        </a>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-16 md:py-24 bg-white">
    <div class="container-custom">
        <div class="text-center mb-12">
            <span class="text-nile-secondary font-semibold text-sm uppercase tracking-wider"><?php echo translate('contact_badge'); ?></span>
            <h2 class="text-3xl md:text-4xl font-bold text-nile-primary mb-4"><?php echo translate('contact_title'); ?></h2>
            <p class="text-gray-600 max-w-2xl mx-auto"><?php echo translate('contact_subtitle'); ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div>
                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2"><?php echo translate('contact_name'); ?></label>
                        <input type="text" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-nile-secondary focus:border-transparent" placeholder="John Doe">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2"><?php echo translate('contact_email'); ?></label>
                        <input type="email" id="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-nile-secondary focus:border-transparent" placeholder="john@example.com">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2"><?php echo translate('contact_message'); ?></label>
                        <textarea id="message" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-nile-secondary focus:border-transparent" placeholder="Tell us about your project..."></textarea>
                    </div>
                    <button type="submit" class="bg-nile-primary hover:bg-nile-secondary text-white font-bold py-3 px-8 rounded-lg w-full transition duration-300 shadow-md hover:shadow-lg">
                        <?php echo translate('contact_send'); ?>
                    </button>
                </form>
            </div>
            <div class="space-y-6">
                <div class="bg-nile-light p-6 rounded-xl">
                    <h3 class="text-xl font-bold text-nile-primary mb-4"><?php echo translate('contact_title'); ?></h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-nile-secondary flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-700"><?php echo translate('contact_address'); ?></p>
                                <p class="text-gray-600"><?php echo translate('contact_address_value'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-nile-secondary flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-700"><?php echo translate('contact_email_label'); ?></p>
                                <p class="text-gray-600"><?php echo translate('footer_email'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-nile-secondary flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-700"><?php echo translate('contact_phone_label'); ?></p>
                                <p class="text-gray-600"><?php echo translate('footer_phone'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-nile-primary p-6 rounded-xl text-white">
                    <h4 class="text-lg font-bold mb-2"><?php echo translate('contact_hours'); ?></h4>
                    <p class="text-blue-100"><?php echo translate('contact_hours_value'); ?></p>
                    <p class="text-blue-100"><?php echo translate('contact_hours_weekend'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>