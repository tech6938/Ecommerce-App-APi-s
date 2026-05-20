<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    public function index()
    {
        $setting = SystemSetting::first();
        return view('system-setting', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg',
            'company_name' => 'nullable|string',
            'company_number' => 'nullable|string',
            'company_address' => 'nullable|string',
            'admin_name' => 'nullable|string',
            'job_types' => 'nullable|string',
        ]);

        $setting = SystemSetting::first();

        if (!$setting) {
            $setting = new SystemSetting();
        }

        $setting->company_name = $request->company_name;
        $setting->company_number = $request->company_number;
        $setting->company_address = $request->company_address;
        $setting->admin_name = $request->admin_name; // ✅ now editable
        $setting->job_types = $request->job_types;

        if ($request->hasFile('logo')) {
            $logo = time() . '_logo.' . $request->logo->extension();
            $request->logo->move(public_path('systemsetting'), $logo);
            $setting->logo = $logo;
        }

        if ($request->hasFile('favicon')) {
            $favicon = time() . '_favicon.' . $request->favicon->extension();
            $request->favicon->move(public_path('systemsetting'), $favicon);
            $setting->favicon = $favicon;
        }

        $setting->save();

        return back()->with('success', 'System settings updated successfully!');
    }
}
