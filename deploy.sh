#!/usr/bin/env bash

# ==============================================================================
# PromptImage — CloudPanel Production Deployment Script
# Domain: promptimage.cc | Server: 152.53.12.170
# Repository: https://github.com/alindevx00x/meigenclone.git
# ==============================================================================

set -e

# Target branch (defaults to main)
BRANCH="${1:-main}"

# Colors for terminal output
BOLD="\033[1m"
GREEN="\033[0;32m"
CYAN="\033[0;36m"
YELLOW="\033[1;33m"
RED="\033[0;31m"
RESET="\033[0m"

echo -e "\n${BOLD}${CYAN}=====================================================${RESET}"
echo -e "${BOLD}${CYAN}   🚀 Starting PromptImage Production Deployment     ${RESET}"
echo -e "${BOLD}${CYAN}   Branch: ${YELLOW}${BRANCH}${CYAN} | Date: $(date '+%Y-%m-%d %H:%M:%S')${RESET}"
echo -e "${BOLD}${CYAN}=====================================================${RESET}\n"

# Navigate to project root directory
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${PROJECT_ROOT}"

# Detect PHP Binary (CloudPanel PHP 8.4 / 8.3 or system default)
if [ -x "/usr/bin/php8.4" ]; then
    PHP_BIN="/usr/bin/php8.4"
elif [ -x "/usr/bin/php8.3" ]; then
    PHP_BIN="/usr/bin/php8.3"
else
    PHP_BIN="$(command -v php || true)"
fi

if [ -z "${PHP_BIN}" ]; then
    echo -e "${RED}❌ Error: PHP binary could not be found.${RESET}"
    exit 1
fi

echo -e "${CYAN}ℹ️  Using PHP binary: ${BOLD}${PHP_BIN}${RESET}"

# Detect Composer Binary
if [ -x "/usr/local/bin/composer" ]; then
    COMPOSER_BIN="/usr/local/bin/composer"
elif [ -x "/usr/bin/composer" ]; then
    COMPOSER_BIN="/usr/bin/composer"
else
    COMPOSER_BIN="$(command -v composer || true)"
fi

if [ -z "${COMPOSER_BIN}" ]; then
    echo -e "${RED}❌ Error: Composer could not be found.${RESET}"
    exit 1
fi

# 1. Put Application in Maintenance Mode (if artisan exists)
if [ -f "artisan" ]; then
    echo -e "\n${YELLOW}▶️  Enabling maintenance mode...${RESET}"
    ${PHP_BIN} artisan down --render="errors::503" --retry=30 || true
fi

# 2. Pull latest code from GitHub
echo -e "\n${CYAN}▶️  Fetching and pulling latest commits from origin/${BRANCH}...${RESET}"
git fetch origin "${BRANCH}"
git reset --hard "origin/${BRANCH}"

CURRENT_COMMIT="$(git rev-parse --short HEAD)"
COMMIT_MSG="$(git log -1 --pretty=%B | head -n 1)"
echo -e "${GREEN}✓ Updated to commit: ${BOLD}${CURRENT_COMMIT}${RESET} — \"${COMMIT_MSG}\""

# 3. Install/Update Composer Dependencies
echo -e "\n${CYAN}▶️  Installing optimized PHP dependencies...${RESET}"
${PHP_BIN} "${COMPOSER_BIN}" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Run Database Migrations
echo -e "\n${CYAN}▶️  Running database migrations...${RESET}"
${PHP_BIN} artisan migrate --force

# 5. Ensure Storage Symlink Exists
echo -e "\n${CYAN}▶️  Ensuring public storage symlink...${RESET}"
${PHP_BIN} artisan storage:link || true

# 6. Optimize and Cache Configurations, Routes, Views, and Events
echo -e "\n${CYAN}▶️  Building optimized production caches...${RESET}"
${PHP_BIN} artisan optimize:clear
${PHP_BIN} artisan config:cache
${PHP_BIN} artisan route:cache
${PHP_BIN} artisan view:cache
${PHP_BIN} artisan event:cache

# 7. Set Permissions on Writable Directories
echo -e "\n${CYAN}▶️  Setting file permissions for web worker...${RESET}"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 8. Reload PHP-FPM / OPcache if systemctl is available
echo -e "\n${CYAN}▶️  Reloading PHP-FPM cache...${RESET}"
if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload php8.4-fpm 2>/dev/null || sudo systemctl reload php8.3-fpm 2>/dev/null || true
fi

# 9. Bring Application Back Online
echo -e "\n${GREEN}▶️  Bringing application out of maintenance mode...${RESET}"
${PHP_BIN} artisan up

echo -e "\n${BOLD}${GREEN}=====================================================${RESET}"
echo -e "${BOLD}${GREEN}   ✅ PromptImage Deployment Successfully Completed!  ${RESET}"
echo -e "${BOLD}${GREEN}   Current Version: ${CURRENT_COMMIT}                     ${RESET}"
echo -e "${BOLD}${GREEN}   URL: https://promptimage.cc                       ${RESET}"
echo -e "${BOLD}${GREEN}=====================================================${RESET}\n"
