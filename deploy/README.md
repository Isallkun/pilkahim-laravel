# Deployment Guide — E-Vote Arutala

## Langkah Deploy ke VPS Ubuntu

### 1. SSH ke VPS
```bash
ssh root@YOUR_VPS_IP
```

### 2. Upload project ke VPS
Dari laptop lo (bukan di VPS):
```bash
# Option A: via Git (recommended)
# Push ke GitHub/GitLab dulu, lalu di VPS:
cd /var/www
git clone https://github.com/YOUR_REPO/pilkahim-laravel.git arutala

# Option B: via SCP (langsung upload)
scp -r . root@YOUR_VPS_IP:/var/www/arutala
```

### 3. Jalankan setup script (sekali aja, pertama kali)
```bash
cd /var/www/arutala
sudo bash deploy/setup-vps.sh
```

### 4. Jalankan deploy script
```bash
sudo bash deploy/deploy.sh
```

### 5. Setup Nginx
```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/arutala
sudo ln -s /etc/nginx/sites-available/arutala /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Test
Buka browser: `http://YOUR_VPS_IP`

---

## Setup HTTPS (setelah domain pointing)

```bash
sudo certbot --nginx -d yourdomain.com
```

Certbot otomatis update Nginx config untuk redirect HTTP → HTTPS.

---

## Setup Cron (auto-close election)

```bash
sudo crontab -e
```

Tambahkan:
```
* * * * * cd /var/www/arutala && php artisan schedule:run >> /dev/null 2>&1
```

---

## Update Deployment (setelah ada perubahan kode)

```bash
cd /var/www/arutala
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data /var/www/arutala
sudo systemctl reload php8.3-fpm
```

---

## Troubleshooting

### 502 Bad Gateway
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### Permission denied
```bash
sudo chown -R www-data:www-data /var/www/arutala
sudo chmod -R 775 /var/www/arutala/storage
sudo chmod -R 775 /var/www/arutala/bootstrap/cache
```

### Check logs
```bash
tail -f /var/www/arutala/storage/logs/laravel.log
tail -f /var/log/nginx/arutala-error.log
```
