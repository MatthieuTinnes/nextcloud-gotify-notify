# Gotify Login Notifier for Nextcloud

A small Nextcloud app that sends a **Gotify** notification on every successful or failed user login.

## 📦 Installation

1. Clone the repository into your Nextcloud `apps` folder:
   ```bash
   cd /var/www/nextcloud/apps
   git clone git@github.com:MatthieuTinnes/nextcloud-gotify-notify.git
   chown -R www-data:www-data gotify_login

2. Add the environment variables in your docker-compose.yml:

        GOTIFY_URL=https://gotify.example.com
        GOTIFY_TOKEN=your_token
        GOTIFY_PRIORITY_SUCCESS=2
        GOTIFY_PRIORITY_FAIL=5