#!/bin/bash
# ============================================================
# E-Vote Arutala — Deploy Script
# Jalankan dari root project: sudo bash deploy/deploy.sh
# ============================================================

set -e

APP_DIR="/var/www/arutala"
APP_USER="www-data"

echo "=========================================="
echo "  Deploying E-Vote Arutala..."
echo "=========================================="

# --- 1. Set permissions ---
echo "[1/6] Setting permissions..."
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

# --- 2. Install PHP dependencies ---
echo "[2/6] Installing Composer dependencies..."
cd $APP_DIR
composer install --no-dev --optimize-autoloader --no-interaction

# --- 3. Setup .env ---
if [ ! -f .env ]; then
    echo "[3/6] Creating .env file..."
    cp .env.example .env
    php artisan key:generate
    echo ""
    echo "⚠️  EDIT .env FILE sebelum lanjut!"
    echo "   nano /var/www/arutala/.env"
    echo ""
    echo "   Set minimal:"
    echo "   APP_ENV=production"
    echo "   APP_DEBUG=false"
    echo "   APP_URL=http://YOUR_IP_OR_DOMAIN"
    echo "   DB_CONNECTION=mysql"
    echo "   DB_HOST=127.0.0.1"
    echo "   DB_DATABASE=arutalavote"
    echo "   DB_USERNAME=arutala"
    echo "   DB_PASSWORD=ArutalaVote2026!"
    echo "   SESSION_LIFETIME=30"
    echo ""
    read -p "Tekan Enter setelah edit .env..."
else
    echo "[3/6] .env already exists, skipping..."
fi

# --- 4. Build frontend assets ---
echo "[4/6] Building frontend assets..."
npm ci --production=false
npm run build

# --- 5. Laravel setup ---
echo "[5/6] Running Laravel setup..."
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# --- 6. Fix permissions again after build ---
echo "[6/6] Final permissions..."
chown -R $APP_USER:$APP_USER $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

echo ""
echo "=========================================="
echo "  Deploy selesai!"
echo "=========================================="
echo ""
echo "Jangan lupa setup Nginx config:"
echo "  sudo cp deploy/nginx.conf /etc/nginx/sites-available/arutala"
echo "  sudo ln -s /etc/nginx/sites-available/arutala /etc/nginx/sites-enabled/"
echo "  sudo rm /etc/nginx/sites-enabled/default"
echo "  sudo nginx -t && sudo systemctl reload nginx"
echo ""
