<?php

namespace App\Models;

/**
 * ============================================================================
 * User Model
 * ============================================================================
 * Purpose: Represents an authenticated system user record in the database.
 * Extends Laravel's authenticatable base class to support session-based auth,
 * password hashing, and notification routing.
 * 
 * PHP 8 Class Attributes:
 *  - #[Fillable] : Defines the mass-assignable attributes (replaces $fillable property).
 *  - #[Hidden]   : Hides sensitive fields from JSON serialization (replaces $hidden property).
 * ============================================================================
 */

// Native Eloquent Attributes (Laravel 11+)
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

// Model Traits & Base Authenticatable Class
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Mass Assignment Protection
 * Specifies which database columns can be safely mass-assigned via User::create() or fill().
 */
#[Fillable(['name', 'email', 'password'])]

/**
 * Serialization Protection
 * Prevents sensitive attributes from being exposed when converting the model to JSON or Array.
 */
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    /** 
     * Type-hinted factory trait annotation for IDE auto-completion.
     * @use HasFactory<UserFactory> 
     */
    use HasFactory;

    /**
     * Trait enabling the model to receive internal or external notifications 
     * (e.g., Mail, Database, Slack, SMS).
     */
    use Notifiable;

    /**
     * Get the attributes that should be cast to specific native PHP data types.
     *
     * - 'email_verified_at' : Automatically converted to a Carbon instance when accessed.
     * - 'password'          : Automatically hashed using Bcrypt/Argon2 upon assignment.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
