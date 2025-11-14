<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Confidential;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class ConfidentialController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function edit()
    {
        $confidential = Confidential::first();

        return view('admin.confidential.edit', compact('confidential'));
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
        $confidential = Confidential::first();
        if (! $confidential) {
            $confidential = new Confidential;
        }
        $confidential->text_en = $data['text_']['en'];
        $confidential->text_ka = $data['text_']['ka'];
        $confidential->save();

        return redirect()->back()->with('success', __('admin.confidential_text_updated'));
    }
}
