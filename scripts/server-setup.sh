#!/usr/bin/env bash
# InvoiceKit — One-time server bootstrap script
# Run as root on a fresh Ubuntu 22.04 / 24.04 server
#
# Usage:
#   bash server-setup.sh
set -euo pipefail

DEPLOY_PATH="/opt/invoicekit"
GITHUB_REPO="https://github.com/ash-rain/invoicekit.git"

# ── Gather inputs ──────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════╗"
echo "║   InvoiceKit — Server Setup Wizard   ║"
echo "╚══════════════════════════════════════╝"
echo ""

read -r -p "GitHub Personal Access Token (repo read scope): " GITHUB_PAT
read -r -p "Application URL (e.g. http://204.168.164.127): " APP_URL
read -r -s -p "Database password: " DB_PASSWORD
echo ""
read -r -p "Mail host (leave blank to use log driver): " MAIL_HOST
read -r -p "Mail from address [hello@example.com]: " MAIL_FROM
MAIL_FROM="${MAIL_FROM:-hello@example.com}"

APP_KEY="base64:$(openssl rand -base64 32)"
MAIL_MAILER="$( [ -n "${MAIL_HOST}" ] && echo "smtp" || echo "log" )"

# ── System update ──────────────────────────────────────────────────────────────
echo ""
echo "» Updating system packages..."
apt-get update -qq
apt-get upgrade -y -qq

echo "» Installing dependencies..."
apt-get install -y -qq \
  git curl wget unzip \
  software-properties-common \
  apt-transport-https \
  ca-certificates gnupg \
  lsb-release ufw

# ── Docker ────────────────────────────────────────────────────────────────────
echo "» Installing Docker..."
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
  | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
chmod a+r /etc/apt/keyrings/docker.gpg

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" \
  > /etc/apt/sources.list.d/docker.list

apt-get update -qq
apt-get install -y -qq \
  docker-ce docker-ce-cli containerd.io \
  docker-buildx-plugin docker-compose-plugin

systemctl enable --now docker

# ── Firewall ──────────────────────────────────────────────────────────────────
echo "» Configuring firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw --force enable

# ── Git credentials ───────────────────────────────────────────────────────────
echo "» Configuring git..."
git config --global credential.helper 'store --file /root/.git-credentials'
echo "https://x-access-token:${GITHUB_PAT}@github.com" > /root/.git-credentials
chmod 600 /root/.git-credentials

# ── Clone repository ──────────────────────────────────────────────────────────
echo "» Cloning repository to ${DEPLOY_PATH}..."
git clone "${GITHUB_REPO}" "${DEPLOY_PATH}"
cd "${DEPLOY_PATH}"

# ── Production .env ───────────────────────────────────────────────────────────
echo "» Creating .env..."
cat > .env <<EOF
APP_NAME=InvoiceKit
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=${APP_URL}
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=invoicekit
DB_USERNAME=invoicekit
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_CONNECTION=log
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=${MAIL_MAILER}
MAIL_HOST=${MAIL_HOST:-}
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=${MAIL_FROM}
MAIL_FROM_NAME="InvoiceKit"

# Docker ports — nginx on 80, db/redis bound to localhost only
NGINX_PORT=80
POSTGRES_PORT=127.0.0.1:5432
REDIS_PORT=127.0.0.1:6379

# Postgres container credentials (must match DB_ above)
POSTGRES_DB=invoicekit
POSTGRES_USER=invoicekit
POSTGRES_PASSWORD=${DB_PASSWORD}
EOF
chmod 600 .env

# ── Build & start ─────────────────────────────────────────────────────────────
echo "» Installing PHP dependencies..."
docker compose run --rm --no-deps app \
  composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "» Building frontend assets..."
docker compose run --rm --no-deps app \
  sh -c "npm ci --silent && npm run build && rm -rf node_modules"

echo "» Starting services..."
docker compose up -d

echo "» Waiting for database to be ready..."
until docker compose exec -T postgres pg_isready -U invoicekit; do
  sleep 2
done

echo "» Running migrations..."
docker compose exec -T app php artisan migrate --force

echo "» Building caches..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache

echo "» Creating storage symlink..."
docker compose exec -T app php artisan storage:link || true

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                   Setup complete!                            ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "  InvoiceKit is running at: ${APP_URL}"
echo ""
echo "  Next — add these secrets to your GitHub repository"
echo "  (Settings → Secrets and variables → Actions → New secret):"
echo ""
echo "    DEPLOY_HOST      →  204.168.164.127"
echo "    DEPLOY_SSH_KEY   →  (paste your SSH private key)"
echo "    GITHUB_PAT       →  ${GITHUB_PAT:0:8}...  (the token you entered above)"
echo ""
echo "  Then create a 'production' environment under:"
echo "  Settings → Environments → New environment"
echo ""
echo "  Push to main (or trigger via workflow_dispatch) to deploy."
echo ""
