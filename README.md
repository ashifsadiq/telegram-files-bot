<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="public/assets/Banner.png" width="100%" alt="Laravel Logo">
  </a>
</p>

# Telegram Cloud Bot

## About

Telegram Cloud Bot is a modern, open-source Telegram Bot built with Laravel, React, and various third-party APIs. It offers seamless file management and messaging functionalities directly via Telegram.

- Create folders with ease
- Upload any file type
- Send messages to any user


## Requirements

- [Composer](https://getcomposer.org/download/) - version 2.8.3 or higher
- [PHP](https://www.php.net/downloads.php) - version 8.4.10 or higher
- [Laravel Installer](https://laravel.com/docs/master/installation) - version 5.10.0 (optional but recommended)
- [Node.js](https://nodejs.org/en/download) - version 23.5.0 or higher

## Architecture Overview

Backend: Laravel API for authentication, file management, user handling, bot operations.

Frontend: Single-Page App (SPA) built with React (optional: Inertia/Vue support).

Bot Service: Communicates with Telegram API, processes commands/events.

Database: Stores users, files, folders, permissions, and message logs.

Queue/Worker: Handles intensive jobs (file processing, notifications) asynchronously.


## Installation

Clone the repository:

```sh
git clone https://github.com/ashifsadiq/telegram-files-bot.git
```

Copy the example environment file and update configurations:

```sh
cp .env.example .env
```

Edit the `.env` file to configure the following:

- `APP_ENV`
- `BOT_TOKEN`
- `COMMON_GROUP`
- `DEVELOPER_TG_ID`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_DATABASE`
- `PORT`

Install PHP dependencies via Composer:

```sh
composer install
```

Install JavaScript packages:

```sh
npm install
```

Build the frontend assets:

```sh
npm run build
```

Generate Laravel application key:

```sh
php artisan key:generate
```


## Contribution

Contributions are welcome! Whether it's bug fixes, feature requests, or improvements, your involvement will help enhance this project.

- Fork the repository
- Create a feature branch (`git checkout -b feature/your-feature`)
- Commit your changes (`git commit -m 'Add some feature'`)
- Push to the branch (`git push origin feature/your-feature`)
- Open a Pull Request


## Developer Support

For advanced features, custom integrations, or professional support, please contact the maintainer or consider sponsoring the project to help fund continued development.

## License

This project is open-source and licensed under the [MIT License](LICENSE).

***

This completion provides clarity, professionalism, and all essential details for users and contributors. Let me know if you want me to add specific sections like usage examples, troubleshooting, or FAQs!

<div style="text-align: center">⁂</div>