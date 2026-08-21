#!/bin/bash
set -e

echo "🚀 Starting Virtual Workplace Production Deployment..."

# 1. Pull latest code from GitHub
echo "📦 Pulling latest changes from Git..."
git pull origin main

# 2. Install/Update PHP Dependencies
echo "🐘 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 3. Migrate Database
echo "🗄️ Running database migrations..."
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force

# 4. Clear and Cache Laravel Configuration & Routes
echo "⚡ Optimizing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Build Frontend Assets
echo "🎨 Building frontend assets..."
npm ci
npm run build

# 6. Build and Restart Realtime WebSocket Service
echo "⚡ Building Realtime Service..."
cd realtime
npm ci
npm run build
cd ..

if command -v pm2 &> /dev/null; then
    echo "🔄 Restarting Realtime WebSocket daemon with PM2..."
    pm2 restart vwork-realtime || pm2 start realtime/dist/server.js --name "vwork-realtime"
fi

# 7. Fix Permissions
echo "🔒 Ensuring correct storage permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Virtual Workplace Deployment successfully completed!"
