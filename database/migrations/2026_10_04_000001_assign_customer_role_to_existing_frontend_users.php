<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $customer = Role::findOrCreate('customer', 'web');

        User::query()->doesntHave('roles')->each(function (User $user) use ($customer): void {
            $user->assignRole($customer);
        });
    }

    public function down(): void
    {
        // Existing accounts may have been edited since migration; do not remove their roles.
    }
};
