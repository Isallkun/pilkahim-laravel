#!/bin/bash
# ============================================================
# E-Vote Arutala — VPS Setup Script (Ubuntu 22.04/24.04)
# Jalankan sebagai root: sudo bash setup-vps.sh
# ============================================================

set -e

echo "=========================================="
echo "  E-Vote Arutala — VPS Setup"
echo "=========================================="

# --- 1. Update system ---
echo "[1/7] Updating system..."
apt update && apt upgrade -y

# --- 2. Install Nginx ---
echo "[2/7] Installing Nginx..."
apt install -y nginx
systemctl enable nginx
systemctl start nginx

# --- 3. Install PHP 8.3 + extensions ---
echo "[3/7] Installing PHP 8.3..."
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-readline \
    php8.3-dom php8.3-fileinfo php8.3-tokenizer
systemctl enable php8.3-fpm
systemctl start php8.3-fpm

# --- 4. Install MySQL 8 ---
echo "[4/7] Installing MySQL..."
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

# Buat database dan user
echo "Creating database..."
mysql -e "CREATE DATABASE IF NOT EXISTS arutalavote CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'arutala'@'localhost' IDENTIFIED BY 'ArutalaVote2026!';"
mysql -e "GRANT ALL PRIVILEGES ON arutalavote.* TO 'arutala'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "  DB Name: arutalavote"
echo "  DB User: arutala"
echo "  DB Pass: ArutalaVote2026!"

# --- 5. Install Composer ---
echo "[5/7] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# --- 6. Install Node.js 22 (untuk build assets) ---
echo "[6/7] Installing Node.js 22..."
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs

# --- 7. Install Certbot (untuk HTTPS nanti) ---
echo "[7/7] Installing Certbot..."
apt install -y certbot python3-certbot-nginx

# --- Firewall ---
echo "Configuring firewall..."
ufw allow 'Nginx Full'
ufw allow OpenSSH
ufw --force enable

echo ""
echo "=========================================="
echo "  Setup selesai!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "  1. Upload project ke /var/www/arutala"
echo "  2. Jalankan: sudo bash /var/www/arutala/deploy/deploy.sh"
echo ""
