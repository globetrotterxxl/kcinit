<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        'keycloak_id',
        'keycloak_roles',
        'keycloak_groups',
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
            'keycloak_roles' => 'array',
            'keycloak_groups' => 'array',
        ];
    }

    public function hasRealmRole(string $role): bool
    {
        return in_array($role, $this->keycloak_roles ?? [], true);
    }

    public function inKeycloakGroup(string $group): bool
    {
        return in_array($group, $this->keycloak_groups ?? [], true);
    }
}
