<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Causal;

class CausalController extends Controller
{
    public function index()
    {
        return view('causal.index');
    }

    public function listDataTable(Request $request)
    {
        $query = Causal::query()->get();

        return datatables($query)
            ->addColumn('type_label', function ($row) {
                $color = $row->type === Causal::TYPE_LOAD ? 'success' : 'danger';
                return '<span class="badge badge-' . $color . '">' . $row->typeLabel() . '</span>';
            })
            ->rawColumns(['type_label'])
            ->toJson();
    }

    public function create()
    {
        $causalTypes = Causal::TYPES;
        return view('causal.create', compact('causalTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(Causal::TYPES))],
        ]);

        Causal::query()->create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return redirect(route('causals.index'));
    }

    public function show(string $id)
    {
        $causal      = Causal::query()->findOrFail($id);
        $causalTypes = Causal::TYPES;
        return view('causal.show', compact('causal', 'causalTypes'));
    }

    public function update(Request $request, string $id)
    {
        $causal = Causal::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(Causal::TYPES))],
        ]);

        $causal->update([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return redirect(route('causals.index'));
    }

    public function destroy(string $id)
    {
        Causal::query()->findOrFail($id)->delete();
        return redirect(route('causals.index'));
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $item = Causal::find($id);
            if ($item === null) {
                continue;
            }
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
