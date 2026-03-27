# InvoiceKit — Deployment Guide

Production and staging deployment on DigitalOcean Kubernetes (DOKS) with DigitalOcean Managed MySQL and in-cluster Redis.

---

## Architecture

```
GitHub (main)    ──► GitHub Actions ──► DOCR ──► DOKS production namespace
GitHub (staging) ──► GitHub Actions ──► DOCR ──► DOKS staging namespace

DOKS Cluster (fra1, 2× s-2vcpu-4gb)
├── production namespace
│   ├── app           (PHP-FPM + Nginx sidecar, 2 replicas)
│   ├── queue-worker  (php artisan queue:work, 1 replica)
│   └── redis         (in-cluster, single pod)
└── staging namespace
    ├── app           (PHP-FPM + Nginx sidecar, 1 replica)
    ├── queue-worker  (1 replica)
    └── redis         (in-cluster, single pod)

DigitalOcean Managed MySQL (fra1, db-s-1vcpu-1gb)
├── invoicekit          — production central DB
├── invoicekit_staging  — staging central DB
└── tenant{uuid}        — per-tenant DBs created dynamically by stancl/tenancy

DigitalOcean Container Registry (DOCR)
└── registry.digitalocean.com/invoicekit-registry/app
    — tagged :latest, :staging-latest, :{git-sha}

Ingress (ingress-nginx + cert-manager + Let's Encrypt via DO DNS-01)
├── invoicekit.eu          → production app
├── *.invoicekit.eu        → production app  (tenant subdomains)
├── staging.invoicekit.eu  → staging app
└── *.staging.invoicekit.eu → staging app   (staging tenant subdomains)
```

---

## Prerequisites

### Local tools

```bash
brew install doctl kubectl helm
doctl auth init        # enter your DO Personal Access Token
```

### Required before starting

- A DigitalOcean account with billing enabled
- Domain `invoicekit.eu` delegated to DigitalOcean nameservers (`ns1.digitalocean.com` etc.)
- A DO Personal Access Token with full read/write scope

---

## Required Code Changes

These changes must be made to the application before deploying.

### 1. `Dockerfile` — switch to pdo_mysql

Replace the `postgresql-dev` system package and `pdo_pgsql` PHP extension:

```dockerfile
# Remove:
    postgresql-dev \

# Remove from docker-php-ext-install:
    pdo_pgsql \

# Add instead:
    pdo_mysql \
```

The full changed blocks:

```dockerfile
RUN apk add --no-cache \
    autoconf \
    bash \
    curl \
    g++ \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    make \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    nodejs \
    npm \
    supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo \
    pdo_mysql \
    xml \
    zip
```

### 2. `config/database.php` — MySQL SSL options

Add SSL options to the `mysql` connection so the app verifies the DO CA certificate:

```php
'mysql' => [
    'driver'      => 'mysql',
    'url'         => env('DB_URL'),
    'host'        => env('DB_HOST', '127.0.0.1'),
    'port'        => env('DB_PORT', '3306'),
    'database'    => env('DB_DATABASE', 'laravel'),
    'username'    => env('DB_USERNAME', 'root'),
    'password'    => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset'     => env('DB_CHARSET', 'utf8mb4'),
    'collation'   => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix'      => '',
    'prefix_indexes' => true,
    'strict'      => true,
    'engine'      => null,
    'options'     => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA             => env('MYSQL_ATTR_SSL_CA'),
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ]) : [],
],
```

### 3. `bootstrap/app.php` — trust the ingress proxy

Add this so Laravel generates `https://` URLs behind the ingress load balancer:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
    // ... existing middleware ...
})
```

---

## Step 1 — DigitalOcean Infrastructure

### 1.1 Kubernetes cluster

```bash
doctl kubernetes cluster create invoicekit \
  --region fra1 \
  --node-pool "name=main;size=s-2vcpu-4gb;count=2" \
  --version latest
```

> 2× `s-2vcpu-4gb` nodes shared across both namespaces ≈ $48/month.  
> Scale the pool to 3 nodes when production traffic warrants it.

Save kubectl credentials:

```bash
doctl kubernetes cluster kubeconfig save invoicekit
kubectl get nodes   # should show 2 Ready nodes
```

### 1.2 Managed MySQL cluster

```bash
doctl databases create invoicekit-mysql \
  --engine mysql \
  --version 8 \
  --region fra1 \
  --size db-s-1vcpu-1gb \
  --num-nodes 1
```

> `db-s-1vcpu-1gb` ≈ $15/month. Upgrade to `db-s-2vcpu-4gb` for production HA.

Wait until status is `online`, then note the cluster ID:

```bash
doctl databases list
```

**Restrict network access to the k8s cluster only:**

```bash
CLUSTER_UUID=$(doctl kubernetes cluster get invoicekit --format ID --no-header)
doctl databases firewalls append <DB_CLUSTER_ID> \
  --rule "k8s:${CLUSTER_UUID}"
```

**Create databases and users:**

Connect via the DO control panel (Databases → your cluster → Connection Details → Open with → MySQL) or the connection string shown by:

```bash
doctl databases connection <DB_CLUSTER_ID>
```

Then run:

```sql
-- Central databases
CREATE DATABASE invoicekit         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE invoicekit_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Production user
-- Needs full access to the central DB + CREATE/DROP on tenant-prefixed DBs
-- (stancl/tenancy creates databases named tenant{uuid})
CREATE USER 'invoicekit'@'%' IDENTIFIED BY '<strong-prod-password>';
GRANT ALL PRIVILEGES ON `invoicekit`.* TO 'invoicekit'@'%';
GRANT CREATE, DROP, ALTER, INDEX ON `tenant%`.* TO 'invoicekit'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `tenant%`.* TO 'invoicekit'@'%';

-- Staging user (tenant DBs prefixed tenantstaging to avoid collision)
CREATE USER 'invoicekit_staging'@'%' IDENTIFIED BY '<strong-staging-password>';
GRANT ALL PRIVILEGES ON `invoicekit_staging`.* TO 'invoicekit_staging'@'%';
GRANT CREATE, DROP, ALTER, INDEX ON `tenantstaging%`.* TO 'invoicekit_staging'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON `tenantstaging%`.* TO 'invoicekit_staging'@'%';

FLUSH PRIVILEGES;
```

> **Note:** For the staging environment, configure `tenancy.database.prefix = 'tenantstaging'` in `config/tenancy.php` (or via env var if you add one), to keep tenant databases isolated from production on the same MySQL server.

**Download the CA certificate:**

Go to: DO Control Panel → Databases → invoicekit-mysql → Connection Details → Download CA certificate.

Save it as `mysql-ca.crt` locally (you'll need this in Step 4).

### 1.3 Container Registry

```bash
doctl registry create invoicekit-registry --region fra1
```

Grant the k8s cluster pull access:

```bash
doctl registry kubernetes-manifest | kubectl apply -f -
```

> This creates a `registry-invoicekit-registry` pull secret in the `default` namespace. You will re-run this per-namespace in Step 2.

---

## Step 2 — Kubernetes Namespaces

```bash
kubectl apply -f k8s/production/namespace.yaml
kubectl apply -f k8s/staging/namespace.yaml
```

Attach the registry pull secret to each namespace:

```bash
for NS in production staging; do
  doctl registry kubernetes-manifest --namespace "$NS" | kubectl apply -f -
done
```

---

## Step 3 — Ingress & TLS

### 3.1 Install ingress-nginx

```bash
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update

helm install ingress-nginx ingress-nginx/ingress-nginx \
  --namespace ingress-nginx \
  --create-namespace \
  --set controller.service.type=LoadBalancer \
  --set controller.service.annotations."service\.beta\.kubernetes\.io/do-loadbalancer-name"=invoicekit-lb \
  --set controller.service.annotations."service\.beta\.kubernetes\.io/do-loadbalancer-region"=fra1
```

Get the Load Balancer IP (takes 1–2 minutes to provision):

```bash
kubectl get svc -n ingress-nginx ingress-nginx-controller \
  --watch
```

Note the `EXTERNAL-IP` — you will use it in DNS.

### 3.2 DNS records

In the DigitalOcean DNS panel for `invoicekit.eu`, add:

| Type | Name       | Value      |
|------|------------|------------|
| A    | `@`        | `<LB_IP>`  |
| A    | `*`        | `<LB_IP>`  |
| A    | `staging`  | `<LB_IP>`  |
| A    | `*.staging`| `<LB_IP>`  |

Wildcard DNS (`*` and `*.staging`) is required for tenant subdomain routing.

### 3.3 Install cert-manager (Let's Encrypt wildcard)

Wildcard certificates require DNS-01 challenge, which cert-manager handles via the DigitalOcean DNS API.

```bash
helm repo add cert-manager https://charts.jetstack.io
helm repo update

helm install cert-manager cert-manager/cert-manager \
  --namespace cert-manager \
  --create-namespace \
  --set crds.enabled=true
```

Create the DNS API token secret for cert-manager:

```bash
kubectl create secret generic do-dns-token \
  --namespace cert-manager \
  --from-literal=access-token=<YOUR_DO_PERSONAL_ACCESS_TOKEN>
```

Apply the ClusterIssuers:

```bash
kubectl apply -f k8s/cluster-issuer.yaml
```

Verify they become `Ready`:

```bash
kubectl get clusterissuers
```

---

## Step 4 — MySQL CA Certificate Secret

Store the CA certificate (downloaded in Step 1.2) as a k8s secret in both namespaces:

```bash
kubectl create secret generic mysql-ca \
  --namespace production \
  --from-file=ca-certificate.crt=./mysql-ca.crt

kubectl create secret generic mysql-ca \
  --namespace staging \
  --from-file=ca-certificate.crt=./mysql-ca.crt
```

---

## Step 5 — Apply Kubernetes Manifests

```bash
kubectl apply -f k8s/production/
kubectl apply -f k8s/staging/
```

Verify everything starts:

```bash
kubectl get pods -n production
kubectl get pods -n staging
kubectl get certificates -n production   # cert-manager TLS status
kubectl get certificates -n staging
```

Allow 2–5 minutes for Let's Encrypt to issue the wildcard certificates via DNS-01 challenge.

---

## Step 6 — Application Secrets (Initial Setup)

Secrets are created/updated automatically by GitHub Actions on every deploy. For the **first manual setup** before CI is configured, run the commands below.

### Production secrets

```bash
kubectl create secret generic app-secrets \
  --namespace production \
  --from-literal=APP_KEY="base64:$(openssl rand -base64 32)" \
  --from-literal=APP_ENV=production \
  --from-literal=APP_DEBUG=false \
  --from-literal=APP_URL=https://invoicekit.eu \
  --from-literal=APP_DOMAIN=invoicekit.eu \
  --from-literal=DB_CONNECTION=mysql \
  --from-literal=DB_HOST="<DO_MYSQL_PRIVATE_HOST>" \
  --from-literal=DB_PORT=25060 \
  --from-literal=DB_DATABASE=invoicekit \
  --from-literal=DB_USERNAME=invoicekit \
  --from-literal=DB_PASSWORD="<prod-password>" \
  --from-literal=MYSQL_ATTR_SSL_CA=/etc/ssl/mysql/ca-certificate.crt \
  --from-literal=REDIS_HOST=redis \
  --from-literal=REDIS_PORT=6379 \
  --from-literal=SESSION_DRIVER=redis \
  --from-literal=CACHE_DRIVER=redis \
  --from-literal=QUEUE_CONNECTION=redis \
  --from-literal=LOG_CHANNEL=stderr \
  --from-literal=STRIPE_KEY="pk_live_..." \
  --from-literal=STRIPE_SECRET="sk_live_..." \
  --from-literal=MAIL_MAILER=smtp \
  --from-literal=MAIL_HOST="smtp.example.com" \
  --from-literal=MAIL_PORT=587 \
  --from-literal=MAIL_USERNAME="..." \
  --from-literal=MAIL_PASSWORD="..." \
  --from-literal=MAIL_FROM_ADDRESS="hello@invoicekit.eu" \
  --from-literal=MAIL_FROM_NAME="InvoiceKit" \
  --dry-run=client -o yaml | kubectl apply -f -
```

### Staging secrets

Same command with `--namespace staging` and staging-specific values:
- `APP_URL=https://staging.invoicekit.eu`
- `APP_DOMAIN=staging.invoicekit.eu`
- `DB_DATABASE=invoicekit_staging`, `DB_USERNAME=invoicekit_staging`
- Stripe **test** keys (`pk_test_...`, `sk_test_...`)

> The `--dry-run=client -o yaml | kubectl apply -f -` pattern is idempotent — safe to run on every deploy to update secrets without recreating them.

---

## Step 7 — First Deployment (Initial Migrations)

After the first `docker build` and `docker push` (or once GitHub Actions runs for the first time), run initial migrations manually:

```bash
# Production
kubectl create job migrate-init \
  --namespace production \
  --image=registry.digitalocean.com/invoicekit-registry/app:latest \
  --restart=Never \
  -- sh -c "php artisan migrate --force"

kubectl wait job/migrate-init \
  --for=condition=complete \
  --timeout=300s \
  --namespace production

kubectl logs job/migrate-init -n production
kubectl delete job migrate-init -n production

# Staging (same, --namespace staging)
```

> Subsequent deployments run migrations automatically via GitHub Actions — no manual step needed.

---

## Step 8 — GitHub Actions (Auto Deploy)

### Required GitHub Secrets

Go to **Settings → Secrets and variables → Actions** in the repository and add:

#### Shared

| Secret | Value |
|--------|-------|
| `DIGITALOCEAN_ACCESS_TOKEN` | DO Personal Access Token |
| `K8S_CLUSTER_NAME` | `invoicekit` |

#### Production

| Secret | Value |
|--------|-------|
| `PROD_APP_KEY` | Output of `php artisan key:generate --show` |
| `PROD_DB_HOST` | DO MySQL private hostname (from connection details) |
| `PROD_DB_DATABASE` | `invoicekit` |
| `PROD_DB_USERNAME` | `invoicekit` |
| `PROD_DB_PASSWORD` | Production DB password |
| `PROD_STRIPE_KEY` | Stripe live publishable key (`pk_live_...`) |
| `PROD_STRIPE_SECRET` | Stripe live secret key (`sk_live_...`) |
| `PROD_MAIL_HOST` | SMTP host |
| `PROD_MAIL_PORT` | SMTP port |
| `PROD_MAIL_USERNAME` | SMTP username |
| `PROD_MAIL_PASSWORD` | SMTP password |
| `PROD_MAIL_FROM_ADDRESS` | `hello@invoicekit.eu` |

#### Staging

| Secret | Value |
|--------|-------|
| `STAGING_APP_KEY` | Separate key for staging |
| `STAGING_DB_HOST` | Same MySQL host |
| `STAGING_DB_DATABASE` | `invoicekit_staging` |
| `STAGING_DB_USERNAME` | `invoicekit_staging` |
| `STAGING_DB_PASSWORD` | Staging DB password |
| `STAGING_STRIPE_KEY` | Stripe test key (`pk_test_...`) |
| `STAGING_STRIPE_SECRET` | Stripe test secret (`sk_test_...`) |
| `STAGING_MAIL_HOST` | Staging SMTP host |
| `STAGING_MAIL_PORT` | Staging SMTP port |
| `STAGING_MAIL_USERNAME` | Staging SMTP username |
| `STAGING_MAIL_PASSWORD` | Staging SMTP password |
| `STAGING_MAIL_FROM_ADDRESS` | `hello@staging.invoicekit.eu` |

### Deploy triggers

| Branch    | Workflow                                  | Environment |
|-----------|-------------------------------------------|-------------|
| `main`    | `.github/workflows/deploy.yml`            | production  |
| `staging` | `.github/workflows/deploy-staging.yml`    | staging     |

Both workflows are also manually triggerable via `workflow_dispatch`.

### What each workflow does

1. **Build** — `docker build`, push `:{sha}` and `:latest` (or `:staging-latest`) to DOCR
2. **Secrets** — `kubectl create secret ... --dry-run=client -o yaml | kubectl apply -f -` (idempotent)
3. **Manifests** — `kubectl apply -f k8s/{env}/`
4. **Image update** — `kubectl set image` to pin the exact `:{sha}` tag on both `app` and `queue-worker`
5. **Migrations** — one-off Job using the new image; waits for completion, logs output, then deletes itself
6. **Rollout** — waits for `deployment/app` and `deployment/queue-worker` to finish rolling out

---

## Operations

### View logs

```bash
# App (stderr)
kubectl logs -n production deployment/app -c app --tail=100 -f

# Nginx access logs
kubectl logs -n production deployment/app -c nginx --tail=50 -f

# Queue worker
kubectl logs -n production deployment/queue-worker --tail=100 -f
```

### Run artisan commands

```bash
kubectl exec -it -n production deployment/app -c app -- php artisan tinker
kubectl exec -it -n production deployment/app -c app -- php artisan tenants:list
kubectl exec -it -n production deployment/app -c app -- php artisan queue:restart
```

### Force a redeployment (without a code change)

```bash
kubectl rollout restart deployment/app -n production
kubectl rollout restart deployment/queue-worker -n production
```

### Scale production app

```bash
kubectl scale deployment/app --replicas=3 -n production
```

### Check rollout status

```bash
kubectl rollout status deployment/app -n production
```

### Update a secret value without redeploying

```bash
kubectl patch secret app-secrets -n production \
  --type='json' \
  -p='[{"op":"replace","path":"/data/STRIPE_SECRET","value":"'$(echo -n "sk_live_new" | base64)'"}]'

kubectl rollout restart deployment/app -n production
```

### Connect to MySQL for maintenance

```bash
doctl databases connection <DB_CLUSTER_ID>
```

### Tail application errors

```bash
kubectl logs -n production deployment/app -c app --since=1h | grep -i "error\|exception\|fatal"
```

---

## Cost Summary (fra1, approximate)

| Resource | Spec | Cost/month |
|----------|------|-----------|
| DOKS node pool | 2× `s-2vcpu-4gb` | $48 |
| Managed MySQL | `db-s-1vcpu-1gb` | $15 |
| Load Balancer | Standard | $12 |
| Container Registry | Starter (5 GB) | $5 |
| **Total** | | **~$80/month** |

> Staging runs in the same cluster at no additional infrastructure cost.
