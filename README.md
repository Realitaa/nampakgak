# NampakGak - Lost and Found System

NampakGak is a web-based application designed to help communities manage lost and found items. Built with a modern technology stack, it provides an intuitive platform for users to report lost belongings or register items they have found, facilitating the process of reuniting owners with their property.

## 🚀 Features

*   **Report Lost Items:** Users can easily submit details about items they have lost, including descriptions, categories, and the location where it was last seen.
*   **Report Found Items:** Finders can list items they have discovered, providing a centralized repository for lost goods.
*   **Item Claims:** A built-in system allows users to claim items they believe belong to them, subject to verification.
*   **Found Notifications:** Automated alerts to notify potential owners when an item matching their lost report is found.
*   **Dashboard & User Profiles:** Personalized dashboards for users to manage their reports, claims, and account settings.
*   **Responsive Design:** Fully responsive interface optimized for both desktop and mobile devices.
*   **Secure Authentication:** User registration, login, password reset, and profile management capabilities.

## 🛠️ Tech Stack

This project is built using the following modern web technologies:

*   **Backend:** [Laravel](https://laravel.com/) (PHP Framework)
*   **Frontend:** [Vue.js 3](https://vuejs.org/) with [Inertia.js](https://inertiajs.com/)
*   **Styling:** [Tailwind CSS](https://tailwindcss.com/)
*   **UI Components:** Custom components inspired by modern design systems (likely based on shadcn/ui or similar given the structure).
*   **Package Managers:** Composer (PHP) and pnpm (JavaScript/TypeScript).
*   **Testing:** Pest/PHPUnit for backend testing.

## ⚙️ Prerequisites

Before you begin, ensure you have the following installed on your local machine:

*   **PHP** (>= 8.2)
*   **Composer**
*   **Node.js** (LTS version recommended)
*   **pnpm** (preferred) or **npm**
*   **Database** (MySQL, PostgreSQL, or SQLite for local development)

## 💻 Installation & Setup

Follow these steps to get the project up and running locally:

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd nampakgak
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Install JavaScript Dependencies:**
    ```bash
    pnpm install
    # or npm install
    ```

4.  **Environment Setup:**
    Copy the example environment file and configure your database settings.
    ```bash
    cp .env.example .env
    ```
    Open `.env` and update your `DB_*` variables according to your local database setup (e.g., set `DB_CONNECTION=sqlite` and create a `database.sqlite` file in the `database` directory for quick testing).

5.  **Generate Application Key:**
    ```bash
    php artisan key:generate
    ```

6.  **Run Database Migrations & Seeders:**
    Set up the database schema and populate it with initial/dummy data.
    ```bash
    php artisan migrate --seed
    ```

7.  **Build Frontend Assets:**
    Compile the Vue components and Tailwind CSS for development.
    ```bash
    pnpm dev
    # or npm run dev
    ```

8.  **Serve the Application:**
    In a new terminal window, start the Laravel development server.
    ```bash
    php artisan serve
    ```

    The application should now be accessible at `http://localhost:8000`.

## 🧪 Testing

To run the automated test suite, use the following command:

```bash
php artisan test
```

## 📂 Project Structure Highlights

*   `app/`: Core application logic (Controllers, Models, Services).
*   `database/`: Migrations, factories, and seeders for database management.
*   `resources/js/`: Frontend assets including Vue pages (`resources/js/pages/`), UI components (`resources/js/components/`), and layouts.
*   `routes/`: Application routing (`web.php` for HTTP routes).
*   `tests/`: Feature and Unit tests ensuring application stability.

## 📄 License

[Specify License Here - e.g., MIT License]