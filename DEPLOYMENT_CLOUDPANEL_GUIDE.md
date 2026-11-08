# CloudPanel Production Deployment Guide — promptimage.cc

This guide provides step-by-step instructions for deploying this Laravel project to **CloudPanel** on server **152.53.12.170** with domain **promptimage.cc** behind **Cloudflare**.

---

## 1. CloudPanel Site Creation

1. Log into your CloudPanel admin interface at `https://152.53.12.170:8443`.
2. Click **+ Add Site** &rarr; Select **Create a PHP Site**.
3. Fill in the site details:
   - **Domain Name**: `promptimage.cc`
   - **Site User**: `promptimage` (or your preferred user)
   - **Site User Password**: *(Set a strong password)*
   - **PHP Version**: `PHP 8.3` or `PHP 8.4` (PHP 8.4 is recommended)
4. Click **Create**.

---

## 2. CloudPanel Nginx Vhost Configuration

In CloudPanel, click on your site **promptimage.cc** &rarr; Go to the **Vhost** tab &rarr; Replace or verify the configuration to match Laravel's standard:

```nginx
server {
  listen 80;
  listen [::]:80;
  listen 443 ssl http2;
  listen [::]:443 ssl http2;
  server_name promptimage.cc www.promptimage.cc;
  
  # Point root strictly to the public directory
  root /home/promptimage/htdocs/promptimage.cc/public;
  index index.php index.html;

  # Cloudflare Real IP restoration
  set_real_ip_from 173.245.48.0/20;
  set_real_ip_from 103.21.244.0/22;
  set_real_ip_from 103.22.200.0/22;
  set_real_ip_from 103.31.4.0/22;
  set_real_ip_from 141.101.64.0/18;
  set_real_ip_from 108.162.192.0/18;
  set_real_ip_from 190.93.240.0/20;
  set_real_ip_from 188.114.96.0/20;
  set_real_ip_from 197.234.240.0/22;
  set_real_ip_from 198.41.128.0/17;
  set_real_ip_from 162.158.0.0/15;
  set_real_ip_from 104.16.0.0/13;
  set_real_ip_from 104.24.0.0/14;
  set_real_ip_from 172.64.0.0/13;
  set_real_ip_from 131.0.72.0/22;
  real_ip_header CF-Connecting-IP;

  client_max_body_size 64M;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location = /favicon.ico { access_log off; log_not_found off; }
  location = /robots.txt  { access_log off; log_not_found off; }

  # Serve media & covers with caching
  location ~* \.(jpg|jpeg|png|gif|webp|svg|ico|css|js|woff|woff2)$ {
    expires 30d;
    add_header Cache-Control "public, no-transform";
    try_files $uri =404;
  }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_intercept_errors on;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    try_files $uri =404;
    fastcgi_read_timeout 300;
    fastcgi_pass 127.0.0.1:9000; # Or your site's PHP-FPM socket
  }

  location ~ /\.(?!well-known).* {
    deny all;
  }
}
```

---

## 3. Database Creation & Content Import

### Step 3.1: Create MySQL Database in CloudPanel
1. In CloudPanel, go to the **Databases** tab.
2. Click **+ Add Database**:
   - **Database Name**: `promptimage_db`
   - **Database User**: `promptimage_user`
   - **Database Password**: *(Set a strong password)*
3. Click **Create**.

### Step 3.2: Import Current Data (Users, Prompts, Categories, Settings)
A complete export of your current database has been generated in:
`database/promptimage_export.sql.gz` (4.4 MB compressed, containing all 1,254 users, 5,026 prompts, 12 categories, 6 models, contact messages, newsletters, settings, etc.).

Transfer and import it to the server:
```bash
# From your local machine:
scp database/promptimage_export.sql.gz promptimage@152.53.12.170:/home/promptimage/

# On the server (SSH into 152.53.12.170):
zcat /home/promptimage/promptimage_export.sql.gz | mysql -u promptimage_user -p promptimage_db
```

---

## 4. Codebase & Storage Transfer

### Step 4.1: Deploy Code via Git or Rsync
Option A: Clone via Git:
```bash
cd /home/promptimage/htdocs/promptimage.cc
# Clone or pull your repository
git clone <YOUR_GIT_REPO_URL> .
```

Option B: Rsync from your local machine:
```bash
rsync -avz --exclude='node_modules' --exclude='.git' --exclude='storage/app/public/prompt-covers' \
  /Users/alindevx00x/.t3/projects/meigenclone/ \
  promptimage@152.53.12.170:/home/promptimage/htdocs/promptimage.cc/
```

### Step 4.2: Transfer the 5,000+ Prompt Covers (6.4 GB)
Transfer the image covers directly to the server:
```bash
# Run from local machine:
rsync -avzP /Users/alindevx00x/.t3/projects/meigenclone/storage/app/public/prompt-covers/ \
  promptimage@152.53.12.170:/home/promptimage/htdocs/promptimage.cc/storage/app/public/prompt-covers/
```

---

## 5. Production Environment Configuration (`.env`)

On the server, in `/home/promptimage/htdocs/promptimage.cc/.env`:

```env
APP_NAME="PromptImage"
APP_ENV=production
APP_KEY=base64:puA2nKRkHP8UFsvnTraM1hbuv88hZskVf+P0C6SyQxY=
APP_DEBUG=false
APP_URL=https://promptimage.cc

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=promptimage_db
DB_USERNAME=promptimage_user
DB_PASSWORD="YOUR_DATABASE_PASSWORD"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

# Mail is configured directly via Admin Settings in https://promptimage.cc/admin/settings
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_FROM_ADDRESS="support@promptimage.cc"
MAIL_FROM_NAME="PromptImage"
```

---

## 6. Permissions, Symlinks, & Optimizations

Run the following commands in `/home/promptimage/htdocs/promptimage.cc`:

```bash
# 1. Install production PHP dependencies
composer install --no-dev --optimize-autoloader

# 2. Re-create storage symlink
php artisan storage:link

# 3. Cache routes, views, events, and configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Set correct file permissions
chown -R promptimage:promptimage /home/promptimage/htdocs/promptimage.cc
chmod -R 775 storage bootstrap/cache
```

---

## 7. Automated Scheduler Cron in CloudPanel

To keep the scraper running automatically every 12 hours (`prompts:scrape-meigen`) and weekly deduplication:

1. In CloudPanel, click on your site **promptimage.cc** &rarr; Go to **Cron Jobs**.
2. Click **+ Add Cron Job**:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**:
     ```bash
     * * * * * cd /home/promptimage/htdocs/promptimage.cc && php artisan schedule:run >> /dev/null 2>&1
     ```
3. Save the cron job.

---

## 8. Cloudflare SSL & Security Settings

Since `promptimage.cc` DNS is already routed through Cloudflare:
1. In Cloudflare Dashboard &rarr; **SSL/TLS**:
   - Set encryption mode to **Full (strict)**.
2. In CloudPanel &rarr; Site **promptimage.cc** &rarr; **SSL/TLS**:
   - Issue a **Let's Encrypt** certificate OR install a **Cloudflare Origin Certificate** (valid for 15 years).
3. In Cloudflare &rarr; **Always Use HTTPS**: Enable.
