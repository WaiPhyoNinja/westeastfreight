<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Support\MailDelivery;
use App\Support\QuoteMailDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class QuoteRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => QuoteRequest::where('user_id', $request->user()->id)
            ->latest()->get()->map->frontendPayload()]);
    }

    public function show(Request $request, int $quote): JsonResponse
    {
        $record = QuoteRequest::where('user_id', $request->user()->id)->findOrFail($quote);

        return response()->json(['data' => $record->frontendPayload()])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request): JsonResponse
    {
        $details = $this->validatedDetails($request);
        $quote = QuoteRequest::create(['user_id' => $request->user()->id, 'status' => 'pending', 'details' => $details]);

        try {
            if (MailDelivery::isConfigured()) {
                app(QuoteMailDispatcher::class)->queueStatus($quote->load('user'), 'pending');
            }
        } catch (Throwable $exception) {
            Log::error('Quotation request receipt could not be queued.', ['quote_id' => $quote->id, 'exception' => $exception]);
        }

        return response()->json(['data' => $quote->frontendPayload()], 201);
    }

    public function update(Request $request, int $quote): JsonResponse
    {
        return DB::transaction(function () use ($request, $quote) {
            $record = QuoteRequest::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($quote);
            abort_unless($record->status === 'pending' && $record->quotation_mail_status !== 'queued', 409,
                'This quote request cannot be edited while a quotation email is queued or after review.');
            $record->update(['details' => $this->validatedDetails($request)]);

            return response()->json(['data' => $record->frontendPayload()]);
        });
    }

    private function validatedDetails(Request $request): array
    {
        $rules = [
            'transport_mode' => ['sometimes', 'required', Rule::in(['multimodal', 'sea', 'air', 'road'])],
            'incoterms' => ['required_with:transport_mode', Rule::in(['unknown', 'EXW', 'FCA', 'CPT', 'CIP', 'DAP', 'DPU', 'DDP', 'FAS', 'FOB', 'CFR', 'CIF'])],
            'departure_type' => ['required_with:transport_mode', Rule::in(['door', 'port', 'airport', 'warehouse'])],
            'arrival_type' => ['required_with:transport_mode', Rule::in(['door', 'port', 'airport', 'warehouse'])],
            'arrival_date' => 'required_with:transport_mode|date_format:Y-m-d',
            'commodity_type' => ['required_with:transport_mode', Rule::in(['general', 'hazardous', 'perishable'])],
            'hazardous_details' => 'nullable|string|max:255',
            'temperature_range' => 'required_if:commodity_type,perishable|nullable|string|max:255',
            'packaging' => ['required_with:transport_mode', Rule::in(['pallet', 'boxes', 'drums', 'bags', 'vehicle', 'unpacked', 'container'])],
            'container_type' => ['required_if:packaging,container', 'nullable', Rule::in(["20' Standard", "20' Reefer", "20' Flat Rack", "20' Open Top", "40' Standard", "40' High Cube", "40' Reefer", "40' Flat Rack", "40' Open Top"])],
            'stackable' => 'sometimes|boolean',
            'shipment_type' => ['required', Rule::in(['document', 'parcel', 'freight', 'express'])],
            'service_type' => ['required', Rule::in(['standard', 'express', 'overnight', 'economy'])],
            'weight' => 'required|numeric|gt:0|max:100000000',
            'quantity' => 'required|integer|min:1|max:1000000',
            'length' => 'nullable|numeric|gt:0|max:1000000',
            'width' => 'nullable|numeric|gt:0|max:1000000',
            'height' => 'nullable|numeric|gt:0|max:1000000',
            'declared_value' => 'nullable|numeric|min:0|max:1000000000000',
            'description' => 'required|string|max:5000',
            'special_instructions' => 'nullable|string|max:5000',
            'insurance' => 'required|boolean',
        ];
        foreach (['sender', 'receiver'] as $party) {
            foreach (['first_name', 'last_name', 'address', 'city', 'phone'] as $field) {
                $rules[$party.'_'.$field] = 'required|string|max:255';
            }
            foreach (['company', 'state', 'postal_code'] as $field) {
                $rules[$party.'_'.$field] = 'nullable|string|max:255';
            }
            $rules[$party.'_email'] = 'required|email|max:255';
            $rules[$party.'_country'] = ['required', 'string', Rule::exists('quotation_countries', 'name')->where('status', true)];
        }

        return $request->validate($rules);
    }
}
