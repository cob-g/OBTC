<?php
/**
 * 10 Days Weekly Challenge - Public Landing Page
 * 
 * Modern dark-themed landing page with:
 * - Stunning visual effects and gradients
 * - 3D trophy hero section
 * - Glassmorphism cards
 * - Testimonials carousel
 * 
 * Accessible without authentication.
 */

$root = dirname(__FILE__);
$GLOBALS['app_config'] = require $root . '/config/config.php';
require_once $root . '/app/helpers.php';

$page_title = '10 Days Weekly Challenge - Transform Your Body';

// Placeholder for coach's Messenger link (update with actual link)
$messenger_link = 'https://www.facebook.com/xynal.panganiban';

// Testimonials data - Add your past challengers' transformation images here
$testimonials = [
    [
        'name' => 'Maria Santos',
        'day1_image' => '/storage/uploads/testimonials/1.png',
        'day10_image' => '/storage/uploads/testimonials/2.png',
        'weight_lost' => '8.5 lbs',
        'quote' => 'I never thought I could see such amazing results in just 10 days! My coach was incredibly supportive throughout the journey.',
        'badge' => 'Challenge Graduate',
        'rating' => 5
    ],
    [
        'name' => 'Juan Dela Cruz',
        'day1_image' => '/storage/uploads/testimonials/4.png',
        'day10_image' => '/storage/uploads/testimonials/3.png',
        'weight_lost' => '12.3 lbs',
        'quote' => 'The daily accountability made all the difference. Best decision I ever made for my health!',
        'badge' => 'Top 3 Winner',
        'rating' => 5
    ],
    [
        'name' => 'Ana Reyes',
        'day1_image' => '/storage/uploads/testimonials/5.png',
        'day10_image' => '/storage/uploads/testimonials/6.jpeg',
        'weight_lost' => '6.8 lbs',
        'quote' => 'Simple, effective, and life-changing. The coach-guided approach removed all the guesswork.',
        'badge' => 'Challenge Graduate',
        'rating' => 5
    ],
    [
        'name' => 'Carlos Garcia',
        'day1_image' => '/storage/uploads/testimonials/5.png',
        'day10_image' => '/storage/uploads/testimonials/6.jpeg',
        'weight_lost' => '10.2 lbs',
        'quote' => 'Joined skeptical, finished as a believer. The results speak for themselves!',
        'badge' => '2x Champion',
        'rating' => 5
    ],
    [
        'name' => 'Patricia Lim',
        'day1_image' => '/storage/uploads/testimonials/1.png',
        'day10_image' => '/storage/uploads/testimonials/2.png',
        'weight_lost' => '9.1 lbs',
        'quote' => 'Already on my third challenge! This program has completely transformed my relationship with fitness.',
        'badge' => '3x Champion',
        'rating' => 5
    ],
    [
        'name' => 'Miguel Torres',
        'day1_image' => '/storage/uploads/testimonials/4.png',
        'day10_image' => '/storage/uploads/testimonials/3.png',
        'weight_lost' => '11.7 lbs',
        'quote' => 'The leaderboard competition kept me motivated every single day. Highly recommend!',
        'badge' => 'Top 3 Winner',
        'rating' => 5
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Join the 10 Days Weekly Challenge and transform your body with guided coaching, daily accountability, and proven results.">
    <title><?= h($page_title) ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#F97316',
                        'primary-dark': '#EA580C',
                        'accent': '#8B5CF6',
                        'accent-dark': '#7C3AED',
                        'dark': {
                            '900': '#0A0A0F',
                            '800': '#12121A',
                            '700': '#1A1A25',
                            '600': '#252532',
                            '500': '#32324A',
                        }
                    },
                }
            }
        }
    </script>
    <style>
        * { scroll-behavior: smooth; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #0A0A0F;
            overflow-x: hidden;
        }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #F97316, #FB923C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-text-purple {
            background: linear-gradient(135deg, #8B5CF6, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Hero Background Gradient - Darker Warm Orange to Purple */
        .hero-section {
            background: linear-gradient(135deg, 
                #7c2d12 0%, 
                #9a3412 10%,
                #c2410c 20%,
                #ea580c 35%,
                #9333ea 65%,
                #7c3aed 80%,
                #5b21b6 95%,
                #4c1d95 100%);
        }
        
        /* Hero to Dark Transition */
        .hero-transition {
            background: linear-gradient(180deg, 
                transparent 0%,
                rgba(10, 10, 15, 0.3) 20%,
                rgba(10, 10, 15, 0.6) 50%,
                rgba(10, 10, 15, 0.9) 80%,
                #0A0A0F 100%);
            pointer-events: none;
        }
        
        /* Glassmorphism Navigation */
        .glass-nav {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        /* Glass Stat Badge with gradient border */
        .glass-stat-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            position: relative;
        }
        
        .glass-stat-badge::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.5), rgba(168, 85, 247, 0.5));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        
        /* Hero Content Card */
        .hero-content-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        /* Trophy Glass Container */
        .trophy-glass {
            background: linear-gradient(145deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 100px rgba(249, 115, 22, 0.1),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }
        
        /* Verified Badge */
        .verified-badge {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.4);
        }
        
        /* Glass Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card-light {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Stats Card Glow */
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 30, 45, 0.8), rgba(20, 20, 35, 0.9));
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        /* Trophy Container - keeping for other sections */
        .trophy-container {
            background: linear-gradient(145deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }
        
        /* Journey Card */
        .journey-card {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.05));
            border: 1px solid rgba(249, 115, 22, 0.3);
        }
        
        .journey-card-alt {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(139, 92, 246, 0.05));
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        /* Glassmorphism Nav Logo */
        .glass-logo {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        /* Dark Section Cards */
        .dark-card {
            background: linear-gradient(145deg, rgba(30, 30, 45, 0.9), rgba(20, 20, 35, 0.95));
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }
        
        /* Testimonial Card Dark */
        .testimonial-card-dark {
            background: linear-gradient(180deg, rgba(30, 30, 45, 0.95), rgba(20, 20, 35, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* CTA Gradient */
        .cta-gradient {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 50%, #DC2626 100%);
        }
        
        /* Button Shine Effect */
        .btn-primary {
            background: linear-gradient(135deg, #F97316, #EA580C);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        /* Process Line */
        .process-line {
            background: repeating-linear-gradient(
                90deg,
                #F97316 0px,
                #F97316 8px,
                transparent 8px,
                transparent 16px
            );
            height: 2px;
        }
        
        /* Testimonial Card */
        .testimonial-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.9));
        }
        
        /* Scroll Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
        }
        
        .mobile-menu.open {
            transform: translateX(0);
        }
        
        /* Timeline Dot */
        .timeline-dot {
            width: 16px;
            height: 16px;
            background: #F97316;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.3);
        }
        
        /* Testimonials Slider */
        .testimonials-slider-wrapper {
            position: relative;
            overflow: hidden;
            padding: 2rem 0;
        }
        
        .testimonials-track {
            display: flex;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform;
        }
        
        .testimonial-slide {
            flex: 0 0 auto;
            padding: 0 16px;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .testimonial-slide .testimonial-inner {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            transform: scale(0.88);
            opacity: 0.6;
        }
        
        .testimonial-slide.active .testimonial-inner {
            transform: scale(1);
            opacity: 1;
        }
        
        .testimonial-slide.adjacent .testimonial-inner {
            transform: scale(0.92);
            opacity: 0.8;
        }
        
        /* Testimonial Card Layout */
        .testimonial-card-wrapper {
            display: flex;
            align-items: stretch;
            gap: 16px;
            width: 480px;
            max-width: 90vw;
        }
        
        /* Testimonial Image - Fixed Size */
        .testimonial-image {
            flex-shrink: 0;
            width: 200px;
            height: 280px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .testimonial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Quote Card Style - Fixed Size */
        .quote-card {
            flex: 1;
            width: 200px;
            height: 280px;
            background: linear-gradient(145deg, rgba(35, 35, 45, 0.98), rgba(25, 25, 35, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .quote-icon {
            color: #F97316;
            font-size: 3rem;
            line-height: 0.8;
            font-family: Georgia, serif;
            margin-bottom: 8px;
        }
        
        .quote-title {
            color: white;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 8px;
        }
        
        .quote-text {
            color: rgba(161, 161, 170, 1);
            font-size: 0.8rem;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        
        .quote-author {
            color: rgba(113, 113, 122, 1);
            font-size: 0.75rem;
            font-style: italic;
        }
        
        @media (max-width: 640px) {
            .testimonial-card-wrapper {
                width: 340px;
                gap: 12px;
            }
            .testimonial-image {
                width: 150px;
                height: 220px;
            }
            .quote-card {
                width: 150px;
                height: 220px;
                padding: 14px;
            }
            .quote-icon {
                font-size: 2rem;
            }
            .quote-title {
                font-size: 0.85rem;
            }
            .quote-text {
                font-size: 0.7rem;
            }
        }
        
        /* Transformation Gallery */
        .transformation-pair {
            background: rgba(20, 20, 30, 0.6);
        }
        
        .transformation-pair:hover {
            box-shadow: 0 10px 40px rgba(249, 115, 22, 0.2);
        }
        
        /* Slider Navigation Dots */
        .slider-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 2rem;
        }
        
        .slider-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
        }
        
        .slider-dot:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        
        .slider-dot.active {
            background: #F97316;
            transform: scale(1.2);
        }
        
        /* Slider Arrows */
        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(30, 30, 40, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .slider-arrow:hover {
            background: rgba(249, 115, 22, 0.8);
            transform: translateY(-50%) scale(1.1);
        }
        
        .slider-arrow.prev {
            left: 1rem;
        }
        
        .slider-arrow.next {
            right: 1rem;
        }
        
        @media (min-width: 768px) {
            .slider-arrow.prev {
                left: 2rem;
            }
            .slider-arrow.next {
                right: 2rem;
            }
        }
        
        /* Hide scrollbar */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Timeline Animations */
        .timeline-item {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .timeline-item.active {
            opacity: 1;
            transform: translateX(0);
        }
        
        .timeline-line {
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, transparent, rgba(234, 88, 12, 0.5) 10%, rgba(234, 88, 12, 0.5) 90%, transparent);
        }
        
        @media (min-width: 1024px) {
            .timeline-line {
                left: 50%;
                transform: translateX(-1px);
            }
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased">

    <!-- ============================================
         NAVIGATION - Floating Glassmorphism
         ============================================ -->
    <nav class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[95%] max-w-6xl">
        <div class="glass-nav rounded-2xl px-4 sm:px-6 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="#home" class="flex items-center gap-2.5 group">
                    <div class="relative flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-primary via-orange-500 to-amber-500 shadow-lg transition-all duration-300 group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="trophyBodyNav" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FFB300"/>
                                    <stop offset="1" stop-color="#FF6F00"/>
                                </linearGradient>
                                <radialGradient id="trophyShineNav" cx="24" cy="12" r="20" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#FFFDE4" stop-opacity="0.8"/>
                                    <stop offset="1" stop-color="#FFB300" stop-opacity="0"/>
                                </radialGradient>
                            </defs>
                            <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="url(#trophyBodyNav)" stroke="#B45309" stroke-width="2"/>
                            <rect x="18" y="34" width="12" height="6" rx="2" fill="#B45309"/>
                            <rect x="16" y="40" width="16" height="4" rx="2" fill="#92400E"/>
                            <path d="M12 14c-4 0-6 4-6 8s2 8 6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                            <path d="M36 14c4 0 6 4 6 8s-2 8-6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                            <ellipse cx="24" cy="14" rx="8" ry="4" fill="url(#trophyShineNav)"/>
                            <polygon points="24,17 25.9,22.1 31.4,22.1 27,25.4 28.9,30.5 24,27.2 19.1,30.5 21,25.4 16.6,22.1 22.1,22.1" fill="#FFFDE4" stroke="#FFB300" stroke-width="1"/>
                        </svg>
                        <div class="absolute inset-0 rounded-lg bg-gradient-to-tr from-white/0 via-white/20 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                    <span class="text-base font-bold text-white">10 Days Challenge</span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center gap-6">
                    <a href="#home" class="text-sm font-medium text-white hover:text-primary transition-colors">Home</a>
                    <a href="#about" class="text-sm font-medium text-white/70 hover:text-primary transition-colors">About</a>
                    <a href="#how-it-works" class="text-sm font-medium text-white/70 hover:text-primary transition-colors">How It Works</a>
                    <a href="#journey" class="text-sm font-medium text-white/70 hover:text-primary transition-colors">Journey</a>
                    <a href="#testimonials" class="text-sm font-medium text-white/70 hover:text-primary transition-colors">Success Stories</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-2">
                    <a href="<?= h(url('/auth/login.php')) ?>" 
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white/90 hover:text-white bg-white/10 hover:bg-white/15 border border-white/20 rounded-full transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Coach Login
                    </a>
                    <a href="<?= h($messenger_link) ?>" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-primary inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white rounded-full shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 transition-all hover:scale-105">
                        <span>Join Now</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="lg:hidden flex items-center justify-center h-9 w-9 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm lg:hidden opacity-0 pointer-events-none transition-opacity duration-300" style="z-index: 9998;"></div>
    <div id="mobileMenu" class="mobile-menu fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-dark-800 shadow-2xl lg:hidden" style="z-index: 9999;">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-5 border-b border-white/10">
                <span class="text-lg font-bold text-white">Menu</span>
                <button id="closeMobileMenu" class="flex items-center justify-center h-10 w-10 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="space-y-1">
                    <a href="#home" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">Home</a>
                    <a href="#about" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">About</a>
                    <a href="#how-it-works" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">How It Works</a>
                    <a href="#journey" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">Journey</a>
                    <a href="#testimonials" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">Success Stories</a>
                </div>
            </div>
            <div class="p-4 border-t border-white/10">
                <a href="<?= h(url('/auth/login.php')) ?>" class="flex items-center justify-center gap-2 w-full px-4 py-3 text-base font-semibold text-zinc-300 hover:text-white bg-white/5 border border-white/10 rounded-xl transition-colors">
                    Coach Login
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================
         HERO SECTION - Exact Match to Reference
         ============================================ -->
    <section id="home" class="hero-section relative min-h-screen flex items-center overflow-hidden">
        <!-- Flowing Curved Lines -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <svg class="absolute w-full h-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice">
                <!-- Multiple flowing wave lines -->
                <path d="M-100 550 Q 200 350 500 400 Q 800 450 1100 300 Q 1400 150 1600 200" stroke="rgba(255,255,255,0.3)" stroke-width="2" fill="none"/>
                <path d="M-50 600 Q 250 400 550 450 Q 850 500 1150 350 Q 1450 200 1650 250" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" fill="none"/>
                <path d="M0 650 Q 300 450 600 500 Q 900 550 1200 400 Q 1500 250 1700 300" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none"/>
                <path d="M-150 500 Q 150 300 450 350 Q 750 400 1050 250 Q 1350 100 1550 150" stroke="rgba(255,255,255,0.35)" stroke-width="2.5" fill="none"/>
                <path d="M50 700 Q 350 500 650 550 Q 950 600 1250 450 Q 1550 300 1750 350" stroke="rgba(255,255,255,0.15)" stroke-width="1" fill="none"/>
            </svg>
        </div>
        
        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-16">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                
                <!-- Left: Trophy in Glass Container -->
                <div class="relative order-2 lg:order-1 flex justify-center lg:justify-start">
                    <div class="trophy-glass relative w-[300px] h-[300px] sm:w-[380px] sm:h-[380px] lg:w-[420px] lg:h-[420px] rounded-[2rem] flex items-center justify-center">
                        <!-- 3D Trophy SVG -->
                        <svg class="w-48 h-48 sm:w-60 sm:h-60 lg:w-72 lg:h-72 drop-shadow-2xl" viewBox="0 0 200 200" fill="none">
                            <!-- Trophy Base -->
                            <rect x="60" y="160" width="80" height="15" rx="4" fill="url(#trophyBase2)"/>
                            <rect x="75" y="145" width="50" height="20" rx="3" fill="url(#trophyStem2)"/>
                            
                            <!-- Trophy Cup -->
                            <path d="M45 50 L45 90 Q45 130 100 140 Q155 130 155 90 L155 50 Z" fill="url(#trophyCup2)"/>
                            
                            <!-- Trophy Handles -->
                            <path d="M45 60 Q20 60 20 90 Q20 110 45 110" stroke="url(#trophyHandle2)" stroke-width="8" fill="none" stroke-linecap="round"/>
                            <path d="M155 60 Q180 60 180 90 Q180 110 155 110" stroke="url(#trophyHandle2)" stroke-width="8" fill="none" stroke-linecap="round"/>
                            
                            <!-- Trophy Rim -->
                            <ellipse cx="100" cy="50" rx="55" ry="12" fill="url(#trophyRim2)"/>
                            
                            <!-- Star -->
                            <path d="M100 70 L105 85 L120 87 L109 97 L112 112 L100 105 L88 112 L91 97 L80 87 L95 85 Z" fill="#FFD700"/>
                            
                            <!-- Shine Effects -->
                            <ellipse cx="70" cy="80" rx="8" ry="15" fill="white" opacity="0.4"/>
                            <ellipse cx="130" cy="75" rx="5" ry="10" fill="white" opacity="0.2"/>
                            
                            <defs>
                                <linearGradient id="trophyCup2" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="50%" style="stop-color:#FFA500"/>
                                    <stop offset="100%" style="stop-color:#FF8C00"/>
                                </linearGradient>
                                <linearGradient id="trophyHandle2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#FFA500"/>
                                </linearGradient>
                                <linearGradient id="trophyRim2" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#FFE44D"/>
                                    <stop offset="50%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#FFE44D"/>
                                </linearGradient>
                                <linearGradient id="trophyBase2" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#8B7355"/>
                                    <stop offset="100%" style="stop-color:#5D4E37"/>
                                </linearGradient>
                                <linearGradient id="trophyStem2" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#B8860B"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
                
                <!-- Right: Content Card -->
                <div class="order-1 lg:order-2">
                    <div class="hero-content-card rounded-3xl p-6 sm:p-8">
                        <!-- Stats Badges Row -->
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <!-- 10 Days Badge -->
                            <div class="glass-stat-badge px-4 py-2.5 rounded-xl">
                                <div class="text-xl sm:text-2xl font-black text-white">10</div>
                                <div class="text-[10px] sm:text-xs text-white/60 font-medium uppercase tracking-wide">Days</div>
                            </div>
                            
                            <!-- 100% Guided Badge -->
                            <div class="glass-stat-badge px-4 py-2.5 rounded-xl">
                                <div class="text-xl sm:text-2xl font-black text-white">100%</div>
                                <div class="text-[10px] sm:text-xs text-white/60 font-medium uppercase tracking-wide">Guided</div>
                            </div>
                            
                            <!-- Top 10 Badge -->
                            <div class="glass-stat-badge px-4 py-2.5 rounded-xl">
                                <div class="text-xl sm:text-2xl font-black text-white">Top 10</div>
                                <div class="text-[10px] sm:text-xs text-white/60 font-medium uppercase tracking-wide">Leaders</div>
                            </div>
                            
                            <!-- Verified Badge -->
                            <div class="verified-badge flex items-center gap-1.5 px-3 py-2 rounded-full">
                                <svg class="h-4 w-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-semibold text-green-400">Verified Results</span>
                            </div>
                        </div>
                        
                        <!-- Main Headline -->
                        <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-black text-white leading-tight mb-4">
                            Transform Your Body<br>
                            in Just <span class="gradient-text">10 Days</span>
                        </h1>
                        
                        <!-- Subheadline -->
                        <p class="text-sm sm:text-base text-white/70 leading-relaxed mb-6">
                            Join hundreds of successful challengers in a <span class="text-white font-semibold">coach-guided fitness journey</span>. Track your progress daily, compete on the leaderboard, and achieve real results.
                        </p>
                        
                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="<?= h($messenger_link) ?>" 
                               target="_blank"
                               rel="noopener noreferrer"
                               class="btn-primary inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white rounded-full shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 transition-all hover:scale-105">
                                Start the Challenge
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                            <a href="#about" 
                               class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white/90 hover:text-white bg-white/10 hover:bg-white/15 border border-white/20 rounded-full transition-all">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transition gradient to dark section -->
        <div class="hero-transition absolute bottom-0 left-0 right-0 h-48"></div>
    </section>

    <!-- ============================================
         ABOUT SECTION
         ============================================ -->
    <section id="about" class="relative py-20 lg:py-32 overflow-hidden" style="background: linear-gradient(to bottom, #0A0A0F 0%, #1a0f1f 30%, #0f0a14 60%, #0A0A0F 100%);">
        <!-- Flowing wave lines continuation from hero -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-30">
            <svg class="absolute w-full h-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice">
                <path d="M-100 100 Q 200 180 500 150 Q 800 120 1100 200 Q 1400 280 1600 250" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" fill="none"/>
                <path d="M-50 150 Q 250 230 550 200 Q 850 170 1150 250 Q 1450 330 1650 300" stroke="rgba(255,255,255,0.1)" stroke-width="1" fill="none"/>
                <path d="M0 50 Q 300 130 600 100 Q 900 70 1200 150 Q 1500 230 1700 200" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" fill="none"/>
            </svg>
        </div>
        
        <!-- Background glow effects -->
        <div class="absolute inset-0">
            <div class="absolute w-[600px] h-[600px] -left-40 top-0 bg-purple-600/20 rounded-full blur-[150px]"></div>
            <div class="absolute w-[500px] h-[500px] right-0 bottom-0 bg-orange-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute w-[400px] h-[400px] left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-primary/10 rounded-full blur-[100px]"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">
                <!-- Left: Content -->
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-6">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-primary">What Is It?</span>
                    </div>
                    
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                        What is the <span class="gradient-text">10 Days Weekly Challenge</span>?
                    </h2>
                    
                    <p class="text-lg text-zinc-400 leading-relaxed mb-6">
                        The 10 Days Weekly Challenge is a <span class="text-white font-semibold">professional coach-guided fitness program</span> that delivers remarkable, measurable results in just 10 days.
                    </p>
                    
                    <p class="text-zinc-400 leading-relaxed mb-8">
                        Your dedicated challenge coach tracks your <span class="text-white">BMI progress</span> and provides daily accountability. No complicated meal plans or workout routines – <span class="text-primary font-semibold">just consistency and commitment</span>. Show up every day and give your best!
                    </p>
                    
                    <!-- Features List -->
                    
                </div>
                
                <!-- Right: Process Cards -->
                <div class="reveal space-y-4" style="animation-delay: 0.2s">
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white font-bold">1</div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Registration Period</h4>
                                <p class="text-sm text-zinc-400">Your coach opens registration, you share initial details.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white font-bold">2</div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Daily Weigh-ins</h4>
                                <p class="text-sm text-zinc-400">Track your weight daily with photo evidence.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary flex items-center justify-center text-white font-bold">3</div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Progress Tracking</h4>
                                <p class="text-sm text-zinc-400">Your coach calculates BMI and tracks your progress.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-accent flex items-center justify-center text-white font-bold">4</div>
                            <div>
                                <h4 class="font-bold text-white mb-1">Results & Rankings</h4>
                                <p class="text-sm text-zinc-400">Final leaderboard reveals Top 10, you have amazing results.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         HOW IT WORKS SECTION
         ============================================ -->
    <section id="how-it-works" class="relative py-20 lg:py-32 overflow-hidden" style="background: linear-gradient(to bottom, #0A0A0F 0%, #12071a 40%, #0d0815 70%, #0A0A0F 100%);">
        <!-- Flowing wave lines - reversed direction from about section -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-25">
            <svg class="absolute w-full h-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice">
                <path d="M1600 150 Q 1300 230 1000 200 Q 700 170 400 250 Q 100 330 -100 300" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" fill="none"/>
                <path d="M1650 200 Q 1350 280 1050 250 Q 750 220 450 300 Q 150 380 -50 350" stroke="rgba(255,255,255,0.1)" stroke-width="1" fill="none"/>
                <path d="M1700 100 Q 1400 180 1100 150 Q 800 120 500 200 Q 200 280 0 250" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" fill="none"/>
            </svg>
        </div>
        
        <!-- Background glow effects - different positioning -->
        <div class="absolute inset-0">
            <div class="absolute w-[550px] h-[550px] right-0 top-0 bg-orange-600/15 rounded-full blur-[140px]"></div>
            <div class="absolute w-[450px] h-[450px] -left-20 bottom-0 bg-purple-600/20 rounded-full blur-[130px]"></div>
            <div class="absolute w-[350px] h-[350px] right-1/3 top-1/3 bg-accent/10 rounded-full blur-[90px]"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-6">
                    <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-sm font-semibold text-primary">Simple Process</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                    How It <span class="gradient-text">Works</span>
                </h2>
                <p class="text-lg text-zinc-400">
                    Getting started is easy. Follow these simple steps and let your coach guide you to success!
                </p>
            </div>
            
            <!-- Feature Cards Grid -->
            <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                <!-- Card 1: Dedicated Coach -->
                <div class="reveal glass-card rounded-3xl p-8 hover:border-primary/40 transition-all hover:-translate-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/20 border border-primary/30 mb-6">
                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Personal Support</span>
                    </div>
                    
                    <!-- Coach Icon -->
                    <div class="relative w-20 h-20 mb-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/30 to-orange-500/30 rounded-2xl blur-lg"></div>
                        <div class="relative w-full h-full rounded-2xl bg-gradient-to-br from-primary/20 to-orange-500/20 border border-primary/30 flex items-center justify-center">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-white mb-3">Dedicated Coach</h3>
                    <p class="text-zinc-400 leading-relaxed">
                        Your personal coach tracks your progress, provides daily accountability, and keeps you motivated throughout the entire challenge.
                    </p>
                </div>
                
                <!-- Card 2: Track Progress -->
                <div class="reveal glass-card rounded-3xl p-8 hover:border-primary/40 transition-all hover:-translate-y-1" style="animation-delay: 0.1s">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/20 border border-primary/30 mb-6">
                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Daily Monitoring</span>
                    </div>
                    
                    <!-- Progress Chart Illustration -->
                    <div class="relative w-full h-32 mb-6 rounded-xl bg-gradient-to-br from-orange-950/40 to-primary/20 border border-primary/20 p-4 overflow-hidden">
                        <svg class="w-full h-full" viewBox="0 0 200 80" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#ea580c;stop-opacity:0.8"/>
                                    <stop offset="50%" style="stop-color:#fb923c;stop-opacity:0.9"/>
                                    <stop offset="100%" style="stop-color:#ea580c;stop-opacity:0.8"/>
                                </linearGradient>
                            </defs>
                            <path d="M 0 60 L 20 55 L 40 58 L 60 50 L 80 45 L 100 40 L 120 35 L 140 30 L 160 25 L 180 22 L 200 20" 
                                  stroke="url(#chartGradient)" 
                                  stroke-width="3" 
                                  fill="none" 
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                            <!-- Dots on the line -->
                            <circle cx="0" cy="60" r="2.5" fill="#ea580c"/>
                            <circle cx="40" cy="58" r="2.5" fill="#fb923c"/>
                            <circle cx="80" cy="45" r="2.5" fill="#ea580c"/>
                            <circle cx="120" cy="35" r="2.5" fill="#fb923c"/>
                            <circle cx="160" cy="25" r="2.5" fill="#ea580c"/>
                            <circle cx="200" cy="20" r="3" fill="#fb923c"/>
                        </svg>
                        <div class="absolute bottom-2 right-2 text-xs font-bold text-primary/60">10 Days</div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-white mb-3">Track Progress</h3>
                    <p class="text-zinc-400 leading-relaxed">
                        Monitor your daily weight changes with photo evidence. See your transformation unfold with real-time tracking and visual progress reports.
                    </p>
                </div>
                
                <!-- Card 3: Compete & Win -->
                <div class="reveal glass-card rounded-3xl p-8 hover:border-primary/40 transition-all hover:-translate-y-1" style="animation-delay: 0.2s">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/20 border border-primary/30 mb-6">
                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Leaderboard</span>
                    </div>
                    
                    <!-- Podium Illustration -->
                    <div class="relative w-full h-32 mb-6 flex items-end justify-center gap-2">
                        <!-- 2nd Place -->
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-10 h-10 rounded-lg bg-zinc-700/50 border border-zinc-600/50 flex items-center justify-center">
                                <span class="text-lg font-black text-zinc-400">2</span>
                            </div>
                            <div class="w-16 h-16 rounded-t-lg bg-gradient-to-b from-zinc-700/40 to-zinc-800/40 border border-zinc-600/30"></div>
                        </div>
                        
                        <!-- 1st Place -->
                        <div class="flex flex-col items-center gap-1">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-primary to-orange-500 rounded-xl blur-md opacity-50"></div>
                                <div class="relative w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-orange-500 border-2 border-primary flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="w-16 h-24 rounded-t-lg bg-gradient-to-b from-primary/30 to-orange-500/20 border border-primary/30"></div>
                        </div>
                        
                        <!-- 3rd Place -->
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-10 h-10 rounded-lg bg-amber-900/40 border border-amber-700/40 flex items-center justify-center">
                                <span class="text-lg font-black text-amber-600">3</span>
                            </div>
                            <div class="w-16 h-12 rounded-t-lg bg-gradient-to-b from-amber-900/30 to-amber-950/30 border border-amber-800/30"></div>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-white mb-3">Compete & Win</h3>
                    <p class="text-zinc-400 leading-relaxed">
                        Join the leaderboard and compete with other challengers. Top 10 performers get special recognition and amazing prizes!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         JOURNEY SECTION - Animated Timeline
         ============================================ -->
    <section id="journey" class="relative py-20 lg:py-32 overflow-hidden" style="background: linear-gradient(to bottom, #0A0A0F 0%, #0d0a15 25%, #150a1a 50%, #0d0a15 75%, #0A0A0F 100%);">
        <!-- Flowing wave lines - diagonal pattern -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
            <svg class="absolute w-full h-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice">
                <path d="M-100 300 Q 400 200 700 350 Q 1000 500 1600 400" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" fill="none"/>
                <path d="M-100 500 Q 400 400 700 550 Q 1000 700 1600 600" stroke="rgba(255,255,255,0.08)" stroke-width="1" fill="none"/>
            </svg>
        </div>
        
        <!-- Background glow effects - unique positioning -->
        <div class="absolute inset-0">
            <div class="absolute w-[500px] h-[500px] left-0 top-1/4 bg-accent/15 rounded-full blur-[140px]"></div>
            <div class="absolute w-[600px] h-[600px] right-0 top-1/2 bg-primary/10 rounded-full blur-[150px]"></div>
            <div class="absolute w-[400px] h-[400px] left-1/3 bottom-0 bg-purple-600/15 rounded-full blur-[120px]"></div>
        </div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-20 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-6">
                    <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-semibold text-primary">10-Day Journey</span>
                </div>
                
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                    Your Path to <span class="gradient-text">Transformation</span>
                </h2>
                
                <p class="text-lg text-zinc-400 max-w-2xl mx-auto">
                    Follow this proven timeline from day one to your final transformation. Each milestone brings you closer to your goal.
                </p>
            </div>
            
            <!-- Vertical Timeline -->
            <div class="relative">
                <!-- Timeline Line -->
                <div class="timeline-line"></div>
                
                <!-- Timeline Items -->
                <div class="space-y-12 lg:space-y-16">
                    <!-- Day 1 -->
                    <div class="timeline-item relative">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            <!-- Timeline Dot -->
                            <div class="absolute left-[19px] lg:left-1/2 lg:-translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-primary to-orange-500 border-4 border-dark-900 flex items-center justify-center shadow-lg shadow-primary/30 z-10">
                                <span class="text-white font-bold text-sm">1</span>
                            </div>
                            
                            <!-- Content -->
                            <div class="lg:w-1/2 lg:pr-16 lg:text-right ml-16 lg:ml-0">
                                <div class="glass-card rounded-2xl p-6 hover:border-primary/40 transition-all">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/20 border border-primary/30 mb-3">
                                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Day 1</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3">Kickoff Day 🚀</h3>
                                    <p class="text-zinc-400 leading-relaxed">
                                        Record your baseline weight with photo evidence. Your coach monitors your starting point and sets you up for success!
                                    </p>
                                </div>
                            </div>
                            
                            <div class="hidden lg:block lg:w-1/2"></div>
                        </div>
                    </div>
                    
                    <!-- Days 2-4 -->
                    <div class="timeline-item relative">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            <!-- Timeline Dot -->
                            <div class="absolute left-[19px] lg:left-1/2 lg:-translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-accent to-purple-500 border-4 border-dark-900 flex items-center justify-center shadow-lg shadow-accent/30 z-10">
                                <span class="text-white font-bold text-sm">2-4</span>
                            </div>
                            
                            <div class="hidden lg:block lg:w-1/2"></div>
                            
                            <!-- Content -->
                            <div class="lg:w-1/2 lg:pl-16 ml-16 lg:ml-0">
                                <div class="glass-card rounded-2xl p-6 hover:border-accent/40 transition-all">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-accent/20 border border-accent/30 mb-3">
                                        <span class="text-xs font-bold text-accent uppercase tracking-wide">Days 2-4</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3">Build Momentum 💪</h3>
                                    <p class="text-zinc-400 leading-relaxed">
                                        Daily weigh-ins continue. Stay consistent with your routine and track your early progress. The habits start forming!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Days 5-9 -->
                    <div class="timeline-item relative">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            <!-- Timeline Dot -->
                            <div class="absolute left-[19px] lg:left-1/2 lg:-translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-primary to-orange-500 border-4 border-dark-900 flex items-center justify-center shadow-lg shadow-primary/30 z-10">
                                <span class="text-white font-bold text-sm">5-9</span>
                            </div>
                            
                            <!-- Content -->
                            <div class="lg:w-1/2 lg:pr-16 lg:text-right ml-16 lg:ml-0">
                                <div class="glass-card rounded-2xl p-6 hover:border-primary/40 transition-all">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/20 border border-primary/30 mb-3">
                                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Days 5-9</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3">Push Through 🔥</h3>
                                    <p class="text-zinc-400 leading-relaxed">
                                        You're halfway there! This is when the real transformation happens. Keep your energy high and trust the process.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="hidden lg:block lg:w-1/2"></div>
                        </div>
                    </div>
                    
                    <!-- Day 10 -->
                    <div class="timeline-item relative">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                            <!-- Timeline Dot -->
                            <div class="absolute left-[19px] lg:left-1/2 lg:-translate-x-1/2 w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 border-4 border-dark-900 flex items-center justify-center shadow-xl shadow-amber-500/40 z-10">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            
                            <div class="hidden lg:block lg:w-1/2"></div>
                            
                            <!-- Content -->
                            <div class="lg:w-1/2 lg:pl-16 ml-16 lg:ml-0">
                                <div class="glass-card rounded-2xl p-6 hover:border-amber-500/40 transition-all bg-gradient-to-br from-amber-950/20 to-orange-950/20">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/20 border border-amber-500/30 mb-3">
                                        <span class="text-xs font-bold text-amber-400 uppercase tracking-wide">Day 10 - Finale</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3">Victory Day! 🏆</h3>
                                    <p class="text-zinc-400 leading-relaxed">
                                        Final weigh-in and celebration! See your remarkable transformation, leaderboard ranking, and earned rewards. You did it!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         TESTIMONIALS SECTION
         ============================================ -->
    <section id="testimonials" class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-b from-dark-800 via-dark-900 to-dark-800"></div>
        <div class="hero-glow-orange left-1/4 top-0 opacity-15"></div>
        <div class="hero-glow-purple right-0 bottom-1/4 opacity-20"></div>
        
        <div class="relative">
            <!-- Section Header -->
            <div class="text-center mb-20 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-6">
                    <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-semibold text-primary">Challenge Graduate</span>
                </div>
                
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                    Success <span class="gradient-text">Stories</span>
                </h2>
            
            </div>
            
            <!-- Testimonials Slider -->
            <div class="testimonials-slider-wrapper relative">
                <!-- Navigation Arrows -->
                <button id="sliderPrev" class="slider-arrow prev" aria-label="Previous testimonial">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="sliderNext" class="slider-arrow next" aria-label="Next testimonial">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <!-- Slider Track -->
                <div id="testimonialsTrack" class="testimonials-track">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="testimonial-slide" data-index="<?= $index ?>">
                        <div class="testimonial-inner">
                            <div class="testimonial-card-wrapper">
                                <!-- Large Image -->
                                <div class="testimonial-image">
                                    <img src="<?= strpos($testimonial['day10_image'], 'http') === 0 ? h($testimonial['day10_image']) : h(url($testimonial['day10_image'])) ?>" 
                                         alt="<?= h($testimonial['name']) ?>">
                                </div>
                                
                                <!-- Quote Card -->
                                <div class="quote-card">
                                    <div class="quote-icon">"</div>
                                    <h3 class="quote-title">
                                        <?= h($testimonial['name']) ?>: Lost <?= h($testimonial['weight_lost']) ?>!
                                    </h3>
                                    <?php if (!empty($testimonial['quote'])): ?>
                                    <p class="quote-text">
                                        <?= h(substr($testimonial['quote'], 0, 50)) ?><?= strlen($testimonial['quote']) > 50 ? '...' : '' ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="quote-author"><?= h($testimonial['name']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Dots Navigation -->
                <div id="sliderDots" class="slider-dots">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                    <button class="slider-dot<?= $index === 0 ? ' active' : '' ?>" data-index="<?= $index ?>" aria-label="Go to testimonial <?= $index + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Real Results Text -->
            <div class="text-center mt-12 mb-10 px-4 reveal">
                <h3 class="text-2xl sm:text-3xl font-black text-white tracking-wide">
                    REAL RESULTS, <span class="gradient-text">REAL PEOPLE</span>
                </h3>
            </div>
            
            <!-- Transformation Gallery Grid -->
            <div class="max-w-5xl mx-auto px-4 reveal">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <?php 
                    // Create transformation pairs for the gallery
                    $transformations = [];
                    foreach ($testimonials as $t) {
                        $transformations[] = [
                            'day1' => $t['day1_image'],
                            'day10' => $t['day10_image'],
                            'name' => $t['name']
                        ];
                    }
                    // Show 8 transformations (4 pairs x 2 images each = 8 slots, or repeat if less)
                    $galleryItems = array_slice($transformations, 0, 4);
                    while (count($galleryItems) < 4) {
                        $galleryItems = array_merge($galleryItems, array_slice($transformations, 0, 4 - count($galleryItems)));
                    }
                    ?>
                    <?php foreach ($galleryItems as $idx => $transform): ?>
                    <!-- Transformation Pair <?= $idx + 1 ?> -->
                    <div class="transformation-pair rounded-xl overflow-hidden border border-white/10 hover:border-primary/40 transition-all hover:scale-[1.02] cursor-pointer">
                        <div class="grid grid-cols-2 h-full">
                            <div class="relative">
                                <img src="<?= strpos($transform['day1'], 'http') === 0 ? h($transform['day1']) : h(url($transform['day1'])) ?>" 
                                     alt="Day 1" 
                                     class="w-full h-full object-cover" style="min-height: 140px;">
                                <div class="absolute bottom-1 left-1">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold text-white bg-zinc-900/80 backdrop-blur-sm rounded">DAY 1</span>
                                </div>
                            </div>
                            <div class="relative">
                                <img src="<?= strpos($transform['day10'], 'http') === 0 ? h($transform['day10']) : h(url($transform['day10'])) ?>" 
                                     alt="Day 10" 
                                     class="w-full h-full object-cover" style="min-height: 140px;">
                                <div class="absolute bottom-1 right-1">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold text-white bg-gradient-to-r from-primary to-orange-400 rounded">DAY 10</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Second Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-3 md:mt-4">
                    <?php 
                    // Get next 4 transformations for second row
                    $galleryItems2 = array_slice($transformations, 2, 4);
                    while (count($galleryItems2) < 4) {
                        $galleryItems2 = array_merge($galleryItems2, array_slice($transformations, 0, 4 - count($galleryItems2)));
                    }
                    ?>
                    <?php foreach ($galleryItems2 as $idx => $transform): ?>
                    <!-- Transformation Pair <?= $idx + 5 ?> -->
                    <div class="transformation-pair rounded-xl overflow-hidden border border-white/10 hover:border-primary/40 transition-all hover:scale-[1.02] cursor-pointer">
                        <div class="grid grid-cols-2 h-full">
                            <div class="relative">
                                <img src="<?= strpos($transform['day1'], 'http') === 0 ? h($transform['day1']) : h(url($transform['day1'])) ?>" 
                                     alt="Day 1" 
                                     class="w-full h-full object-cover" style="min-height: 140px;">
                                <div class="absolute bottom-1 left-1">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold text-white bg-zinc-900/80 backdrop-blur-sm rounded">DAY 1</span>
                                </div>
                            </div>
                            <div class="relative">
                                <img src="<?= strpos($transform['day10'], 'http') === 0 ? h($transform['day10']) : h(url($transform['day10'])) ?>" 
                                     alt="Day 10" 
                                     class="w-full h-full object-cover" style="min-height: 140px;">
                                <div class="absolute bottom-1 right-1">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold text-white bg-gradient-to-r from-primary to-orange-400 rounded">DAY 10</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         FINAL CTA SECTION
         ============================================ -->
    
    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="bg-dark-900 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Footer Content -->
            <div class="py-12 lg:py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                    <!-- Brand Column -->
                    <div class="lg:col-span-1">
                        <a href="#home" class="flex items-center gap-3 mb-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-primary via-orange-500 to-amber-500 shadow-lg">
                                <svg class="h-6 w-6 text-white" viewBox="0 0 48 48" fill="none">
                                    <path d="M24 4L28 16H40L30 24L34 36L24 28L14 36L18 24L8 16H20L24 4Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-white">10 Days Challenge</span>
                        </a>
                        <p class="text-sm text-zinc-400 leading-relaxed mb-5">
                            Transform your body in just 10 days with our coach-guided weight loss challenge. Real results, real accountability.
                        </p>
                        <!-- Social Icons -->
                        <div class="flex items-center gap-3">
                            <a href="<?= h($messenger_link) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-white/5 hover:bg-primary/20 border border-white/10 hover:border-primary/40 flex items-center justify-center text-zinc-400 hover:text-primary transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.908 1.438 5.503 3.688 7.2V22l3.405-1.867c.91.252 1.87.387 2.907.387 5.523 0 10-4.145 10-9.243S17.523 2 12 2zm1.06 12.446l-2.548-2.715-4.97 2.715 5.467-5.804 2.61 2.715 4.907-2.715-5.466 5.804z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-white/5 hover:bg-primary/20 border border-white/10 hover:border-primary/40 flex items-center justify-center text-zinc-400 hover:text-primary transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-white/5 hover:bg-primary/20 border border-white/10 hover:border-primary/40 flex items-center justify-center text-zinc-400 hover:text-primary transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5">Quick Links</h4>
                        <ul class="space-y-3">
                            <li><a href="#home" class="text-sm text-zinc-400 hover:text-primary transition-colors">Home</a></li>
                            <li><a href="#about" class="text-sm text-zinc-400 hover:text-primary transition-colors">About the Challenge</a></li>
                            <li><a href="#how-it-works" class="text-sm text-zinc-400 hover:text-primary transition-colors">How It Works</a></li>
                            <li><a href="#journey" class="text-sm text-zinc-400 hover:text-primary transition-colors">Your Journey</a></li>
                            <li><a href="#testimonials" class="text-sm text-zinc-400 hover:text-primary transition-colors">Success Stories</a></li>
                        </ul>
                    </div>
                    
                    <!-- For Coaches -->
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5">For Coaches</h4>
                        <ul class="space-y-3">
                            <li><a href="<?= h(url('/auth/login.php')) ?>" class="text-sm text-zinc-400 hover:text-primary transition-colors">Coach Login</a></li>
                            <li><a href="<?= h($messenger_link) ?>" target="_blank" class="text-sm text-zinc-400 hover:text-primary transition-colors">Become a Coach</a></li>
                            <li><a href="#" class="text-sm text-zinc-400 hover:text-primary transition-colors">Coach Resources</a></li>
                            <li><a href="#" class="text-sm text-zinc-400 hover:text-primary transition-colors">Support</a></li>
                        </ul>
                    </div>
                    
                    <!-- Get Started -->
                    <div>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-5">Get Started</h4>
                        <p class="text-sm text-zinc-400 mb-4">Ready to transform your body? Start your 10-day journey today!</p>
                        <a href="<?= h($messenger_link) ?>" 
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-primary to-orange-500 rounded-full hover:shadow-lg hover:shadow-primary/30 transition-all">
                            Join Now
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="py-6 border-t border-white/5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-zinc-500">&copy; <?= date('Y') ?> 10 Days Weekly Challenge. All rights reserved.</p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">Privacy Policy</a>
                        <a href="#" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">Terms of Service</a>
                        <a href="#" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">Contact</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeMobileMenuBtn = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        
        function openMobileMenu() {
            mobileMenu.classList.add('open');
            mobileMenuOverlay.classList.remove('opacity-0', 'pointer-events-none');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenuFn() {
            mobileMenu.classList.remove('open');
            mobileMenuOverlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.style.overflow = '';
        }
        
        mobileMenuBtn.addEventListener('click', openMobileMenu);
        closeMobileMenuBtn.addEventListener('click', closeMobileMenuFn);
        mobileMenuOverlay.addEventListener('click', closeMobileMenuFn);
        mobileNavLinks.forEach(link => link.addEventListener('click', closeMobileMenuFn));
        
        // Testimonials Slider
        const sliderTrack = document.getElementById('testimonialsTrack');
        const slides = document.querySelectorAll('.testimonial-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const prevBtn = document.getElementById('sliderPrev');
        const nextBtn = document.getElementById('sliderNext');
        
        let currentIndex = 0;
        let slideWidth = 0;
        let autoSlideInterval;
        const totalSlides = slides.length;
        
        function calculateSlideWidth() {
            if (slides.length > 0) {
                slideWidth = slides[0].offsetWidth;
            }
        }
        
        function updateSlider() {
            const containerWidth = sliderTrack.parentElement.offsetWidth;
            const offset = (containerWidth / 2) - (slideWidth / 2) - (currentIndex * slideWidth);
            sliderTrack.style.transform = `translateX(${offset}px)`;
            
            // Update slide classes
            slides.forEach((slide, index) => {
                slide.classList.remove('active', 'adjacent');
                if (index === currentIndex) {
                    slide.classList.add('active');
                } else if (index === currentIndex - 1 || index === currentIndex + 1) {
                    slide.classList.add('adjacent');
                }
            });
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }
        
        function goToSlide(index) {
            currentIndex = Math.max(0, Math.min(index, totalSlides - 1));
            updateSlider();
            resetAutoSlide();
        }
        
        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateSlider();
        }
        
        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
            updateSlider();
        }
        
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }
        
        // Event Listeners
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoSlide();
        });
        
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoSlide();
        });
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });
        
        // Touch/Swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        sliderTrack.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        sliderTrack.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
                resetAutoSlide();
            }
        }, { passive: true });
        
        // Initialize
        window.addEventListener('load', () => {
            calculateSlideWidth();
            updateSlider();
            startAutoSlide();
        });
        
        window.addEventListener('resize', () => {
            calculateSlideWidth();
            updateSlider();
        });
        
        // Scroll Reveal Animation
        const revealElements = document.querySelectorAll('.reveal');
        
        function checkReveal() {
            const windowHeight = window.innerHeight;
            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const revealPoint = 150;
                
                if (elementTop < windowHeight - revealPoint) {
                    element.classList.add('active');
                }
            });
        }
        
        // Timeline Animation
        const timelineItems = document.querySelectorAll('.timeline-item');
        
        function checkTimeline() {
            const windowHeight = window.innerHeight;
            timelineItems.forEach((item, index) => {
                const itemTop = item.getBoundingClientRect().top;
                const revealPoint = 100;
                
                if (itemTop < windowHeight - revealPoint) {
                    setTimeout(() => {
                        item.classList.add('active');
                    }, index * 150); // Stagger animation by 150ms
                }
            });
        }
        
        window.addEventListener('scroll', checkReveal);
        window.addEventListener('scroll', checkTimeline);
        window.addEventListener('load', checkReveal);
        window.addEventListener('load', checkTimeline);
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
