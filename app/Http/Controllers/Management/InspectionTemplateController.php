<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Support\InspectionTemplate;
use Illuminate\Http\Request;

/**
 * Superadmin: atur daftar baku pemeriksaan Gate (alat wajib & logistik terhitung).
 */
class InspectionTemplateController extends Controller
{
    public function index()
    {
        return view('management.settings.inspection', ['template' => InspectionTemplate::get()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'gear' => 'nullable|array',
            'gear.*' => 'nullable|string|max:100',
            'logistic' => 'nullable|array',
            'logistic.*.name' => 'nullable|string|max:100',
            'logistic.*.unit' => 'nullable|string|max:20',
        ]);

        $gear = collect((array) $request->input('gear', []))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $logistic = collect((array) $request->input('logistic', []))
            ->map(fn ($r) => ['name' => trim((string) ($r['name'] ?? '')), 'unit' => trim((string) ($r['unit'] ?? '')) ?: 'pcs'])
            ->filter(fn ($r) => $r['name'] !== '')
            ->unique('name')
            ->values()
            ->all();

        InspectionTemplate::save($gear, $logistic);

        AuditService::log(
            action: 'INSPECTION_TEMPLATE_UPDATED',
            description: 'Checklist pemeriksaan Gate diperbarui: '.count($gear).' alat wajib, '.count($logistic).' jenis logistik.',
        );

        return back()->with('success', 'Checklist pemeriksaan Gate disimpan.');
    }
}
