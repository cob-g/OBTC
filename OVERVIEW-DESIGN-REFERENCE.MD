<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Coach Dashboard Overview</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <!-- Lexend for all text (display font) -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols for icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Base CSS with Design System -->
    <style>
        /* 
        ============================================
        DASHBOARD DESIGN SYSTEM
        ============================================
        
        COLOR SCHEME:
        - Primary: #e64c05 (orange)
        - Secondary: #683FB7 (indigo-bloom)
        - Light Mode:
          Background: #f8f6f5 (light beige)
          Card Background: #ffffff (white)
          Border: #e9d6ce (light brown)
        - Dark Mode:
          Background: #23150f (dark brown)
          Card Background: #2f1c15 (darker brown)
          Border: #523d35 (brown-gray)
        
        TYPOGRAPHY:
        - All text: Lexend, 300-800 weights
        - Display weights: 800 for headers, 700 for subheaders
        - Regular text: 400-500
        
        SPACING SYSTEM (8px base):
        - xs: 0.25rem (4px)
        - sm: 0.5rem (8px)
        - md: 1rem (16px)
        - lg: 1.5rem (24px)
        - xl: 2rem (32px)
        - 2xl: 3rem (48px)
        
        BORDER RADIUS:
        - sm: 0.25rem (4px)
        - md: 0.5rem (8px)
        - lg: 0.75rem (12px)
        - xl: 1rem (16px)
        - full: 9999px (circle/pill)
        
        SHADOWS:
        - sm: 0 1px 2px 0 rgba(0,0,0,0.05)
        - md: 0 4px 6px -1px rgba(0,0,0,0.1)
        - lg: 0 10px 15px -3px rgba(0,0,0,0.1)
        
        TRANSITIONS:
        - Default: 200ms ease-in-out
        - Hover: 150ms ease-in-out
        - Active: 100ms ease-in-out
        */
        
        /* CSS Custom Properties */
        :root {
            /* Colors - Light Theme */
            --color-primary: #e64c05;
            --color-primary-hover: #d14500;
            --color-secondary: #683FB7;
            --color-secondary-hover: #5c36a1;
            --color-background: #f8f6f5;
            --color-surface: #ffffff;
            --color-surface-dark: #2f1c15;
            --color-border: #e9d6ce;
            --color-text-main: #1c120d;
            --color-text-muted: #6b7280;
            --color-text-light: #ffffff;
            
            /* Status Colors */
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --color-info: #3b82f6;
            
            /* Typography */
            --font-display: 'Lexend', sans-serif;
            
            /* Spacing */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition-fast: 150ms;
            --transition-base: 200ms;
            --transition-slow: 300ms;
            --transition-timing: ease-in-out;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            
            /* Layout */
            --max-width-content: 1200px;
            --header-height: 72px;
        }
        
        /* Dark Theme Overrides */
        [data-theme="dark"] {
            --color-background: #23150f;
            --color-surface: #2f1c15;
            --color-border: #523d35;
            --color-text-main: #ffffff;
            --color-text-muted: #9ca3af;
        }
        
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            font-size: 16px;
            height: 100%;
        }
        
        body {
            font-family: var(--font-display);
            color: var(--color-text-main);
            background-color: var(--color-background);
            line-height: 1.5;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            transition: background-color var(--transition-base) var(--transition-timing),
                        color var(--transition-base) var(--transition-timing);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 800;
            line-height: 1.2;
        }
        
        /* Material Icons */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body data-theme="light">
    <!-- 
    ============================================
    NAVIGATION HEADER
    ============================================
    Design: Fixed header with logo, notifications, and user profile
    Height: 72px (header-height)
    Background: Light background (dark in dark mode)
    Border: Bottom border for separation
    Layout: Flexbox with max-width container
    -->
    <header class="dashboard-header">
        <div class="header-container">
            <!-- 
            BRANDING AREA
            Design: Logo icon + brand name (hidden on mobile)
            Logo: 40x40px, rounded corners, primary color background
            -->
            <div class="branding">
                <div class="logo-icon">
                    <span class="material-symbols-outlined">fitness_center</span>
                </div>
                <h2 class="brand-name">10 Days Weekly Challenge</h2>
            </div>
            
            <!-- 
            USER AREA
            Design: Notifications button and user profile
            Profile: Circular image with white border
            Notifications: Circular button with hover effect
            -->
            <div class="user-actions">
                <button class="icon-button notification-button" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <div class="user-profile">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQ_pvNWFfPVVrWMy9siwuifDW-tgmCnofQQBOtO134q6q01SxTZ1SFAsdTQf9s_7KMqoDeJY0w-bKOhH_KUOFEfiQe92wUmpoz91uNUjfgbWsKKzBGJSLIBmjSsgk4VutBphzSNnkcjmqoO-MoUIWa7-vl45_y7iAZKpj78aEqtWaathAjbT5KgxBmzeWACM83bLZrhGZcHJw11SrBEELjkxLSbsiY0rl1lVcqzYX_Ku1ITEstaGS-f06N9Ss0SLTd6oFEi-cm6gSJ" 
                         alt="Coach profile picture showing a smiling fit person" 
                         class="profile-image"/>
                </div>
            </div>
        </div>
    </header>
    
    <!-- 
    ============================================
    MAIN DASHBOARD CONTENT
    ============================================
    Design: Responsive container with consistent spacing
    Layout: Flex column with gaps between sections
    Max-width: 1200px, centered on large screens
    -->
    <main class="dashboard-main">
        <div class="dashboard-container">
            
            <!-- 
            ====================================
            PAGE HEADER SECTION
            ====================================
            Design: Main title with welcome message
            Title: Large, bold text with tight tracking
            Welcome: Primary color, medium weight
            Spacing: Bottom margin for separation
            -->
            <section class="page-header-section">
                <h1 class="page-title">Coach Dashboard</h1>
                <p class="welcome-message">Welcome back, Coach!</p>
            </section>
            
            <!-- 
            ====================================
            STATS OVERVIEW CARDS
            ====================================
            Design: 3 cards in grid layout
            Mobile: 1 column
            Tablet: 2 columns
            Desktop: 3 columns
            Card design: White/dark background, border, subtle shadow
            Content: Icon + title on top, large number + status below
            -->
            <section class="stats-grid">
                <!-- Active Challenges Card -->
                <article class="stat-card">
                    <div class="card-header">
                        <p class="card-title">Active Challenges</p>
                        <span class="card-icon material-symbols-outlined">emoji_events</span>
                    </div>
                    <div class="card-content">
                        <p class="stat-number">3</p>
                        <p class="stat-status">
                            <span class="status-icon material-symbols-outlined">check_circle</span>
                            All systems go
                        </p>
                    </div>
                </article>
                
                <!-- Total Challengers Card -->
                <article class="stat-card">
                    <div class="card-header">
                        <p class="card-title">Total Challengers</p>
                        <span class="card-icon material-symbols-outlined">groups</span>
                    </div>
                    <div class="card-content">
                        <p class="stat-number">142</p>
                        <p class="stat-status success">
                            <span class="status-icon material-symbols-outlined">trending_up</span>
                            +5 this week
                        </p>
                    </div>
                </article>
                
                <!-- Current Week Status Card -->
                <article class="stat-card">
                    <div class="card-header">
                        <p class="card-title">Current Week Status</p>
                        <span class="card-icon material-symbols-outlined">today</span>
                    </div>
                    <div class="card-content">
                        <div class="week-display">
                            <p class="stat-number">Day 7<span class="week-total">/10</span></p>
                        </div>
                        <!-- Progress Bar -->
                        <div class="progress-container">
                            <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <p class="progress-label">Week 4 in progress</p>
                    </div>
                </article>
            </section>
            
            <!-- 
            ====================================
            QUICK ACTIONS SECTION
            ====================================
            Design: Two prominent CTA buttons
            Layout: Stacked on mobile, side-by-side on tablet+
            Button 1: Secondary color (indigo)
            Button 2: Primary color (orange)
            Interactive: Hover brightness, active scale
            -->
            <section class="actions-section">
                <h2 class="section-title">Quick Actions</h2>
                <div class="actions-grid">
                    <button class="action-button secondary">
                        <span class="material-symbols-outlined">person_add</span>
                        <span>Pre-Register Challenger</span>
                    </button>
                    <button class="action-button primary">
                        <span class="material-symbols-outlined">play_arrow</span>
                        <span>View Active Challenge</span>
                    </button>
                </div>
            </section>
            
            <!-- 
            ====================================
            DASHBOARD CONTENT GRID
            ====================================
            Design: Two-column layout on desktop
            Left: Recent Activity Feed (2/3 width)
            Right: Attention Needed (1/3 width)
            Mobile: Stacked vertically
            -->
            <div class="content-grid">
                
                <!-- 
                ====================================
                RECENT ACTIVITY FEED
                ====================================
                Design: List of activities with icons and timestamps
                Layout: Each item has icon, text, and timestamp
                Icons: Color-coded by activity type
                Hover: Light background change
                -->
                <section class="activity-feed">
                    <div class="feed-header">
                        <h2 class="section-title">Recent Activity</h2>
                        <button class="view-all-button">View All</button>
                    </div>
                    <div class="feed-list">
                        <!-- Activity Item 1: Completion -->
                        <article class="activity-item">
                            <div class="activity-icon success">
                                <span class="material-symbols-outlined">check_circle</span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Sarah M. completed Day 7 Workout</p>
                                <p class="activity-meta">2 minutes ago • HIIT Blast</p>
                            </div>
                        </article>
                        
                        <!-- Activity Item 2: Registration -->
                        <article class="activity-item">
                            <div class="activity-icon info">
                                <span class="material-symbols-outlined">how_to_reg</span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">John D. registered for Next Week</p>
                                <p class="activity-meta">1 hour ago • New Challenger</p>
                            </div>
                        </article>
                        
                        <!-- Activity Item 3: Warning -->
                        <article class="activity-item">
                            <div class="activity-icon warning">
                                <span class="material-symbols-outlined">warning</span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Mike T. missed 2 consecutive logs</p>
                                <p class="activity-meta">3 hours ago • Alert</p>
                            </div>
                        </article>
                        
                        <!-- Activity Item 4: Achievement -->
                        <article class="activity-item">
                            <div class="activity-icon highlight">
                                <span class="material-symbols-outlined">star</span>
                            </div>
                            <div class="activity-content">
                                <p class="activity-title">Emily R. achieved a new PR</p>
                                <p class="activity-meta">5 hours ago • Strength Training</p>
                            </div>
                        </article>
                    </div>
                </section>
                
                <!-- 
                ====================================
                ATTENTION NEEDED SECTION
                ====================================
                Design: Stack of profile cards needing attention
                Layout: Vertical stack of cards
                Card design: Left border indicates priority/type
                Content: Profile image, name, status, action button
                -->
                <section class="attention-section">
                    <h2 class="section-title">Attention Needed</h2>
                    <div class="attention-list">
                        
                        <!-- Attention Card 1: Error Priority -->
                        <article class="attention-card error-priority">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCvgO0MbntjZ2Yqnn_gPcli3KQPcQh2xtpeABNYeBw-yr1TtWbhHPib8Ql6At-UGH9u5AuT5CsDrBzdhrkcUdyY5w_sVEV0I8EEPLH4DHW0HSwClj3JJIkFE2LN9hlA3ewwqJySwP8QzOEDqGMvlcuKyCrWyiQvdxVINTKvc8YDwnw_G5bN6es9BEd7EXw9xVNSlQp34TJhdMIxknNpMH279AEZ8K4ptEQ8sUKLil6V_xoKC8WjQI9e1BwvsVeOtagLnSzFQERFQIP7" 
                                 alt="Profile picture of Mike T" 
                                 class="profile-thumbnail"/>
                            <div class="profile-info">
                                <p class="profile-name">Mike T.</p>
                                <p class="profile-status">Missed Day 3 & 4</p>
                            </div>
                            <button class="action-icon" aria-label="Send message to Mike T">
                                <span class="material-symbols-outlined">mail</span>
                            </button>
                        </article>
                        
                        <!-- Attention Card 2: Warning Priority -->
                        <article class="attention-card warning-priority">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuTYvVVQ-CyWJqFYzdwMl9HtcnyXPYadRjI-YgPi5GZImqkpXcxhGjRBJh7ddogq0EB9xz_AV3-3FzlNzp3EYak8fO0xhCFH6amlNwwxkaIOzEWkpQ0rywq4wmYzmb5q1O_S15bT6HcuBKqw6NZaKoHhY4dVhbQxY7KqwMkXeyPQjiGzBSwWx95N_IIZFMSzI4j_xd4JZVYi18ypXQsy8a3x2ThZvWiI8F5RjkX2zwfkeGA9xSzsb1UHwjYCAI_Nq_h54LEFrSkXEM" 
                                 alt="Profile picture of Jessica K" 
                                 class="profile-thumbnail"/>
                            <div class="profile-info">
                                <p class="profile-name">Jessica K.</p>
                                <p class="profile-status">Pending payment</p>
                            </div>
                            <button class="action-icon" aria-label="Manage payment for Jessica K">
                                <span class="material-symbols-outlined">payments</span>
                            </button>
                        </article>
                        
                        <!-- Attention Card 3: Info Priority -->
                        <article class="attention-card info-priority">
                            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5ZyPCuy2sE6Y5nZdmvihKfjrpdscr4Ywogju4i2gcZImFN5R60Pezp0MrapPC2ldz04a7f78O3VmQhsSE4Gzj0eVxutKKDMat7cMLsCcF4RHQm-10b7KLV1373dYiYPb4vnGMWcroMloP2WrMEP1vMah4YlXAFe5-LKow_rHMhiHG8HkTsxUHMFKPXawMvcMYyCXUb14ektxvrjQDNm4yYYOImsML7zkOnKtixxEpgF5xkZ7m9uMkyWJuSlInPFptoLmi-9nX8RCY" 
                                 alt="Profile picture of Alex B" 
                                 class="profile-thumbnail"/>
                            <div class="profile-info">
                                <p class="profile-name">Alex B.</p>
                                <p class="profile-status">Requested feedback</p>
                            </div>
                            <button class="action-icon" aria-label="Chat with Alex B">
                                <span class="material-symbols-outlined">chat</span>
                            </button>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- 
    ============================================
    DASHBOARD STYLES
    ============================================
    These styles implement the design described above
    -->
    <style>
        /* Header Styles */
        .dashboard-header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            height: var(--header-height);
            border-bottom: 1px solid var(--color-border);
            background-color: var(--color-background);
            padding: 0 var(--space-md);
            display: flex;
            align-items: center;
        }
        
        .header-container {
            width: 100%;
            max-width: var(--max-width-content);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .branding {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            background-color: rgba(230, 76, 5, 0.1);
            color: var(--color-primary);
        }
        
        .brand-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text-main);
            display: none;
        }
        
        .user-actions {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .icon-button {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            background: transparent;
            border: none;
            color: var(--color-text-main);
            cursor: pointer;
            transition: background-color var(--transition-fast) var(--transition-timing);
        }
        
        .icon-button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        [data-theme="dark"] .icon-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .user-profile {
            width: 2.5rem;
            height: 2.5rem;
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 2px solid var(--color-surface);
        }
        
        /* Main Dashboard Styles */
        .dashboard-main {
            flex: 1;
            padding: var(--space-xl) var(--space-md);
        }
        
        .dashboard-container {
            width: 100%;
            max-width: var(--max-width-content);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: var(--space-2xl);
        }
        
        /* Page Header Section */
        .page-header-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .page-title {
            font-size: 2.25rem;
            font-weight: 900;
            letter-spacing: -0.033em;
            color: var(--color-text-main);
        }
        
        @media (min-width: 768px) {
            .page-title {
                font-size: 3rem;
            }
        }
        
        .welcome-message {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--color-primary);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-md);
        }
        
        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .stat-card {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            background-color: var(--color-surface);
            padding: var(--space-xl);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
            transition: box-shadow var(--transition-base) var(--transition-timing);
        }
        
        .stat-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 500;
            color: var(--color-text-muted);
        }
        
        .card-icon {
            color: var(--color-primary);
            font-size: 1.5rem;
        }
        
        .card-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--color-text-main);
            line-height: 1;
        }
        
        .stat-status {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-success);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .stat-status.success {
            color: var(--color-success);
        }
        
        .status-icon {
            font-size: 1rem;
        }
        
        .week-display {
            display: flex;
            align-items: baseline;
            gap: var(--space-xs);
        }
        
        .week-total {
            font-size: 1.5rem;
            color: var(--color-text-muted);
            margin-left: var(--space-xs);
        }
        
        .progress-container {
            width: 100%;
            height: 0.5rem;
            border-radius: var(--radius-full);
            background-color: rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        [data-theme="dark"] .progress-container {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .progress-bar {
            height: 100%;
            border-radius: var(--radius-full);
            background-color: var(--color-primary);
        }
        
        .progress-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-primary);
        }
        
        /* Actions Section */
        .actions-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--color-text-main);
        }
        
        .actions-grid {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        @media (min-width: 640px) {
            .actions-grid {
                flex-direction: row;
            }
        }
        
        .action-button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            padding: var(--space-lg) var(--space-xl);
            border: none;
            border-radius: var(--radius-xl);
            font-weight: 700;
            color: var(--color-text-light);
            cursor: pointer;
            transition: all var(--transition-base) var(--transition-timing);
            min-width: 0;
            max-width: 100%;
        }
        
        @media (min-width: 640px) {
            .action-button {
                max-width: 20rem;
            }
        }
        
        .action-button.primary {
            background-color: var(--color-primary);
            box-shadow: 0 4px 6px -1px rgba(230, 76, 5, 0.1);
        }
        
        .action-button.primary:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .action-button.primary:active {
            transform: translateY(0);
        }
        
        .action-button.secondary {
            background-color: var(--color-secondary);
            box-shadow: 0 4px 6px -1px rgba(104, 63, 183, 0.1);
        }
        
        .action-button.secondary:hover {
            background-color: var(--color-secondary-hover);
            filter: brightness(110%);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .action-button.secondary:active {
            transform: scale(0.98);
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-2xl);
        }
        
        @media (min-width: 1024px) {
            .content-grid {
                grid-template-columns: 2fr 1fr;
                gap: var(--space-xl);
            }
        }
        
        /* Activity Feed */
        .activity-feed {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        .feed-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .view-all-button {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-primary);
            background: none;
            border: none;
            cursor: pointer;
            transition: color var(--transition-fast) var(--transition-timing);
        }
        
        .view-all-button:hover {
            color: var(--color-primary-hover);
        }
        
        .feed-list {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            background-color: var(--color-surface);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: var(--space-md) var(--space-xl);
            transition: background-color var(--transition-fast) var(--transition-timing);
        }
        
        .activity-item:not(:last-child) {
            border-bottom: 1px solid var(--color-border);
        }
        
        .activity-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        [data-theme="dark"] .activity-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .activity-icon {
            width: 2.5rem;
            height: 2.5rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            font-size: 1.25rem;
        }
        
        .activity-icon.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--color-success);
        }
        
        .activity-icon.info {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--color-info);
        }
        
        .activity-icon.warning {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--color-error);
        }
        
        .activity-icon.highlight {
            background-color: rgba(230, 76, 5, 0.1);
            color: var(--color-primary);
        }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text-main);
            margin-bottom: var(--space-xs);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .activity-meta {
            font-size: 0.75rem;
            color: var(--color-text-muted);
        }
        
        /* Attention Section */
        .attention-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        .attention-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        .attention-card {
            border: 1px solid var(--color-border);
            border-left-width: 4px;
            border-radius: var(--radius-lg);
            background-color: var(--color-surface);
            padding: var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            cursor: pointer;
            transition: box-shadow var(--transition-base) var(--transition-timing);
        }
        
        .attention-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .attention-card.error-priority {
            border-left-color: var(--color-error);
        }
        
        .attention-card.warning-priority {
            border-left-color: var(--color-warning);
        }
        
        .attention-card.info-priority {
            border-left-color: var(--color-info);
        }
        
        .profile-thumbnail {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-full);
            object-fit: cover;
            background-color: var(--color-border);
            flex-shrink: 0;
        }
        
        .profile-info {
            flex: 1;
            min-width: 0;
        }
        
        .profile-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--color-text-main);
            margin-bottom: var(--space-xs);
        }
        
        .profile-status {
            font-size: 0.75rem;
            font-weight: 400;
        }
        
        .attention-card.error-priority .profile-status {
            color: var(--color-error);
        }
        
        .attention-card.warning-priority .profile-status {
            color: var(--color-warning);
        }
        
        .attention-card.info-priority .profile-status {
            color: var(--color-info);
        }
        
        .action-icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            background-color: rgba(0, 0, 0, 0.05);
            border: none;
            color: var(--color-text-muted);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
            flex-shrink: 0;
        }
        
        .action-icon:hover {
            background-color: var(--color-primary);
            color: var(--color-text-light);
        }
        
        [data-theme="dark"] .action-icon {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Dark Mode Adjustments */
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .feed-list,
        [data-theme="dark"] .attention-card {
            background-color: var(--color-surface-dark);
        }
        
        [data-theme="dark"] .activity-item:not(:last-child) {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .card-title {
            color: rgba(255, 255, 255, 0.7);
        }
        
        [data-theme="dark"] .activity-meta {
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Responsive Brand Name */
        @media (min-width: 640px) {
            .brand-name {
                display: block;
            }
        }
    </style>
    
</body>
</html>