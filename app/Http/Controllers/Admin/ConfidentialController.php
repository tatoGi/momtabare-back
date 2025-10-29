<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Confidential;
use Illuminate\Http\Request;

class ConfidentialController extends Controller
{
    public function edit()
    {
        $confidential = Confidential::first();
        return view('admin.confidential.edit', compact('confidential'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_' => 'required|array',
            'text_.en' => 'required|string',
            'text_.ka' => 'required|string',
        ]);
        $confidential = Confidential::first();
        if (!$confidential) {
            $confidential = new Confidential();
        }
        $confidential->text_en = $data['text_']['en'];
        $confidential->text_ka = $data['text_']['ka'];
        $confidential->save();
        return redirect()->back()->with('success', __('admin.confidential_text_updated'));
    }
}
