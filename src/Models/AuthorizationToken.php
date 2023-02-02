<?php

namespace App\Sevenrooms\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AuthorizationToken extends Model
{
    protected $table = 'insee_authorization_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token', 'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the AuthorizationToken's token
     * Handle encrypt and decrypt to not store plain value in database
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function token(): Attribute
    {
        return Attribute::make(
            get:function ($value) {
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    throw $e;
                }
            }, set:fn($value) => Crypt::encryptString($value)
        );
    }

}
