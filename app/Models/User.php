<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'plan',
        'stripe_customer_id',
        'stripe_connect_id',
        'stripe_connect_onboarded',
        'stripe_subscription_id',
        'subscription_status',
        'trial_ends_at',
        'subscribed_until',
        'onboarding_completed',
        'current_company_id',
        'display_name',
        'tagline',
        'website',
        'phone',
        'locale',
        'reminder_before_due_days',
        'reminder_on_due_day',
        'reminder_overdue_intervals',
        'gemini_api_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'stripe_connect_onboarded' => 'boolean',
            'reminder_on_due_day' => 'boolean',
            'reminder_overdue_intervals' => 'array',
            'trial_ends_at' => 'datetime',
            'subscribed_until' => 'datetime',
            'gemini_api_key' => 'encrypted',
        ];
    }

    public function hasStripeConnect(): bool
    {
        return $this->stripe_connect_onboarded === true && $this->stripe_connect_id !== null;
    }

    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            || ($this->subscribed_until !== null && $this->subscribed_until->isFuture());
    }

    public function isStarter(): bool
    {
        return in_array($this->plan, ['starter', 'pro']);
    }

    public function isFree(): bool
    {
        return $this->plan === 'free';
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function documentImports(): HasMany
    {
        return $this->hasMany(DocumentImport::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }
}
