<?php

namespace App\Models;

use Illuminate\Bus\Queueable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'avatar',
        'is_premium',
        'referral_code',
        'referrer_id',
        'pin_code',
        'is_pin_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'pin_code',
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
            'is_premium' => 'boolean',
            'is_pin_enabled' => 'boolean',
            'pin_code' => 'hashed',
            'password' => 'hashed',
            'banned_at' => 'datetime',
        ];
    }

    /**
     * Social accounts associated with user
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * User's wallet
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Users referred by this user
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /**
     * User who referred this user
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Referral earnings
     */
    public function referralEarnings(): HasMany
    {
        return $this->hasMany(ReferralEarning::class);
    }

    /**
     * Game rooms user is in
     */
    public function gameRooms(): BelongsToMany
    {
        return $this->belongsToMany(GameRoom::class, 'game_room_players')
            ->withTimestamps();
    }

    /**
     * Tournaments user is in
     */
    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_players')
            ->withTimestamps();
    }

    /**
     * Check if user has password authentication
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * Check if user has PIN code set
     */
    public function hasPin(): bool
    {
        return !empty($this->pin_code);
    }

    /**
     * Check if PIN verification is required
     */
    public function requiresPinVerification(): bool
    {
        return $this->is_pin_enabled && $this->hasPin();
    }

    /**
     * Verify PIN code
     */
    public function verifyPin(?string $pin): bool
    {
        if (!$this->requiresPinVerification()) {
            return true;
        }

        if (!$pin) {
            return false;
        }

        return Hash::check($pin, $this->pin_code);
    }

    /**
     * Send password reset notification
     */
    public function sendPasswordResetNotification($token): void
    {
        // Create a custom notification class inline
        $notification = new class($token) extends Notification {
            use Queueable;

            public string $token;

            public function __construct(string $token)
            {
                $this->token = $token;
            }

            public function via($notifiable): array
            {
                return ['mail'];
            }

            public function toMail($notifiable): MailMessage
            {
                // Вручную формируем URL, используя наш FRONTEND_URL
                $url = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/')
                    . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

                return (new MailMessage)
                    ->subject('Password Reset Request')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', $url)
                    ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
                    ->line('If you did not request a password reset, no further action is required.');
            }
        };

        $this->notify($notification);
    }

    /**
     * Get display name (username or first part of email)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username ?? $this->display_name ?? strstr($this->email, '@', true);
    }


    public function getTotalReferralEarningsAttribute(): float
    {
        return (float) $this->referralEarnings()->sum('amount');
    }

    /**
     * Get active referrals count (those who earned something)
     */
    public function getActiveReferralsCountAttribute(): int
    {
        return $this->referralEarnings()
            ->distinct('referral_id')
            ->count('referral_id');
    }

    /**
     * Get referral conversion rate
     */
    public function getReferralConversionRateAttribute(): float
    {
        $totalReferrals = $this->referrals()->count();
        $activeReferrals = $this->active_referrals_count;

        if ($totalReferrals === 0) {
            return 0.0;
        }

        return round(($activeReferrals / $totalReferrals) * 100, 2);
    }

    /**
     * Scope for premium users
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for non-banned users
     */
    public function scopeActive($query)
    {
        return $query->whereNull('banned_at');
    }

    /**
     * Scope for banned users
     */
    public function scopeBanned($query)
    {
        return $query->whereNotNull('banned_at');
    }

    /**
     * Scope for users with referrals
     */
    public function scopeWithReferrals($query)
    {
        return $query->has('referrals');
    }

    /**
     * Scope for users created within a date range
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Set referrer ID with circular reference protection
     */
    public function setReferrerIdAttribute($value)
    {
        if ($value === $this->id) {
            throw new \InvalidArgumentException('Cannot refer yourself');
        }
        
        if ($value && $this->wouldCreateCircularReference($value)) {
            throw new \InvalidArgumentException('Circular referral detected');
        }
        
        $this->attributes['referrer_id'] = $value;
    }

    /**
     * Check if setting this referrer would create a circular reference
     */
    private function wouldCreateCircularReference($referrerId, $depth = 0)
    {
        if ($depth > 10) {
            return true;
        }
        
        $referrer = self::find($referrerId);
        
        if (!$referrer || !$referrer->referrer_id) {
            return false;
        }
        
        if ($referrer->referrer_id === $this->id) {
            return true;
        }
        
        return $this->wouldCreateCircularReference($referrer->referrer_id, $depth + 1);
    }
}