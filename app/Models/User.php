<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements HasMedia, CanResetPassword
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia, HasApiTokens, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id', // Agregamos company_id a fillable
        'email_verified_at',
        'language',
        'time_zone',
        'date_format',
        'time_format',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'company_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the company that owns the user.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * Get the currently selected company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if user belongs to a company
     */
    public function hasCompany(): bool
    {
        return !is_null($this->company_id);
    }

    /**
     * Set the current company for the user
     */
    public function setCurrentCompany(int $companyId): bool
    {
        // Verify the user has access to this company
        if (!$this->companies()->where('companies.id', $companyId)->exists()) {
            return false;
        }

        $this->update(['company_id' => $companyId]);
        return true;
    }

    /**
     * Get the current company or first available company
     */
    public function getCurrentCompany(): ?Company
    {
        // If user has a company_id set, return that company
        if ($this->company_id) {
            return $this->company;
        }

        // Otherwise, return the first available company
        return $this->companies()->first();
    }

    /**
     * Get the current company ID or first available company ID
     */
    public function getCurrentCompanyId(): ?int
    {
        if ($this->company_id) {
            return $this->company_id;
        }

        $firstCompany = $this->companies()->first();
        return $firstCompany ? $firstCompany->id : null;
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function frequencies()
    {
        return $this->hasMany(UserFrequency::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function setNotificationPreference($type, $channel, $enabled, $frequency = 'immediate')
    {
        $notificationType = NotificationType::where('key', $type)->firstOrFail();

        return $this->notificationPreferences()->updateOrCreate(
            ['notification_type_id' => $notificationType->id],
            [
                $channel . '_enabled' => $enabled,
                'frequency' => $frequency
            ]
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile-photo')
            ->singleFile(); // Esto asegura que solo haya una imagen de perfil a la vez
    }

    /**
     * Primera palabra del nombre completo (menú de navegación, saludos, etc.).
     */
    public function firstName(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '';
        }
        $parts = preg_split('/\s+/u', $name);

        return $parts[0] ?? '';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
