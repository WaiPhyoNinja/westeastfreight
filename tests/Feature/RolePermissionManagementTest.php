<?php

namespace Tests\Feature;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\HeaderNavigationResource;
use App\Filament\Resources\NewsletterResource;
use App\Filament\Resources\WebsiteSubmissionResource;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_staff_roles_have_scoped_permissions(): void
    {
        $websiteManager = Role::findByName('website_manager', 'web');
        $quotationManager = Role::findByName('quotation_manager', 'web');
        $customerSupport = Role::findByName('customer_support', 'web');

        $this->assertTrue($websiteManager->hasPermissionTo('navigation.manage'));
        $this->assertTrue($websiteManager->hasPermissionTo('pages.update'));
        $this->assertFalse($websiteManager->hasPermissionTo('users.manage'));

        $this->assertTrue($quotationManager->hasPermissionTo('quote-requests.manage'));
        $this->assertTrue($quotationManager->hasPermissionTo('customers.view'));
        $this->assertFalse($quotationManager->hasPermissionTo('invoices.delete'));

        $this->assertTrue($customerSupport->hasPermissionTo('contact-requests.manage'));
        $this->assertTrue($customerSupport->hasPermissionTo('newsletters.manage'));
        $this->assertFalse($customerSupport->hasPermissionTo('settings.manage'));
    }

    public function test_new_permissions_only_open_the_related_resources(): void
    {
        $support = User::factory()->create();
        $support->assignRole('customer_support');
        $this->actingAs($support);

        $this->assertTrue(CustomerResource::canViewAny());
        $this->assertTrue(WebsiteSubmissionResource::canViewAny());
        $this->assertTrue(NewsletterResource::canViewAny());
        $this->assertFalse(HeaderNavigationResource::canViewAny());

        $websiteManager = User::factory()->create();
        $websiteManager->assignRole('website_manager');
        $this->actingAs($websiteManager);

        $this->assertTrue(HeaderNavigationResource::canViewAny());
        $this->assertFalse(WebsiteSubmissionResource::canViewAny());
    }

    public function test_admin_can_create_role_with_permissions_and_assign_it_to_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $pages = Permission::findOrCreate('pages.view', 'web');
        $invoices = Permission::findOrCreate('invoices.view', 'web');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)->test(CreateRole::class)->fillForm([
            'name' => 'content_manager',
            'permissions' => [$pages->id],
        ])->call('create')->assertHasNoFormErrors();

        $role = Role::findByName('content_manager', 'web');
        $this->assertTrue($role->hasPermissionTo('pages.view'));

        Livewire::actingAs($admin)->test(CreateUser::class)->fillForm([
            'name' => 'Content Manager',
            'email' => 'manager@example.test',
            'password' => 'secure-password',
            'roles' => [$role->id],
        ])->call('create')->assertHasNoFormErrors();

        $manager = User::where('email', 'manager@example.test')->firstOrFail();
        $this->assertTrue($manager->hasRole('content_manager'));
        $this->assertTrue($manager->canAccessPanel(Filament::getCurrentPanel()));
        $this->assertTrue($manager->can('pages.view'));
        $this->assertFalse($manager->can('invoices.view'));
        $this->assertCount(0, $manager->permissions);
        $this->assertFalse($manager->can('users.manage'));

        Livewire::actingAs($admin)->test(EditRole::class, ['record' => $role->id])
            ->fillForm(['permissions' => [$invoices->id]])
            ->call('save')->assertHasNoFormErrors();

        $this->assertFalse($role->fresh()->hasPermissionTo('pages.view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('invoices.view'));
        $this->assertFalse($manager->fresh()->can('pages.view'));
        $this->assertTrue($manager->fresh()->can('invoices.view'));
    }

    public function test_customer_cannot_access_admin_and_core_roles_cannot_be_edited(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole(Role::findOrCreate('customer', 'web'));
        $this->assertFalse($customer->canAccessPanel(Filament::getPanel('admin')));

        $admin = Role::findOrCreate('admin', 'web');
        $this->assertFalse(RoleResource::canEdit($admin));
        $this->assertFalse(RoleResource::canDelete($admin));
    }

    public function test_changing_user_roles_changes_effective_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();
        $editor = Role::findOrCreate('editor', 'web');
        $editor->givePermissionTo(Permission::findOrCreate('pages.update', 'web'));
        $accountant = Role::findOrCreate('accountant', 'web');
        $accountant->givePermissionTo(Permission::findOrCreate('invoices.create', 'web'));
        $user->assignRole($editor);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertTrue($user->can('pages.update'));
        $this->assertFalse($user->can('invoices.create'));

        Livewire::actingAs($admin)->test(EditUser::class, ['record' => $user->id])
            ->fillForm(['roles' => [$accountant->id]])
            ->call('save')->assertHasNoFormErrors();

        $user = $user->fresh();
        $this->assertFalse($user->can('pages.update'));
        $this->assertTrue($user->can('invoices.create'));
        $this->assertCount(0, $user->permissions);
    }

    public function test_existing_direct_permissions_move_to_a_role_without_losing_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('customer', 'web'));
        $user->givePermissionTo([
            Permission::findOrCreate('pages.view', 'web'),
            Permission::findOrCreate('settings.manage', 'web'),
        ]);

        $migration = require database_path('migrations/2026_10_14_000006_move_direct_user_permissions_to_roles.php');
        $migration->up();

        $user = $user->fresh();
        $this->assertTrue($user->hasRole('legacy_access_user_'.$user->id));
        $this->assertCount(0, $user->permissions);
        $this->assertTrue($user->can('pages.view'));
        $this->assertTrue($user->can('settings.manage'));
    }
}
