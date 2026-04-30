# Marlin Barbershop POS 💈

A premium, modern Point of Sale (POS) system built for the Marlin Barbershop. This system leverages **Filament 5** and **Laravel 11** to provide a seamless, multi-role experience for administrators, barbers, and receptionists.

## 🚀 Key Features

-   **Multi-Panel Architecture**: Dedicated dashboards for Admin, Staff, and Reception.
-   **Smart Checkout**: Real-time price calculation with dynamic service selection.
-   **Loyalty System**: Automated "Buy 9, Get 1 Free" shave loyalty program with instant eligibility tracking.
-   **Service Management**: Dynamic service catalog with custom pricing and sorting.
-   **Role-Based Access**: Strict permissions ensuring users only see what they need.
-   **Staff Performance**: Tracking of barber transactions and daily logs.

## 🛠 Tech Stack

-   **Framework**: Laravel 11
-   **UI Engine**: Filament v5 (Schema-based architecture)
-   **Language**: PHP 8.3
-   **Styling**: Vanilla CSS with Filament Blade components
-   **State Management**: Real-time `Get`/`Set` utilities for dynamic form interactions.

## 📦 Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/ERICK353/marlinpos.git
    cd marlinpos
    ```

2.  **Install dependencies**:
    ```bash
    composer install
    npm install && npm run dev
    ```

3.  **Setup Environment**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Migrate and Seed**:
    ```bash
    php artisan migrate:fresh --seed
    ```

## 🔐 Default Credentials

The system comes pre-seeded with the following accounts (all use `password` as the password):

-   **Admin**: `admin@marlin.local`
-   **Staff (Barber)**: `barber@marlin.local`
-   **Reception**: `reception@marlin.local`

## 🎨 Design Aesthetics

The application follows a premium dark-themed design (by default via Filament) with custom HSL-tailored color palettes for status badges and transaction states. It uses modern typography (Inter) and subtle micro-animations for a high-end feel.
