# Stripe Setup Walkthrough

This guide covers every step needed to wire up Stripe for InvoiceKit — subscription billing (Starter / Pro plans), the customer portal, payment links on individual invoices, and webhook handling.

---

## 1. Create a Stripe Account & Get API Keys

1. Sign up at [dashboard.stripe.com](https://dashboard.stripe.com).
2. In the top-left selector, make sure you are in **Test mode** while setting up locally.
3. Go to **Developers → API keys**.
4. Copy the two keys you need:
   - **Publishable key** — starts with `pk_test_`
   - **Secret key** — starts with `sk_test_`

---

## 2. Create Products & Prices

InvoiceKit has two paid plans: **Starter** ($9/month) and **Pro** ($29/month).

1. In the Stripe dashboard go to **Product catalogue → Add product**.
2. Create the **Starter** product:
   - Name: `InvoiceKit Starter`
   - Pricing: **Recurring**, $9.00 USD / month
   - Copy the generated **Price ID** (starts with `price_`).
3. Create the **Pro** product:
   - Name: `InvoiceKit Pro`
   - Pricing: **Recurring**, $29.00 USD / month
   - Copy the generated **Price ID**.

---

## 3. Configure Environment Variables

Add the following to your `.env` file:

```env
# Stripe
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...        # filled in step 4
STRIPE_STARTER_PRICE_ID=price_...     # from step 2
STRIPE_PRO_PRICE_ID=price_...         # from step 2
```

These map directly to `config/services.php` under the `stripe` key.

---

## 4. Set Up the Webhook Endpoint

The webhook route is already registered at `POST /billing/webhook` and is excluded from CSRF verification in `bootstrap/app.php`.

### Local development — Stripe CLI

Install the [Stripe CLI](https://stripe.com/docs/stripe-cli) and log in:

```bash
stripe login
```

Forward events to your local dev server:

```bash
stripe listen --forward-to http://localhost:8000/billing/webhook
```

The CLI will print a **webhook signing secret** that looks like `whsec_...`. Copy it into `STRIPE_WEBHOOK_SECRET` in your `.env`.

> The app validates the `Stripe-Signature` header using this secret. If `STRIPE_WEBHOOK_SECRET` is not set, signature verification is skipped (useful for debugging, **not safe for production**).

### Production

1. In the Stripe dashboard go to **Developers → Webhooks → Add endpoint**.
2. Set the endpoint URL to `https://yourdomain.com/billing/webhook`.
3. Select the following events to listen to:
   - `checkout.session.completed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_failed`
4. After saving, reveal the **Signing secret** and copy it into `STRIPE_WEBHOOK_SECRET` on your server.

---

## 5. Enable the Customer Billing Portal

Before the "Manage Billing" button works, you need to enable the portal in Stripe:

1. Go to **Settings → Billing → Customer portal**.
2. Enable it and configure what customers can self-serve (cancel subscriptions, update payment method, view invoices).
3. Save.

No code changes are needed — `BillingController::portal()` calls `billingPortal->sessions->create()` which redirects to this hosted portal.

---

## 6. How the Billing Flows Work

### Subscription Checkout

A user on the **Billing** page clicks **Upgrade to Starter** or **Upgrade to Pro**:

1. The browser posts to `POST /billing/checkout/{plan}`.
2. `BillingController::checkout()`:
   - Creates a Stripe Customer if the user doesn't have a `stripe_customer_id` yet, then saves it to the `users` table.
   - Creates a **Checkout Session** in `subscription` mode using the matching `STRIPE_STARTER_PRICE_ID` or `STRIPE_PRO_PRICE_ID`.
   - Redirects the user to Stripe-hosted checkout.
3. After payment, Stripe redirects to `/billing?checkout=success`.
4. Stripe also fires `checkout.session.completed` to the webhook, which sets `subscription_status = active`, `plan = pro/starter`, and saves the `stripe_subscription_id`.

### Plan Cancellation

A user clicks **Cancel Subscription** on the billing page:

1. Posts to `POST /billing/cancel` with `cancel_at_period_end=1` (schedule end of period) or `cancel_at_period_end=0` (immediate).
2. `BillingController::cancel()` calls the Stripe subscriptions API accordingly.
3. For immediate cancellation the user's `plan` is set to `free` right away. For end-of-period cancellation, the subscription is flagged but the plan remains active until the period expires (handled by the `customer.subscription.deleted` webhook).

### Customer Portal

The **Manage Billing** button (shown when a user has a `stripe_customer_id`) posts to `POST /billing/portal`, which redirects the user to Stripe's self-serve portal where they can update their payment method, download invoices, or cancel.

### Stripe Connect & Payment Methods

When a user completes Stripe Connect onboarding, a **Stripe payment method** is automatically created in their company's payment methods list. This allows Stripe to appear as a selectable payment method on invoices. When Stripe is disconnected, the payment method is automatically removed (and if it was the default, the next available method is promoted).

### Payment Links (Invoice-level)

Individual invoices can have a Stripe Payment Link attached:

1. From the invoice list, click **Create Payment Link** on any invoice.
2. Posts to `POST /invoices/{invoice}/payment-link`.
3. `BillingController::createPaymentLink()` creates a one-time Stripe Price and Payment Link, then saves the URL to `invoices.stripe_payment_link_url`.
4. The link can be shared directly with the client.

---

## 7. Webhook Event Handling

`StripeWebhookController` handles the four subscribed events:

| Event | Handler | Effect |
|---|---|---|
| `checkout.session.completed` | `handleCheckoutCompleted()` | Sets `subscription_status = active`, saves `stripe_subscription_id`, upgrades `plan` |
| `customer.subscription.updated` | `handleSubscriptionUpdated()` | Syncs `subscription_status`, updates `subscribed_until` (period end timestamp) |
| `customer.subscription.deleted` | `handleSubscriptionDeleted()` | Downgrades `plan` to `free`, clears `stripe_subscription_id` and `subscribed_until` |
| `invoice.payment_failed` | `handlePaymentFailed()` | Sets `subscription_status = past_due`, sends `PaymentFailedNotification` email |

All events are matched against a Stripe Customer ID → User lookup via `stripe_customer_id`.

---

## 8. Database Fields

The following `users` table columns are involved:

| Column | Purpose |
|---|---|
| `stripe_customer_id` | Stripe Customer object ID (`cus_...`) |
| `stripe_subscription_id` | Active subscription ID (`sub_...`) |
| `subscription_status` | Mirrors Stripe status: `active`, `past_due`, `canceled`, etc. |
| `subscribed_until` | Timestamp of current period end |
| `plan` | Application-level plan: `free`, `starter`, `pro` |

These are added by migrations `2026_03_09_100000` and `2026_03_28_131030`.

---

## 9. Testing Locally

Use Stripe's test card numbers — no real charges are made in test mode:

| Scenario | Card Number |
|---|---|
| Successful payment | `4242 4242 4242 4242` |
| Payment requires authentication (3DS) | `4000 0025 0000 3155` |
| Card declined | `4000 0000 0000 0002` |
| Insufficient funds | `4000 0000 0000 9995` |

Use any future expiry date, any 3-digit CVC, and any ZIP.

To manually trigger a webhook event during development:

```bash
stripe trigger checkout.session.completed
stripe trigger customer.subscription.updated
stripe trigger invoice.payment_failed
```

---

## 10. Production Checklist

- [ ] Switch Stripe dashboard to **Live mode** and generate live API keys (`sk_live_`, `pk_live_`).
- [ ] Create live Products & Prices and update `STRIPE_PRO_PRICE_ID` / `STRIPE_STARTER_PRICE_ID`.
- [ ] Register the production webhook endpoint in the Stripe dashboard and set `STRIPE_WEBHOOK_SECRET`.
- [ ] Ensure `STRIPE_WEBHOOK_SECRET` is set — signature verification is always enforced in production.
- [ ] Enable the Customer Portal in Stripe live mode settings.
- [ ] Verify the billing route is accessible at `https://yourdomain.com/billing/webhook` (POST, no auth).
- [ ] Run `php artisan config:cache` after updating `.env` on the server.
