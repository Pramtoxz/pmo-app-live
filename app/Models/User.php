<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'pgsql';
    public ?PersonalAccessToken $accessToken = null;

    protected $fillable = [
        'name',
        'email',
        'password',
        'fk_toko',
        'role',
        'fcm_token',
        'collection_pin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'collection_pin',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shop::class, 'fk_toko', 'kd_toko');
    }

    public function carts()
    {
        return $this->hasMany(\App\Models\Cart::class);
    }

    public function activeCart()
    {
        return $this->hasOne(\App\Models\Cart::class)->where('status', 'active');
    }

    public function createToken(string $name)
    {
        $token = bin2hex(random_bytes(32));

        $accessToken = PersonalAccessToken::create([
            'tokenable_type' => self::class,
            'tokenable_id' => $this->id,
            'name' => $name,
            'token' => hash('sha256', $token),
            'abilities' => ['*'],
        ]);

        return (object) [
            'accessToken' => $accessToken,
            'plainTextToken' => $accessToken->id . '|' . $token,
        ];
    }

    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function currentAccessToken()
    {
        return $this->accessToken ?? null;
    }
}
