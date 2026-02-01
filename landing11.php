<?php
/**
 * 10 Days Weekly Challenge - Public Landing Page
 * 
 * Professional light-themed landing page with:
 * - Sticky navigation with section links
 * - Testimonials section
 * - Modern, clean UI design
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
// To add your own testimonials:
// 1. Replace 'day1_image' with the client's Day 1 photo (local path like '/storage/uploads/testimonials/client1_day1.jpg')
// 2. Replace 'day10_image' with the client's Day 10 photo (local path like '/storage/uploads/testimonials/client1_day10.jpg')
// 3. Update 'name' with the client's name
// 4. Update 'weight_lost' with their total weight loss
// 5. Update 'quote' with their testimonial message (optional, can be empty)
// 6. Update 'badge' with their achievement (e.g., 'Challenge Graduate', 'Top 3 Winner', '3x Champion')
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
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Lexend', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        molten: '#E74B05',
                        pumpkin: '#F26E10',
                        indigo_bloom: '#683FB7',
                        brandy: '#722806'
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.6s ease-out forwards',
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                        'scale-in': 'scaleIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-15px)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        scaleIn: {
                            '0%': { opacity: '0', transform: 'scale(0.9)' },
                            '100%': { opacity: '1', transform: 'scale(1)' },
                        },
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Styles -->
    <style>
        html { scroll-behavior: smooth; }
        
        /* Gradient text for headings */
        .gradient-text {
            background: linear-gradient(135deg, #E74B05 0%, #F26E10 50%, #FF8C42 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Subtle grid pattern background */
        .grid-pattern {
            background-image: 
                linear-gradient(rgba(231, 75, 5, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 75, 5, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        
        /* Card hover effects */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(231, 75, 5, 0.15);
        }
        
        /* Button shine effect */
        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        .btn-shine::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }
        .btn-shine:hover::after {
            left: 100%;
        }
        
        /* Nav link active indicator */
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #E74B05, #F26E10);
            transition: all 0.3s ease;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        /* Testimonial card */
        .testimonial-card {
            transition: all 0.4s ease;
        }
        .testimonial-card:hover {
            transform: scale(1.02);
        }
        
        /* Floating animation delays */
        .float-delay-1 { animation-delay: 0s; }
        .float-delay-2 { animation-delay: -2s; }
        .float-delay-3 { animation-delay: -4s; }
        
        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Mobile menu */
        .mobile-menu {
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.open {
            transform: translateX(0);
        }
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-800 font-sans antialiased">

    <!-- ============================================
         STICKY NAVIGATION
         ============================================ -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-zinc-100 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <a href="#home" class="flex items-center gap-3 group">
                    <div class="flex h-10 w-10 lg:h-12 lg:w-12 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-pumpkin shadow-lg shadow-molten/20 transition-transform group-hover:scale-105 group-hover:rotate-3">
                        <svg class="h-6 w-6 lg:h-7 lg:w-7 text-white" viewBox="0 0 48 48" fill="none">
                            <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="currentColor" opacity="0.9"/>
                            <rect x="18" y="34" width="12" height="6" rx="2" fill="currentColor" opacity="0.7"/>
                            <rect x="16" y="40" width="16" height="4" rx="2" fill="currentColor" opacity="0.5"/>
                            <polygon points="24,14 25.5,18 30,18 26.5,21 28,25 24,22 20,25 21.5,21 18,18 22.5,18" fill="#FFD700"/>
                        </svg>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-lg lg:text-xl font-bold tracking-tight text-zinc-800">10 Days</span>
                        <span class="text-lg lg:text-xl font-bold tracking-tight text-molten"> Challenge</span>
                    </div>
                </a>
                
                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#home" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">Home</a>
                    <a href="#about" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">About</a>
                    <a href="#how-it-works" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">How It Works</a>
                    <a href="#journey" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">Journey</a>
                    <a href="#benefits" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">Benefits</a>
                    <a href="#testimonials" class="nav-link text-sm font-semibold text-zinc-600 hover:text-molten transition-colors">Success Stories</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    <a href="<?= h(url('/auth/login.php')) ?>" 
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-molten transition-all rounded-xl bg-orange-50 hover:bg-orange-100 hover:scale-105">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Coach Login
                    </a>
                    <a href="<?= h($messenger_link) ?>" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-shine inline-flex items-center gap-2 px-5 lg:px-6 py-2.5 lg:py-3 text-sm font-bold text-white bg-gradient-to-r from-molten to-pumpkin rounded-full shadow-lg shadow-molten/25 hover:shadow-xl hover:shadow-molten/30 transition-all hover:scale-105">
                        <span>Join Now</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="lg:hidden flex items-center justify-center h-10 w-10 rounded-xl text-zinc-600 hover:bg-orange-50 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Menu (Outside nav for proper z-index stacking) -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm lg:hidden opacity-0 pointer-events-none transition-opacity duration-300" style="z-index: 9998;"></div>
    <div id="mobileMenu" class="fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white shadow-2xl lg:hidden translate-x-full transition-transform duration-300 ease-out" style="z-index: 9999;">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-zinc-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-pumpkin">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 48 48" fill="none">
                            <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="currentColor" opacity="0.9"/>
                            <polygon points="24,14 25.5,18 30,18 26.5,21 28,25 24,22 20,25 21.5,21 18,18 22.5,18" fill="#FFD700"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-zinc-800">Menu</span>
                </div>
                <button id="closeMobileMenu" class="flex items-center justify-center h-10 w-10 rounded-xl text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto p-4">
                <div class="space-y-1">
                    <a href="#home" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Home
                    </a>
                    <a href="#about" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        About
                    </a>
                    <a href="#how-it-works" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        How It Works
                    </a>
                    <a href="#journey" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Journey
                    </a>
                    <a href="#benefits" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        Benefits
                    </a>
                    <a href="#testimonials" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-700 hover:text-molten hover:bg-orange-50 rounded-xl transition-colors">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Success Stories
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t border-zinc-100 bg-zinc-50">
                <a href="<?= h(url('/auth/login.php')) ?>" class="flex items-center justify-center gap-2 w-full px-4 py-3 text-base font-semibold text-zinc-600 hover:text-molten bg-white border border-zinc-200 rounded-xl transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Coach Login
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================
         HERO SECTION
         ============================================ -->
    <section id="home" class="relative min-h-screen flex items-center pt-20 lg:pt-24 pb-16 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 grid-pattern"></div>
        <div class="absolute top-20 right-0 w-96 h-96 bg-gradient-to-br from-orange-100 to-amber-50 rounded-full blur-3xl opacity-60 animate-float float-delay-1"></div>
        <div class="absolute bottom-20 left-0 w-80 h-80 bg-gradient-to-br from-orange-50 to-yellow-50 rounded-full blur-3xl opacity-50 animate-float float-delay-2"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Content Side -->
                <div class="text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-100 mb-6 animate-fade-in">
                        <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        <span class="text-sm font-semibold text-molten">New Challenge Starting Every Monday</span>
                    </div>
                    
                    <!-- Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-black tracking-tight text-zinc-800 mb-6 leading-[1.1] animate-slide-up">
                        Transform Your Body in
                        <span class="block gradient-text">Just 10 Days</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="text-lg lg:text-xl text-zinc-600 mb-8 leading-relaxed max-w-xl mx-auto lg:mx-0 animate-slide-up" style="animation-delay: 0.1s">
                        Join hundreds of successful challengers in a <strong class="text-zinc-800">coach-guided fitness journey</strong>. 
                        Daily accountability, proven results, and expert support every step of the way.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-10 animate-slide-up" style="animation-delay: 0.2s">
                        <a href="<?= h($messenger_link) ?>" 
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn-shine group w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-molten to-pumpkin rounded-2xl shadow-xl shadow-molten/25 hover:shadow-2xl hover:shadow-molten/30 transition-all hover:scale-105">
                            <span>Start Your Journey</span>
                            <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#about" 
                           class="group w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base font-semibold text-zinc-700 bg-white border-2 border-zinc-200 rounded-2xl hover:border-molten/30 hover:bg-orange-50/50 transition-all">
                            <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Learn More</span>
                        </a>
                    </div>
                    
                    <!-- Trust Indicators -->
                    <!-- <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-zinc-500 animate-fade-in" style="animation-delay: 0.4s">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>100% Coach Guided</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Proven Results</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>No App Needed</span>
                        </div>
                    </div> -->
                </div>
                
                <!-- Visual Side -->
                <div class="relative animate-scale-in" style="animation-delay: 0.3s">
                    <!-- Main Card -->
                    <div class="relative bg-white rounded-3xl shadow-2xl shadow-zinc-200/50 p-6 sm:p-8 border border-zinc-100">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-100">
                                <div class="text-2xl sm:text-3xl font-black text-molten">10</div>
                                <div class="text-xs sm:text-sm text-zinc-600 font-medium">Days</div>
                            </div>
                            <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-100">
                                <div class="text-2xl sm:text-3xl font-black text-pumpkin">100%</div>
                                <div class="text-xs sm:text-sm text-zinc-600 font-medium">Guided</div>
                            </div>
                            <div class="text-center p-4 rounded-2xl bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-100">
                                <div class="text-2xl sm:text-3xl font-black text-amber-600">Top 10</div>
                                <div class="text-xs sm:text-sm text-zinc-600 font-medium">Leaders</div>
                            </div>
                        </div>
                        
                        <!-- Trophy Visual -->
                        <div class="relative flex items-center justify-center py-8">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-100/50 to-amber-100/50 rounded-2xl"></div>
                            <svg class="relative h-40 w-40 sm:h-52 sm:w-52 animate-float" viewBox="0 0 120 120" fill="none">
                                <defs>
                                    <linearGradient id="heroTrophyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#FFD700"/>
                                        <stop offset="50%" stop-color="#FFA500"/>
                                        <stop offset="100%" stop-color="#FF8C00"/>
                                    </linearGradient>
                                    <filter id="heroGlow" x="-50%" y="-50%" width="200%" height="200%">
                                        <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                        <feMerge>
                                            <feMergeNode in="coloredBlur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                                <path d="M30 25c0 20 10 40 30 40s30-20 30-40" fill="url(#heroTrophyGrad)" filter="url(#heroGlow)"/>
                                <rect x="45" y="85" width="30" height="15" rx="4" fill="#B8860B"/>
                                <rect x="40" y="100" width="40" height="10" rx="4" fill="#8B4513"/>
                                <path d="M30 35c-10 0-15 10-15 20s5 20 15 20" stroke="#FFD700" stroke-width="4" fill="none"/>
                                <path d="M90 35c10 0 15 10 15 20s-5 20-15 20" stroke="#FFD700" stroke-width="4" fill="none"/>
                                <polygon points="60,30 64,42 77,42 67,50 71,62 60,54 49,62 53,50 43,42 56,42" fill="white" filter="url(#heroGlow)"/>
                            </svg>
                        </div>
                        
                        <!-- Bottom Message -->
                        <div class="text-center pt-4 border-t border-zinc-100">
                            <p class="text-sm text-zinc-500">Join the <span class="font-semibold text-molten">next challenge</span> starting Monday!</p>
                        </div>
                    </div>
                    
                    <!-- Floating Badges -->
                    <div class="absolute -top-4 -right-4 bg-white rounded-2xl p-3 shadow-xl shadow-zinc-200/50 border border-zinc-100 animate-float float-delay-2">
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-zinc-800">Verified</div>
                                <div class="text-[10px] text-zinc-500">Results</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute -bottom-4 -left-4 bg-white rounded-2xl p-3 shadow-xl shadow-zinc-200/50 border border-zinc-100 animate-float float-delay-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                                <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-zinc-800">Quick</div>
                                <div class="text-[10px] text-zinc-500">10 Days Only</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#about" class="flex flex-col items-center gap-2 text-zinc-400 hover:text-molten transition-colors">
                <span class="text-xs font-medium">Scroll to explore</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- ============================================
         ABOUT SECTION
         ============================================ -->
    <section id="about" class="relative py-20 lg:py-32 bg-gradient-to-b from-white to-orange-50/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Visual Side -->
                <div class="relative reveal">
                    <div class="relative bg-gradient-to-br from-orange-100 to-amber-50 rounded-3xl p-8 lg:p-12">
                        <!-- Feature Cards Stack -->
                        <div class="space-y-4">
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-orange-100/50 border border-orange-100 card-hover">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-pumpkin shadow-lg shadow-molten/20">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-zinc-800">Dedicated Coach</h4>
                                        <p class="text-sm text-zinc-500">Personal guidance every step</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-orange-100/50 border border-orange-100 card-hover ml-8">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-pumpkin to-amber-500 shadow-lg shadow-pumpkin/20">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-zinc-800">Track Progress</h4>
                                        <p class="text-sm text-zinc-500">Daily weigh-ins & BMI tracking</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-orange-100/50 border border-orange-100 card-hover">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo_bloom to-purple-500 shadow-lg shadow-indigo_bloom/20">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-zinc-800">Compete & Win</h4>
                                        <p class="text-sm text-zinc-500">Top 10 leaderboard rankings</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Side -->
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-molten mb-6">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-sm font-semibold">About the Challenge</span>
                    </div>
                    
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-800 mb-6 leading-tight">
                        What is the <span class="gradient-text">10 Days Weekly Challenge</span>?
                    </h2>
                    
                    <p class="text-lg text-zinc-600 mb-8 leading-relaxed">
                        The 10 Days Weekly Challenge is a <strong class="text-zinc-800">professionally coach-guided fitness program</strong> designed to deliver real, measurable results in just 10 days.
                    </p>
                    
                    <p class="text-lg text-zinc-600 mb-8 leading-relaxed">
                        Your dedicated coach handles everything – from tracking your daily progress to calculating your BMI and monitoring your improvements. <strong class="text-zinc-800">No complicated apps, no guesswork</strong> – just show up and give your best!
                    </p>
                    
                    <!-- <div class="grid sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-green-50 border border-green-100">
                            <svg class="h-6 w-6 text-green-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <div class="font-bold text-zinc-800 text-sm">Simple & Effective</div>
                                <div class="text-xs text-zinc-500">Coach manages all data</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-blue-50 border border-blue-100">
                            <svg class="h-6 w-6 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <div class="font-bold text-zinc-800 text-sm">Quick Results</div>
                                <div class="text-xs text-zinc-500">Just 10 days commitment</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-purple-50 border border-purple-100">
                            <svg class="h-6 w-6 text-purple-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <div class="font-bold text-zinc-800 text-sm">Competition</div>
                                <div class="text-xs text-zinc-500">Leaderboard rankings</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-orange-50 border border-orange-100">
                            <svg class="h-6 w-6 text-orange-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <div>
                                <div class="font-bold text-zinc-800 text-sm">Rejoin Anytime</div>
                                <div class="text-xs text-zinc-500">Unlimited challenges</div>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         HOW IT WORKS SECTION
         ============================================ -->
    <section id="how-it-works" class="relative py-20 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-molten mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-sm font-semibold">Simple 4-Step Process</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-800 mb-6">
                    How It <span class="gradient-text">Works</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    Getting started is easy. Follow these simple steps and let your coach guide you to success.
                </p>
            </div>
            
            <!-- Steps -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <!-- Step 1 -->
                <div class="reveal card-hover group relative bg-white rounded-3xl p-6 lg:p-8 border-2 border-zinc-100 hover:border-molten/20 shadow-lg shadow-zinc-100/50">
                    <div class="absolute -top-4 -left-4 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-molten to-pumpkin text-white text-lg font-black shadow-lg shadow-molten/30">1</div>
                    <div class="pt-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100 mb-6 group-hover:bg-gradient-to-br group-hover:from-molten group-hover:to-pumpkin transition-all duration-300">
                            <svg class="h-8 w-8 text-molten group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-3">Contact Your Coach</h3>
                        <p class="text-zinc-600 leading-relaxed">Reach out via Messenger to express your interest in joining the next challenge.</p>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="reveal card-hover group relative bg-white rounded-3xl p-6 lg:p-8 border-2 border-zinc-100 hover:border-pumpkin/20 shadow-lg shadow-zinc-100/50" style="animation-delay: 0.1s">
                    <div class="absolute -top-4 -left-4 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-pumpkin to-amber-500 text-white text-lg font-black shadow-lg shadow-pumpkin/30">2</div>
                    <div class="pt-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100 mb-6 group-hover:bg-gradient-to-br group-hover:from-pumpkin group-hover:to-amber-500 transition-all duration-300">
                            <svg class="h-8 w-8 text-pumpkin group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-3">Pre-Registration</h3>
                        <p class="text-zinc-600 leading-relaxed">Your coach collects your basic info, measurements, and progress photos for tracking.</p>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="reveal card-hover group relative bg-white rounded-3xl p-6 lg:p-8 border-2 border-zinc-100 hover:border-amber-500/20 shadow-lg shadow-zinc-100/50" style="animation-delay: 0.2s">
                    <div class="absolute -top-4 -left-4 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-yellow-400 text-white text-lg font-black shadow-lg shadow-amber-500/30">3</div>
                    <div class="pt-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 mb-6 group-hover:bg-gradient-to-br group-hover:from-amber-500 group-hover:to-yellow-400 transition-all duration-300">
                            <svg class="h-8 w-8 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-3">Challenge Begins</h3>
                        <p class="text-zinc-600 leading-relaxed">Challenge starts every Monday. Your coach tracks your daily weigh-ins for 10 days.</p>
                    </div>
                </div>
                
                <!-- Step 4 -->
                <div class="reveal card-hover group relative bg-white rounded-3xl p-6 lg:p-8 border-2 border-zinc-100 hover:border-indigo_bloom/20 shadow-lg shadow-zinc-100/50" style="animation-delay: 0.3s">
                    <div class="absolute -top-4 -left-4 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo_bloom to-purple-500 text-white text-lg font-black shadow-lg shadow-indigo_bloom/30">4</div>
                    <div class="pt-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-100 mb-6 group-hover:bg-gradient-to-br group-hover:from-indigo_bloom group-hover:to-purple-500 transition-all duration-300">
                            <svg class="h-8 w-8 text-indigo_bloom group-hover:text-white transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-zinc-800 mb-3">Results & Rankings</h3>
                        <p class="text-zinc-600 leading-relaxed">After Day 10, see your results on the leaderboard ranked by weight loss percentage!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         JOURNEY TIMELINE SECTION
         ============================================ -->
    <section id="journey" class="relative py-20 lg:py-32 bg-gradient-to-b from-orange-50/50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-molten mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-semibold">Weekly Structure</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-800 mb-6">
                    Your <span class="gradient-text">10-Day Journey</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    Every challenge starts on Monday. Here's what your transformation journey looks like.
                </p>
            </div>
            
            <!-- Timeline -->
            <div class="relative max-w-4xl mx-auto">
                <!-- Vertical Line -->
                <div class="absolute left-4 lg:left-1/2 top-0 bottom-0 w-1 bg-gradient-to-b from-molten via-pumpkin via-amber-500 to-indigo_bloom rounded-full lg:transform lg:-translate-x-1/2"></div>
                
                <!-- Timeline Items -->
                <div class="space-y-8">
                    <!-- Day 1 -->
                    <div class="reveal relative flex items-start gap-6 lg:gap-0">
                        <div class="lg:w-1/2 lg:pr-12 lg:text-right">
                            <div class="lg:hidden absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-molten text-white text-xs font-bold shadow-lg shadow-molten/30 z-10">1</div>
                            <div class="hidden lg:flex absolute left-1/2 top-0 transform -translate-x-1/2 h-10 w-10 items-center justify-center rounded-full bg-molten text-white text-sm font-bold shadow-lg shadow-molten/30 z-10">1</div>
                            <div class="ml-12 lg:ml-0 bg-white rounded-2xl p-6 shadow-lg shadow-zinc-100/50 border border-zinc-100 card-hover">
                                <div class="flex items-center gap-3 lg:justify-end mb-3">
                                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider bg-green-100 text-green-700 rounded-full">Start</span>
                                    <span class="text-sm font-semibold text-molten">Monday</span>
                                </div>
                                <h3 class="text-xl font-bold text-zinc-800 mb-2">Day 1 - Kickoff! 🚀</h3>
                                <p class="text-zinc-600">Challenge officially begins. Initial weigh-in recorded by your coach. Let's go!</p>
                            </div>
                        </div>
                        <div class="hidden lg:block lg:w-1/2"></div>
                    </div>
                    
                    <!-- Days 2-5 -->
                    <div class="reveal relative flex items-start gap-6 lg:gap-0" style="animation-delay: 0.1s">
                        <div class="hidden lg:block lg:w-1/2"></div>
                        <div class="lg:w-1/2 lg:pl-12">
                            <div class="lg:hidden absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-pumpkin text-white text-xs font-bold shadow-lg shadow-pumpkin/30 z-10">2-5</div>
                            <div class="hidden lg:flex absolute left-1/2 top-0 transform -translate-x-1/2 h-10 w-10 items-center justify-center rounded-full bg-pumpkin text-white text-[10px] font-bold shadow-lg shadow-pumpkin/30 z-10">2-5</div>
                            <div class="ml-12 lg:ml-0 bg-white rounded-2xl p-6 shadow-lg shadow-zinc-100/50 border border-zinc-100 card-hover">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider bg-orange-100 text-pumpkin rounded-full">Week 1</span>
                                    <span class="text-sm font-semibold text-pumpkin">Tue - Fri</span>
                                </div>
                                <h3 class="text-xl font-bold text-zinc-800 mb-2">Days 2-5 - Build Momentum 💪</h3>
                                <p class="text-zinc-600">Daily weigh-ins continue. Stay consistent, follow the program, and trust the process.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Days 6-9 -->
                    <div class="reveal relative flex items-start gap-6 lg:gap-0" style="animation-delay: 0.2s">
                        <div class="lg:w-1/2 lg:pr-12 lg:text-right">
                            <div class="lg:hidden absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white text-xs font-bold shadow-lg shadow-amber-500/30 z-10">6-9</div>
                            <div class="hidden lg:flex absolute left-1/2 top-0 transform -translate-x-1/2 h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-white text-[10px] font-bold shadow-lg shadow-amber-500/30 z-10">6-9</div>
                            <div class="ml-12 lg:ml-0 bg-white rounded-2xl p-6 shadow-lg shadow-zinc-100/50 border border-zinc-100 card-hover">
                                <div class="flex items-center gap-3 lg:justify-end mb-3">
                                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-700 rounded-full">Week 2</span>
                                    <span class="text-sm font-semibold text-amber-600">Sat - Tue</span>
                                </div>
                                <h3 class="text-xl font-bold text-zinc-800 mb-2">Days 6-9 - Push Through 🔥</h3>
                                <p class="text-zinc-600">You're past halfway! This is where champions are made. Keep the energy high!</p>
                            </div>
                        </div>
                        <div class="hidden lg:block lg:w-1/2"></div>
                    </div>
                    
                    <!-- Day 10 -->
                    <div class="reveal relative flex items-start gap-6 lg:gap-0" style="animation-delay: 0.3s">
                        <div class="hidden lg:block lg:w-1/2"></div>
                        <div class="lg:w-1/2 lg:pl-12">
                            <div class="lg:hidden absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-indigo_bloom text-white text-xs font-bold shadow-lg shadow-indigo_bloom/30 z-10">10</div>
                            <div class="hidden lg:flex absolute left-1/2 top-0 transform -translate-x-1/2 h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo_bloom to-purple-500 text-white text-sm font-bold shadow-lg shadow-indigo_bloom/30 z-10">10</div>
                            <div class="ml-12 lg:ml-0 bg-gradient-to-br from-indigo_bloom/5 to-purple-500/5 rounded-2xl p-6 shadow-lg shadow-indigo_bloom/10 border border-indigo_bloom/20 card-hover">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider bg-indigo_bloom/20 text-indigo_bloom rounded-full">Finish</span>
                                    <span class="text-sm font-semibold text-indigo_bloom">Wednesday</span>
                                </div>
                                <h3 class="text-xl font-bold text-zinc-800 mb-2">Day 10 - Victory Day! 🏆</h3>
                                <p class="text-zinc-600">Final weigh-in recorded. Leaderboard generated. Celebrate your amazing transformation!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         BENEFITS SECTION
         ============================================ -->
    <section id="benefits" class="relative py-20 lg:py-32 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-indigo_bloom mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span class="text-sm font-semibold">Why Join Us</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-800 mb-6">
                    Benefits of <span class="gradient-text">Joining</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    More than just weight loss – it's a complete transformation experience.
                </p>
            </div>
            
            <!-- Benefits Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $benefits = [
                    ['Personal Coach Support', 'Dedicated coach managing your progress, calculating metrics, and keeping you accountable every day.', 'from-molten to-pumpkin', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['Track Real Progress', 'Daily weight tracking with BMI calculations and measurable results you can see and celebrate.', 'from-pumpkin to-amber-500', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['Just 10 Days', 'Short, focused challenge that fits your schedule. Big results, minimal time commitment required.', 'from-amber-500 to-yellow-400', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['No App Required', 'No complicated apps or accounts to manage. Simply follow your coach\'s guidance and show up.', 'from-green-500 to-emerald-400', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Compete & Win', 'Top 10 leaderboard at the end. Compete with others and celebrate your achievements together.', 'from-indigo_bloom to-purple-500', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['Rejoin Anytime', 'Completed a challenge? Rejoin future challenges without creating a new profile. Keep improving!', 'from-pink-500 to-rose-400', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                ];
                foreach ($benefits as $index => $benefit): ?>
                <div class="reveal card-hover group bg-white rounded-3xl p-6 lg:p-8 border-2 border-zinc-100 hover:border-zinc-200 shadow-lg shadow-zinc-100/50" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br <?= $benefit[2] ?> mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $benefit[3] ?>"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-800 mb-3"><?= h($benefit[0]) ?></h3>
                    <p class="text-zinc-600 leading-relaxed"><?= h($benefit[1]) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         TESTIMONIALS SECTION
         ============================================ -->
    <section id="testimonials" class="relative py-20 lg:py-32 bg-gradient-to-b from-orange-50/50 to-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-molten mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="text-sm font-semibold">Success Stories</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-800 mb-6">
                    Real Results from <span class="gradient-text">Real People</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    Don't just take our word for it. See what our challengers have to say about their transformation journey.
                </p>
            </div>
            
            <!-- Testimonials Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                <?php foreach ($testimonials as $index => $testimonial): ?>
                <div class="reveal testimonial-card bg-white rounded-3xl overflow-hidden shadow-xl shadow-zinc-100/50 border border-zinc-100 group" style="animation-delay: <?= $index * 0.1 ?>s">
                    <!-- Before/After Comparison Images -->
                    <div class="relative aspect-[4/5] bg-zinc-50">
                        <!-- Day 1 & Day 10 Images Side by Side -->
                        <div class="absolute inset-0 flex">
                            <!-- Day 1 Image -->
                            <div class="relative flex-1 overflow-hidden">
                                <img src="<?= strpos($testimonial['day1_image'], 'http') === 0 ? h($testimonial['day1_image']) : h(url($testimonial['day1_image'])) ?>" alt="<?= h($testimonial['name']) ?> - Day 1" class="absolute inset-0 w-full h-full object-cover">
                                <div class="absolute top-3 left-3">
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-zinc-900/80 backdrop-blur-sm rounded-lg">
                                        DAY 1
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Divider Line -->
                            <div class="w-0.5 bg-white shadow-lg z-10"></div>
                            
                            <!-- Day 10 Image -->
                            <div class="relative flex-1 overflow-hidden">
                                <img src="<?= strpos($testimonial['day10_image'], 'http') === 0 ? h($testimonial['day10_image']) : h(url($testimonial['day10_image'])) ?>" alt="<?= h($testimonial['name']) ?> - Day 10" class="absolute inset-0 w-full h-full object-cover">
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-molten to-pumpkin backdrop-blur-sm rounded-lg shadow-lg">
                                        DAY 10
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Weight Loss Badge -->
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2">
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-xl border-2 border-orange-100">
                                <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-black text-molten"><?= h($testimonial['weight_lost']) ?></span>
                                <span class="text-xs text-zinc-500 font-semibold">Lost</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <!-- Name and Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-zinc-800"><?= h($testimonial['name']) ?></h3>
                                <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 text-xs font-semibold text-molten bg-orange-50 rounded-full">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <?= h($testimonial['badge'] ?? 'Challenge Graduate') ?>
                                </div>
                            </div>
                            <!-- Rating Stars -->
                            <div class="flex items-center gap-0.5">
                                <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Quote (if provided) -->
                        <?php if (!empty($testimonial['quote'])): ?>
                        <div class="relative">
                            <svg class="absolute -top-1 -left-1 h-6 w-6 text-orange-100" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                            </svg>
                            <p class="text-sm text-zinc-600 leading-relaxed italic pl-5">"<?= h($testimonial['quote']) ?>"</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         FINAL CTA SECTION
         ============================================ -->
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-molten via-pumpkin to-amber-500"></div>
        <div class="absolute inset-0 grid-pattern opacity-10"></div>
        
        <!-- Decorative Elements -->
        <div class="absolute top-10 left-10 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-float float-delay-2"></div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Icon -->
            <div class="flex justify-center mb-8 reveal">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-2xl">
                    <svg class="h-10 w-10 text-white" viewBox="0 0 48 48" fill="none">
                        <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="currentColor" opacity="0.9"/>
                        <rect x="18" y="34" width="12" height="6" rx="2" fill="currentColor" opacity="0.7"/>
                        <rect x="16" y="40" width="16" height="4" rx="2" fill="currentColor" opacity="0.5"/>
                        <polygon points="24,14 25.5,18 30,18 26.5,21 28,25 24,22 20,25 21.5,21 18,18 22.5,18" fill="#FFD700"/>
                    </svg>
                </div>
            </div>
            
            <!-- Headline -->
            <h2 class="reveal text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6 leading-tight">
                Ready to Transform Your Body?
            </h2>
            
            <p class="reveal text-lg sm:text-xl text-white/90 mb-10 max-w-2xl mx-auto leading-relaxed" style="animation-delay: 0.1s">
                Join the next 10 Days Weekly Challenge and discover what you're truly capable of. 
                Your coach is waiting to guide you to success!
            </p>
            
            <!-- CTA Buttons -->
            <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-4" style="animation-delay: 0.2s">
                <a href="<?= h($messenger_link) ?>" 
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn-shine group w-full sm:w-auto inline-flex items-center justify-center gap-3 px-10 py-5 text-lg font-bold text-molten bg-white rounded-2xl shadow-2xl hover:bg-zinc-50 transition-all hover:scale-105">
                    <span>Join the Challenge Now</span>
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="<?= h(url('/auth/login.php')) ?>" 
                   class="group w-full sm:w-auto inline-flex items-center justify-center gap-3 px-10 py-5 text-lg font-semibold text-white border-2 border-white/40 rounded-2xl hover:bg-white/10 transition-all">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Coach Login</span>
                </a>

                
            </div>
        </div>
    </section>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="bg-zinc-900">
        <!-- Main Footer -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                <!-- Brand Column -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-pumpkin shadow-lg">
                            <svg class="h-7 w-7 text-white" viewBox="0 0 48 48" fill="none">
                                <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="currentColor" opacity="0.9"/>
                                <polygon points="24,14 25.5,18 30,18 26.5,21 28,25 24,22 20,25 21.5,21 18,18 22.5,18" fill="#FFD700"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-white">10 Days Challenge</span>
                        </div>
                    </div>
                    <p class="text-zinc-400 leading-relaxed mb-6 max-w-md">
                        Transform your body in just 10 days with our coach-guided fitness challenge. Daily accountability, proven results, and a supportive community.
                    </p>
                    <!-- Social / Contact -->
                    <div class="flex items-center gap-4">
                        <a href="<?= h($messenger_link) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center h-10 w-10 rounded-lg bg-zinc-800 text-zinc-400 hover:bg-molten hover:text-white transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.936 1.444 5.544 3.706 7.248V22l3.39-1.861c.905.251 1.865.387 2.904.387 5.523 0 10-4.145 10-9.243S17.523 2 12 2zm.992 12.42l-2.547-2.72-4.97 2.72 5.467-5.803 2.61 2.72 4.907-2.72-5.467 5.803z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#home" class="text-zinc-400 hover:text-white transition-colors">Home</a></li>
                        <li><a href="#about" class="text-zinc-400 hover:text-white transition-colors">About</a></li>
                        <li><a href="#how-it-works" class="text-zinc-400 hover:text-white transition-colors">How It Works</a></li>
                        <li><a href="#testimonials" class="text-zinc-400 hover:text-white transition-colors">Testimonials</a></li>
                    </ul>
                </div>
                
                <!-- Get Started -->
                <div>
                    <h4 class="text-white font-bold mb-4">Get Started</h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="<?= h($messenger_link) ?>" target="_blank" rel="noopener noreferrer" class="text-zinc-400 hover:text-white transition-colors">Join Challenge</a>
                        </li>
                        <li>
                            <a href="<?= h(url('/auth/login.php')) ?>" class="text-zinc-400 hover:text-white transition-colors">Coach Login</a>
                        </li>
                    </ul>
                    
                    <!-- CTA Button -->
                    <a href="<?= h($messenger_link) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-molten to-pumpkin rounded-lg hover:opacity-90 transition-opacity">
                        <span>Start Now</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-zinc-500 text-center sm:text-left">
                        &copy; <?= date('Y') ?> 10 Days Weekly Challenge. All rights reserved.
                    </p>
                    <div class="flex items-center gap-1 text-sm text-zinc-500">
                        <span>Made with</span>
                        <svg class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        <span>for your transformation</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================
         SCRIPTS
         ============================================ -->
    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const closeMobileMenuBtn = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        
        function openMobileMenu() {
            mobileMenu.classList.remove('translate-x-full');
            mobileMenuOverlay.classList.remove('opacity-0', 'pointer-events-none');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenuFn() {
            mobileMenu.classList.add('translate-x-full');
            mobileMenuOverlay.classList.add('opacity-0', 'pointer-events-none');
            document.body.style.overflow = '';
        }
        
        mobileMenuBtn.addEventListener('click', openMobileMenu);
        closeMobileMenuBtn.addEventListener('click', closeMobileMenuFn);
        mobileMenuOverlay.addEventListener('click', closeMobileMenuFn);
        mobileNavLinks.forEach(link => link.addEventListener('click', closeMobileMenuFn));
        
        // Navbar background on scroll
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-lg', 'shadow-zinc-100/50');
                navbar.classList.remove('border-b', 'border-zinc-100');
            } else {
                navbar.classList.remove('shadow-lg', 'shadow-zinc-100/50');
                navbar.classList.add('border-b', 'border-zinc-100');
            }
        });
        
        // Active nav link on scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active', 'text-molten');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active', 'text-molten');
                }
            });
        });
        
        // Scroll reveal animation
        const revealElements = document.querySelectorAll('.reveal');
        
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        });
        
        revealElements.forEach(el => revealObserver.observe(el));
    </script>
</body>
</html>
