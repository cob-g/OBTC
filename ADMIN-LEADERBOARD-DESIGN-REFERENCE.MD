<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>FitCoach - Challenge Leaderboard</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <!-- Lexend font -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Base CSS with Design System -->
    <style>
        /* 
        ============================================
        CHALLENGE LEADERBOARD DESIGN SYSTEM
        ============================================
        
        COLOR SCHEME:
        - Primary: #e64c05 (Molten Orange)
        - Secondary: #f97316 (Pumpkin Spice)
        - Light Mode:
          Background: #fcf9f8 (off-white)
          Surface: #ffffff (white)
          Text Primary: #1c120d (dark brown)
          Text Secondary: #9e6347 (medium brown)
          Border: #e9d6ce (light beige)
        - Dark Mode:
          Background: #1f1815 (dark brown)
          Surface: #2d2420 (darker brown)
          Text Primary: #ffffff (white)
          Text Secondary: #9e6347 (medium brown)
          Border: #3d2f2a (dark brown)
        
        TYPOGRAPHY:
        - All text: Lexend, 300-900 weights
        - Headings: 900 weight for main titles, 800 for subtitles
        - Table headers: 700 weight, uppercase
        - Body text: 400-500 weights
        
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
        - primary: 0 4px 12px rgba(230, 76, 5, 0.2)
        
        TRANSITIONS:
        - Default: 200ms ease-in-out
        - Fast: 150ms ease-in-out
        - Table row hover: 150ms ease-in-out
        - Card hover: 200ms ease-in-out
        
        LAYOUT:
        - Max width: 1280px
        - Header height: 64px
        - Podium card heights: Elevated for 1st place
        - Table row height: 72px
        */
        
        /* CSS Custom Properties */
        :root {
            /* Colors - Light Theme */
            --color-primary: #e64c05;
            --color-primary-hover: #d14500;
            --color-primary-light: rgba(230, 76, 5, 0.1);
            --color-secondary: #f97316;
            --color-secondary-light: rgba(249, 115, 22, 0.1);
            --color-background: #fcf9f8;
            --color-surface: #ffffff;
            --color-text-primary: #1c120d;
            --color-text-secondary: #9e6347;
            --color-text-light: #ffffff;
            --color-border: #e9d6ce;
            --color-border-light: #f4ebe6;
            
            /* Podium Colors */
            --color-gold: #e64c05;
            --color-silver: #f97316;
            --color-bronze: #f97316;
            
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
            --shadow-primary: 0 4px 12px rgba(230, 76, 5, 0.2);
            
            /* Layout */
            --max-width-container: 1280px;
            --header-height: 64px;
            --table-row-height: 72px;
        }
        
        /* Dark Theme Overrides */
        [data-theme="dark"] {
            --color-background: #1f1815;
            --color-surface: #2d2420;
            --color-text-primary: #ffffff;
            --color-text-secondary: #9e6347;
            --color-border: #3d2f2a;
            --color-border-light: #4a3b32;
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
            display: flex;
            flex-direction: column;
            transition: background-color var(--transition-base) var(--transition-timing),
                        color var(--transition-base) var(--transition-timing);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 800;
            line-height: 1.2;
        }
        
        /* Material Icons - Filled by default */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body data-theme="light">
    <!-- 
    ============================================
    HEADER NAVIGATION
    ============================================
    Design: Sticky header with logo, navigation, and profile
    Layout: Logo left, navigation center-right, profile right
    Visual: Glass-morphism effect with backdrop blur
    Border: Bottom border for separation
    -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo -->
            <div class="logo-section">
                <div class="logo-icon">
                    <span class="material-symbols-outlined">fitness_center</span>
                </div>
            </div>
            
            
            <!-- Profile & Notifications -->
            <div class="profile-section">
                <button class="notification-button" aria-label="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <div class="user-avatar">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBwb616hnbas0mnWBHWmb9nJ9A5FhVB3y4TCit2UTl21ZUfMAdgKZR9mKNgPAFe448-mps-YukeeCSWDJR-nVisLQjmG1ffWzFd5D-IbLcWtgXdC5QwfTx9b1LBsuor0gRmhkh0L4mPzV2DVbJuVE4DisLu_K7ARzggyJS2JfLoASYzoHcirVqYBEMXs60KUUctL2Agz9ITMcNg6fuHpWLDo4xaEM3M37wE_L_XlukQpU6okP5J_hqXR5HDvl5esGe9NtnxtL4yJekF" 
                         alt="Coach profile picture showing a smiling man" 
                         class="avatar-image"/>
                </div>
            </div>
        </div>
    </header>
    
    <!-- 
    ============================================
    MAIN CONTENT AREA
    ============================================
    Design: Centered container with leaderboard content
    Layout: Flex column with consistent spacing
    Max-width: 1280px for optimal readability
    Responsive padding: Adjusts for mobile/desktop
    -->
    <main class="leaderboard-main">
        <div class="leaderboard-container">
            
            <!-- 
            ====================================
            PAGE HEADER SECTION
            ====================================
            Design: Challenge title with breadcrumb and actions
            Layout: Title left, actions right on desktop
            Responsive: Stacked on mobile
            Breadcrumb: Shows path (Weekly Challenge > Week 4)
            Actions: Week selector and export button
            -->
            <header class="page-header">
                <div class="header-content">
                    <div class="title-section">
                        <div class="breadcrumb">
                            <span class="breadcrumb-item">Weekly Challenge</span>
                            <span class="breadcrumb-divider material-symbols-outlined">chevron_right</span>
                            <span class="breadcrumb-item">Week 4</span>
                        </div>
                        <h2 class="page-title">Leaderboard</h2>
                        <p class="page-description">
                            Top performers ranked by weight loss percentage for the current 10-day sprint.
                        </p>
                    </div>
                    
                    <div class="header-actions">
                        <button class="week-selector">
                            <span class="material-symbols-outlined">calendar_today</span>
                            Week 4
                        </button>
                        <button class="export-button">
                            <span class="material-symbols-outlined">download</span>
                            Export Data
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- 
            ====================================
            PODIUM SECTION (TOP 3)
            ====================================
            Design: Visual podium with 1st place elevated
            Layout: 3 cards, 1st place in middle on desktop
            Visual: Different backgrounds and badges for each rank
            Interactive: Hover effects on cards
            Positioning: 1st place slightly elevated on desktop
            -->
            <section class="podium-section">
                <!-- 2nd Place Card -->
                <article class="podium-card silver">
                    <div class="rank-badge">
                        <span class="material-symbols-outlined">military_tech</span>
                        2nd Place
                    </div>
                    <div class="participant-info">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAgxUlX75QzyF7ipFe8P8i6VHrajERl0_T_XIaglIGtuI15cFNUheV-XQlkjGAZFkdGT6KKw0osxNv18uBymoNvnkhOxFUQMc1Sz63813YYR7szt98i-58eQogiqTzrPvWb6AmEcFUpVj-cjOimDpwNTwU-BiTZiyI2NrI-txFFx8LIXEBJyTfdORFdvWUB4EXcSBpR73OPsnV7zdWs-A-nU3hysSJEDldyLIQVAxIZi_na9lTk51hBTLJ-6X5RUlkvronjAhG94Eyg" 
                             alt="Profile picture of John Smith" 
                             class="participant-image"/>
                        <div class="participant-details">
                            <h3 class="participant-name">John Smith</h3>
                            <p class="participant-plan">Basic Plan Member</p>
                        </div>
                    </div>
                    <div class="performance-stats">
                        <div class="loss-percentage">
                            <span class="percentage-value">-3.910%</span>
                            <span class="percentage-label">loss</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 85%"></div>
                        </div>
                    </div>
                </article>
                
                <!-- 1st Place Card -->
                <article class="podium-card gold">
                    <div class="rank-badge">
                        <span class="material-symbols-outlined">emoji_events</span>
                        Current Leader
                    </div>
                    <div class="participant-info">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA796zXOXSeTpH40XnQitDgqfssQkFFSuQA8470L1XLHRO-M7CMMHoVzhcs_jOPio0eQz07d_sFybLA5ZoB1oA3z7Z2PgHMfbikKaOvRCfG7_ici5n_Uucg6lIQKC8IrAsygaRkhnk8pxlAerrr5opppSp8p15YUxHjQZFVZq_g5jzEVX2rgJ1W_13YynjaxdrY-UIJzV9P-zQCBDMvw7fqtmLZPIgxgNbommiHutLe-5ChnG7qeG2gmXny9m91gEmZojT7scz7EWra" 
                             alt="Profile picture of Jane Doe" 
                             class="participant-image"/>
                        <div class="participant-details">
                            <h3 class="participant-name">Jane Doe</h3>
                            <p class="participant-plan">Pro Plan Member</p>
                        </div>
                    </div>
                    <div class="performance-stats">
                        <div class="loss-percentage">
                            <span class="percentage-value">-4.235%</span>
                            <span class="percentage-label">Total Weight Loss</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 95%"></div>
                        </div>
                    </div>
                </article>
                
                <!-- 3rd Place Card -->
                <article class="podium-card bronze">
                    <div class="rank-badge">
                        <span class="material-symbols-outlined">military_tech</span>
                        3rd Place
                    </div>
                    <div class="participant-info">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuADap2prdf9GwprzNX7AMocnNWJA9WU1vLj_zIU5F111INO2SwhvYgXjMxJ41yva-H7CzvQnvuZAIfIGnRx1Mo5vp-b3qOAsvwutJe_v6FMOdYnhq5Z64ziJi1kz0lKCxbu7phU93nb1xFElJTkjiI_13RG4HPBVuKmnSni7wDgwk9xRbH3ddEKtAiDmceQBDgOzxPNj7OzatMFzEUIllAPyXvXMrS1ljCW5xYXXlz5ee7a_23ABSuk88Za94ACu6NIGOX7yf9V6ovP" 
                             alt="Profile picture of Alice Wong" 
                             class="participant-image"/>
                        <div class="participant-details">
                            <h3 class="participant-name">Alice Wong</h3>
                            <p class="participant-plan">Basic Plan Member</p>
                        </div>
                    </div>
                    <div class="performance-stats">
                        <div class="loss-percentage">
                            <span class="percentage-value">-3.500%</span>
                            <span class="percentage-label">loss</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 70%"></div>
                        </div>
                    </div>
                </article>
            </section>
            
            <!-- 
            ====================================
            SEARCH AND FILTERS BAR
            ====================================
            Design: Search input with filter controls
            Layout: Search left, filters right
            Responsive: Stacked on mobile, side by side on desktop
            Search: Icon inside input field
            Filters: Dropdown and filter button
            -->
            <div class="filter-bar">
                <div class="search-container">
                    <span class="search-icon material-symbols-outlined">search</span>
                    <input type="text" 
                           class="search-input" 
                           placeholder="Search participant name..."
                           aria-label="Search participants"/>
                </div>
                
                <div class="filter-controls">
                    <span class="filter-label">Filter by:</span>
                    <select class="group-filter" aria-label="Filter by group">
                        <option value="all">All Groups</option>
                        <option value="beginners">Beginners</option>
                        <option value="advanced">Advanced</option>
                    </select>
                    <button class="filter-button" aria-label="More filters">
                        <span class="material-symbols-outlined">tune</span>
                    </button>
                </div>
            </div>
            
            <!-- 
            ====================================
            LEADERBOARD TABLE
            ====================================
            Design: Scrollable table with ranking data
            Layout: 6 columns: Rank, Participant, Start Weight, Current Weight, Loss, Weight Loss %
            Visual: Alternating hover states, color-coded ranks
            Interactive: Hover effects, clickable rows in production
            Accessibility: Semantic table with proper headers
            -->
            <div class="leaderboard-table-container">
                <table class="leaderboard-table" aria-label="Challenge leaderboard">
                    <thead>
                        <tr>
                            <th scope="col" class="rank-column">Rank</th>
                            <th scope="col" class="participant-column">Participant</th>
                            <th scope="col" class="start-column">Start</th>
                            <th scope="col" class="current-column">Current</th>
                            <th scope="col" class="loss-column">Loss</th>
                            <th scope="col" class="percentage-column">Weight Loss %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rank 1: Jane Doe -->
                        <tr class="table-row gold-row">
                            <td class="rank-cell">
                                <div class="rank-icon gold">
                                    <span class="material-symbols-outlined">emoji_events</span>
                                </div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDACQ4uiYoIRnk6vclBTl9Q0a0ftcyvXdzvZR32EiSLUETM8RJEJ7GL1BIaAecQmScXqrwK6yKnH77sXbfTNVqjNsuMeg1vHFCTlDWsmFVZqZlLumzpcRoJjeh--HgOmz8XoJslNR0IgWw73w5VFBvvwrtu6RVYB9JKu4P9Oh2P0LZyd2MXDCdRwQCWHaL-nhHnugyUfXvTZaqzXCxL8Cfgbs6zDdUvvZEu73uGAMVfCyPDjAaL4cZl4gqzuCdbGpRjjE45NlHpmpf8" 
                                         alt="Jane Doe profile" 
                                         class="participant-image"/>
                                    <div class="participant-details">
                                        <p class="participant-name">Jane Doe</p>
                                        <p class="participant-handle">@janed_fit</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                180.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                172.4 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -7.6
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-badge gold">
                                    <span class="material-symbols-outlined">trending_down</span>
                                    4.235%
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Rank 2: John Smith -->
                        <tr class="table-row silver-row">
                            <td class="rank-cell">
                                <div class="rank-icon silver">
                                    <span class="material-symbols-outlined">military_tech</span>
                                </div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_OG7OeJmb1dO0bno8GGlCDAQB-CoyiqDSOlmJ2EJw14pkFk8eIaeH3IkvGojIpG9DeFjlICQKUtmV9Y2-thlNWNPo40M500SnRTGscLdm0NAXjfU92-PKuN39Tc4jl1n2bm_ODPoqR554KeCEWaMzcA5dNi7clQccmPEWejO6ofENWuVI3nJ6bJDS7erOgganTqviOtJeoVJMgS7coQ3FmW4YFUjGIdR89xh7Bt98ZZjBZF-1t8ZDj2mFspB6ebEla2DUfcIb1wmZ" 
                                         alt="John Smith profile" 
                                         class="participant-image"/>
                                    <div class="participant-details">
                                        <p class="participant-name">John Smith</p>
                                        <p class="participant-handle">@jsmith_99</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                210.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                201.8 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -8.2
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-badge silver">
                                    <span class="material-symbols-outlined">trending_down</span>
                                    3.910%
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Rank 3: Alice Wong -->
                        <tr class="table-row bronze-row">
                            <td class="rank-cell">
                                <div class="rank-icon bronze">
                                    <span class="material-symbols-outlined">military_tech</span>
                                </div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuATBdan4qUrIq2fRcokV2h5tr7oHjoAKRopyaCFfRRLXU3OqmAjhFsKu8mQBmWRUwvrWoY68T9JDZjXJxujXkGyCxVvml9YIurgj8lJniNFtI-K6MSUjYtD5Gw1ICI7Z77iCw1nI4gyfvS5ax_6pnAQWEkkgNB12eDOjyWQZ9pgpnMcnovjPBmsQzEHQ__Mjpmc_VD4K4cE5sJcLyEvG26XTSJobZWZPzfIQI2OHQfKP3L9yLXERka2uOAup8uKisa2OwDItbfO1yJV" 
                                         alt="Alice Wong profile" 
                                         class="participant-image"/>
                                    <div class="participant-details">
                                        <p class="participant-name">Alice Wong</p>
                                        <p class="participant-handle">@alice_w</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                150.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                144.7 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -5.3
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-badge bronze">
                                    <span class="material-symbols-outlined">trending_down</span>
                                    3.500%
                                </span>
                            </td>
                        </tr>
                        
                        <!-- Rank 4: Mike Chen -->
                        <tr class="table-row">
                            <td class="rank-cell">
                                <div class="rank-number">4</div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <div class="participant-image placeholder"></div>
                                    <div class="participant-details">
                                        <p class="participant-name">Mike Chen</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                195.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                189.1 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -5.9
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-text">3.025%</span>
                            </td>
                        </tr>
                        
                        <!-- Rank 5: Sarah Connor -->
                        <tr class="table-row">
                            <td class="rank-cell">
                                <div class="rank-number">5</div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <div class="participant-image placeholder"></div>
                                    <div class="participant-details">
                                        <p class="participant-name">Sarah Connor</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                165.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                160.5 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -4.5
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-text">2.727%</span>
                            </td>
                        </tr>
                        
                        <!-- Rank 6: David Miller -->
                        <tr class="table-row">
                            <td class="rank-cell">
                                <div class="rank-number">6</div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <div class="participant-image placeholder"></div>
                                    <div class="participant-details">
                                        <p class="participant-name">David Miller</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                220.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                214.5 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -5.5
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-text">2.500%</span>
                            </td>
                        </tr>
                        
                        <!-- Rank 7: Emily Stone -->
                        <tr class="table-row">
                            <td class="rank-cell">
                                <div class="rank-number">7</div>
                            </td>
                            <td class="participant-cell">
                                <div class="participant-info">
                                    <div class="participant-image placeholder"></div>
                                    <div class="participant-details">
                                        <p class="participant-name">Emily Stone</p>
                                    </div>
                                </div>
                            </td>
                            <td class="weight-cell start-weight">
                                140.0 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="weight-cell current-weight">
                                136.8 <span class="weight-unit">lbs</span>
                            </td>
                            <td class="loss-cell">
                                -3.2
                            </td>
                            <td class="percentage-cell">
                                <span class="percentage-text">2.285%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- 
                ====================================
                PAGINATION CONTROLS
                ====================================
                Design: Page info with navigation buttons
                Layout: Info left, navigation right
                Buttons: Previous/Next with disabled states
                Info: Showing X-Y of Z participants
                -->
                <div class="pagination-controls">
                    <div class="page-info">
                        Showing <span class="highlight">1-7</span> of <span class="highlight">42</span> participants
                    </div>
                    <div class="page-navigation">
                        <button class="nav-button prev" disabled aria-label="Previous page">
                            <span class="material-symbols-outlined">chevron_left</span>
                        </button>
                        <button class="nav-button next" aria-label="Next page">
                            <span class="material-symbols-outlined">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 
    ============================================
    LEADERBOARD STYLES
    ============================================
    These styles implement the design described above
    -->
    <style>
        /* Header Styles */
        .main-header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            height: var(--header-height);
            border-bottom: 1px solid var(--color-border);
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        
        [data-theme="dark"] .main-header {
            background-color: rgba(45, 36, 32, 0.95);
        }
        
        .header-container {
            max-width: var(--max-width-container);
            height: 100%;
            margin: 0 auto;
            padding: 0 var(--space-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        @media (min-width: 1024px) {
            .header-container {
                padding: 0 var(--space-lg);
            }
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .logo-icon {
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius-lg);
            background-color: var(--color-primary);
            color: var(--color-text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .brand-name {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        /* Desktop Navigation */
        .desktop-navigation {
            display: none;
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
            color: var(--color-text-secondary);
            text-decoration: none;
            transition: color var(--transition-fast) var(--transition-timing);
        }
        
        .nav-link:hover {
            color: var(--color-primary);
        }
        
        .nav-link.active {
            color: var(--color-primary);
        }
        
        /* Profile Section */
        .profile-section {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .notification-button {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: color var(--transition-fast) var(--transition-timing);
        }
        
        .notification-button:hover {
            color: var(--color-text-primary);
        }
        
        .user-avatar {
            width: 2.25rem;
            height: 2.25rem;
        }
        
        .avatar-image {
            width: 100%;
            height: 100%;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 2px solid var(--color-border);
        }
        
        /* Main Content */
        .leaderboard-main {
            flex: 1;
            width: 100%;
            padding: var(--space-xl) var(--space-md);
        }
        
        .leaderboard-container {
            max-width: var(--max-width-container);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }
        
        /* Page Header */
        .page-header {
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
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }
        
        .breadcrumb-item {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .breadcrumb-divider {
            font-size: 0.875rem;
            color: var(--color-primary);
            opacity: 0.8;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 900;
        }
        
        @media (min-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
        }
        
        .page-description {
            font-size: 1rem;
            color: var(--color-text-secondary);
            max-width: 36rem;
        }
        
        /* Header Actions */
        .header-actions {
            display: flex;
            gap: var(--space-md);
        }
        
        .week-selector,
        .export-button {
            height: 2.5rem;
            padding: 0 var(--space-md);
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            white-space: nowrap;
        }
        
        .week-selector {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            color: var(--color-text-primary);
        }
        
        .week-selector:hover {
            background-color: var(--color-border-light);
        }
        
        .export-button {
            background-color: var(--color-primary);
            border: none;
            color: var(--color-text-light);
            font-weight: 700;
            box-shadow: var(--shadow-primary);
        }
        
        .export-button:hover {
            background-color: var(--color-primary-hover);
        }
        
        /* Podium Section */
        .podium-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-md);
        }
        
        @media (min-width: 768px) {
            .podium-section {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }
        
        .podium-card {
            position: relative;
            padding: var(--space-xl);
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border);
            background-color: var(--color-surface);
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
            transition: all var(--transition-base) var(--transition-timing);
        }
        
        @media (min-width: 768px) {
            .podium-card.silver {
                margin-top: var(--space-md);
                order: 1;
            }
            
            .podium-card.gold {
                transform: translateY(-1rem);
                order: 2;
                z-index: 10;
            }
            
            .podium-card.bronze {
                margin-top: var(--space-md);
                order: 3;
            }
        }
        
        .podium-card.gold {
            background: linear-gradient(135deg, var(--color-surface) 0%, rgba(230, 76, 5, 0.05) 100%);
            border: 2px solid rgba(230, 76, 5, 0.2);
        }
        
        [data-theme="dark"] .podium-card.gold {
            background: linear-gradient(135deg, var(--color-surface) 0%, rgba(230, 76, 5, 0.1) 100%);
        }
        
        .podium-card:hover {
            border-color: var(--color-primary);
        }
        
        .rank-badge {
            position: absolute;
            top: -0.75rem;
            left: var(--space-xl);
            padding: var(--space-xs) var(--space-sm);
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            white-space: nowrap;
        }
        
        .podium-card.gold .rank-badge {
            background-color: var(--color-primary);
            color: var(--color-text-light);
            box-shadow: var(--shadow-primary);
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.875rem;
            padding: var(--space-sm) var(--space-md);
        }
        
        .podium-card.silver .rank-badge {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-secondary);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        .podium-card.bronze .rank-badge {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-secondary);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }
        
        .participant-info {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-top: var(--space-sm);
        }
        
        .podium-card.gold .participant-info {
            flex-direction: column;
            text-align: center;
            gap: var(--space-sm);
        }
        
        .participant-image {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 2px solid var(--color-border);
        }
        
        .podium-card.gold .participant-image {
            width: 5rem;
            height: 5rem;
            border: 4px solid rgba(230, 76, 5, 0.2);
        }
        
        .podium-card.silver .participant-image {
            border-color: rgba(249, 115, 22, 0.3);
        }
        
        .podium-card.bronze .participant-image {
            border-color: rgba(249, 115, 22, 0.2);
        }
        
        .participant-details {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .podium-card.gold .participant-details {
            align-items: center;
        }
        
        .participant-name {
            font-size: 1.125rem;
            font-weight: 700;
        }
        
        .podium-card.gold .participant-name {
            font-size: 1.25rem;
        }
        
        .participant-plan {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
        }
        
        .performance-stats {
            margin-top: auto;
        }
        
        .loss-percentage {
            display: flex;
            align-items: baseline;
            gap: var(--space-xs);
            margin-bottom: var(--space-sm);
        }
        
        .podium-card.gold .loss-percentage {
            justify-content: center;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }
        
        .percentage-value {
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
        }
        
        .podium-card.gold .percentage-value {
            font-size: 2.5rem;
        }
        
        .podium-card.gold .percentage-value {
            color: var(--color-primary);
        }
        
        .podium-card.silver .percentage-value {
            color: var(--color-secondary);
        }
        
        .podium-card.bronze .percentage-value {
            color: var(--color-secondary);
        }
        
        .percentage-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
        }
        
        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background-color: var(--color-border-light);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        
        .podium-card.gold .progress-bar {
            height: 0.75rem;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width var(--transition-slow) var(--transition-timing);
        }
        
        .podium-card.gold .progress-fill {
            background-color: var(--color-primary);
        }
        
        .podium-card.silver .progress-fill {
            background-color: var(--color-secondary);
        }
        
        .podium-card.bronze .progress-fill {
            background-color: var(--color-secondary);
        }
        
        /* Filter Bar */
        .filter-bar {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: var(--space-sm);
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        @media (min-width: 640px) {
            .filter-bar {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        .search-container {
            position: relative;
            flex: 1;
            min-width: 0;
        }
        
        @media (min-width: 640px) {
            .search-container {
                max-width: 28rem;
            }
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
            background-color: var(--color-background);
            border: 1px solid transparent;
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .search-input::placeholder {
            color: var(--color-text-secondary);
        }
        
        .filter-controls {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
            display: none;
        }
        
        @media (min-width: 640px) {
            .filter-label {
                display: inline;
            }
        }
        
        .group-filter {
            height: 2.5rem;
            padding: 0 var(--space-md);
            font-size: 0.875rem;
            color: var(--color-text-primary);
            background-color: var(--color-background);
            border: 1px solid transparent;
            border-radius: var(--radius-lg);
            cursor: pointer;
            min-width: 8.75rem;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }
        
        .group-filter:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .filter-button {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: color var(--transition-fast) var(--transition-timing);
        }
        
        .filter-button:hover {
            color: var(--color-primary);
        }
        
        /* Leaderboard Table Container */
        .leaderboard-table-container {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        /* Leaderboard Table */
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .leaderboard-table thead {
            background-color: var(--color-background);
            border-bottom: 1px solid var(--color-border);
        }
        
        .leaderboard-table th {
            padding: var(--space-md) var(--space-xl);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        
        .leaderboard-table th.rank-column {
            width: 6rem;
            text-align: center;
        }
        
        .leaderboard-table th.participant-column {
            text-align: left;
        }
        
        .leaderboard-table th.start-column,
        .leaderboard-table th.current-column,
        .leaderboard-table th.loss-column,
        .leaderboard-table th.percentage-column {
            text-align: right;
        }
        
        /* Table Rows */
        .table-row {
            border-bottom: 1px solid var(--color-border);
            transition: background-color var(--transition-fast) var(--transition-timing);
            height: var(--table-row-height);
        }
        
        .table-row:hover {
            background-color: rgba(230, 76, 5, 0.05);
        }
        
        .table-row:last-child {
            border-bottom: none;
        }
        
        .table-row td {
            padding: var(--space-md) var(--space-xl);
        }
        
        /* Rank Column */
        .rank-cell {
            text-align: center;
        }
        
        .rank-icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            margin: 0 auto;
        }
        
        .rank-icon.gold {
            background-color: var(--color-primary-light);
            color: var(--color-primary);
        }
        
        .rank-icon.silver {
            background-color: var(--color-secondary-light);
            color: var(--color-secondary);
        }
        
        .rank-icon.bronze {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-secondary);
        }
        
        .rank-number {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            background-color: var(--color-border-light);
            color: var(--color-text-secondary);
            font-size: 0.875rem;
            font-weight: 700;
            margin: 0 auto;
        }
        
        /* Participant Column */
        .participant-cell .participant-info {
            display: flex;
            align-items: center;
            gap: var(--space-md);
        }
        
        .participant-cell .participant-image {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 1px solid var(--color-border);
        }
        
        .participant-cell .participant-image.placeholder {
            background-color: var(--color-border);
        }
        
        .participant-cell .participant-details {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .participant-cell .participant-name {
            font-size: 1rem;
            font-weight: 700;
        }
        
        .participant-cell .participant-handle {
            font-size: 0.75rem;
            color: var(--color-text-secondary);
        }
        
        /* Weight Cells */
        .weight-cell {
            font-size: 1rem;
            font-weight: 500;
            text-align: right;
        }
        
        .start-weight {
            color: var(--color-text-secondary);
        }
        
        .current-weight {
            color: var(--color-text-primary);
            font-weight: 700;
        }
        
        .weight-unit {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--color-text-secondary);
            margin-left: var(--space-xs);
        }
        
        /* Loss Cell */
        .loss-cell {
            font-size: 1rem;
            font-weight: 500;
            color: var(--color-text-primary);
            text-align: right;
        }
        
        .gold-row .loss-cell {
            color: var(--color-primary);
        }
        
        .silver-row .loss-cell {
            color: var(--color-secondary);
        }
        
        .bronze-row .loss-cell {
            color: var(--color-secondary);
        }
        
        /* Percentage Cell */
        .percentage-cell {
            text-align: right;
        }
        
        .percentage-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 700;
            font-family: monospace;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .percentage-badge.gold {
            background-color: var(--color-primary-light);
            color: var(--color-primary);
        }
        
        .gold-row:hover .percentage-badge.gold {
            background-color: var(--color-primary);
            color: var(--color-text-light);
        }
        
        .percentage-badge.silver {
            background-color: var(--color-secondary-light);
            color: var(--color-secondary);
        }
        
        .silver-row:hover .percentage-badge.silver {
            background-color: var(--color-secondary);
            color: var(--color-text-light);
        }
        
        .percentage-badge.bronze {
            background-color: rgba(249, 115, 22, 0.1);
            color: var(--color-secondary);
        }
        
        .bronze-row:hover .percentage-badge.bronze {
            background-color: var(--color-secondary);
            color: var(--color-text-light);
        }
        
        .percentage-text {
            font-family: monospace;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-primary);
        }
        
        /* Pagination Controls */
        .pagination-controls {
            border-top: 1px solid var(--color-border);
            background-color: var(--color-background);
            padding: var(--space-md) var(--space-xl);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-info {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
        }
        
        .page-info .highlight {
            font-weight: 700;
            color: var(--color-text-primary);
        }
        
        .page-navigation {
            display: flex;
            gap: var(--space-xs);
        }
        
        .nav-button {
            width: 2rem;
            height: 2rem;
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
        
        .nav-button:hover:not(:disabled) {
            background-color: var(--color-border-light);
        }
        
        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Responsive Table */
        @media (max-width: 768px) {
            .leaderboard-table {
                display: block;
                overflow-x: auto;
            }
            
            .leaderboard-table th,
            .leaderboard-table td {
                white-space: nowrap;
            }
        }
        
        /* Dark Mode Adjustments */
        [data-theme="dark"] .filter-bar {
            background-color: var(--color-surface);
        }
        
        [data-theme="dark"] .search-input,
        [data-theme="dark"] .group-filter {
            background-color: var(--color-background);
        }
        
        [data-theme="dark"] .table-row:hover {
            background-color: rgba(230, 76, 5, 0.1);
        }
        
        [data-theme="dark"] .rank-number {
            background-color: var(--color-border);
        }
        
        [data-theme="dark"] .percentage-badge.gold {
            background-color: rgba(230, 76, 5, 0.2);
        }
        
        [data-theme="dark"] .percentage-badge.silver {
            background-color: rgba(249, 115, 22, 0.2);
        }
        
        [data-theme="dark"] .percentage-badge.bronze {
            background-color: rgba(249, 115, 22, 0.2);
        }
    </style>
    
    <!-- Leaderboard Functionality Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.querySelector('.search-input');
            const tableRows = document.querySelectorAll('.table-row');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    tableRows.forEach(row => {
                        const nameCell = row.querySelector('.participant-name');
                        const handleCell = row.querySelector('.participant-handle');
                        const name = nameCell ? nameCell.textContent.toLowerCase() : '';
                        const handle = handleCell ? handleCell.textContent.toLowerCase() : '';
                        
                        if (name.includes(searchTerm) || handle.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Filter functionality
            const groupFilter = document.querySelector('.group-filter');
            const filterButton = document.querySelector('.filter-button');
            
            if (groupFilter) {
                groupFilter.addEventListener('change', function() {
                    const selectedGroup = this.value;
                    
                    // In production, this would filter the table
                    alert(`Filtering by group: ${selectedGroup}. In production, this would filter the leaderboard.`);
                });
            }
            
            if (filterButton) {
                filterButton.addEventListener('click', function() {
                    // In production, this would open advanced filters
                    alert('Opening advanced filter options.');
                });
            }
            
            // Export functionality
            const exportButton = document.querySelector('.export-button');
            if (exportButton) {
                exportButton.addEventListener('click', function() {
                    // Simulate export process
                    this.innerHTML = '<span class="material-symbols-outlined">download</span> Exporting...';
                    this.disabled = true;
                    
                    setTimeout(() => {
                        this.innerHTML = '<span class="material-symbols-outlined">download</span> Export Data';
                        this.disabled = false;
                        alert('Data exported successfully!');
                    }, 1500);
                });
            }
            
            // Week selector functionality
            const weekSelector = document.querySelector('.week-selector');
            if (weekSelector) {
                weekSelector.addEventListener('click', function() {
                    // In production, this would open a week picker
                    alert('Opening week selection. In production, this would change the leaderboard week.');
                });
            }
            
            // Pagination functionality
            const prevButton = document.querySelector('.nav-button.prev');
            const nextButton = document.querySelector('.nav-button.next');
            
            if (prevButton) {
                prevButton.addEventListener('click', function() {
                    if (!this.disabled) {
                        // In production, this would load previous page
                        alert('Loading previous page...');
                    }
                });
            }
            
            if (nextButton) {
                nextButton.addEventListener('click', function() {
                    // In production, this would load next page
                    alert('Loading next page...');
                });
            }
            
            // Table row click functionality (for drill-down)
            tableRows.forEach(row => {
                row.addEventListener('click', function() {
                    const nameCell = this.querySelector('.participant-name');
                    if (nameCell) {
                        const name = nameCell.textContent;
                        // In production, this would navigate to participant details
                        alert(`Viewing details for ${name}. In production, this would open participant profile.`);
                    }
                });
            });
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                    }
                }
                
                // Ctrl/Cmd + E to export
                if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                    e.preventDefault();
                    if (exportButton) {
                        exportButton.click();
                    }
                }
            });
            
            // Add live updates simulation (for demo purposes)
            function simulateLiveUpdates() {
                // Simulate occasional rank changes
                setInterval(() => {
                    const isDemoMode = Math.random() > 0.8; // 20% chance
                    if (isDemoMode && tableRows.length > 3) {
                        // Swap positions of rank 4 and 5 for demo effect
                        const rowsArray = Array.from(tableRows);
                        const rank4Row = rowsArray[3];
                        const rank5Row = rowsArray[4];
                        
                        if (rank4Row && rank5Row) {
                            // Briefly highlight the rows
                            rank4Row.style.backgroundColor = 'rgba(230, 76, 5, 0.1)';
                            rank5Row.style.backgroundColor = 'rgba(230, 76, 5, 0.1)';
                            
                            setTimeout(() => {
                                rank4Row.style.backgroundColor = '';
                                rank5Row.style.backgroundColor = '';
                            }, 1000);
                        }
                    }
                }, 10000); // Check every 10 seconds
            }
            
            // Uncomment to enable live updates simulation
            // simulateLiveUpdates();
            
            // Add tooltips for podium cards
            const podiumCards = document.querySelectorAll('.podium-card');
            podiumCards.forEach(card => {
                const rank = card.classList.contains('gold') ? '1st' : 
                            card.classList.contains('silver') ? '2nd' : '3rd';
                
                card.title = `Click to view ${rank} place participant details`;
                
                card.addEventListener('click', function() {
                    const name = this.querySelector('.participant-name').textContent;
                    alert(`Viewing ${rank} place participant: ${name}`);
                });
            });
            
            // Add sorting functionality to table headers
            const tableHeaders = document.querySelectorAll('.leaderboard-table th');
            tableHeaders.forEach((header, index) => {
                // Make percentage column sortable
                if (header.textContent.includes('Weight Loss %')) {
                    header.style.cursor = 'pointer';
                    header.title = 'Click to sort by percentage';
                    
                    header.addEventListener('click', function() {
                        // In production, this would sort the table
                        alert('Sorting by Weight Loss Percentage. In production, this would reorder the leaderboard.');
                    });
                }
            });
        });
    </script>
</body>
</html>