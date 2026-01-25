<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Active Challenge List </title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <!-- Lexend for headings -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <!-- Noto Sans for body text -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300..800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols for icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Base CSS with Design System -->
    <style>
        /* 
        ============================================
        ACTIVE CHALLENGE DASHBOARD DESIGN SYSTEM
        ============================================
        
        COLOR SCHEME:
        - Primary: #e64c05 (orange)
        - Light Mode:
          Background: #fcf9f8 (off-white)
          Surface: #ffffff (white)
          Surface Dark: #2f1f18 (dark brown)
          Text Primary: #1c120d (dark brown)
          Text Secondary: #9e6347 (medium brown)
          Border: #e9d6ce (light beige)
        - Dark Mode:
          Background: #23150f (dark brown)
          Surface: #2f1f18 (dark brown)
          Surface Dark: #2a1e17 (darker brown)
          Text Primary: #fcf9f8 (light beige)
          Text Secondary: #d1b0a0 (medium beige)
          Border: #4a3b32 (dark brown)
        
        TYPOGRAPHY:
        - Headings: Lexend, 100-900 weights
        - Body: Noto Sans, 300-800 weights
        - Weights: 900 for main titles, 800 for card titles
        
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
        - 2xl: 1.5rem (24px)
        - full: 9999px
        
        SHADOWS:
        - sm: 0 1px 2px 0 rgba(0,0,0,0.05)
        - md: 0 4px 6px -1px rgba(0,0,0,0.1)
        - lg: 0 10px 15px -3px rgba(0,0,0,0.1)
        
        TRANSITIONS:
        - Default: 200ms ease-in-out
        - Fast: 150ms ease-in-out
        - Card hover: subtle shadow increase
        
        LAYOUT:
        - Max width: 1200px
        - Header height: 60px
        - Card dimensions: Flexible grid
        */
        
        /* CSS Custom Properties */
        :root {
            /* Colors - Light Theme */
            --color-primary: #e64c05;
            --color-primary-hover: #d14500;
            --color-primary-light: rgba(230, 76, 5, 0.1);
            --color-background: #fcf9f8;
            --color-surface: #ffffff;
            --color-surface-dark: #2f1f18;
            --color-text-primary: #1c120d;
            --color-text-secondary: #9e6347;
            --color-text-light: #fcf9f8;
            --color-border: #e9d6ce;
            --color-border-light: #f4ebe6;
            
            /* Status Colors */
            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --color-info: #3b82f6;
            
            /* Typography */
            --font-display: 'Lexend', sans-serif;
            --font-body: 'Noto Sans', sans-serif;
            
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
            --radius-2xl: 1.5rem;
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
            --max-width-container: 1200px;
            --header-height: 60px;
            --card-min-width: 300px;
        }
        
        /* Dark Theme Overrides */
        [data-theme="dark"] {
            --color-background: #23150f;
            --color-surface: #2f1f18;
            --color-surface-dark: #2a1e17;
            --color-text-primary: #fcf9f8;
            --color-text-secondary: #d1b0a0;
            --color-border: #4a3b32;
            --color-border-light: #3d2b24;
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
            color: var(--color-text-primary);
            background-color: var(--color-background);
            line-height: 1.5;
            min-height: 100vh;
            transition: background-color var(--transition-base) var(--transition-timing),
                        color var(--transition-base) var(--transition-timing);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-display);
            font-weight: 800;
            line-height: 1.2;
        }
        
        p {
            font-family: var(--font-body);
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
    HEADER NAVIGATION
    ============================================
    Design: Sticky header with logo, navigation, and user controls
    Layout: Logo left, navigation center-right, user controls right
    Responsive: Mobile menu button on small screens
    -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <svg class="logo-svg" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 42.4379C4 42.4379 14.0962 36.0744 24 41.1692C35.0664 46.8624 44 42.2078 44 42.2078L44 7.01134C44 7.01134 35.068 11.6577 24.0031 5.96913C14.0971 0.876274 4 7.27094 4 7.27094L4 42.4379Z"></path>
                    </svg>
                </div>
            
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="desktop-navigation">
                <a href="#" class="nav-link active">Challenges</a>
            </nav>
            
            <!-- User Controls -->
            <div class="user-controls">
                <button class="notification-button" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-dot"></span>
                </button>
                <div class="user-avatar">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAwmbS5m8C8mHE4m6_c12ApJxyzPtzNRwpoVKRO7zHF1ACV1UfsNc7KwZ-aEOFrHoJ53dlBzyWf2PsoABLHUYD1M5MaBKZOMPh18XdQssOoq_-f0x1TYvh2Z_nHNOK_rCzYHMuMJd-rjdu-XbPaZ0U3jNdOdyQBzbEHJgV8xyRb7VIs2pztzRntYnmMDpbeH0ckJyVhtGrbljarYnFXqfo-8gDDsndpJisqxW_BT9tnhRMzuNF58W9eI5ScsFIecLgaar_vCqayAVt4" 
                         alt="Coach profile picture" 
                         class="avatar-image"/>
                </div>
            </div>
            
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-button" aria-label="Open menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </header>
    
    <!-- 
    ============================================
    MAIN DASHBOARD CONTENT
    ============================================
    Design: Centered container with challenge details and athlete cards
    Layout: Flexible grid with responsive breakpoints
    Max-width: 1200px for optimal content width
    -->
    <main class="dashboard-main">
        <div class="dashboard-container">
            
            <!-- 
            ====================================
            PAGE HEADER & ACTIONS
            ====================================
            Design: Challenge title with dates and action buttons
            Layout: Title left, buttons right on desktop
            Responsive: Stacked on mobile
            -->
            <div class="page-header-section">
                <div class="header-content">
                    <div class="title-section">
                        <div class="challenge-label">
                            <span class="material-symbols-outlined">calendar_month</span>
                            Active Challenge
                        </div>
                        <h1 class="page-title">Week 4: The 10-Day Grind</h1>
                        <p class="challenge-dates">
                            June 12 - June 22 • <span class="challenger-count">15 Active Challengers</span>
                        </p>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="secondary-action">
                            <span class="material-symbols-outlined">download</span>
                            Export Report
                        </button>
                        <button class="primary-action">
                            <span class="material-symbols-outlined">person_add</span>
                            Add Challenger
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- 
            ====================================
            STATISTICS SUMMARY
            ====================================
            Design: 3 stats cards with icons and trend indicators
            Layout: 3 columns on desktop, stacked on mobile
            Cards: Colored icons with metric values and trends
            -->
            <div class="stats-grid">
                <!-- Total Weight Lost Card -->
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">monitor_weight</span>
                        </div>
                        <p class="stat-label">Total Weight Lost</p>
                    </div>
                    <p class="stat-value">45 lbs</p>
                    <div class="stat-trend negative">
                        <span class="material-symbols-outlined">trending_down</span>
                        -12% from last week
                    </div>
                </article>
                
                <!-- Average Progress Card -->
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon positive">
                            <span class="material-symbols-outlined">trending_up</span>
                        </div>
                        <p class="stat-label">Avg. Progress</p>
                    </div>
                    <p class="stat-value">3.2%</p>
                    <div class="stat-trend positive">
                        <span class="material-symbols-outlined">arrow_upward</span>
                        +0.5%
                    </div>
                </article>
                
                <!-- Top Performer Card -->
                <article class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon highlight">
                            <span class="material-symbols-outlined">emoji_events</span>
                        </div>
                        <p class="stat-label">Top Performer</p>
                    </div>
                    <p class="stat-value">Sarah J.</p>
                    <div class="stat-message">Consistency is key!</div>
                </article>
            </div>
            
            <!-- 
            ====================================
            TOOLBAR SECTION
            ====================================
            Design: Search bar and filter controls
            Layout: Search left, filters right
            Responsive: Stacked on mobile, filters wrap
            -->
            <div class="toolbar-section">
                <div class="search-container">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input type="text" 
                           class="search-input" 
                           placeholder="Search challengers..."
                           aria-label="Search challengers"/>
                </div>
                
                <div class="filter-controls">
                    <button class="filter-button">
                        <span class="material-symbols-outlined">filter_list</span>
                        <span class="filter-text">Filter</span>
                    </button>
                    <button class="sort-button">
                        <span class="material-symbols-outlined">sort</span>
                        <span class="sort-text">Sort by Progress</span>
                    </button>
                    
                    <div class="view-toggle">
                        <button class="view-button active" aria-label="Grid view">
                            <span class="material-symbols-outlined">grid_view</span>
                        </button>
                        <button class="view-button" aria-label="List view">
                            <span class="material-symbols-outlined">list</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- 
            ====================================
            CHALLENGER CARDS GRID
            ====================================
            Design: Grid of athlete cards with progress tracking
            Layout: 3 columns on desktop, 2 on tablet, 1 on mobile
            Card content: Profile, stats, progress bar, actions
            Interactive: Hover effects, status indicators
            -->
            <div class="challengers-grid">
                
                <!-- Challenger Card 1: Sarah Jenkins -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuD7NuNZ5lf_DFQ5V3vZ2vcvMDUu2-wg8B1k83XJk0YSR4EhiTQbx62OWFwGOfeBpZ0x0ocnqbbV6_0j1Kb_CHClcejoN7_A2Y-cuJprYEsPhskwdNkdEiEgvgHAJEdI1QjYEqK4tM7BfTok6FbpsMwN25LC8UrPgvzLs5utP62SQCWPcRFDogIopN2R9IdHx-mq-uYBNq-mjmsmbYxBJrGDBC9C0mbU-t5kvqMLQXas13SYdaZgy5D7kIA8nBGqvt-bino3tULqYbzo" 
                                     alt="Portrait of Sarah Jenkins" 
                                     class="avatar-image"/>
                                <div class="status-indicator online" title="Online"></div>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Sarah Jenkins</h3>
                                <div class="status-label success">
                                    <span class="status-dot"></span>
                                    On Track
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">165 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">158 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-4.2%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 85%"></div>
                            </div>
                            <p class="progress-note">85% to weekly goal</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
                
                <!-- Challenger Card 2: Mike Ross -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBC-0n3jhHcbV3WyGkj2iHOlKGxaXJasJc_usfrNSAk6Pwg1STR9yzAF2BjVSrdM9qRqIXBUZpweYMhJTM8CrlvFmeyb8I4F5BqvzQwNvp64eZ6m0AIAWdlVpvOS10L96NzAuLJHmVbPPC4rLsg4216uIK6pKr9w138QbQ_q6K5JJZErc118jEc8bctQAvVI7f2p8mafAldGffBZU2RFaMQMM5GC1sp0fKARd7Hkkw0dW2YYLFI2A7UPE3IFfyTi9z4CCZ-ljAwi6u7" 
                                     alt="Portrait of Mike Ross" 
                                     class="avatar-image"/>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Mike Ross</h3>
                                <div class="status-label warning">
                                    <span class="status-dot"></span>
                                    Needs Attention
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">210 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">202 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-3.8%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 65%"></div>
                            </div>
                            <p class="progress-note">No weigh-in for 2 days</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
                
                <!-- Challenger Card 3: Jessica Pearson -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA9KEhbwEUamjK9IadkS0zp3u1_s2DcVFmjZelWZ0MNf6UeVrbVy-r94ogmrEe_gjOMyRygxGR2cYcy-9SdoHvlGnVxobFb2W83xjsnI1CZpqtyyI96cZEHZaPVK4qUy4YE6HxNwRIDK0YyaX9GG19BxsbVJwLaKe4dOd8wTk3IeabxY8AhC_PQfdOSB1W3OMscOU6UnYJxIteOLlUTqpapVL6XFpsGmFelbubiywFYf7c0zlye0fA0VIEdGGqwE-sIqSprW0FYEgvD" 
                                     alt="Portrait of Jessica Pearson" 
                                     class="avatar-image"/>
                                <div class="status-indicator online" title="Online"></div>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Jessica Pearson</h3>
                                <div class="status-label success">
                                    <span class="status-dot"></span>
                                    On Track
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">140 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">138 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-1.4%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%"></div>
                            </div>
                            <p class="progress-note">Keep Pushing!</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
                
                <!-- Challenger Card 4: Harvey Specter -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCC0SFntz92WIEYskQRaG2tr319zXmVfilOgGg2fC0p5r0Fyvup-lM9qiZ7GzgNaIQtdRfm8VFqoIaM_Yd6PKAPTkcVodpang68Z-McXmayRLjHr3Gca_4P5-bgBZPOGfPGtvvsrz2rVvdxmVdUEQACFJsUqIbR_y1kL-Z-F2G244mTz4QJaafusCpKJyqXP0_LfnCiOD86iYcphbvHG3208ljlSsbM46YZdxWAHI15-yvEPN_2jBuRaaz18qp6RMwwqXgdpUc0wwUx" 
                                     alt="Portrait of Harvey Specter" 
                                     class="avatar-image"/>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Harvey Specter</h3>
                                <div class="status-label success">
                                    <span class="status-dot"></span>
                                    On Track
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">185 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">180 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-2.7%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 80%"></div>
                            </div>
                            <p class="progress-note">Consistent performer</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
                
                <!-- Challenger Card 5: Louis Litt -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCzhL01KN360RieoAntzPyAeswCNzMsrLQ8HJKSydyoOHo7JE3XoCxXOE4d6mNzFMPvBGqJf7xX-Ttg9ZGe9NUBrrbgVBRJg9BBU31Qd9GI-AozNMVbAA_eh-PwONS0FXbT84oETZ7z6DP55gEEECMYozoqXCCi2ZVTWq6Mt9OQe8p3Yn953cHGDt8IqoEeosgq25fwoNkwGrJWBq3UFGKhcu8WmyPbmF4erw1D_hm239Nw1qaMUAeCSgHZI3S-vRyCjO0gwV8wRKmh" 
                                     alt="Portrait of Louis Litt" 
                                     class="avatar-image"/>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Louis Litt</h3>
                                <div class="status-label error">
                                    <span class="status-dot"></span>
                                    At Risk
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">195 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">192 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-1.5%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 30%"></div>
                            </div>
                            <p class="progress-note">Missed 2 weekly goals</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
                
                <!-- Challenger Card 6: Donna Paulsen -->
                <article class="challenger-card">
                    <div class="card-header">
                        <div class="profile-section">
                            <div class="profile-avatar">
                                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuD-vAF920ivxly5i-YpkS0ql5PDNEr-7V2naeSIH31rNxhgfWVAw4cvuTA1opAD94C16PlI_R2uUYbXekg-6dFT6gSviWhNxT-q34Tl5JbaJKSgN8WVT8-Wg5lU6LJzbWX4PKx7vNkJSgNvvfczCWt3UTpBh2qElAmBvwKdk3ikmqlUgFbb6HF3MIml3Rmu09uHynVhwS9i907N1jotd2fkOS5m9r6M74yxSYCEarAV089yyfrXSazHOiYHY5JreLQNG5hj_TgkOXQ0" 
                                     alt="Portrait of Donna Paulsen" 
                                     class="avatar-image"/>
                                <div class="status-indicator online" title="Online"></div>
                            </div>
                            <div class="profile-info">
                                <h3 class="challenger-name">Donna Paulsen</h3>
                                <div class="status-label success">
                                    <span class="status-dot"></span>
                                    On Track
                                </div>
                            </div>
                        </div>
                        <button class="more-options" aria-label="More options">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <div class="weight-stats">
                            <div class="weight-stat">
                                <p class="stat-label">Start Weight</p>
                                <p class="stat-value">135 <span class="stat-unit">lbs</span></p>
                            </div>
                            <div class="weight-stat">
                                <p class="stat-label">Current</p>
                                <p class="stat-value primary">130 <span class="stat-unit">lbs</span></p>
                            </div>
                        </div>
                        
                        <div class="progress-section">
                            <div class="progress-header">
                                <p class="progress-label">Progress Goal</p>
                                <span class="progress-percentage">-3.7%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 90%"></div>
                            </div>
                            <p class="progress-note">Crushing it!</p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="primary-action">
                            <span class="material-symbols-outlined">add_circle</span>
                            Log Weigh-In
                        </button>
                        <button class="secondary-action" aria-label="Chat">
                            <span class="material-symbols-outlined">chat</span>
                        </button>
                    </div>
                </article>
            </div>
            
            <!-- 
            ====================================
            LOAD MORE / VIEW ALL
            ====================================
            Design: Centered button to view all challengers
            Style: Text button with arrow icon
            Interactive: Hover effect
            -->
            <div class="load-more-section">
                <button class="view-all-button">
                    View All Challengers
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
    </main>

    <!-- 
    ============================================
    CHALLENGE DASHBOARD STYLES
    ============================================
    These styles implement the design described above
    -->
    <style>
        /* Header Styles */
        .main-header {
            position: sticky;
            top: 0;
            z-index: 50;
            height: var(--header-height);
            border-bottom: 1px solid var(--color-border);
            background-color: var(--color-surface);
            padding: 0 var(--space-md);
        }
        
        .header-container {
            max-width: var(--max-width-container);
            height: 100%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .logo-icon {
            width: 2rem;
            height: 2rem;
            color: var(--color-primary);
        }
        
        .logo-svg {
            width: 100%;
            height: 100%;
        }
        
        .brand-name {
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        /* Desktop Navigation */
        .desktop-navigation {
            display: none;
            flex: 1;
            justify-content: center;
            align-items: center;
            gap: 2rem;
        }
        
        @media (min-width: 768px) {
            .desktop-navigation {
                display: flex;
            }
        }
        
        .nav-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-primary);
            text-decoration: none;
            transition: color var(--transition-fast) var(--transition-timing);
        }
        
        .nav-link:hover {
            color: var(--color-primary);
        }
        
        .nav-link.active {
            font-weight: 700;
            color: var(--color-primary);
        }
        
        /* User Controls */
        .user-controls {
            display: none;
            align-items: center;
            gap: var(--space-md);
        }
        
        @media (min-width: 768px) {
            .user-controls {
                display: flex;
            }
        }
        
        .notification-button {
            position: relative;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            background: transparent;
            border: none;
            color: var(--color-text-primary);
            cursor: pointer;
            transition: background-color var(--transition-fast) var(--transition-timing);
        }
        
        .notification-button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        [data-theme="dark"] .notification-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .notification-dot {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: var(--radius-full);
            background-color: var(--color-primary);
        }
        
        .user-avatar {
            width: 2.5rem;
            height: 2.5rem;
        }
        
        .avatar-image {
            width: 100%;
            height: 100%;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 2px solid var(--color-border);
        }
        
        /* Mobile Menu Button */
        .mobile-menu-button {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--color-text-primary);
            border-radius: var(--radius-md);
            cursor: pointer;
        }
        
        @media (min-width: 768px) {
            .mobile-menu-button {
                display: none;
            }
        }
        
        /* Main Content */
        .dashboard-main {
            padding: var(--space-lg) var(--space-md);
            display: flex;
            justify-content: center;
        }
        
        .dashboard-container {
            width: 100%;
            max-width: var(--max-width-container);
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }
        
        /* Page Header Section */
        .page-header-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        .header-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }
        
        @media (min-width: 768px) {
            .header-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-end;
            }
        }
        
        .title-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .challenge-label {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .challenge-label .material-symbols-outlined {
            font-size: 1.125rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1.2;
        }
        
        @media (min-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
        }
        
        .challenge-dates {
            font-size: 1rem;
            color: var(--color-text-secondary);
        }
        
        .challenger-count {
            font-weight: 700;
            color: var(--color-text-primary);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: var(--space-md);
        }
        
        .secondary-action,
        .primary-action {
            height: 3rem;
            padding: 0 var(--space-lg);
            font-size: 0.875rem;
            font-weight: 700;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-base) var(--transition-timing);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            white-space: nowrap;
        }
        
        .secondary-action {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            color: var(--color-text-primary);
        }
        
        .secondary-action:hover {
            background-color: var(--color-border-light);
        }
        
        .primary-action {
            background-color: var(--color-primary);
            border: none;
            color: var(--color-text-light);
            box-shadow: var(--shadow-md);
        }
        
        .primary-action:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-md);
        }
        
        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .stat-card {
            padding: var(--space-xl);
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
        }
        
        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
        }
        
        .stat-icon.success {
            background-color: rgba(230, 76, 5, 0.1);
            color: var(--color-primary);
        }
        
        .stat-icon.positive {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--color-success);
        }
        
        .stat-icon.highlight {
            background-color: rgba(59, 130, 246, 0.1);
            color: var(--color-info);
        }
        
        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
        }
        
        .stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: var(--space-sm);
        }
        
        .stat-trend {
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .stat-trend.positive {
            color: var(--color-success);
        }
        
        .stat-trend.negative {
            color: var(--color-error);
        }
        
        .stat-message {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
            font-weight: 500;
        }
        
        /* Toolbar Section */
        .toolbar-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
            padding: var(--space-sm) 0;
        }
        
        @media (min-width: 768px) {
            .toolbar-section {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        .search-container {
            position: relative;
            flex: 1;
            min-width: 280px;
            max-width: 480px;
        }
        
        .search-icon {
            position: absolute;
            left: var(--space-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-secondary);
            font-size: 1.25rem;
        }
        
        .search-input {
            width: 100%;
            height: 2.75rem;
            padding: 0 var(--space-md) 0 calc(var(--space-md) * 2 + 1.25rem);
            font-size: 0.875rem;
            color: var(--color-text-primary);
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(230, 76, 5, 0.5);
        }
        
        .search-input::placeholder {
            color: var(--color-text-secondary);
            opacity: 0.7;
        }
        
        .filter-controls {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .filter-button,
        .sort-button {
            height: 2.75rem;
            padding: 0 var(--space-md);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            color: var(--color-text-primary);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .filter-button:hover,
        .sort-button:hover {
            background-color: var(--color-border-light);
        }
        
        .filter-text,
        .sort-text {
            display: none;
        }
        
        @media (min-width: 640px) {
            .filter-text,
            .sort-text {
                display: inline;
            }
        }
        
        .view-toggle {
            display: flex;
            gap: var(--space-xs);
        }
        
        .view-button {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-lg);
            background-color: var(--color-primary-light);
            border: none;
            color: var(--color-primary);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .view-button:not(.active) {
            background-color: transparent;
            color: var(--color-text-secondary);
        }
        
        .view-button:not(.active):hover {
            color: var(--color-primary);
        }
        
        /* Challengers Grid */
        .challengers-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }
        
        @media (min-width: 768px) {
            .challengers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .challengers-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* Challenger Card */
        .challenger-card {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: box-shadow var(--transition-base) var(--transition-timing);
            display: flex;
            flex-direction: column;
        }
        
        .challenger-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            padding: var(--space-xl);
            border-bottom: 1px solid var(--color-border-light);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .profile-section {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .profile-avatar {
            position: relative;
        }
        
        .profile-avatar .avatar-image {
            width: 3rem;
            height: 3rem;
        }
        
        .status-indicator {
            position: absolute;
            bottom: -0.25rem;
            right: -0.25rem;
            width: 1rem;
            height: 1rem;
            border-radius: var(--radius-full);
            border: 2px solid var(--color-surface);
        }
        
        .status-indicator.online {
            background-color: var(--color-success);
        }
        
        .profile-info {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .challenger-name {
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .status-label {
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .status-label.success {
            color: var(--color-success);
        }
        
        .status-label.warning {
            color: var(--color-warning);
        }
        
        .status-label.error {
            color: var(--color-error);
        }
        
        .status-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: var(--radius-full);
        }
        
        .status-label.success .status-dot {
            background-color: var(--color-success);
        }
        
        .status-label.warning .status-dot {
            background-color: var(--color-warning);
        }
        
        .status-label.error .status-dot {
            background-color: var(--color-error);
        }
        
        .more-options {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--color-text-secondary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .more-options:hover {
            color: var(--color-primary);
            background-color: var(--color-border-light);
        }
        
        .card-content {
            padding: var(--space-xl);
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }
        
        .weight-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-sm) var(--space-md);
        }
        
        .weight-stat {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .weight-stat .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        
        .weight-stat .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .weight-stat .stat-value.primary {
            color: var(--color-primary);
        }
        
        .weight-stat .stat-unit {
            font-size: 0.875rem;
            font-weight: 400;
            color: var(--color-text-secondary);
        }
        
        .progress-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .progress-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-primary);
        }
        
        .progress-percentage {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        
        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background-color: var(--color-border);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: var(--color-primary);
            border-radius: var(--radius-full);
            transition: width var(--transition-slow) var(--transition-timing);
        }
        
        .progress-note {
            font-size: 0.75rem;
            color: var(--color-text-secondary);
            text-align: right;
        }
        
        .card-actions {
            padding: var(--space-md);
            background-color: var(--color-surface-dark);
            border-top: 1px solid var(--color-border);
            display: flex;
            gap: var(--space-md);
        }
        
        .card-actions .primary-action {
            flex: 1;
            height: 2.5rem;
            font-size: 0.875rem;
        }
        
        .card-actions .secondary-action {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .card-actions .secondary-action:hover {
            color: var(--color-primary);
            border-color: var(--color-primary);
        }
        
        /* Load More Section */
        .load-more-section {
            display: flex;
            justify-content: center;
            padding: var(--space-xl) 0;
        }
        
        .view-all-button {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: color var(--transition-fast) var(--transition-timing);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .view-all-button:hover {
            color: var(--color-primary);
        }
        
        /* Dark Mode Adjustments */
        [data-theme="dark"] .secondary-action:hover {
            background-color: var(--color-surface-dark);
        }
        
        [data-theme="dark"] .filter-button:hover,
        [data-theme="dark"] .sort-button:hover {
            background-color: var(--color-surface-dark);
        }
        
        [data-theme="dark"] .card-actions {
            background-color: var(--color-surface-dark);
        }
    </style>
    
    <!-- Dashboard Functionality Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View toggle functionality
            const gridViewButton = document.querySelector('.view-button.active');
            const listViewButton = document.querySelector('.view-button:not(.active)');
            const challengersGrid = document.querySelector('.challengers-grid');
            
            if (listViewButton) {
                listViewButton.addEventListener('click', function() {
                    gridViewButton.classList.remove('active');
                    this.classList.add('active');
                    challengersGrid.classList.add('list-view');
                    
                    // In production, this would switch to a list layout
                    alert('Switching to list view. In production, this would change the layout.');
                });
            }
            
            if (gridViewButton) {
                gridViewButton.addEventListener('click', function() {
                    listViewButton.classList.remove('active');
                    this.classList.add('active');
                    challengersGrid.classList.remove('list-view');
                });
            }
            
            // Search functionality
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const cards = document.querySelectorAll('.challenger-card');
                    
                    cards.forEach(card => {
                        const name = card.querySelector('.challenger-name').textContent.toLowerCase();
                        const status = card.querySelector('.status-label').textContent.toLowerCase();
                        
                        if (name.includes(searchTerm) || status.includes(searchTerm)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
            
            // Filter and sort buttons
            const filterButton = document.querySelector('.filter-button');
            const sortButton = document.querySelector('.sort-button');
            
            if (filterButton) {
                filterButton.addEventListener('click', function() {
                    // In production, this would open a filter modal/dropdown
                    alert('Filter functionality would open here.');
                });
            }
            
            if (sortButton) {
                sortButton.addEventListener('click', function() {
                    // Sort by progress percentage
                    const grid = document.querySelector('.challengers-grid');
                    const cards = Array.from(grid.querySelectorAll('.challenger-card'));
                    
                    cards.sort((a, b) => {
                        const progressA = parseFloat(a.querySelector('.progress-percentage').textContent);
                        const progressB = parseFloat(b.querySelector('.progress-percentage').textContent);
                        return progressB - progressA; // Descending order
                    });
                    
                    // Clear and re-append sorted cards
                    cards.forEach(card => {
                        grid.appendChild(card);
                    });
                    
                    // Visual feedback
                    this.classList.add('sorted');
                    setTimeout(() => this.classList.remove('sorted'), 500);
                });
            }
            
            // Action button functionality
            const logWeighInButtons = document.querySelectorAll('.card-actions .primary-action');
            logWeighInButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.challenger-card');
                    const name = card.querySelector('.challenger-name').textContent;
                    
                    // In production, this would open a weigh-in modal
                    alert(`Log weigh-in for ${name}. This would open a form to record new weight.`);
                });
            });
            
            // Chat button functionality
            const chatButtons = document.querySelectorAll('.card-actions .secondary-action');
            chatButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.challenger-card');
                    const name = card.querySelector('.challenger-name').textContent;
                    
                    // In production, this would open a chat interface
                    alert(`Open chat with ${name}.`);
                });
            });
            
            // More options functionality
            const moreOptionsButtons = document.querySelectorAll('.more-options');
            moreOptionsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const card = this.closest('.challenger-card');
                    const name = card.querySelector('.challenger-name').textContent;
                    
                    // In production, this would open a dropdown menu
                    alert(`More options for ${name}: Edit, Remove, View Details, etc.`);
                });
            });
            
            // View all button
            const viewAllButton = document.querySelector('.view-all-button');
            if (viewAllButton) {
                viewAllButton.addEventListener('click', function() {
                    // In production, this would load more cards or navigate to full list
                    alert('Loading all challengers...');
                    
                    // Simulate loading
                    this.textContent = 'Loading...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = 'View All Challengers <span class="material-symbols-outlined">arrow_forward</span>';
                        this.disabled = false;
                        alert('All challengers loaded!');
                    }, 1000);
                });
            }
            
            // Export report button
            const exportButton = document.querySelector('.action-buttons .secondary-action');
            if (exportButton) {
                exportButton.addEventListener('click', function() {
                    // In production, this would generate and download a report
                    alert('Generating export report...');
                    
                    // Simulate report generation
                    this.textContent = 'Generating...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = '<span class="material-symbols-outlined">download</span> Export Report';
                        this.disabled = false;
                        alert('Report exported successfully!');
                    }, 1500);
                });
            }
            
            // Add challenger button
            const addChallengerButton = document.querySelector('.action-buttons .primary-action');
            if (addChallengerButton) {
                addChallengerButton.addEventListener('click', function() {
                    // In production, this would open an add challenger form
                    alert('Opening add challenger form...');
                });
            }
            
            // Notification dot animation
            const notificationDot = document.querySelector('.notification-dot');
            if (notificationDot) {
                // Pulsing animation for notifications
                setInterval(() => {
                    notificationDot.style.opacity = notificationDot.style.opacity === '0.5' ? '1' : '0.5';
                }, 1000);
            }
        });
    </script>
</body>
</html>