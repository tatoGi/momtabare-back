<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function edit()
    {
        $service = Service::first();

        return view('admin.service.edit', compact('service'));
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
