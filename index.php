<?php
// Include translation system
include_once 'includes/translations.php';
// Include header
include 'layout/header.php';
?>

<!-- Hero Section -->
<section class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-nile-primary via-nile-primary to-nile-secondary">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-r from-nile-primary/90 to-nile-primary/70"></div>
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('images/hero-splash.jpg'); opacity: 0.10;"></div>
    </div>

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white/5 rounded-full animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-400/5 rounded-full animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-white/5 rounded-full animate-pulse delay-500"></div>
    </div>

    <div class="container-custom relative z-10 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

            <!-- Left Content - Text & Badge -->
            <div class="text-white order-2 lg:order-1">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full mb-6 border border-white/10">
                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                    <span class="text-xs font-semibold tracking-wider uppercase"><?php echo translate('hero_badge'); ?></span>
                </div>

                <!-- Heading -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-extrabold leading-tight mb-6">
                    <?php echo translate('hero_title'); ?>
                    <br>
                    <span class="text-blue-200" style="color: #93c5fd;">
                        <?php echo translate('hero_title_highlight'); ?>
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-lg md:text-xl text-blue-100 mb-8 max-w-xl leading-relaxed">
                    <?php echo translate('hero_subtitle'); ?>
                </p>
            </div>

            <!-- Right Content - Image -->
            <div class="relative order-1 lg:order-2">
                <div class="relative aspect-square max-w-sm md:max-w-md lg:max-w-lg mx-auto">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-400/20 to-transparent rounded-3xl blur-3xl"></div>
                    <div class="relative w-full h-full rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-gradient-to-br from-nile-primary/30 to-nile-secondary/30">
                        <img src="images/Niletech.png" alt="Niletech" class="w-full h-full object-contain p-8 md:p-12">
                    </div>
                </div>
            </div>

            <!-- CTA Buttons & Stats -->
            <div class="col-span-1 lg:col-span-2 order-3">
                <!-- CTA Buttons - Side by side with spacing -->
                <div class="flex flex-wrap items-center gap-4 md:gap-6">
                    <a href="#services" class="group inline-flex items-center justify-center gap-2 md:gap-3 bg-white text-nile-primary hover:bg-blue-50 font-bold py-3.5 md:py-4 px-4 md:px-8 rounded-xl transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1.5 text-sm md:text-base flex-1 md:flex-none min-w-[140px]">
                        <?php echo translate('hero_cta_primary'); ?>
                        <svg class="w-4 h-4 md:w-5 md:h-5 transition-all duration-300 group-hover:translate-x-1.5 rtl:group-hover:-translate-x-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                    <a href="#about" class="group inline-flex items-center justify-center gap-2 border-2 border-white/30 text-white hover:bg-white hover:text-nile-primary font-semibold py-3.5 md:py-4 px-4 md:px-8 rounded-xl transition-all duration-300 hover:-translate-y-1.5 text-sm md:text-base flex-1 md:flex-none min-w-[140px]">
                        <?php echo translate('hero_cta_secondary'); ?>
                    </a>
                </div>

                <!-- Stats Section - With PROPER SPACING from CTA -->
                <!-- <div class="mt-12 pt-8 border-t-2 border-white/30">
                    <div class="flex flex-wrap items-center gap-8 md:gap-12">
                     
                        <div class="flex items-center gap-3 md:gap-4">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-blue-400/20 border border-white/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-base md:text-lg font-extrabold text-white">50+</span>
                            </div>
                            <div>
                                <p class="text-xs md:text-sm font-semibold text-white">Projects Delivered</p>
                                <p class="text-[10px] md:text-xs text-blue-200">Completed Successfully</p>
                            </div>
                        </div>
                        <div class="w-px h-10 md:h-12 bg-white/30"></div>
                        <div class="flex items-center gap-3 md:gap-4">
                            <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-blue-400/20 border border-white/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-base md:text-lg font-extrabold text-white">98%</span>
                            </div>
                            <div>
                                <p class="text-xs md:text-sm font-semibold text-white">Satisfaction Rate</p>
                                <p class="text-[10px] md:text-xs text-blue-200">Happy Clients</p>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</section>

<style>
    /* Pulse Animation for Background Elements */
    @keyframes pulse {

        0%,
        100% {
            opacity: 0.3;
            transform: scale(1);
        }

        50% {
            opacity: 0.6;
            transform: scale(1.05);
        }
    }

    .animate-pulse {
        animation: pulse 4s ease-in-out infinite;
    }

    .delay-1000 {
        animation-delay: 1s;
    }

    .delay-500 {
        animation-delay: 0.5s;
    }

    /* Image Container Hover */
    .relative .rounded-3xl {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .relative .rounded-3xl:hover {
        transform: scale(1.02);
        box-shadow: 0 30px 80px rgba(0, 68, 185, 0.3);
    }

    /* Button Hover Effects */
    .group:hover .group-hover\:translate-x-1\.5 {
        transform: translateX(6px);
    }

    /* RTL Arrow Direction Fix */
    .rtl .group-hover\:-translate-x-1\.5 {
        transform: translateX(-6px) !important;
    }

    .group:hover .group-hover\:-translate-y-1\.5 {
        transform: translateY(-6px);
    }

    /* Responsive Fixes */
    @media (max-width: 768px) {
        .container-custom {
            padding-left: 20px;
            padding-right: 20px;
        }

        /* CTA buttons spacing on mobile */
        .flex-wrap.items-center.gap-4 {
            gap: 12px;
        }

        .flex-1 {
            flex: 1 1 auto;
        }

        .min-w-\[140px\] {
            min-width: 140px;
        }

        /* Stats spacing on mobile */
        .flex-wrap.items-center.gap-8 {
            gap: 16px;
        }

        .w-px.h-10 {
            display: block !important;
        }

        /* Keep proper spacing on mobile */
        .mt-12 {
            margin-top: 28px;
        }

        .pt-8 {
            padding-top: 16px;
        }
    }

    @media (max-width: 1024px) {

        /* Image between content and CTA on mobile */
        .order-1 {
            order: 1;
        }

        .order-2 {
            order: 0;
        }

        .order-3 {
            order: 2;
        }
    }

    /* Add this to your existing styles */

    /* Fix CTA buttons height on mobile */
    @media (max-width: 768px) {

        /* Make CTA buttons taller on mobile */
        .flex-wrap.items-center.gap-4 .group {
            padding-top: 14px !important;
            padding-bottom: 14px !important;
            font-size: 15px !important;
            min-height: 52px !important;
        }

        /* Adjust the secondary button as well */
        .flex-wrap.items-center.gap-4 .group.border-2 {
            padding-top: 14px !important;
            padding-bottom: 14px !important;
        }
    }
</style>

<!-- Services Section -->
<section id="services" class="py-20 md:py-28 bg-white">
    <div class="container-custom">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-nile-secondary font-semibold text-sm uppercase tracking-wider"><?php echo translate('services_expertise'); ?></span>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-nile-primary mt-2 mb-4"><?php echo translate('services_title'); ?></h2>
            <p class="text-gray-600 text-lg"><?php echo translate('services_subtitle'); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $services = [
                ['key' => 'web', 'title' => 'web_dev_title', 'desc' => 'web_dev_desc', 'features' => ['web_dev_feature_1', 'web_dev_feature_2', 'web_dev_feature_3']],
                ['key' => 'software', 'title' => 'software_title', 'desc' => 'software_desc', 'features' => ['software_feature_1', 'software_feature_2', 'software_feature_3']],
                ['key' => 'cloud', 'title' => 'cloud_title', 'desc' => 'cloud_desc', 'features' => ['cloud_feature_1', 'cloud_feature_2', 'cloud_feature_3']],
                ['key' => 'security', 'title' => 'security_title', 'desc' => 'security_desc', 'features' => ['security_feature_1', 'security_feature_2', 'security_feature_3']],
                ['key' => 'support', 'title' => 'support_title', 'desc' => 'support_desc', 'features' => ['support_feature_1', 'support_feature_2', 'support_feature_3']],
                ['key' => 'digital', 'title' => 'digital_title', 'desc' => 'digital_desc', 'features' => ['digital_feature_1', 'digital_feature_2', 'digital_feature_3']],
            ];

            foreach ($services as $service):
            ?>
                <div class="group bg-white p-8 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-nile-primary/20 hover:-translate-y-2">
                    <!-- Icon Container - Background changes but icon STAYS VISIBLE -->
                    <div class="icon-container w-16 h-16 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center mx-auto mb-5 transition-all duration-500">
                        <?php if ($service['key'] === 'web'): ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        <?php elseif ($service['key'] === 'software'): ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        <?php elseif ($service['key'] === 'cloud'): ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        <?php elseif ($service['key'] === 'security'): ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        <?php elseif ($service['key'] === 'support'): ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636L16.95 7.05m4.95 4.95l-1.414 1.414M6.343 6.343L4.93 7.757M7.757 4.93L6.343 6.343M12 6a6 6 0 016 6 6 6 0 01-6 6 6 6 0 01-6-6 6 6 0 016-6z" />
                            </svg>
                        <?php else: ?>
                            <svg class="icon-svg w-8 h-8 text-nile-primary transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-bold text-nile-primary mb-3 text-center transition-colors duration-300"><?php echo translate($service['title']); ?></h3>
                    <p class="text-gray-600 mb-4 leading-relaxed text-center"><?php echo translate($service['desc']); ?></p>
                    <ul class="space-y-2 text-sm max-w-xs mx-auto">
                        <?php foreach ($service['features'] as $feature): ?>
                            <li class="flex items-center justify-center text-gray-600">
                                <svg class="w-4 h-4 text-nile-secondary mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <?php echo translate($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    /* Fix: Icons stay visible on hover */
    .group:hover .icon-container {
        background: linear-gradient(135deg, #0044B9, #001E72) !important;
        box-shadow: 0 10px 40px rgba(0, 68, 185, 0.25);
        transform: scale(1.05);
    }

    .group:hover .icon-svg {
        color: #ffffff !important;
    }

    /* Ensure smooth transition */
    .icon-container,
    .icon-svg {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- About Section - With CSS Override -->
<section id="about" class="py-20 md:py-28" style="background-color: #f8fafc;">
    <div class="container-custom">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-nile-secondary font-semibold text-sm uppercase tracking-wider"><?php echo translate('about_badge'); ?></span>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-nile-primary mt-2 mb-4"><?php echo translate('about_title'); ?></h2>
            <div class="w-20 h-1 bg-gradient-to-r from-nile-primary to-nile-secondary mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start">
            <!-- Left Content -->
            <div>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    <?php echo translate('about_text_1'); ?>
                </p>
                <p class="text-gray-600 leading-relaxed mb-8">
                    <?php echo translate('about_text_2'); ?>
                </p>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-nile-primary/20 border border-transparent">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-nile-primary/10 rounded-xl flex items-center justify-center group-hover:bg-nile-primary transition-colors duration-300">
                                <svg class="w-5 h-5 text-nile-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-3xl font-extrabold text-nile-primary">50+</span>
                        </div>
                        <span class="text-gray-600 text-sm font-medium"><?php echo translate('about_stat_1'); ?></span>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-nile-secondary/20 border border-transparent">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-nile-secondary/10 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-nile-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                                </svg>
                            </div>
                            <span class="text-3xl font-extrabold text-nile-secondary">98%</span>
                        </div>
                        <span class="text-gray-600 text-sm font-medium"><?php echo translate('about_stat_2'); ?></span>
                    </div>
                </div>

                <!-- Professional Learn More Button -->
                <a href="#contact" class="learn-more-btn inline-flex items-center gap-3 bg-nile-primary hover:bg-nile-secondary text-white font-semibold text-base py-4 px-10 rounded-xl transition-all duration-300 shadow-lg hover:shadow-2xl hover:-translate-y-1 group">
                    <span><?php echo translate('about_cta'); ?></span>
                    <svg class="w-5 h-5 transition-all duration-300 group-hover:translate-x-1 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <!-- Right Content - Feature Cards with Animations -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Feature 1 -->
                <div class="feature-card p-6 rounded-2xl text-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-[1.02] group" style="background: linear-gradient(135deg, #0044B9, #001E72);">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:bg-white/30 group-hover:scale-110">
                        <svg class="w-7 h-7 text-white transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg mb-1" style="color: #ffffff;"><?php echo translate('about_feature_1'); ?></h4>
                    <p class="text-sm" style="color: #93c5fd;">Agile Delivery</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card p-6 rounded-2xl text-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-[1.02] group" style="background: linear-gradient(135deg, #1a5276, #0e2f4f);">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:bg-white/30 group-hover:scale-110">
                        <svg class="w-7 h-7 text-white transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg mb-1" style="color: #ffffff;"><?php echo translate('about_feature_2'); ?></h4>
                    <p class="text-sm" style="color: #93c5fd;">Enterprise Security</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card p-6 rounded-2xl text-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-[1.02] group" style="background: linear-gradient(135deg, #0087F2, #0056b3);">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:bg-white/30 group-hover:scale-110">
                        <svg class="w-7 h-7 text-white transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg mb-1" style="color: #ffffff;"><?php echo translate('about_feature_3'); ?></h4>
                    <p class="text-sm" style="color: #93c5fd;">Scalable Solutions</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card p-6 rounded-2xl text-center shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-[1.02] group" style="background: linear-gradient(135deg, #1c2833, #0d1419);">
                    <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:bg-white/30 group-hover:scale-110">
                        <svg class="w-7 h-7 text-white transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-lg mb-1" style="color: #ffffff;"><?php echo translate('about_feature_4'); ?></h4>
                    <p class="text-sm" style="color: #93c5fd;">Innovation Driven</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Force dark backgrounds on feature cards */
    .feature-card {
        background: #0044B9 !important;
        color: white !important;
        position: relative;
        overflow: hidden;
    }

    /* Subtle shine effect on feature cards */
    .feature-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.6s ease;
        pointer-events: none;
    }

    .feature-card:hover::before {
        opacity: 1;
    }

    .feature-card h4 {
        color: white !important;
    }

    .feature-card p {
        color: #93c5fd !important;
    }

    .feature-card svg {
        color: white !important;
    }

    .feature-card .bg-white\/20 {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    /* Professional Learn More Button */
    .learn-more-btn {
        position: relative;
        overflow: hidden;
        padding: 16px 40px !important;
        font-size: 16px !important;
        min-height: 58px;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Button shine effect */
    .learn-more-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
    }

    .learn-more-btn:hover::before {
        left: 100%;
    }

    /* Button pulse animation */
    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(0, 68, 185, 0.4);
        }

        50% {
            box-shadow: 0 0 0 15px rgba(0, 68, 185, 0);
        }
    }

    .learn-more-btn {
        animation: pulse 2s infinite;
    }

    .learn-more-btn:hover {
        animation: none;
    }

    /* Stats card hover effects */
    .stats-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    }
</style>
<!-- CTA Section -->
<section class="relative py-20 md:py-28 overflow-hidden">
    <!-- Niletech (1).png as Background - Force No Repeat -->
    <div class="absolute inset-0">
        <div class="absolute inset-0" style="background-image: url('images/Niletech (1).png'); background-repeat: no-repeat; background-position: center; background-size: cover;"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-nile-primary/85 to-nile-secondary/85"></div>
    </div>

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-20 w-72 h-72 bg-white/5 rounded-full animate-pulse"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-blue-400/5 rounded-full animate-pulse delay-1000"></div>
    </div>

    <div class="container-custom relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-4"><?php echo translate('cta_title'); ?></h2>
            <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto"><?php echo translate('cta_subtitle'); ?></p>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap justify-center gap-4 md:gap-6">
                <a href="mailto:<?php echo translate('footer_email'); ?>" class="group inline-flex items-center gap-2 md:gap-3 bg-white text-nile-primary hover:bg-blue-50 font-bold py-3.5 md:py-4 px-6 md:px-10 rounded-xl transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1.5 text-sm md:text-lg min-h-[52px] md:min-h-[60px]">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-nile-primary transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <?php echo translate('footer_email'); ?>
                </a>
                <a href="tel:+254712345678" class="group inline-flex items-center gap-2 md:gap-3 bg-white/10 backdrop-blur-sm border-2 border-white text-white hover:bg-white hover:text-nile-primary font-bold py-3.5 md:py-4 px-6 md:px-10 rounded-xl transition-all duration-300 hover:-translate-y-1.5 text-sm md:text-lg min-h-[52px] md:min-h-[60px]">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-white group-hover:text-nile-primary transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <?php echo formatPhoneNumber(translate('phone')); ?>
                </a>
                <a href="#" class="group inline-flex items-center gap-2 md:gap-3 bg-white text-nile-primary hover:bg-blue-50 font-bold py-3.5 md:py-4 px-6 md:px-10 rounded-xl transition-all duration-300 shadow-2xl hover:shadow-3xl hover:-translate-y-1.5 text-sm md:text-lg min-h-[52px] md:min-h-[60px]">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-nile-primary transition-all duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Get a Quote
                </a>
            </div>

            <!-- Trust Indicators -->
            <div class="mt-10 md:mt-12 pt-8 border-t border-white/20 flex flex-wrap justify-center gap-6 md:gap-12">
                <div class="flex items-center gap-2 text-blue-100">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm md:text-base">Response within 24 hours</span>
                </div>
                <div class="flex items-center gap-2 text-blue-100">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm md:text-base">Free consultation</span>
                </div>
                <div class="flex items-center gap-2 text-blue-100">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-sm md:text-base">Custom solutions</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'layout/footer.php'; ?>