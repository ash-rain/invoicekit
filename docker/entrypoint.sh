#!/usr/bin/env bash
set -e

BOOTSTRAP_FLAG="/var/www/storage/app/.bootstrapped"

# ---------------------------------------------------------------------------
# Wait for PostgreSQL to accept connections
# ---------------------------------------------------------------------------
echo "[entrypoint] Waiting for PostgreSQL..."
until php -r "
try {
    new PDO(
        sprintf('pgsql:host=%s;port=%s;dbname=%s',
            getenv('DB_HOST')     ?: 'postgres',
            getenv('DB_PORT')     ?: '5432',
            getenv('DB_DATABASE') ?: 'invoicekit'),
        getenv('DB_USERNAME') ?: 'invoicekit',
        getenv('DB_PASSWORD') ?: 'secret'
    );
    exit(0);
} catch (Exception \$e) { exit(1); }
" 2>/dev/null; do
    sleep 2
done
echo "[entrypoint] PostgreSQL is ready."

# ---------------------------------------------------------------------------
# Clear and rebuild all caches — ensures fresh routes/config/views on every
# deploy without needing manual artisan calls on the server
# ---------------------------------------------------------------------------
echo "[entrypoint] Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "[entrypoint] Caches rebuilt."

# ---------------------------------------------------------------------------
# Migrations — always run (idempotent, picks up new migrations on redeploy)
# ---------------------------------------------------------------------------
php artisan migrate --force

# ---------------------------------------------------------------------------
# First-time bootstrap — guarded by a flag file in storage/app/
# ---------------------------------------------------------------------------
if [ ! -f "$BOOTSTRAP_FLAG" ]; then
    echo "[entrypoint] Running first-time bootstrap..."

    # Generate APP_KEY if the .env does not already contain one
    if ! grep -q "^APP_KEY=base64:" /var/www/.env 2>/dev/null; then
        echo "[entrypoint] Generating application key..."
        php artisan key:generate --force
    fi

    # Seed the database
    echo "[entrypoint] Seeding database..."
    php artisan db:seed --force

    # Generate VAPID keys for web push (writes keys back to .env)
    if ! grep -q "^VAPID_PUBLIC_KEY=.\+" /var/www/.env 2>/dev/null; then
        echo "[entrypoint] Generating VAPID keys..."
        php artisan webpush:vapid || true
    fi

    # Initialise MinIO bucket (best-effort — MinIO may take a moment to start)
    echo "[entrypoint] Initialising MinIO bucket..."
    php artisan storage:minio-init || true

    touch "$BOOTSTRAP_FLAG"
    echo "[entrypoint] First-time bootstrap complete."
fi

exec "$@"
