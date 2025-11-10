<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function edit()
    {
        $about = About::first();

        return view('admin.about.about-us', compact('about'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_' => 'required|array',
            'text_.en' => 'required|string',
            'text_.ka' => 'required|string',
        ]);
        $about = About::first();
        if (! $about) {
            $about = new About;
        }
        $about->text_en = $data['text_']['en'];
        $about->text_ka = $data['text_']['ka'];
        $about->save();

        return redirect()->back()->with('success', __('admin.about_us_text_updated'));
    }
}
