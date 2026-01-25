# 10-Day Weekly Challenge Website – Requirements

## 1. Project Overview

The purpose of this website is to **reduce the workload of coaches** when managing clients participating in a **10-Day Weekly Fitness Challenge**. All data entry, calculations, and monitoring are handled **only by the coach**, not the client.

The system must be **mobile-responsive**, user-friendly, and production-ready.

---

## 2. Technology Stack

* **Frontend:** HTML, Tailwind CSS
* **Backend:** PHP
* **Database:** MySQL

---

## 3. User Roles

### 3.1 Coach (Primary User)

* Inputs all client data
* Calculates BMI
* Assigns BMI category
* Inputs daily weights
* Manages challenge progress

### 3.2 Admin (System-Level)

* Views overall challenge data
* Accesses auto-generated Top 10 leaderboard
* Manages data backup and reset after each challenge

> **Note:** Clients do **not** create accounts and do **not** input any data themselves.

---

## 4. Challenge Rules & Schedule

* The challenge lasts **10 days**
* Challenge **always starts on Monday**
* Client registration is **pre-registration only** (does not start immediately)

### Late Registration Rule

* If a client registers on **Tuesday**, they are still considered **Day 1 / Day 2 compliant**
* **Day 1 is NOT marked as missed** for Tuesday catch-ups
* Registration beyond Tuesday is not allowed (unless defined later)

---

## 5. Client Pre-Registration (Coach Dashboard)

### 5.1 Progressive Form Input

Client data must be entered using a **step-by-step (progress-style) form**.

### 5.2 Required Client Data Fields

All inputs are entered **by the coach only**:

* Gender
* Age
* Height (**Feet & Inches only**)
* Weight (**Pounds only**)
* Waistline
* Front Photo
* Side Photo

---

## 6. Photo Upload Requirements

* Front and Side photo uploads are required
* Each upload section must include:

  * **Example images** showing correct positioning
  * Clear instructions for user-friendly guidance

---

## 7. Consent & Privacy Compliance

A **required checkbox** must appear at the bottom of the registration form:

> "I confirm that the client has provided consent for the collection and processing of their personal and health-related data in accordance with the Privacy Policy."

* Form submission must be **blocked** if the checkbox is not checked

---

## 8. BMI Calculation & Classification

### 8.1 BMI Handling

* BMI is **calculated only by the coach/system**
* Clients do not see or edit BMI values

### 8.2 BMI Categories

The coach assigns or confirms the category based on system-calculated BMI:

| Category        | BMI Range      |
| --------------- | -------------- |
| Normal          | 18.5 – 22.9    |
| Overweight      | 23.0 – 24.9    |
| Obese I         | 25.0 – 29.9    |
| Obese II        | 30.0 – 34.9    |
| Extremely Obese | 35.0 and above |

---

## 9. Validation & Error Handling

* The system must:

  * Detect missing fields
  * Prevent form submission if required data is incomplete
  * Display clear error or warning messages to the coach

---

## 10. Daily Weight Tracking

* Once the challenge starts:

  * Coach inputs **daily weight** for each client
  * This continues for **10 consecutive days**

---

## 11. Challenge Completion & Leaderboard

### 11.1 Leaderboard Generation

* After Day 10, the system automatically generates a **Top 10 Leaderboard**
* Ranking is based on **weight loss percentage**

### 11.2 Calculation Rules

* Weight loss percentage must:

  * Display **up to 3 decimal places**
  * **No rounding up** allowed

---

## 12. Data Reset & Rejoin Flow

* After the 10-day challenge:

  * Client data is **backed up**
  * Active challenge data is **reset**
  * Clients can **rejoin future challenges without creating a new account**

---

## 13. Responsive Design Requirement

* The entire system must be:

  * Fully **mobile-responsive**
  * Optimized for phones and tablets

---

## 14. UI Color Palette

### Primary Colors (Main Theme)

* **#E74B05 – Molten Orange**
* **#F26E10 – Pumpkin Spice**

### Secondary / Accent Colors

* **#FFFFFF – White**
* **#683FB7 – Indigo Bloom**
* **#722806 – Brandy**

> The **main palette must primarily use Molten Orange or Pumpkin Spice**.

---

## 15. Summary Key Rules

* Coach-only data input
* Monday-based challenge start
* Progressive form input
* Mandatory consent checkbox
* Automatic leaderboard
* No client accounts
* Mobile-first design



Coach Dashboard Navbar Structure
| DASHBOARD | PRE-REGISTRATION | CHALLENGE | CLIENT | PROFILE |

1. DASHBOARD
- Overview of active challenge
- Total clients
- Current day of challenge
- Quick stats (avg weight loss, active clients)

2. PRE-REGISTRATION
- Add new challenger
- Progressive form input
- Consent checkbox
- Photo uploads (front & side)

3. CHALLENGES
Active Challenge
-  List of all active clients
- Current day indicator (Day 1–Day 10)
- Quick access to daily weight input
Completed Challenges
- Past challenge results
- Read-only view
- No editing allowed


4. CLIENT

CLIENT PROGRESS
- Individual client profile
- Starting weight vs current weight
- BMI & category
- Photo comparison (before vs current)

CATCH-UP CLIENTS
- Clients who joined on Tuesday
- Clearly marked as Day 1 / Day 2 compliant
- Prevents accidental “missed day” tagging

5. PROFILE
- Coach info
- Change password
- Logout

Daily Weigh-In MODAL 
- Select client
- Input daily weight
- Auto-save & validation
- Missed entry warnings


🛠️ Admin Dashboard – Navbar Structure
|OVERVIEW|CHALLENGES|LEADERBOARD|CLIENTS|DATA BACKUP| REPORTS | PRIVACY LOGS | PROFILE |

1. OVERVIEW

- Total coaches
- Total participants
- Current challenge status
- System health summary

2. CHALLENGES

- Current challenge overview
- Start / End dates
- Status (Upcoming, Active, Completed)

3. LEADERBOARD
- Auto-generated Top 10 (by category)
- Ranked by weight loss %
- 3 decimal places (no rounding)
- download / export option


4. CLIENTS (Read-Only)
- View all clients
- No edit permissions
- Progress history

5 DATA BACKUP & RESET
- Backup completed challenge data
- Reset active challenge data
- Prepare system for next Monday cycle

6. REPORTS & ANALYTICS
- Weight loss trends
- Completion rate
- Average results per challenge

7. Privacy & Consent Logs

View consent confirmations
Timestamped records
Compliance tracking


9. PROFILE
- Admin account
- Change password
- Logout
-- SETTINGS 
-- BMI category ranges
-- Challenge duration (default 10 days)
-- Late registration rules (Monday–Tuesday)

