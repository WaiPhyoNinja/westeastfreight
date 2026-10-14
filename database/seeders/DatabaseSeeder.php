<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.manage', 'roles.manage', 'settings.manage', 'quotation-countries.manage',
            'quote-requests.manage', 'submissions.manage',
            'customers.view', 'navigation.manage',
            'contact-requests.manage', 'newsletters.manage',
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete',
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Reload the registrar cache before resolving permission names for roles.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('customer', 'web');

        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions($permissions);

        $editor = Role::findOrCreate('editor', 'web');
        $editor->syncPermissions(['pages.view', 'pages.create', 'pages.update']);

        $accountant = Role::findOrCreate('accountant', 'web');
        $accountant->syncPermissions(['invoices.view', 'invoices.create', 'invoices.update']);

        $websiteManager = Role::findOrCreate('website_manager', 'web');
        $websiteManager->syncPermissions([
            'pages.view', 'pages.create', 'pages.update', 'pages.delete',
            'settings.manage', 'navigation.manage', 'quotation-countries.manage',
        ]);

        $quotationManager = Role::findOrCreate('quotation_manager', 'web');
        $quotationManager->syncPermissions([
            'quote-requests.manage', 'customers.view',
            'invoices.view', 'invoices.create', 'invoices.update',
        ]);

        $customerSupport = Role::findOrCreate('customer_support', 'web');
        $customerSupport->syncPermissions([
            'customers.view', 'contact-requests.manage', 'newsletters.manage',
        ]);

        $user = User::query()->firstOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Administrator',
            'password' => 'password',
        ]);
        $user->syncRoles([$admin]);

        $this->call(PageSeeder::class);
        $this->call(ServicePageSeeder::class);
        $this->call(SourceServicePageSeeder::class);
        $this->call(ContactPageSeeder::class);
        $this->call(SiteSettingSeeder::class);
        $this->call(QuotationCountrySeeder::class);
    }
}
