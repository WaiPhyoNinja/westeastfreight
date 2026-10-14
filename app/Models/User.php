<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** Allow staff with a panel permission while keeping customers out. */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin') || $this->canAny([
            'users.manage', 'roles.manage', 'settings.manage', 'quotation-countries.manage',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'quote-requests.manage', 'submissions.manage',
            'customers.view', 'navigation.manage',
            'contact-requests.manage', 'newsletters.manage',
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_details' => 'array',
            'password' => 'hashed',
        ];
    }
}
