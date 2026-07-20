<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('warehouse.index');
    }

    public function listDataTable(Request $request)
    {
        $query = Warehouse::query()->get();
        return datatables($query)->toJson();
    }

    public function create()
    {
        return view('warehouse.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Warehouse::query()->create([
            'name' => $request->name,
        ]);

        return redirect(route('warehouses.index'));
    }

    public function show(string $id)
    {
        $warehouse = Warehouse::query()->findOrFail($id);
        return view('warehouse.show', compact('warehouse'));
    }

    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::query()->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $warehouse->update([
            'name' => $request->name,
        ]);

        return redirect(route('warehouses.index'));
    }

    public function destroy(string $id)
    {
        Warehouse::query()->findOrFail($id)->delete();
        return redirect(route('warehouses.index'));
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $item = Warehouse::find($id);
            if ($item === null) {
                continue;
            }
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
