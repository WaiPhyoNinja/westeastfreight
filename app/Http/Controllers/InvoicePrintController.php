<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\SiteSetting;
use App\Support\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class InvoicePrintController extends Controller
{
    public function __invoke(Invoice $invoice): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect('/admin/login');
        }

        abort_unless(auth()->user()?->can('invoices.view') || auth()->user()?->can('quote-requests.manage'), 403);

        $invoiceSettings = InvoiceSetting::query()->first();

        return view('invoices.print', [
            'invoice' => $invoice->load(['items', 'quoteRequest']),
            'invoiceSettings' => $invoiceSettings,
            'invoiceAddressHtml' => HtmlSanitizer::clean((string) $invoiceSettings?->address),
            'invoiceHeaderHtml' => HtmlSanitizer::clean((string) $invoiceSettings?->company_header_html),
            'invoiceTermsHtml' => HtmlSanitizer::clean((string) $invoiceSettings?->terms_and_conditions_html),
            'site' => SiteSetting::query()->first(),
        ]);
    }
}
