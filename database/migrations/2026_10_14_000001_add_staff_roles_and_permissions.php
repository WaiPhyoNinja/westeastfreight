<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'users.manage', 'roles.manage', 'settings.manage', 'quotation-countries.manage',
            'quote-requests.manage', 'submissions.manage',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'customers.view', 'navigation.manage', 'contact-requests.manage', 'newsletters.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('admin', 'web')->givePermissionTo($permissions);

        Role::findOrCreate('website_manager', 'web')->givePermissionTo([
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'settings.manage', 'navigation.manage', 'quotation-countries.manage',
        ]);

        Role::findOrCreate('quotation_manager', 'web')->givePermissionTo([
            'quote-requests.manage', 'customers.view',
            'invoices.view', 'invoices.create', 'invoices.update',
        ]);

        Role::findOrCreate('customer_support', 'web')->givePermissionTo([
            'customers.view', 'contact-requests.manage', 'newsletters.manage',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Preserve staff assignments if this migration is rolled back.
    }
};
