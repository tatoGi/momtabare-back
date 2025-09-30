<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class WebUser extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'surname',
        'email',
        'password',
        'phone',
        'avatar',
        'email_verification_token',
        'retailer_requested_at',
        'email_verified_at',
        // Extra profile fields
        'personal_id',
        'birth_date',
        'gender',
        // Retailer capability
        'is_retailer',
        'retailer_status',
        'retailer_requested_at',
        'verification_code',
        'verification_expires_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    /**
     * Get the retailer shop associated with the user.
     */
    public function retailerShop()
    {
        return $this->hasOne(RetailerShop::class, 'user_id');
    }

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (! $this->email_verification_token) {
            $this->email_verification_token = \Illuminate\Support\Str::random(60);
            $this->save();
        }

        $verificationUrl = url("/verify-email/{$this->email_verification_token}");
        $this->notify(new \App\Notifications\VerifyEmail($verificationUrl));
    }

    /**
     * Get the verification token for the user.
     *
     * @return string
     */
    public function getVerificationToken()
    {
        return $this->email_verification_token;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_token' => null,
            'verification_code' => null,
            'verification_expires_at' => null,
        ])->save();
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'is_retailer' => 'boolean',
        'retailer_requested_at' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'retailer_id');
    }

    /**
     * Get the comments written by this user
     */
    public function comments()
    {
        return $this->hasMany(ProductComment::class, 'user_id');
    }
}
