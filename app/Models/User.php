<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'pgsql_nms';

    protected $fillable = [
        'name',
        'email',
        'password',
        'kd_kariawan',
        'username',
        'level',
        'no_hp',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function flp()
    {
        return $this->hasOne(Flp::class, 'id_flp', 'kd_kariawan');
    }

    public function createToken($name)
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
