<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pre-Registration: Photo Upload</title>
    
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
        PHOTO UPLOAD FORM DESIGN SYSTEM
        ============================================
        
        COLOR SCHEME:
        - Primary: #e64c05 (orange)
        - Light Mode:
          Background: #f8f6f5 (light beige)
          Card Background: #ffffff (white)
          Card Background Secondary: #fcf9f8 (off-white)
          Border: #f4ebe6 (light beige)
          Border Dark: #e9d6ce (medium beige)
          Text Primary: #1c120d (dark brown)
          Text Secondary: #9e6347 (medium brown)
        - Dark Mode:
          Background: #23150f (dark brown)
          Card Background: #1e120d (dark brown)
          Card Background Secondary: #23150f (darker brown)
          Border: #3d2b24 (brown)
          Border Dark: #523d33 (dark brown)
          Text Primary: #f4ebe6 (light beige)
          Text Secondary: #bca499 (medium beige)
        
        TYPOGRAPHY:
        - Headings: Lexend, 100-900 weights
        - Body: Noto Sans, 300-800 weights
        - Emphasis: Bold for important text
        
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
        - Slow: 500ms ease-out (progress bar)
        - Interactive: hover and active states
        
        ASPECT RATIOS:
        - Photo placeholder: 3/4 (portrait)
        */
        
        /* CSS Custom Properties */
        :root {
            /* Colors - Light Theme */
            --color-primary: #e64c05;
            --color-primary-hover: #d14500;
            --color-primary-light: rgba(230, 76, 5, 0.1);
            --color-primary-border: rgba(230, 76, 5, 0.2);
            --color-background: #f8f6f5;
            --color-surface: #ffffff;
            --color-surface-secondary: #fcf9f8;
            --color-border: #f4ebe6;
            --color-border-dark: #e9d6ce;
            --color-text-primary: #1c120d;
            --color-text-secondary: #9e6347;
            --color-text-light: #fcf9f8;
            --color-text-muted: #9e6347;
            
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
            --transition-ease-out: ease-out;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-primary: 0 4px 12px rgba(230, 76, 5, 0.2);
            
            /* Layout */
            --max-width-container: 60rem; /* 960px */
            --header-height: 60px;
        }
        
        /* Dark Theme Overrides */
        [data-theme="dark"] {
            --color-background: #23150f;
            --color-surface: #1e120d;
            --color-surface-secondary: #23150f;
            --color-border: #3d2b24;
            --color-border-dark: #523d33;
            --color-text-primary: #f4ebe6;
            --color-text-secondary: #bca499;
            --color-text-muted: #bca499;
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
            font-family: var(--font-display);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.033em;
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
    Design: Simple header with logo and user controls
    Layout: Flex container with logo left, user right
    Background: Light background that changes with theme
    Border: Bottom border for separation
    -->
    <header class="main-header">
        <div class="header-content">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <svg class="logo-svg" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 42.4379C4 42.4379 14.0962 36.0744 24 41.1692C35.0664 46.8624 44 42.2078 44 42.2078L44 7.01134C44 7.01134 35.068 11.6577 24.0031 5.96913C14.0971 0.876274 4 7.27094 4 7.27094L4 42.4379Z" fill="currentColor"></path>
                    </svg>
                </div>
                <h2 class="brand-name">10 Days Weekly Challenge</h2>
            </div>
            
            <!-- User Controls -->
            <div class="user-controls">
                <div class="user-avatar">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBDnSHMXGgO-qa-GtnOLfb8iKZAlQMU0s4P6RunbkhHBU9gbQgcUvIh1egWeTyh0kNABQhPK9Flu9bAROkH3ARrQkWFjWIOWDS7YKfhPYSbjiMSfAejI65RrU5t4olZo4mymZFvWihmeugdy5DKma9mt3BEU6EQbD-MNUGRt4sCWFyvj8RlNN3bwsWBmhSBZS0bu7wJcP5YpBxW1CQGvIXEPZXidhCv8uA1zCWP7fdhnok6wDc1w1ciSMQCseIsQNTMDZxAVDliKJTc" 
                         alt="User profile picture placeholder" 
                         class="avatar-image"/>
                </div>
            </div>
        </div>
    </header>
    
    <!-- 
    ============================================
    MAIN CONTENT AREA
    ============================================
    Design: Centered container with progress and upload cards
    Layout: Flex column with consistent spacing
    Max-width: 960px for optimal content width
    Responsive: Padding adjusts for mobile/desktop
    -->
    <main class="photo-upload-main">
        <div class="upload-container">
            
            <!-- 
            ====================================
            PROGRESS INDICATOR
            ====================================
            Design: Shows step 2 of 4 with percentage
            Progress bar: 8px height, smooth width transition
            Labels: Step counter (uppercase) and percentage
            Animation: 500ms ease-out for width changes
            -->
            <div class="progress-section">
                <div class="progress-labels">
                    <span class="step-label">Step 2 of 4</span>
                    <span class="percentage-label">50% Completed</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
            
            <!-- 
            ====================================
            PAGE HEADER SECTION
            ====================================
            Design: Large heading with descriptive paragraph
            Heading: 36px desktop, 30px mobile, black weight
            Description: Secondary color, relaxed line height
            Tips box: Primary background with icon and tips
            -->
            <section class="page-header">
                <h1 class="page-title">Let's capture your starting point</h1>
                <p class="page-description">
                    Upload your 'Before' photos to track your progress accurately. These are completely <strong class="privacy-emphasis">private</strong> and only visible to your assigned coach.
                </p>
                
                <!-- Tips Alert -->
                <div class="tips-alert">
                    <span class="tips-icon material-symbols-outlined">lightbulb</span>
                    <div class="tips-content">
                        <p class="tips-title">Pro Tips for Best Results</p>
                        <p class="tips-text">Ensure good lighting, wear form-fitting gym wear, and keep a plain background if possible.</p>
                    </div>
                </div>
            </section>
            
            <!-- 
            ====================================
            UPLOAD CARDS GRID
            ====================================
            Design: Two cards side by side on desktop, stacked on mobile
            Card structure: Header, image placeholder, upload area
            Interactive: Hover effects on cards and upload areas
            Required badges: Styled labels for required fields
            -->
            <div class="upload-grid">
                
                <!-- Front View Card -->
                <article class="upload-card">
                    <div class="card-header">
                        <div class="card-title-section">
                            <span class="card-icon material-symbols-outlined">accessibility_new</span>
                            <h3 class="card-title">Front View</h3>
                        </div>
                        <span class="required-badge">Required</span>
                    </div>
                    
                    <div class="card-content">
                        <!-- Image Placeholder with Example -->
                        <div class="image-placeholder">
                            <div class="example-image" 
                                 data-alt="Silhouette illustration of a person standing facing forward"
                                 style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAxFzZfN7djDMG4OxNb5ZNt7S-qIpPBVw-NZXNRDoedP_Q8z4jKv30FwWCzrTRstPTuBSSvwpnL4UMNrMOgFv-kX1HNseMx_UzM4mxG4FzBMBUqGeM0MklXQpgWpQU8e9E5eI901euubZ2rVlR3Oj_I1xTO7i39knz92YpWxniokgVg-gmV9wv9J7nfgdXhZVhNM__4RWhyYSTiNnzQBwKCBh02MzzNpV-0AApViM5n9UYwjBAO7jwr2ijC77AODnxxyGb2aQdhfEA7')">
                            </div>
                            <div class="image-overlay">
                                <p class="overlay-text">Stand straight, arms relaxed by sides.</p>
                            </div>
                        </div>
                        
                        <!-- Upload Area -->
                        <div class="upload-area" id="front-upload">
                            <div class="upload-icon">
                                <span class="material-symbols-outlined">cloud_upload</span>
                            </div>
                            <div class="upload-instructions">
                                <p class="upload-title">Click to upload</p>
                                <p class="upload-subtitle">or drag and drop here</p>
                            </div>
                            <p class="file-requirements">JPG, PNG up to 10MB</p>
                        </div>
                    </div>
                </article>
                
                <!-- Side View Card -->
                <article class="upload-card">
                    <div class="card-header">
                        <div class="card-title-section">
                            <span class="card-icon material-symbols-outlined">directions_walk</span>
                            <h3 class="card-title">Right Side View</h3>
                        </div>
                        <span class="required-badge">Required</span>
                    </div>
                    
                    <div class="card-content">
                        <!-- Image Placeholder with Example -->
                        <div class="image-placeholder">
                            <div class="example-image" 
                                 data-alt="Silhouette illustration of a person standing sideways facing right"
                                 style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBlbDU9Gg2XdvMJJOgEnr4xVCy6JdWnYZc5d-qGwOXfzbd6H5WcuMKYUPEJ-Rh0lLZdqwdEpz8VTULq2_rWjVKDyZ7keA57VtOndgcjTpRm1V8MBr88aQEDmwWvwe-stZt3t9OFuMdlkomvHX5rA7P07l0jlm6JhLbAYhh7frtjV00kITlNbJ3pQRn-lBMloTk5FAsX2tRoUWr6_luLVXkdiwHgZROohnqCnUVXA-SaZmJ_3AfAOtny4h1RxW6x23QCYANxq3Q7tqbN')">
                            </div>
                            <div class="image-overlay">
                                <p class="overlay-text">Turn 90° right, look forward.</p>
                            </div>
                        </div>
                        
                        <!-- Upload Area -->
                        <div class="upload-area" id="side-upload">
                            <div class="upload-icon">
                                <span class="material-symbols-outlined">cloud_upload</span>
                            </div>
                            <div class="upload-instructions">
                                <p class="upload-title">Click to upload</p>
                                <p class="upload-subtitle">or drag and drop here</p>
                            </div>
                            <p class="file-requirements">JPG, PNG up to 10MB</p>
                        </div>
                    </div>
                </article>
            </div>
            
            <!-- 
            ====================================
            PRIVACY NOTE
            ====================================
            Design: Centered with lock icon
            Text: Reassuring message about photo security
            Style: Secondary color, small icon
            -->
            <div class="privacy-note">
                <span class="privacy-icon material-symbols-outlined">lock</span>
                <p class="privacy-text">Your photos are securely encrypted and private.</p>
            </div>
            
            <!-- 
            ====================================
            NAVIGATION BUTTONS
            ====================================
            Design: Back and Continue buttons
            Layout: Stacked on mobile, side by side on desktop
            Back button: Outlined, secondary style
            Continue button: Primary color with shadow and arrow
            Interactive: Hover and active states
            -->
            <div class="navigation-buttons">
                <button class="back-button">
                    Back
                </button>
                <button class="continue-button">
                    Continue to Step 3
                    <span class="button-icon material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
    </main>

    <!-- 
    ============================================
    PHOTO UPLOAD STYLES
    ============================================
    These styles implement the design described above
    -->
    <style>
        /* Header Styles */
        .main-header {
            height: var(--header-height);
            border-bottom: 1px solid var(--color-border);
            background-color: var(--color-surface-secondary);
            padding: 0 var(--space-md);
        }
        
        .header-content {
            max-width: 80rem; /* 1280px */
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
            color: var(--color-text-primary);
            display: none;
        }
        
        .user-controls {
            display: flex;
            align-items: center;
            gap: var(--space-md);
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
            border: 2px solid var(--color-border-dark);
        }
        


        /* Main Content */
        .photo-upload-main {
            flex: 1;
            width: 100%;
            padding: var(--space-lg) var(--space-md);
            display: flex;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .upload-container {
            width: 100%;
            max-width: var(--max-width-container);
            display: flex;
            flex-direction: column;
            gap: var(--space-2xl);
        }
        
        /* Progress Section */
        .progress-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }
        
        .progress-labels {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .step-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .percentage-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
        }
        
        .progress-bar {
            width: 100%;
            height: 0.5rem;
            border-radius: var(--radius-full);
            background-color: var(--color-border-dark);
            overflow: hidden;
        }
        
        .progress-fill {
            width: 50%;
            height: 100%;
            background-color: var(--color-primary);
            border-radius: var(--radius-full);
            transition: width var(--transition-slow) var(--transition-ease-out);
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            flex-direction: column;
            gap: var(--space-md);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 900;
            color: var(--color-text-primary);
            line-height: 1.2;
        }
        
        @media (min-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
        }
        
        .page-description {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--color-text-secondary);
            max-width: 42rem; /* 672px */
        }
        
        .privacy-emphasis {
            font-weight: 600;
            color: var(--color-text-primary);
        }
        
        /* Tips Alert */
        .tips-alert {
            display: flex;
            gap: var(--space-md);
            padding: var(--space-md);
            background-color: var(--color-primary-light);
            border: 1px solid var(--color-primary-border);
            border-radius: var(--radius-lg);
            margin-top: var(--space-md);
        }
        
        .tips-icon {
            font-size: 1.5rem;
            color: var(--color-primary);
            flex-shrink: 0;
        }
        
        .tips-content {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
        }
        
        .tips-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--color-text-primary);
        }
        
        .tips-text {
            font-size: 0.875rem;
            color: var(--color-text-secondary);
        }
        
        /* Upload Grid */
        .upload-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }
        
        @media (min-width: 768px) {
            .upload-grid {
                grid-template-columns: 1fr 1fr;
                gap: var(--space-xl);
            }
        }
        
        /* Upload Card */
        .upload-card {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            background-color: var(--color-surface);
            overflow: hidden;
            transition: border-color var(--transition-base) var(--transition-timing);
        }
        
        .upload-card:hover {
            border-color: rgba(230, 76, 5, 0.4);
        }
        
        .card-header {
            padding: var(--space-md);
            border-bottom: 1px solid var(--color-border);
            background-color: var(--color-surface-secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title-section {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .card-icon {
            font-size: 1.25rem;
            color: var(--color-text-secondary);
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text-primary);
        }
        
        .required-badge {
            padding: var(--space-xs) var(--space-sm);
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--color-text-secondary);
            background-color: var(--color-border-dark);
            border-radius: var(--radius-md);
        }
        
        .card-content {
            padding: var(--space-xl);
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }
        
        /* Image Placeholder */
        .image-placeholder {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4;
            border-radius: var(--radius-lg);
            background-color: var(--color-border);
            overflow: hidden;
        }
        
        .example-image {
            position: absolute;
            inset: 0;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.4;
            filter: grayscale(100%) blur(1px);
            mix-blend-mode: multiply;
        }
        
        [data-theme="dark"] .example-image {
            mix-blend-mode: screen;
        }
        
        .image-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-lg);
            text-align: center;
        }
        
        .overlay-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-primary);
            background-color: rgba(255, 255, 255, 0.8);
            padding: var(--space-sm);
            border-radius: var(--radius-md);
        }
        
        [data-theme="dark"] .overlay-text {
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--color-border-dark);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: var(--space-md);
            cursor: pointer;
            transition: all var(--transition-base) var(--transition-timing);
        }
        
        .upload-area:hover {
            border-color: var(--color-primary);
            background-color: rgba(230, 76, 5, 0.05);
        }
        
        .upload-icon {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--color-primary-light);
            color: var(--color-primary);
            border-radius: var(--radius-full);
        }
        
        .upload-icon .material-symbols-outlined {
            font-size: 1.5rem;
        }
        
        .upload-instructions {
            text-align: center;
        }
        
        .upload-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: var(--space-xs);
        }
        
        .upload-subtitle {
            font-size: 0.75rem;
            color: var(--color-text-secondary);
        }
        
        .file-requirements {
            font-size: 0.625rem;
            color: var(--color-text-muted);
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        /* Privacy Note */
        .privacy-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            color: var(--color-text-secondary);
            font-size: 0.875rem;
            margin-top: var(--space-md);
        }
        
        .privacy-icon {
            font-size: 1.125rem;
        }
        
        /* Navigation Buttons */
        .navigation-buttons {
            display: flex;
            flex-direction: column-reverse;
            gap: var(--space-md);
            padding-top: var(--space-xl);
            border-top: 1px solid var(--color-border);
        }
        
        @media (min-width: 768px) {
            .navigation-buttons {
                flex-direction: row;
                justify-content: space-between;
                gap: var(--space-md);
            }
        }
        
        .back-button,
        .continue-button {
            height: 3rem;
            padding: 0 var(--space-lg);
            font-size: 1rem;
            font-weight: 700;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-base) var(--transition-timing);
        }
        
        .back-button {
            min-width: 120px;
            color: var(--color-text-primary);
            background-color: transparent;
            border: 1px solid var(--color-border-dark);
        }
        
        .back-button:hover {
            background-color: var(--color-border);
        }
        
        [data-theme="dark"] .back-button:hover {
            background-color: var(--color-surface);
        }
        
        .continue-button {
            min-width: 200px;
            color: var(--color-text-light);
            background-color: var(--color-primary);
            border: none;
            box-shadow: var(--shadow-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
        }
        
        .continue-button:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .continue-button:active {
            transform: scale(0.95);
        }
        
        .button-icon {
            font-size: 1.125rem;
        }
        
        /* Responsive Styles */
        @media (min-width: 640px) {
            .photo-upload-main {
                padding: var(--space-xl) var(--space-md);
            }
            
            .main-header {
                padding: 0 var(--space-lg);
            }
            
            .brand-name {
                display: block;
            }
        }
        
        @media (min-width: 1024px) {
            .main-header {
                padding: 0 var(--space-xl);
            }
            
            .photo-upload-main {
                padding: var(--space-2xl) var(--space-md);
            }
        }
        
        /* Dark Mode Adjustments */
        [data-theme="dark"] .upload-card {
            background-color: var(--color-surface);
        }
        
        [data-theme="dark"] .card-header {
            background-color: var(--color-surface-secondary);
        }
        
        [data-theme="dark"] .image-placeholder {
            background-color: var(--color-surface);
        }
    </style>
    
    <!-- Photo Upload Functionality Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Upload functionality
            const frontUpload = document.getElementById('front-upload');
            const sideUpload = document.getElementById('side-upload');
            const continueButton = document.querySelector('.continue-button');
            const backButton = document.querySelector('.back-button');
            
            // File validation function
            function validateFile(file) {
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (!validTypes.includes(file.type)) {
                    alert('Please upload only JPG or PNG files.');
                    return false;
                }
                
                if (file.size > maxSize) {
                    alert('File size must be less than 10MB.');
                    return false;
                }
                
                return true;
            }
            
            // Preview image function
            function previewImage(file, uploadArea) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove existing preview if any
                    const existingPreview = uploadArea.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Create preview element
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.style.cssText = `
                        position: absolute;
                        inset: 0;
                        background-size: cover;
                        background-position: center;
                        background-image: url('${e.target.result}');
                        border-radius: var(--radius-lg);
                    `;
                    
                    // Find the image placeholder
                    const card = uploadArea.closest('.upload-card');
                    const placeholder = card.querySelector('.image-placeholder');
                    placeholder.appendChild(preview);
                    
                    // Add success indicator
                    uploadArea.classList.add('upload-success');
                    const icon = uploadArea.querySelector('.material-symbols-outlined');
                    if (icon) {
                        icon.textContent = 'check_circle';
                    }
                    
                    // Check if both uploads are complete
                    checkUploadsComplete();
                };
                reader.readAsDataURL(file);
            }
            
            // Check if both uploads are complete
            function checkUploadsComplete() {
                const frontComplete = frontUpload.classList.contains('upload-success');
                const sideComplete = sideUpload.classList.contains('upload-success');
                
                if (frontComplete && sideComplete) {
                    continueButton.disabled = false;
                    continueButton.style.opacity = '1';
                    continueButton.style.cursor = 'pointer';
                } else {
                    continueButton.disabled = true;
                    continueButton.style.opacity = '0.5';
                    continueButton.style.cursor = 'not-allowed';
                }
            }
            
            // Setup upload area click and drag events
            function setupUploadArea(uploadArea) {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = 'image/jpeg,image/jpg,image/png';
                fileInput.style.display = 'none';
                
                // Add file input to DOM
                document.body.appendChild(fileInput);
                
                // Click to upload
                uploadArea.addEventListener('click', function() {
                    fileInput.click();
                });
                
                // Handle file selection
                fileInput.addEventListener('change', function(e) {
                    if (fileInput.files.length > 0) {
                        const file = fileInput.files[0];
                        if (validateFile(file)) {
                            previewImage(file, uploadArea);
                        }
                    }
                    fileInput.value = ''; // Reset input
                });
                
                // Drag and drop functionality
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadArea.style.borderColor = 'var(--color-primary)';
                    uploadArea.style.backgroundColor = 'rgba(230, 76, 5, 0.1)';
                });
                
                uploadArea.addEventListener('dragleave', function() {
                    uploadArea.style.borderColor = '';
                    uploadArea.style.backgroundColor = '';
                });
                
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadArea.style.borderColor = '';
                    uploadArea.style.backgroundColor = '';
                    
                    if (e.dataTransfer.files.length > 0) {
                        const file = e.dataTransfer.files[0];
                        if (validateFile(file)) {
                            previewImage(file, uploadArea);
                        }
                    }
                });
            }
            
            // Initialize upload areas
            setupUploadArea(frontUpload);
            setupUploadArea(sideUpload);
            
            // Continue button click handler
            continueButton.addEventListener('click', function(e) {
                if (this.disabled) {
                    e.preventDefault();
                    alert('Please upload both required photos before continuing.');
                    return;
                }
                
                // Simulate upload progress
                this.textContent = 'Processing...';
                this.disabled = true;
                
                // In production, this would upload to server
                setTimeout(() => {
                    alert('Photos uploaded successfully! Proceeding to next step.');
                    // Here you would navigate to step 3
                    window.location.href = '#step3'; // Replace with actual navigation
                }, 1500);
            });
            
            // Back button click handler
            backButton.addEventListener('click', function() {
                if (confirm('Any unsaved changes will be lost. Go back to Step 1?')) {
                    // Navigate to previous step
                    window.location.href = '#step1'; // Replace with actual navigation
                }
            });
            
            // Add CSS for upload success state
            const style = document.createElement('style');
            style.textContent = `
                .upload-success {
                    border-color: var(--color-primary) !important;
                    border-style: solid !important;
                    background-color: rgba(16, 185, 129, 0.05) !important;
                }
                
                .upload-success .upload-icon {
                    background-color: rgba(16, 185, 129, 0.2) !important;
                    color: #10b981 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>