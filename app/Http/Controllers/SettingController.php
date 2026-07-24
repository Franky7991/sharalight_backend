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
        $causals  = Causal::query()->where('type', Causal::TYPE_LOAD)->orderBy('name')->get();
        $settings = Setting::query()->pluck('value', 'key');

        return view('setting.index', compact('causals', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            Setting::KEY_WAREHOUSE_LOAD_CAUSAL => [
                'nullable',
                'exists:causals,id',
            ],
        ]);

        Setting::set(
            Setting::KEY_WAREHOUSE_LOAD_CAUSAL,
            $request->input(Setting::KEY_WAREHOUSE_LOAD_CAUSAL)
        );

        return redirect()->route('settings.index')
            ->with('success', 'Impostazioni salvate con successo.');
    }
}
