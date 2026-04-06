#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/opt/invoicekit"

echo "==> Pulling latest code..."
git -C "$APP_DIR" pull origin main

echo "==> Installing Node dependencies and building assets..."
npm --prefix "$APP_DIR" ci
npm --prefix "$APP_DIR" run build

echo "==> Building Docker image..."
docker compose -f "$APP_DIR/docker-compose.yml" build app

echo "==> Restarting containers..."
docker compose -f "$APP_DIR/docker-compose.yml" up -d

echo "==> Running migrations..."
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan migrate --force

echo "==> Clearing caches..."
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan config:cache
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan route:cache
docker compose -f "$APP_DIR/docker-compose.yml" exec -T app php artisan view:cache

echo "==> Deploy complete!"
docker compose -f "$APP_DIR/docker-compose.yml" ps
