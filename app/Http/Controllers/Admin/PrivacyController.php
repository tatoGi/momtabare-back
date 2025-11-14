<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Privacy;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function edit()
    {
        $privacy = Privacy::first();

        return view('admin.privacy.edit', compact('privacy'));
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
        $privacy = Privacy::first();
        if (! $privacy) {
            $privacy = new Privacy;
        }
        $privacy->text_en = $data['text_']['en'];
        $privacy->text_ka = $data['text_']['ka'];
        $privacy->save();

        return redirect()->back()->with('success', __('admin.privacy_text_updated'));
    }
}
