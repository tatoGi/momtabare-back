<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Privacy;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    public function edit()
    {
        $privacy = Privacy::first();
        return view('admin.privacy.edit', compact('privacy'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_' => 'required|array',
            'text_.en' => 'required|string',
            'text_.ka' => 'required|string',
        ]);
        $privacy = Privacy::first();
        if (!$privacy) {
            $privacy = new Privacy();
        }
        $privacy->text_en = $data['text_']['en'];
        $privacy->text_ka = $data['text_']['ka'];
        $privacy->save();
        return redirect()->back()->with('success', __('admin.privacy_text_updated'));
    }
}
