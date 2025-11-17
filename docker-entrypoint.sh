#!/bin/bash

# Start the queue worker in the background
echo "Starting Laravel queue worker..."
php /var/www/html/artisan queue:work --tries=3 --sleep=3 --max-jobs=1000 > /var/log/queue-worker.log 2>&1 &

# Start the web server using supervisord (default Laravel Sail behavior)
echo "Starting Laravel web server..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf