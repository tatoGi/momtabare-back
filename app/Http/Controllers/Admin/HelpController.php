<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Help;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function edit()
    {
        $help = Help::first();

        return view('admin.help.edit', compact('help'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'text_' => 'required|array',
            'text_.en' => 'required|string',
            'text_.ka' => 'required|string',
        ]);
        $help = Help::first();
        if (! $help) {
            $help = new Help;
        }
        $help->text_en = $data['text_']['en'];
        $help->text_ka = $data['text_']['ka'];
        $help->save();

        return redirect()->back()->with('success', __('admin.help_text_updated'));
    }
}
