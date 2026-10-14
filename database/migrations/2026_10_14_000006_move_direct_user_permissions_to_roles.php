<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        User::query()->whereHas('permissions')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                DB::transaction(function () use ($user): void {
                    $permissions = $user->permissions->where('guard_name', 'web')->pluck('name')->all();

                    if ($permissions === []) {
                        return;
                    }

                    // A dedicated role preserves the user's current access without changing shared roles.
                    $role = Role::findOrCreate('legacy_access_user_'.$user->id, 'web');
                    $role->syncPermissions($permissions);
                    $user->assignRole($role);
                    $user->revokePermissionTo($permissions);
                });
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep role assignments and access intact on rollback.
    }
};
