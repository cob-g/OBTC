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
        
        /* Hero Glow Effects */
        .hero-glow-orange {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.3) 0%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
        }
        
        .hero-glow-purple {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.25) 0%, transparent 70%);
            filter: blur(80px);
            pointer-events: none;
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
        
        /* Trophy Container */
        .trophy-container {
            background: linear-gradient(145deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255,255,255,0.1);
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
        
        /* Carousel */
        .carousel-container {
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        
        .carousel-item {
            scroll-snap-align: start;
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased">

    <!-- ============================================
         NAVIGATION
         ============================================ -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-dark-900/80 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <a href="#home" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary-dark">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 48 48" fill="none">
                            <path d="M24 4L28 16H40L30 24L34 36L24 28L14 36L18 24L8 16H20L24 4Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">10 Days Challenge</span>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#home" class="text-sm font-medium text-white hover:text-primary transition-colors">Home</a>
                    <a href="#about" class="text-sm font-medium text-zinc-400 hover:text-primary transition-colors">About</a>
                    <a href="#how-it-works" class="text-sm font-medium text-zinc-400 hover:text-primary transition-colors">How It Works</a>
                    <a href="#journey" class="text-sm font-medium text-zinc-400 hover:text-primary transition-colors">Journey</a>
                    <a href="#benefits" class="text-sm font-medium text-zinc-400 hover:text-primary transition-colors">Benefits</a>
                    <a href="#testimonials" class="text-sm font-medium text-zinc-400 hover:text-primary transition-colors">Success Stories</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="flex items-center gap-3">
                    <a href="<?= h(url('/auth/login.php')) ?>" 
                       class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-zinc-300 hover:text-white transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Coach Login
                    </a>
                    <a href="<?= h($messenger_link) ?>" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="btn-primary inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-full shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 transition-all hover:scale-105">
                        <span>Join Now</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="lg:hidden flex items-center justify-center h-10 w-10 rounded-xl text-zinc-400 hover:text-white hover:bg-white/10 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                    <a href="#benefits" class="mobile-nav-link flex items-center gap-3 px-4 py-3.5 text-base font-semibold text-zinc-300 hover:text-primary hover:bg-white/5 rounded-xl transition-colors">Benefits</a>
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
         HERO SECTION
         ============================================ -->
    <section id="home" class="relative min-h-screen pt-20 lg:pt-0 flex items-center overflow-hidden">
        <!-- Background Glows -->
        <div class="hero-glow-orange -left-40 top-1/4"></div>
        <div class="hero-glow-purple right-0 top-0"></div>
        <div class="hero-glow-orange right-1/4 bottom-0 opacity-50"></div>
        
        <!-- Curved Lines Background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <svg class="absolute w-full h-full" viewBox="0 0 1440 900" fill="none" preserveAspectRatio="xMidYMid slice">
                <path d="M-100 600 Q 400 200 800 400 T 1600 300" stroke="url(#gradient1)" stroke-width="2" fill="none" opacity="0.3"/>
                <path d="M-100 700 Q 500 300 900 500 T 1700 400" stroke="url(#gradient2)" stroke-width="2" fill="none" opacity="0.2"/>
                <defs>
                    <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#F97316"/>
                        <stop offset="100%" style="stop-color:#8B5CF6"/>
                    </linearGradient>
                    <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" style="stop-color:#8B5CF6"/>
                        <stop offset="100%" style="stop-color:#F97316"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <!-- Left: Trophy Image -->
                <div class="relative order-2 lg:order-1 flex justify-center">
                    <div class="trophy-container relative w-72 h-72 sm:w-96 sm:h-96 rounded-3xl flex items-center justify-center">
                        <!-- Trophy SVG -->
                        <svg class="w-48 h-48 sm:w-64 sm:h-64" viewBox="0 0 200 200" fill="none">
                            <!-- Trophy Base -->
                            <rect x="60" y="160" width="80" height="15" rx="4" fill="url(#trophyBase)"/>
                            <rect x="75" y="145" width="50" height="20" rx="3" fill="url(#trophyStem)"/>
                            
                            <!-- Trophy Cup -->
                            <path d="M45 50 L45 90 Q45 130 100 140 Q155 130 155 90 L155 50 Z" fill="url(#trophyCup)"/>
                            
                            <!-- Trophy Handles -->
                            <path d="M45 60 Q20 60 20 90 Q20 110 45 110" stroke="url(#trophyHandle)" stroke-width="8" fill="none" stroke-linecap="round"/>
                            <path d="M155 60 Q180 60 180 90 Q180 110 155 110" stroke="url(#trophyHandle)" stroke-width="8" fill="none" stroke-linecap="round"/>
                            
                            <!-- Trophy Rim -->
                            <ellipse cx="100" cy="50" rx="55" ry="12" fill="url(#trophyRim)"/>
                            
                            <!-- Star -->
                            <path d="M100 70 L105 85 L120 87 L109 97 L112 112 L100 105 L88 112 L91 97 L80 87 L95 85 Z" fill="#FFD700"/>
                            
                            <!-- Shine Effect -->
                            <ellipse cx="70" cy="80" rx="8" ry="15" fill="white" opacity="0.3"/>
                            
                            <defs>
                                <linearGradient id="trophyCup" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="50%" style="stop-color:#FFA500"/>
                                    <stop offset="100%" style="stop-color:#FF8C00"/>
                                </linearGradient>
                                <linearGradient id="trophyHandle" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#FFA500"/>
                                </linearGradient>
                                <linearGradient id="trophyRim" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#FFE44D"/>
                                    <stop offset="50%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#FFE44D"/>
                                </linearGradient>
                                <linearGradient id="trophyBase" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#4A4A5A"/>
                                    <stop offset="100%" style="stop-color:#2A2A3A"/>
                                </linearGradient>
                                <linearGradient id="trophyStem" x1="50%" y1="0%" x2="50%" y2="100%">
                                    <stop offset="0%" style="stop-color:#FFD700"/>
                                    <stop offset="100%" style="stop-color:#FFA500"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        
                        <!-- Glow behind trophy -->
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-accent/20 rounded-3xl blur-2xl -z-10"></div>
                    </div>
                </div>
                
                <!-- Right: Content -->
                <div class="order-1 lg:order-2 text-center lg:text-left">
                    <!-- Stats Row -->
                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 mb-8">
                        <div class="stat-card px-5 py-3 rounded-xl">
                            <div class="text-2xl font-black text-white">10</div>
                            <div class="text-xs text-zinc-400">Days</div>
                        </div>
                        <div class="stat-card px-5 py-3 rounded-xl">
                            <div class="text-2xl font-black text-white">100%</div>
                            <div class="text-xs text-zinc-400">Guided</div>
                        </div>
                        <div class="stat-card px-5 py-3 rounded-xl">
                            <div class="text-2xl font-black text-white">Top 10</div>
                            <div class="text-xs text-zinc-400">Leaders</div>
                        </div>
                        
                        <!-- Verified Badge -->
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/20 border border-green-500/30">
                            <svg class="h-4 w-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold text-green-400">Verified Results</span>
                        </div>
                    </div>
                    
                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
                        Transform Your Body<br>
                        in Just <span class="gradient-text">10 Days</span>
                    </h1>
                    
                    <!-- Subheadline -->
                    <p class="text-lg text-zinc-400 leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                        Join hundreds of successful challengers in a <span class="text-white font-semibold">coach-guided fitness journey</span>. Track progress daily, access results, and achieve expert-worthy only let nothing.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="<?= h($messenger_link) ?>" 
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn-primary inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-full shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 transition-all hover:scale-105">
                            Start the Challenge
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#about" 
                           class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold text-zinc-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-full transition-all">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         ABOUT SECTION
         ============================================ -->
    <section id="about" class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-b from-dark-900 via-dark-800 to-dark-900"></div>
        <div class="hero-glow-purple left-0 top-1/2 -translate-y-1/2 opacity-30"></div>
        
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
                        The 10 Days Weekly Challenge is a <span class="text-white font-semibold">professional coach-guided fitness program</span> delivers remarkable, measurable results in just 10 days.
                    </p>
                    
                    <p class="text-zinc-400 leading-relaxed mb-8">
                        Your dedicated challenge coach calculates your <span class="text-white">Body Counting</span> score progression. If it's minimizing your BMI, poor maintaining lead improvements, as <span class="text-primary font-semibold">compliment ages, as processed</span> – Just Show up and give your best!
                    </p>
                    
                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                                <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Professional Guidance</h4>
                                <p class="text-sm text-zinc-400">Expert coach monitoring your progress daily</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center">
                                <svg class="h-5 w-5 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Track Your Progress</h4>
                                <p class="text-sm text-zinc-400">Daily weigh-ins with BMI tracking</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Just 10 Days</h4>
                                <p class="text-sm text-zinc-400">Short commitment, lasting results</p>
                            </div>
                        </div>
                    </div>
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
    <section id="how-it-works" class="relative py-20 lg:py-32 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-sm font-semibold">Simple Process</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-900 mb-6">
                    How It <span class="gradient-text">Works</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    Getting started is easy. Follow these forms 1 and and let your coach guide you to success!
                </p>
            </div>
            
            <!-- Process Steps -->
            <div class="relative">
                <!-- Connection Line (Desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-primary via-accent to-primary opacity-30"></div>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Step 1 -->
                    <div class="reveal relative">
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl transition-shadow">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 mb-2">Contact Your Coach</h3>
                            <p class="text-sm text-zinc-500">Reach out via messenger to start to your journey to new-life changes.</p>
                        </div>
                        <div class="hidden lg:flex absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-primary text-white items-center justify-center font-bold text-sm z-10">1</div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="reveal relative" style="animation-delay: 0.1s">
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl transition-shadow">
                            <div class="w-14 h-14 rounded-2xl bg-accent/10 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-accent" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 mb-2">Pre-Registration</h3>
                            <p class="text-sm text-zinc-500">Your coach collects your basic information and acquires relevant for training.</p>
                        </div>
                        <div class="hidden lg:flex absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-accent text-white items-center justify-center font-bold text-sm z-10">2</div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="reveal relative" style="animation-delay: 0.2s">
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl transition-shadow">
                            <div class="w-14 h-14 rounded-2xl bg-green-500/10 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 mb-2">Challenge Begins</h3>
                            <p class="text-sm text-zinc-500">Ready for those hustle, first count hustle, party day, begin it for 10 days.</p>
                        </div>
                        <div class="hidden lg:flex absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-green-500 text-white items-center justify-center font-bold text-sm z-10">3</div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="reveal relative" style="animation-delay: 0.3s">
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl transition-shadow">
                            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 mb-2">Results & Rankings</h3>
                            <p class="text-sm text-zinc-500">At it, it's all ever since of the administered season having great amazing results.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Steps Row -->
                <div class="grid sm:grid-cols-2 gap-8 mt-8 max-w-2xl mx-auto">
                    <!-- Step: Weigh In -->
                    <div class="reveal" style="animation-delay: 0.4s">
                        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl transition-shadow">
                            <div class="w-14 h-14 rounded-2xl bg-blue-500/10 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-zinc-900 mb-2">Weigh or Step</h3>
                            <p class="text-sm text-zinc-500">Your body first is bodies evidence as administrator for assess it impact.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         JOURNEY SECTION
         ============================================ -->
    <section id="journey" class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-dark-900 via-dark-800 to-dark-900"></div>
        <div class="hero-glow-orange left-1/4 top-0 opacity-40"></div>
        <div class="hero-glow-purple right-0 bottom-0 opacity-30"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-start">
                <!-- Left: Content -->
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20 mb-6">
                        <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm font-semibold text-primary">Weekly Structure</span>
                    </div>
                    
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                        Your <span class="gradient-text">10-Day Journey</span>
                    </h2>
                    
                    <p class="text-lg text-zinc-400 leading-relaxed">
                        Every challenge starts on Monday. Ready 1 when your transformation your transfer maker journey.
                    </p>
                </div>
                
                <!-- Right: Timeline -->
                <div class="reveal space-y-6" style="animation-delay: 0.2s">
                    <!-- Day 1 -->
                    <div class="journey-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 text-xs font-bold text-primary bg-primary/20 rounded-full">STAGE 1</span>
                                <span class="mt-2 px-3 py-1 text-xs font-bold text-white bg-primary rounded-full">Baseline</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white flex items-center gap-2">
                                    Day 1 - Kickoff 🚀
                                </h4>
                                <p class="text-sm text-zinc-400 mt-2">Set completed until delivered, forth throughout monitored by your coach self-up!</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Day 2-3 -->
                    <div class="journey-card-alt rounded-2xl p-6 ml-8">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 text-xs font-bold text-accent bg-accent/20 rounded-full">STAGE 2</span>
                                <span class="mt-2 px-3 py-1 text-xs font-bold text-white bg-accent rounded-full">Days 2-4</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white flex items-center gap-2">
                                    Day 2-2 - Build Momentum 💪
                                </h4>
                                <p class="text-sm text-zinc-400 mt-2">Daily steps, day continue. Stay consistent. Show the progress, your track the process.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Days 4-9 -->
                    <div class="journey-card rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 text-xs font-bold text-primary bg-primary/20 rounded-full">STAGE 3</span>
                                <span class="mt-2 px-3 py-1 text-xs font-bold text-white bg-primary rounded-full">Day 5-9</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white flex items-center gap-2">
                                    Days 4-9 - Push Through 🔥
                                </h4>
                                <p class="text-sm text-zinc-400 mt-2">You're come half year! This is when the custom come made. Keep up energy high!</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Day 10 -->
                    <div class="journey-card-alt rounded-2xl p-6 ml-8">
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col items-center">
                                <span class="px-3 py-1 text-xs font-bold text-accent bg-accent/20 rounded-full">STAGE 4</span>
                                <span class="mt-2 px-3 py-1 text-xs font-bold text-white bg-amber-500 rounded-full">Final Weigh</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white flex items-center gap-2">
                                    Day 10 - Baileo Day! 🏆
                                </h4>
                                <p class="text-sm text-zinc-400 mt-2">Final finish celebration! A remarkable combines their only passing transformation.</p>
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
    <section id="benefits" class="relative py-20 lg:py-32 bg-zinc-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-accent/10 text-accent mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span class="text-sm font-semibold">Why Join</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-900 mb-6">
                    Benefits of <span class="gradient-text-purple">Joining</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    More just your weight less – it's complete experience/result experience.
                </p>
            </div>
            
            <!-- Benefits Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                $benefits = [
                    ['Personal Coach Support', 'Dedicated coach managing your progress, sometimes, friend any helping you amount other every day.', 'from-primary to-orange-400', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['Track Real Program', 'Daily weight tracking, with data processed and the source available schedule than can aid validate.', 'from-accent to-purple-400', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    ['Just 10 Days', 'Short, simple challenges that fit your process life forever, consist total, measurement require.', 'from-amber-500 to-yellow-400', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['No App Required', 'No complicated app are successor to implement. Simple timer, own branded platform and time up.', 'from-green-500 to-emerald-400', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Compete & Win', 'Top 10 overall active 1 One once Complete this meant one in speed is in store achievement together.', 'from-blue-500 to-cyan-400', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['Rejoin Anytime', '"Pro proof is dedicated base" base there available different activity at low profile. Keep progress!"', 'from-pink-500 to-rose-400', 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                ];
                foreach ($benefits as $index => $benefit): ?>
                <div class="reveal bg-white rounded-3xl p-6 shadow-xl shadow-zinc-200/50 border border-zinc-100 hover:shadow-2xl hover:-translate-y-1 transition-all" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?= $benefit[2] ?> flex items-center justify-center mb-4 shadow-lg">
                        <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $benefit[3] ?>"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2"><?= h($benefit[0]) ?></h3>
                    <p class="text-sm text-zinc-500 leading-relaxed"><?= h($benefit[1]) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         TESTIMONIALS SECTION
         ============================================ -->
    <section id="testimonials" class="relative py-20 lg:py-32 bg-zinc-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary mb-6">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="text-sm font-semibold">Success Stories</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-900 mb-6">
                    Real Results from <span class="gradient-text">Real People</span>
                </h2>
                <p class="text-lg text-zinc-600">
                    Don't don't our word for it. See what our challengers have to say about their transformation journey.
                </p>
            </div>
            
            <!-- Testimonials Carousel -->
            <div class="relative">
                <!-- Navigation Arrows -->
                <button id="prevBtn" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-12 h-12 rounded-full bg-white shadow-xl flex items-center justify-center text-zinc-600 hover:text-primary hover:scale-110 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button id="nextBtn" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-12 h-12 rounded-full bg-white shadow-xl flex items-center justify-center text-zinc-600 hover:text-primary hover:scale-110 transition-all">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <!-- Carousel Container -->
                <div id="testimonialCarousel" class="carousel-container flex gap-6 overflow-x-auto pb-4 px-4 -mx-4 snap-x snap-mandatory scrollbar-hide" style="scroll-padding-left: 1rem;">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="carousel-item flex-shrink-0 w-[320px] sm:w-[380px]">
                        <div class="testimonial-card rounded-3xl overflow-hidden shadow-xl">
                            <!-- Before/After Images -->
                            <div class="relative aspect-[4/5]">
                                <div class="absolute inset-0 flex">
                                    <!-- Day 1 -->
                                    <div class="relative flex-1 overflow-hidden">
                                        <img src="<?= strpos($testimonial['day1_image'], 'http') === 0 ? h($testimonial['day1_image']) : h(url($testimonial['day1_image'])) ?>" alt="<?= h($testimonial['name']) ?> - Day 1" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute top-3 left-3">
                                            <span class="px-3 py-1.5 text-xs font-bold text-white bg-zinc-900/80 backdrop-blur-sm rounded-lg">DAY 1</span>
                                        </div>
                                    </div>
                                    <div class="w-0.5 bg-white z-10"></div>
                                    <!-- Day 10 -->
                                    <div class="relative flex-1 overflow-hidden">
                                        <img src="<?= strpos($testimonial['day10_image'], 'http') === 0 ? h($testimonial['day10_image']) : h(url($testimonial['day10_image'])) ?>" alt="<?= h($testimonial['name']) ?> - Day 10" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute top-3 right-3">
                                            <span class="px-3 py-1.5 text-xs font-bold text-white bg-gradient-to-r from-primary to-orange-400 rounded-lg">DAY 10</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Weight Loss Badge -->
                                <div class="absolute bottom-3 left-1/2 -translate-x-1/2">
                                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-xl">
                                        <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-black text-primary"><?= h($testimonial['weight_lost']) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="font-bold text-zinc-900"><?= h($testimonial['name']) ?></h3>
                                        <span class="text-xs font-semibold text-primary"><?= h($testimonial['badge']) ?></span>
                                    </div>
                                    <div class="flex gap-0.5">
                                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                        <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if (!empty($testimonial['quote'])): ?>
                                <p class="text-sm text-zinc-600 leading-relaxed italic">"<?= h($testimonial['quote']) ?>"</p>
                                <?php endif; ?>
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
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 cta-gradient"></div>
        
        <!-- Decorative -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Trophy Icon -->
            <div class="flex justify-center mb-8 reveal">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="h-10 w-10 text-white" viewBox="0 0 48 48" fill="none">
                        <path d="M24 4L28 16H40L30 24L34 36L24 28L14 36L18 24L8 16H20L24 4Z" fill="currentColor"/>
                    </svg>
                </div>
            </div>
            
            <h2 class="reveal text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6">
                Ready to Transform Your Body?
            </h2>
            
            <p class="reveal text-lg text-white/80 leading-relaxed mb-10 max-w-2xl mx-auto" style="animation-delay: 0.1s">
                Join the next 10 Days Weekly Challenge and discover your inner. Truly upgrade all you. Good is waiting to guide you to success!
            </p>
            
            <div class="reveal flex flex-col sm:flex-row items-center justify-center gap-4" style="animation-delay: 0.2s">
                <a href="<?= h($messenger_link) ?>" 
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-primary bg-white rounded-full shadow-xl hover:shadow-2xl hover:scale-105 transition-all">
                    Join the Challenge Now
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="<?= h(url('/auth/login.php')) ?>" 
                   class="inline-flex items-center gap-2 px-8 py-4 text-base font-semibold text-white bg-white/20 hover:bg-white/30 border border-white/30 rounded-full transition-all">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Coach Login
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer class="bg-dark-900 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid md:grid-cols-3 gap-8 items-center">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary-dark">
                        <svg class="h-6 w-6 text-white" viewBox="0 0 48 48" fill="none">
                            <path d="M24 4L28 16H40L30 24L34 36L24 28L14 36L18 24L8 16H20L24 4Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">10 Days Challenge</span>
                </div>
                
                <!-- Links -->
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="#home" class="text-sm text-zinc-400 hover:text-white transition-colors">Home</a>
                    <a href="#about" class="text-sm text-zinc-400 hover:text-white transition-colors">About</a>
                    <a href="#how-it-works" class="text-sm text-zinc-400 hover:text-white transition-colors">How It Works</a>
                    <a href="#testimonials" class="text-sm text-zinc-400 hover:text-white transition-colors">Testimonials</a>
                </div>
                
                <!-- Copyright -->
                <div class="text-center md:text-right">
                    <p class="text-sm text-zinc-500">&copy; <?= date('Y') ?> 10 Days Weekly Challenge</p>
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
        
        // Testimonial Carousel
        const carousel = document.getElementById('testimonialCarousel');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const cardWidth = 400;
        
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -cardWidth, behavior: 'smooth' });
        });
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: cardWidth, behavior: 'smooth' });
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
        
        window.addEventListener('scroll', checkReveal);
        window.addEventListener('load', checkReveal);
        
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
