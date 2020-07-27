<?php

namespace Walsh\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Walsh\Notifications\ResetPassword;

/**
 * @property int $id
 * @property string $email
 * @property string $google_id
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isFromSocialite(): bool
    {
        return ! is_null($this->google_id);
    }

    public function scopeFromSocialite(Builder $query, bool $fromSocialite = true)
    {
        $query->whereNull('google_id', 'and', $fromSocialite);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}
