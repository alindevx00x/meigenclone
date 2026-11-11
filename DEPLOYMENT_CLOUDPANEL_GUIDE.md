# CloudPanel Production Deployment Guide — promptimage.cc

This comprehensive guide walks you through deploying **promptimage.cc** to **CloudPanel** on server **152.53.12.170** using **GitHub pull**, and using the automated `deploy.sh` script for all future updates.

---

## Architecture Overview

- **Server IP**: `152.53.12.170`
- **Domain**: `promptimage.cc` (and `www.promptimage.cc`)
- **Control Panel**: CloudPanel (`https://152.53.12.170:8443`)
- **Site Directory**: `/home/promptimage/htdocs/promptimage.cc`
- **Document Root**: `/home/promptimage/htdocs/promptimage.cc/public`
- **Repository**: `https://github.com/alindevx00x/meigenclone.git`
- **Branch**: `main`
- **PHP Version**: `PHP 8.4` (or `8.3`)

---

## Phase 1: CloudPanel Setup & Prerequisites

### 1.1 Create the PHP Site in CloudPanel
1. Open CloudPanel: `https://152.53.12.170:8443`.
2. Click **+ Add Site** &rarr; select **Create a PHP Site**.
3. Configure site settings:
   - **Domain Name**: `promptimage.cc`
   - **Site User**: `promptimage`
   - **Password**: *(Create a secure password)*
   - **PHP Version**: `PHP 8.4`
4. Click **Create**.

### 1.2 Verify Nginx Vhost Configuration
In CloudPanel, click **promptimage.cc** &rarr; **Vhost** tab. Ensure:
- The document root points to `public`:
  ```nginx
  root /home/promptimage/htdocs/promptimage.cc/public;
  ```
- FastCGI / PHP routing matches Laravel:
  ```nginx
  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }
  ```
- Static assets caching is enabled for WebP, images, CSS, and JS:
  ```nginx
  location ~* \.(jpg|jpeg|png|gif|webp|svg|ico|css|js|woff|woff2)$ {
    expires 30d;
    add_header Cache-Control "public, no-transform";
    try_files $uri =404;
  }
  ```

### 1.3 Create the MySQL Database
1. In CloudPanel, click **Databases** &rarr; **+ Add Database**.
2. Configure database credentials:
   - **Database Name**: `promptimage_db`
   - **Database User**: `promptimage_user`
   - **Password**: *(Set a strong password)*
3. Click **Create**.

---

## Phase 2: Deploying via GitHub Pull (First-Time Setup)

### 2.1 Connect via SSH
Open your local terminal and SSH into your CloudPanel server:
```bash
ssh promptimage@152.53.12.170
```
*(Or login as `root@152.53.12.170` and switch to `su - promptimage`).*

### 2.2 Clone the GitHub Repository
In the site directory:
```bash
# Navigate to the htdocs folder
cd /home/promptimage/htdocs

# If CloudPanel created a default index.html in promptimage.cc, backup or remove it:
rm -rf promptimage.cc

# Clone directly from GitHub
git clone https://github.com/alindevx00x/meigenclone.git promptimage.cc

# Enter the project directory
cd promptimage.cc
```

> **Tip for Private Repositories**: If the repository is set to private in the future:
> 1. Run `ssh-keygen -t ed25519 -C "deploy@promptimage.cc"` on the server.
> 2. Copy `cat ~/.ssh/id_ed25519.pub` and add it to **GitHub Repository &rarr; Settings &rarr; Deploy Keys**.
> 3. Clone using `git clone git@github.com:alindevx00x/meigenclone.git promptimage.cc`.

---

## Phase 3: Environment Configuration (`.env`) & Database Import

### 3.1 Create Production `.env`
In `/home/promptimage/htdocs/promptimage.cc`:
```bash
cp .env.example .env
nano .env
```
Ensure the production values are filled:
```env
APP_NAME="PromptImage"
APP_ENV=production
APP_KEY=base64:puA2nKRkHP8UFsvnTraM1hbuv88hZskVf+P0C6SyQxY=
APP_DEBUG=false
APP_URL=https://promptimage.cc

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=promptimagedb
DB_USERNAME=promptimageusr
DB_PASSWORD="YOUR_DATABASE_PASSWORD_HERE"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```
Press `Ctrl + O` to save and `Ctrl + X` to exit.

### 3.2 Import Database Dump
The repository includes the complete production dump `database/promptimage_export.sql.gz` containing all 1,254 users, 5,026 prompts, 12 categories, 6 AI models, contact messages, and site settings.

Import it directly on the server:
```bash
zcat /home/promptimage/htdocs/promptimage.cc/database/promptimage_export.sql.gz | mysql -u promptimageusr -p promptimagedb
```

### 3.3 Transfer Local Cover Images
Transfer the 5,000+ local image covers from your local development machine:
```bash
# Run this from your local Mac terminal:
rsync -avzP /Users/alindevx00x/.t3/projects/meigenclone/storage/app/public/prompt-covers/ \
  promptimage@152.53.12.170:/home/promptimage/htdocs/promptimage.cc/storage/app/public/prompt-covers/
```

---

## Phase 4: Run Initial Deployment & Verification

On the server in `/home/promptimage/htdocs/promptimage.cc`:
```bash
# Make sure deploy.sh is executable
chmod +x deploy.sh

# Run the deployment script
./deploy.sh
```

The script will automatically:
1. Enable maintenance mode.
2. Pull the latest commits from GitHub `main`.
3. Install optimized Composer dependencies (`--no-dev --optimize-autoloader`).
4. Run any pending database migrations.
5. Create the public storage symlink (`public/storage` &rarr; `storage/app/public`).
6. Cache routes, views, configuration, and events.
7. Set 775 permissions on `storage` and `bootstrap/cache`.
8. Reload PHP-FPM / OPcache.
9. Take the application out of maintenance mode.

---

## Phase 5: Automated Scheduler (Cron Job)

To keep prompt synchronization (`prompts:sync`), queue jobs, and scheduled tasks running:
1. In CloudPanel, click **promptimage.cc** &rarr; **Cron Jobs** tab.
2. Click **+ Add Cron Job**:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**:
     ```bash
     * * * * * cd /home/promptimage/htdocs/promptimage.cc && /usr/bin/php8.4 artisan schedule:run >> /dev/null 2>&1
     ```
3. Save the cron job.

---

## Phase 6: How to Deploy Future Updates

Whenever you make changes, commit, and push to GitHub:
```bash
# Locally:
git add .
git commit -m "Your update description"
git push origin main
```

To deploy to production, run **one single command** on the server:
```bash
# On the server:
cd /home/promptimage/htdocs/promptimage.cc && ./deploy.sh
```

### (Optional) One-Click Deployment via SSH from Local Machine
You can deploy directly from your local terminal without logging in interactively:
```bash
ssh promptimage@152.53.12.170 "cd /home/promptimage/htdocs/promptimage.cc && ./deploy.sh"
```
