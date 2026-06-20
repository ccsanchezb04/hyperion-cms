<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()
            ->groupBy('sett_nmgrou')
            ->map(fn ($items) => $items->pluck('sett_dsvalu', 'sett_cdkeys'));

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group'    => ['required', Rule::in([
                Setting::GROUP_GENERAL,
                Setting::GROUP_SEO,
                Setting::GROUP_MEDIA,
                Setting::GROUP_MAIL,
            ])],
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['sett_cdkeys' => $key],
                [
                    'sett_dsvalu' => is_array($value) ? json_encode($value) : (string) $value,
                    'sett_nmgrou' => $validated['group'],
                ]
            );
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
