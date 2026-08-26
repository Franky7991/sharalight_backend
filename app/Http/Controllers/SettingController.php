<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Causal;

class SettingController extends Controller
{
    public function index()
    {
        $loadCausals   = Causal::query()->where('type', Causal::TYPE_LOAD)  ->orderBy('name')->get();
        $unloadCausals = Causal::query()->where('type', Causal::TYPE_UNLOAD)->orderBy('name')->get();
        $settings      = Setting::query()->pluck('value', 'key');

        return view('setting.index', compact('loadCausals', 'unloadCausals', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            Setting::KEY_WAREHOUSE_LOAD_CAUSAL    => ['nullable', 'exists:causals,id'],
            Setting::KEY_PRODUCTION_UNLOAD_CAUSAL => ['nullable', 'exists:causals,id'],
            Setting::KEY_PRODUCTION_LOAD_CAUSAL   => ['nullable', 'exists:causals,id'],
            Setting::KEY_SHIPMENT_UNLOAD_CAUSAL   => ['nullable', 'exists:causals,id'],
        ]);

        Setting::set(Setting::KEY_WAREHOUSE_LOAD_CAUSAL,    $request->input(Setting::KEY_WAREHOUSE_LOAD_CAUSAL));
        Setting::set(Setting::KEY_PRODUCTION_UNLOAD_CAUSAL, $request->input(Setting::KEY_PRODUCTION_UNLOAD_CAUSAL));
        Setting::set(Setting::KEY_PRODUCTION_LOAD_CAUSAL,   $request->input(Setting::KEY_PRODUCTION_LOAD_CAUSAL));
        Setting::set(Setting::KEY_SHIPMENT_UNLOAD_CAUSAL,   $request->input(Setting::KEY_SHIPMENT_UNLOAD_CAUSAL));

        return redirect()->route('settings.index')
            ->with('success', 'Impostazioni salvate con successo.');
    }
}
