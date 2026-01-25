<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pre-Registration: Basic Info</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <!-- Lexend font for all text -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;900&display=swap" rel="stylesheet"/>
    <!-- Material Symbols for icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Base CSS with Design System -->
    <style>
        /* 
        ============================================
        PRE-REGISTRATION FORM DESIGN SYSTEM
        ============================================
        
        COLOR SCHEME:
        - Primary: #e64c05 (orange)
        - Light Mode:
          Background: #f8f6f5 (light beige)
          Card Background: #ffffff (white)
          Border: #e5e7eb (light gray)
          Text Primary: #1f2937 (dark gray)
          Text Secondary: #6b7280 (medium gray)
        - Dark Mode:
          Background: #23150f (dark brown)
          Card Background: #111827 (dark gray)
          Border: #374151 (medium gray)
          Text Primary: #f9fafb (light gray)
          Text Secondary: #9ca3af (light gray)
        
        TYPOGRAPHY:
        - All text: Lexend, 300-900 weights
        - Headings: 900 weight, tight tracking
        - Labels: 600 weight
        - Body text: 400 weight
        
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
        - 2xl: 1.5rem (24px) - card corners
        
        SHADOWS:
        - sm: 0 1px 2px 0 rgba(0,0,0,0.05)
        - md: 0 4px 6px -1px rgba(0,0,0,0.1)
        - button: 0 4px 12px rgba(230, 76, 5, 0.2)
        
        TRANSITIONS:
        - Default: 200ms ease-in-out
        - Fast: 150ms ease-in-out
        - Button active: 100ms ease-in-out
        
        LAYOUT:
        - Max width: 56rem (896px) for form container
        - Header height: 64px
        - Card padding: 2rem mobile, 2.5rem desktop
        */
        
        /* CSS Custom Properties */
        :root {
            /* Colors - Light Theme */
            --color-primary: #e64c05;
            --color-primary-hover: #d14500;
            --color-primary-light: rgba(230, 76, 5, 0.1);
            --color-background: #f8f6f5;
            --color-surface: #ffffff;
            --color-surface-dark: #111827;
            --color-border: #e5e7eb;
            --color-border-dark: #374151;
            --color-text-primary: #1f2937;
            --color-text-secondary: #6b7280;
            --color-text-light: #ffffff;
            --color-neutral-50: #f9fafb;
            --color-neutral-100: #f3f4f6;
            --color-neutral-200: #e5e7eb;
            --color-neutral-800: #1f2937;
            
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
            --max-width-container: 56rem;
            --header-height: 64px;
            --backdrop-blur: 8px;
        }
        
        /* Dark Theme Overrides */
        [data-theme="dark"] {
            --color-background: #23150f;
            --color-surface: #111827;
            --color-border: #374151;
            --color-text-primary: #f9fafb;
            --color-text-secondary: #9ca3af;
            --color-neutral-50: #1f2937;
            --color-neutral-100: #374151;
            --color-neutral-200: #4b5563;
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
            font-weight: 900;
            line-height: 1.2;
            letter-spacing: -0.025em;
        }
        
        /* Material Icons */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--color-neutral-200);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-text-secondary);
        }
        
        [data-theme="dark"] ::-webkit-scrollbar-thumb {
            background: var(--color-neutral-200);
        }
    </style>
</head>
<body data-theme="light">
    <!-- 
    ============================================
    HEADER NAVIGATION
    ============================================
    Design: Sticky header with backdrop blur
    Contains: Logo, navigation links, user profile
    Background: White with transparency (dark in dark mode)
    Border: Bottom border for separation
    Layout: Flexbox with max-width container
    -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <span class="material-symbols-outlined">fitness_center</span>
                </div>
                <h1 class="brand-name">10 Days Weekly Challenge</h1>
            </div>
            
            <!-- User Profile -->
            <div class="user-profile">
                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCGa_V8fleF661KMVpR1XhutEz_6HLpeI43Mc13Jk0BLZUhFVftUynpCI29rxvza5nipINBc7FD1-B3-aByEjhxcm46VwLYOE73qvL1jOS0vZeN-AfhbBb8HGOMZXX2yIjqWgNHS5fu8smSHfM3xXvxruYPjaviJMj7maBye_Ocw2ihPOUb_P2GmOuAexR5Bcfm6JZ81S9DR24jLKqQSsnBySVs1pRiEQaF3DvTbec2ke8_Ljd3eQILElJYtpwaVBbbOZdhEO86pAfi" 
                     alt="Portrait of a user" 
                     class="profile-image"/>
            </div>
            
            <!-- Mobile Menu Button (hidden on desktop) -->
            <button class="mobile-menu-button" aria-label="Open menu">
                <span class="material-symbols-outlined">menu</span>
            </button>
        </div>
    </header>
    
    <!-- 
    ============================================
    MAIN CONTENT AREA
    ============================================
    Design: Centered form container with progress indicator
    Layout: Flex column, centered horizontally and vertically
    Max-width: 56rem (896px) for optimal readability
    Spacing: Responsive padding
    -->
    <main class="registration-main">
        <div class="registration-container">
            
            <!-- 
            ====================================
            PROGRESS INDICATOR
            ====================================
            Design: Shows 4-step progress (step 1 of 4)
            Progress bar: 8px height, primary color fill
            Labels: Step counter and percentage
            Animation: Smooth width transition
            -->
            <div class="progress-section">
                <div class="progress-labels">
                    <span class="step-counter">Step 1 of 4</span>
                    <span class="step-percentage">25% Completed</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill"></div>
                </div>
            </div>
            
            <!-- 
            ====================================
            FORM CARD
            ====================================
            Design: Elevated card with border and shadow
            Background: White (dark in dark mode)
            Border: Subtle border for definition
            Corners: 24px radius for modern look
            Spacing: Responsive padding (32px mobile, 40px desktop)
            -->
            <div class="form-card">
                
                <!-- 
                FORM HEADER
                Design: Large heading with descriptive paragraph
                Heading: 36px on desktop, 30px on mobile, 900 weight
                Paragraph: Secondary color, 18px, relaxed line height
                -->
                <header class="form-header">
                    <h2>Basic Information</h2>
                    <p class="form-description">
                        Tell us a bit about yourself to customize your personalized fitness plan.
                    </p>
                </header>
                
                <!-- 
                ====================================
                REGISTRATION FORM
                ====================================
                Design: Grid layout for responsive form fields
                Layout: 2 columns on desktop, 1 column on mobile
                Spacing: Consistent 24px between form groups
                Focus states: Primary color ring with 20% opacity
                -->
                <form class="registration-form">
                    
                    <!-- 
                    ROW 1: Gender & Age
                    Design: Two fields side by side on desktop, stacked on mobile
                    Gender: Custom styled select dropdown
                    Age: Number input with validation
                    -->
                    <div class="form-row">
                        <!-- Gender Select Field -->
                        <div class="form-field">
                            <label for="gender" class="field-label">Gender</label>
                            <div class="select-wrapper">
                                <select id="gender" class="form-select" required>
                                    <option value="" disabled selected>Select your gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="non-binary">Non-binary</option>
                                    <option value="prefer-not-to-say">Prefer not to say</option>
                                </select>
                                <span class="select-icon material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                        
                        <!-- Age Input Field -->
                        <div class="form-field">
                            <label for="age" class="field-label">Age</label>
                            <input type="number" 
                                   id="age" 
                                   class="form-input" 
                                   placeholder="e.g., 28" 
                                   min="16" 
                                   max="100" 
                                   required/>
                        </div>
                    </div>
                    
                    <!-- 
                    ROW 2: Height (Split into Feet and Inches)
                    Design: Two inputs side by side with unit labels
                    Layout: Equal width fields with gap between
                    Units: Positioned inside input on right side
                    -->
                    <div class="form-field">
                        <label class="field-label">Height</label>
                        <div class="height-inputs">
                            <div class="unit-input-wrapper">
                                <input type="number" 
                                       class="form-input" 
                                       placeholder="5" 
                                       min="3" 
                                       max="8" 
                                       step="1"/>
                                <span class="unit-label">Ft</span>
                            </div>
                            <div class="unit-input-wrapper">
                                <input type="number" 
                                       class="form-input" 
                                       placeholder="8" 
                                       min="0" 
                                       max="11" 
                                       step="1"/>
                                <span class="unit-label">In</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 
                    ROW 3: Weight & Waistline
                    Design: Two fields side by side on desktop
                    Both have unit labels positioned inside input
                    Validation: Number inputs with appropriate ranges
                    -->
                    <div class="form-row">
                        <!-- Weight Input Field -->
                        <div class="form-field">
                            <label for="weight" class="field-label">Current Weight</label>
                            <div class="unit-input-wrapper">
                                <input type="number" 
                                       id="weight" 
                                       class="form-input" 
                                       placeholder="e.g. 165" 
                                       min="50" 
                                       max="500" 
                                       step="0.1" 
                                       required/>
                                <span class="unit-label">lbs</span>
                            </div>
                        </div>
                        
                        <!-- Waistline Input Field -->
                        <div class="form-field">
                            <label for="waistline" class="field-label">Waistline</label>
                            <div class="unit-input-wrapper">
                                <input type="number" 
                                       id="waistline" 
                                       class="form-input" 
                                       placeholder="e.g. 32" 
                                       min="20" 
                                       max="60" 
                                       step="0.1" 
                                       required/>
                                <span class="unit-label">In</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 
                    ====================================
                    ACTION BUTTONS
                    ====================================
                    Design: Two buttons - secondary and primary
                    Layout: Stacked on mobile, side by side on desktop
                    Primary button: Orange with shadow and arrow icon
                    Secondary button: Text button with hover effect
                    Responsive: Full width on mobile, auto width on desktop
                    -->
                    <div class="form-actions">
                        <button type="button" class="secondary-button">
                            Save & Finish Later
                        </button>
                        <button type="button" class="primary-button">
                            Next Step
                            <span class="button-icon material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- 
            ====================================
            FOOTER COPYRIGHT
            ====================================
            Design: Centered, small, muted text
            Position: Below form card
            Spacing: Top margin for separation
            -->
            <p class="copyright">
                © 2024 10 Days Weekly Challenge. All rights reserved.
            </p>
        </div>
    </main>

    <!-- 
    ============================================
    REGISTRATION FORM STYLES
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
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(var(--backdrop-blur));
            display: flex;
            align-items: center;
        }
        
        [data-theme="dark"] .main-header {
            background-color: rgba(17, 24, 39, 0.8);
            border-bottom-color: var(--color-border-dark);
        }
        
        .header-container {
            width: 100%;
            max-width: 80rem; /* 1280px */
            margin: 0 auto;
            padding: 0 var(--space-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .logo-icon {
            width: 2rem;
            height: 2rem;
            border-radius: var(--radius-full);
            background-color: var(--color-primary);
            color: var(--color-text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        
        .brand-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--color-text-primary);
        }
        
        /* Desktop Navigation */
        .desktop-nav {
            display: none;
            align-items: center;
            gap: 2rem;
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
        
        /* User Profile */
        .user-profile {
            display: none;
            width: 2.5rem;
            height: 2.5rem;
        }
        
        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: var(--radius-full);
            object-fit: cover;
            border: 2px solid var(--color-surface);
            box-shadow: var(--shadow-sm);
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
            color: var(--color-text-secondary);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-fast) var(--transition-timing);
        }
        
        .mobile-menu-button:hover {
            color: var(--color-primary);
            background-color: var(--color-neutral-100);
        }
        
        /* Main Content */
        .registration-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: var(--space-xl) var(--space-md);
        }
        
        .registration-container {
            width: 100%;
            max-width: var(--max-width-container);
            display: flex;
            flex-direction: column;
            gap: var(--space-2xl);
        }
        
        /* Progress Section */
        .progress-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }
        
        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
        }
        
        .step-counter {
            font-weight: 500;
            color: var(--color-primary);
        }
        
        .step-percentage {
            color: var(--color-text-secondary);
        }
        
        .progress-bar-container {
            width: 100%;
            height: 0.5rem;
            background-color: var(--color-neutral-200);
            border-radius: var(--radius-full);
            overflow: hidden;
        }
        
        .progress-bar-fill {
            width: 25%;
            height: 100%;
            background-color: var(--color-primary);
            border-radius: var(--radius-full);
            transition: width var(--transition-slow) var(--transition-timing);
        }
        
        /* Form Card */
        .form-card {
            border: 1px solid var(--color-border);
            border-radius: var(--radius-2xl);
            background-color: var(--color-surface);
            padding: var(--space-xl);
            box-shadow: var(--shadow-sm);
        }
        
        [data-theme="dark"] .form-card {
            background-color: var(--color-surface-dark);
            border-color: var(--color-border-dark);
        }
        
        /* Form Header */
        .form-header {
            margin-bottom: var(--space-xl);
        }
        
        .form-header h2 {
            font-size: 2rem;
            margin-bottom: var(--space-sm);
            color: var(--color-text-primary);
        }
        
        @media (min-width: 640px) {
            .form-header h2 {
                font-size: 2.5rem;
            }
        }
        
        .form-description {
            font-size: 1.125rem;
            line-height: 1.6;
            color: var(--color-text-secondary);
        }
        
        /* Registration Form */
        .registration-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-xl);
        }
        
        /* Form Layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-xl);
        }
        
        @media (min-width: 768px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: var(--space-xl);
            }
        }
        
        /* Form Field Styles */
        .form-field {
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }
        
        .field-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--color-text-primary);
        }
        
        /* Input Styles */
        .form-input,
        .form-select {
            width: 100%;
            padding: 0.875rem 1rem;
            font-family: var(--font-display);
            font-size: 1rem;
            color: var(--color-text-primary);
            background-color: var(--color-neutral-50);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            transition: all var(--transition-fast) var(--transition-timing);
            appearance: none;
        }
        
        [data-theme="dark"] .form-input,
        [data-theme="dark"] .form-select {
            background-color: var(--color-neutral-100);
            border-color: var(--color-border-dark);
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(230, 76, 5, 0.2);
        }
        
        .form-input::placeholder {
            color: var(--color-text-secondary);
            opacity: 0.7;
        }
        
        /* Select Wrapper */
        .select-wrapper {
            position: relative;
        }
        
        .select-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-text-secondary);
            pointer-events: none;
            font-size: 1.25rem;
        }
        
        /* Height Inputs Layout */
        .height-inputs {
            display: flex;
            gap: var(--space-md);
        }
        
        /* Unit Input Wrapper */
        .unit-input-wrapper {
            position: relative;
            flex: 1;
        }
        
        .unit-input-wrapper .form-input {
            padding-right: 3rem;
        }
        
        .unit-label {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text-secondary);
            pointer-events: none;
        }
        
        /* Form Actions */
        .form-actions {
            padding-top: var(--space-lg);
            display: flex;
            flex-direction: column-reverse;
            gap: var(--space-md);
        }
        
        @media (min-width: 640px) {
            .form-actions {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        /* Button Styles */
        .primary-button,
        .secondary-button {
            padding: 0.875rem 2rem;
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: all var(--transition-base) var(--transition-timing);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            width: 100%;
        }
        
        @media (min-width: 640px) {
            .primary-button,
            .secondary-button {
                width: auto;
            }
        }
        
        /* Primary Button */
        .primary-button {
            background-color: var(--color-primary);
            color: var(--color-text-light);
            box-shadow: var(--shadow-primary);
        }
        
        .primary-button:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .primary-button:active {
            transform: scale(0.98);
        }
        
        .primary-button .button-icon {
            transition: transform var(--transition-fast) var(--transition-timing);
        }
        
        .primary-button:hover .button-icon {
            transform: translateX(2px);
        }
        
        /* Secondary Button */
        .secondary-button {
            background: transparent;
            color: var(--color-text-secondary);
        }
        
        .secondary-button:hover {
            color: var(--color-text-primary);
        }
        
        /* Copyright */
        .copyright {
            text-align: center;
            font-size: 0.75rem;
            color: var(--color-text-secondary);
            margin-top: var(--space-md);
        }
        
        /* Responsive Styles */
        @media (min-width: 768px) {
            .header-container {
                padding: 0 var(--space-lg);
            }
            
            .form-card {
                padding: var(--space-2xl) var(--space-2xl);
            }
            
            .desktop-nav,
            .user-profile {
                display: flex;
            }
            
            .mobile-menu-button {
                display: none;
            }
        }
        
        @media (min-width: 1024px) {
            .header-container {
                padding: 0 var(--space-xl);
            }
        }
        
        /* Dark Mode Adjustments */
        [data-theme="dark"] .form-input,
        [data-theme="dark"] .form-select {
            color: var(--color-text-primary);
        }
        
        [data-theme="dark"] .form-input::placeholder {
            color: var(--color-text-secondary);
        }
        
        [data-theme="dark"] .secondary-button:hover {
            color: var(--color-text-light);
        }
    </style>
    
    <!-- Form Validation and Interaction Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation and interaction
            const form = document.querySelector('.registration-form');
            const primaryButton = document.querySelector('.primary-button');
            const secondaryButton = document.querySelector('.secondary-button');
            
            // Add input validation styles
            const inputs = document.querySelectorAll('.form-input, .form-select');
            inputs.forEach(input => {
                // Add validation on blur
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '' && this.hasAttribute('required')) {
                        this.classList.add('invalid');
                    } else {
                        this.classList.remove('invalid');
                    }
                });
                
                // Real-time validation for number inputs
                if (input.type === 'number') {
                    input.addEventListener('input', function() {
                        const min = parseFloat(this.getAttribute('min')) || 0;
                        const max = parseFloat(this.getAttribute('max')) || Infinity;
                        const value = parseFloat(this.value);
                        
                        if (this.value && (value < min || value > max)) {
                            this.classList.add('invalid');
                        } else {
                            this.classList.remove('invalid');
                        }
                    });
                }
            });
            
            // Primary button click handler
            primaryButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Validate all required fields
                let isValid = true;
                const requiredFields = document.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('invalid');
                        isValid = false;
                        
                        // Scroll to first invalid field
                        if (isValid === false) {
                            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
                
                if (isValid) {
                    // Simulate form submission
                    this.textContent = 'Processing...';
                    this.disabled = true;
                    
                    // In production, this would be an actual form submission
                    setTimeout(() => {
                        alert('Form validated successfully! In production, this would proceed to the next step.');
                        this.textContent = 'Next Step';
                        this.disabled = false;
                    }, 1000);
                }
            });
            
            // Secondary button click handler
            secondaryButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Save progress logic
                const formData = {};
                inputs.forEach(input => {
                    if (input.value) {
                        formData[input.id || input.name] = input.value;
                    }
                });
                
                // In production, save to localStorage or backend
                localStorage.setItem('registrationProgress', JSON.stringify(formData));
                alert('Progress saved! You can continue later.');
            });
            
            // Load saved progress if exists
            const savedProgress = localStorage.getItem('registrationProgress');
            if (savedProgress) {
                try {
                    const progress = JSON.parse(savedProgress);
                    Object.keys(progress).forEach(key => {
                        const input = document.getElementById(key) || document.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.value = progress[key];
                        }
                    });
                } catch (e) {
                    console.log('No saved progress found');
                }
            }
            
            // Add invalid input styles
            const style = document.createElement('style');
            style.textContent = `
                .invalid {
                    border-color: #ef4444 !important;
                    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
                }
                
                .invalid:focus {
                    border-color: #ef4444 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>