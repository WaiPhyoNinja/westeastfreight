<?php

namespace Tests\Feature;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Pages\CreateInvoice;
use App\Filament\Resources\InvoiceSettingResource\Pages\CreateInvoiceSetting;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_total_is_calculated_from_line_items_and_null_total_cannot_break_creation(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'TEST-001',
            'currency' => 'THB',
            'total_amount' => null,
        ]);

        $this->assertSame('0.00', $invoice->total_amount);

        $invoice->items()->createMany([
            ['description' => 'Customs clearance', 'unit_price' => 4000, 'quantity' => 1],
            ['description' => 'Transport charge', 'unit_price' => 1250.50, 'quantity' => 2],
        ]);
        $invoice->recalculateTotal();

        $this->assertSame('6501.00', $invoice->fresh()->total_amount);
        $this->assertSame(6501.0, InvoiceResource::calculateTotal([
            ['unit_price' => 4000, 'quantity' => 1],
            ['unit_price' => 1250.50, 'quantity' => 2],
        ]));
    }

    public function test_admin_invoice_form_creates_invoice_and_calculates_total(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $admin->givePermissionTo(Permission::findOrCreate('invoices.create', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)->test(CreateInvoice::class)->fillForm([
            'date' => '2026-10-14',
            'currency' => 'THB',
            'carrier' => 'LAND',
            'name' => 'Test Customer',
            'items' => [
                ['group_no' => 1, 'description' => 'Transport charge', 'unit_price' => 39000, 'quantity' => 1, 'remark' => 'Truck'],
                ['group_no' => 2, 'description' => 'Handling fee', 'unit_price' => 1000, 'quantity' => 2],
            ],
        ])->call('create')->assertHasNoFormErrors();

        $invoice = Invoice::latest('id')->firstOrFail();
        $this->assertSame('41000.00', $invoice->total_amount);
        $this->assertStringStartsWith('WEF-INV-2026-', $invoice->invoice_number);
        $this->assertCount(2, $invoice->items);
    }

    public function test_invoice_settings_are_managed_in_admin_and_rendered_on_invoice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $admin->givePermissionTo([
            Permission::findOrCreate('settings.manage', 'web'),
            Permission::findOrCreate('invoices.view', 'web'),
        ]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)->test(CreateInvoiceSetting::class)->fillForm([
            'company_name' => 'Example Freight Logistics Co., Ltd.',
            'invoice_title' => 'TAX INVOICE',
            'address' => '<p><strong>35 Example Road</strong><br>Bangkok, Thailand<script>alert(2)</script></p>',
            'phone' => '+66 00 000 0000',
            'whatsapp' => '+66 00 000 0001',
            'email' => 'billing@example.test',
            'secondary_email' => 'accounts@example.test',
            'company_header_html' => '<p><strong>Bangkok Office</strong><script>alert(1)</script></p>',
            'terms_and_conditions_html' => '<ol><li>Payment is due according to the agreed terms.</li><li><strong>Rates remain valid for 21 days.</strong></li></ol>',
            'item_descriptions' => [
                ['value' => 'Customs clearance'],
                ['value' => 'Transport charge'],
            ],
            'item_units' => [
                ['value' => 'FILE'],
                ['value' => 'TRUCK'],
            ],
            'footer_note' => 'Thank you for your business.',
        ])->call('create')->assertHasNoFormErrors();

        $this->assertSame('Example Freight Logistics Co., Ltd.', InvoiceSetting::firstOrFail()->company_name);
        $this->assertArrayHasKey('TRUCK', InvoiceResource::invoiceSettingOptions('item_units'));
        $this->assertSame('BL', InvoiceResource::addInvoiceSettingOption('item_units', 'BL'));
        $this->assertArrayHasKey('BL', InvoiceResource::invoiceSettingOptions('item_units'));

        $invoice = Invoice::create(['invoice_number' => 'TEST-SETTINGS', 'currency' => 'THB']);
        $invoice->items()->createMany([
            ['group_no' => 1, 'description' => 'Customs clearance', 'unit_price' => 4000, 'quantity' => 1, 'unit' => 'FILE'],
            ['group_no' => 1, 'description' => 'Export clearance', 'unit_price' => 3500, 'quantity' => 1, 'unit' => 'FILE'],
            ['group_no' => 1, 'description' => 'License charge', 'unit_price' => 500, 'quantity' => 1, 'unit' => 'FILE'],
        ]);
        $invoice->recalculateTotal();
        $this->actingAs($admin)->get(route('invoices.print', $invoice))
            ->assertOk()
            ->assertSee('Example Freight Logistics Co., Ltd.')
            ->assertSee('TAX INVOICE')
            ->assertSee('Rates remain valid for 21 days.')
            ->assertSee('Thank you for your business.')
            ->assertSee('Bangkok Office')
            ->assertSee('rowspan="3"', false)
            ->assertSee('Export clearance')
            ->assertDontSee('alert(1)')
            ->assertDontSee('alert(2)');
    }
}
