#!/bin/bash

# Fetch the latest updates
git fetch

# Display current branch
git status | head -1

# Reset to origin
git reset --hard origin/master

# Pull the latest updates
git pull

# Install composer dependencies
php composer.phar install --optimize-autoloader --prefer-dist --no-dev --no-interaction

# Restart httpd service if it exists
if sudo service --status-all | grep -Fq 'httpd'; then
    echo "" | sudo -S service httpd reload
fi

# Restart apache2 service if it exists
if sudo service --status-all | grep -Fq 'apache2'; then
    echo "" | sudo -S service apache2 reload
fi

# Restart php7.3-fpm service if it exists
if sudo service --status-all | grep -Fq 'php7.3-fpm'; then
    echo "" | sudo -S service php7.3-fpm reload
fi

# Restart php7.4-fpm service if it exists
if sudo service --status-all | grep -Fq 'php7.4-fpm'; then
    echo "" | sudo -S service php7.4-fpm reload
fi

# Clear all cached data
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Run migrations
php artisan migrate --force

# Restart the queues
php artisan queue:restart

# Cache configuration and routes
# php artisan config:cache
# php artisan route:cache
