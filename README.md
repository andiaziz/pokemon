# Pokemon Database

A Laravel application that fetches Pokemon data from [PokeAPI](https://pokeapi.co/) and displays it in an interactive table.

## Features

- Fetches Pokemon data (ID 1-400) from PokeAPI
- Filters Pokemon with weight >= 100
- Downloads and stores Pokemon images locally
- Stores Pokemon abilities (non-hidden only)
- Many-to-many relationship between Pokemon and Abilities
- Interactive data table with:
  - Search by name
  - Filter by weight category (Light, Medium, Heavy)
  - Sortable columns (Name, Base Experience, Weight)
  - Default sort by weight (heaviest first)
  - Pagination (10, 25, 50 per page)
  - Pokemon images displayed inline

## Requirements

- PHP 8.2+
- Laravel 12
- SQLite (or any database)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/andiaziz/pokemon.git
cd pokemon
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure database in `.env` (SQLite is default):
```
DB_CONNECTION=sqlite
```

6. Run migrations:
```bash
php artisan migrate
```

7. Create storage symbolic link:
```bash
php artisan storage:link
```

## Fetch Pokemon Data

Run the fetch command to download Pokemon data from PokeAPI:
```bash
php artisan pokemon:fetch
```

This will:
- Fetch Pokemon ID 1-400
- Filter only Pokemon with weight >= 100
- Download images to local storage
- Save only non-hidden abilities

## Usage

Visit the homepage (`/`) to view the Pokemon database table.

### Weight Filter Categories
- **Light**: Weight 100-150
- **Medium**: Weight 151-199
- **Heavy**: Weight ≥200

## Database Structure

### Tables
- `pokemons` - Stores Pokemon data (name, base_experience, weight, image_path)
- `abilities` - Stores ability names
- `pokemon_abilities` - Pivot table for Pokemon-Ability relationship

## File Storage

Pokemon images are stored in:
- `storage/app/public/pokemon_images/`
- Accessible via `public/storage/pokemon_images/`

## Tech Stack

- Laravel 12
- Livewire 3
- Tailwind CSS (CDN)
- SQLite

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
