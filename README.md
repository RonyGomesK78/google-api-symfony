
# Symfony Google API Integration Project

This Symfony project integrates with the Google API to read the latest 10 messages from Gmail and list the latest 10 files from Google Drive. The project also includes cron jobs to automate these tasks.

## Prerequisites

- **PHP** >= 8.0
- **Composer** for dependency management
- **Symfony** >=7.0
- Google Cloud credentials with access to Gmail and Google Drive APIs

## Project Setup

### 1. Clone the Repository

```bash
git clone https://github.com/RonyGomesK78/google-api-symfony
cd <PROJECT_DIRECTORY>
```

### 2. Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

### 3. Google API Setup

1. In the Google Cloud Console, create a project and enable the **Gmail API** and **Google Drive API**.
2. Download the `credentials.json` file and place it in the `config/google` directory.

### 4. Generate Google OAuth Tokens

1. Run the application locally:

   ```bash
   symfony server:start
   ```

2. Open `http://127.0.0.1:8000/google/oauth` to authenticate with Google and save your OAuth tokens.
3. Tokens will be saved to `src/Controller/token.json`.

## Commands

The project includes Symfony console commands for fetching the latest Gmail messages and Google Drive files for each authenticated user.

## Setting Up Cron Jobs

To automate fetching messages and files, set up cron jobs that execute these commands at your preferred intervals.

### Example Cron Jobs

1. Open the cron file:

   ```bash
   crontab -e
   ```

2. Add the following entries to schedule the commands:

   ```bash
   # Fetch latest Gmail messages every 5 minutes
   */5 * * * *  /php_installation_path/path_to_project/bin/console app:fetch-latest-messages >> /path_to_project/var/log/cron.log 2>&1

   # Fetch latest Google Drive files every 10 minutes
   */10 * * * *  /php_installation_path/path_to_project/bin/console app:fetch_drive_files >> /path_to_project/var/log/cron.log 2>&1
   ```
   Replace `/path_to_project` with the actual path to your Symfony project.

   Run `which php` and replace `/php_installation_path` with the actual php path


3. To view the logs of your cron job execution, you can use the following command::

   ```bash
   tail -f /path_to_project/var/log/cron.log
   ```

## Project Structure

- **src/Controller/GoogleOAuthController.php** - Handles OAuth authentication and callback.
- **src/Service/GoogleClientService.php** - Manages Google API client setup.
- **src/Command/FetchLatestMessagesCommand.php** - Command to fetch the 10 latest Gmail messages.
- **src/Command/FetchDriveFilesCommand.php** - Command to fetch the 10 latest files from Google Drive.
**src/Controller/token.json** - Users accessTokens for Google API (excluded from version control).
- **config/google/credentials.json** - OAuth credentials for Google API (excluded from version control).

## Troubleshooting

### "Insufficient Permission" Error

Ensure that the necessary scopes are set in `GoogleClientService` for Gmail and Google Drive:
```php
$this->client->addScope(Gmail::MAIL_GOOGLE_COM);
$this->client->addScope(Drive::DRIVE);
```

## License

This project is open source and available under the [MIT License](LICENSE).
