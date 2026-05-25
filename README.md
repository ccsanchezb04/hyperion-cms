# Hyperion CMS

A powerful content management system built with Laravel 12 + Vue 3 + Inertia.js.

## Features

- **Content Management**: Full CRUD with versioning system
- **Media Library**: Upload and manage files (images, videos, documents)
- **Category Management**: Hierarchical category tree structure
- **User Management**: Role-based access control (RBAC)
- **Menu System**: Dynamic menu management with hierarchical items
- **Settings Management**: Global configuration system
- **AI Integration**: Content generation and SEO optimization (simulated)
- **Modern Frontend**: Vue 3 Composition API with TypeScript
- **Responsive Design**: Mobile-friendly interface with Tailwind CSS

## Tech Stack

### Backend
- **PHP 8.2+**
- **Laravel 12**
- **MySQL**
- **Laravel Sanctum** (Authentication)
- **Inertia.js** (SPA without API)

### Frontend
- **Vue 3** (Composition API)
- **TypeScript**
- **Vite** (Build tool)
- **Tailwind CSS** (Styling)
- **Radix Vue** (UI components)
- **Lucide Icons** (Icon library)

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL or MariaDB

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/ccsanchezb04/hyperion-cms.git
   cd hyperion-cms
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   ```
   Configure your database settings in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hyperion_cms
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   npm run dev
   ```

## Default Credentials

After running the seeders, you can log in with:

- **Super Admin**: admin@hyperion.local / admin123
- **Editor**: editor@hyperion.local / editor123
- **Viewer**: viewer@hyperion.local / viewer123

## Project Structure

```
hyperion-cms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API Controllers
│   │   │   ├── Auth/         # Authentication Controllers
│   │   │   └── Settings/     # Settings Controllers
│   │   ├── Middleware/       # Custom Middleware
│   │   ├── Requests/         # Form Request Validators
│   │   └── Resources/        # API Resources
│   ├── Models/               # Eloquent Models
│   └── Traits/               # Reusable Traits
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/             # Database Seeders
├── resources/
│   ├── js/
│   │   ├── pages/           # Inertia Pages
│   │   ├── components/      # Vue Components
│   │   └── layouts/         # Vue Layouts
│   └── css/                 # Stylesheets
├── routes/
│   ├── web.php              # Web Routes
│   ├── api.php              # API Routes
│   └── api/v1/              # API v1 Routes
└── public/                  # Public Directory
```

## API Endpoints

### Contents
- `GET /api/v1/contents` - List contents
- `POST /api/v1/contents` - Create content
- `GET /api/v1/contents/{slug}` - Get content
- `PUT /api/v1/contents/{content}` - Update content
- `DELETE /api/v1/contents/{content}` - Delete content
- `POST /api/v1/contents/{content}/publish` - Publish content
- `POST /api/v1/contents/{content}/archive` - Archive content

### Categories
- `GET /api/v1/categories` - List categories
- `GET /api/v1/categories/tree` - Get category tree
- `POST /api/v1/categories` - Create category
- `PUT /api/v1/categories/{category}` - Update category
- `DELETE /api/v1/categories/{category}` - Delete category
- `POST /api/v1/categories/{category}/move` - Move category

### Media
- `GET /api/v1/media` - List media files
- `POST /api/v1/media/upload` - Upload file
- `POST /api/v1/media/batch-upload` - Batch upload
- `DELETE /api/v1/media/{media}` - Delete media

### Users
- `GET /api/v1/users` - List users
- `POST /api/v1/users` - Create user
- `PUT /api/v1/users/{user}` - Update user
- `DELETE /api/v1/users/{user}` - Delete user

### Settings
- `GET /api/v1/settings` - Get all settings
- `PUT /api/v1/settings` - Update settings

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
npm run lint
npm run format
```

### Building for Production
```bash
npm run build
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the MIT license.

## Credits

Built with [Laravel](https://laravel.com), [Vue.js](https://vuejs.org), and [Inertia.js](https://inertiajs.com).
