<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function edit()
    {
        $service = Service::first();

        return view('admin.service.edit', compact('service'));
    }

    public function translateFields(Request $request)
    {
        $request->validate([
            'source_locale' => 'required|string',
            'target_locale' => 'required|string',
            'data' => 'required|array',
        ]);

        try {
            $translated = $this->translationService->translatePostFields(
                $request->data,
                $request->source_locale,
                $request->target_locale
            );

            return response()->json([
                'success' => true,
                'translated' => $translated,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Translation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_' => 'required|array',
            'text_.en' => 'required|string',
            'text_.ka' => 'required|string',
        ]);
        $service = Service::first();
        if (! $service) {
            $service = new Service;
        }
        $service->text_en = $data['text_']['en'];
        $service->text_ka = $data['text_']['ka'];
        $service->save();

        return redirect()->back()->with('success', __('admin.our_services_text_updated'));
    }
}
