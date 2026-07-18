<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitOfMeasure;

class UnitOfMeasureController extends Controller
{
    public function index() {
        return view('unit_of_measure.index');
    }

    public function listDataTable(Request $request) {
        $query = UnitOfMeasure::query()->get();
        return datatables($query)->toJson();
    }

    public function create() {
        return view('unit_of_measure.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:50'],
        ]);
        UnitOfMeasure::query()->create([
            'name'   => $request->name,
            'symbol' => $request->symbol,
        ]);
        return redirect(route('unit-of-measures.index'));
    }

    public function show(string $id) {
        $unitOfMeasure = UnitOfMeasure::query()->findOrFail($id);
        return view('unit_of_measure.show', compact('unitOfMeasure'));
    }

    public function update(Request $request, string $id) {
        $unitOfMeasure = UnitOfMeasure::query()->findOrFail($id);
        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:50'],
        ]);
        $unitOfMeasure->update([
            'name'   => $request->name,
            'symbol' => $request->symbol,
        ]);
        return redirect(route('unit-of-measures.index'));
    }

    public function destroy(string $id) {
        UnitOfMeasure::query()->findOrFail($id)->delete();
        return redirect(route('unit-of-measures.index'));
    }

    public function delete(Request $request) {
        foreach ($request->ids as $id) {
            $item = UnitOfMeasure::find($id);
            if ($item === null) continue;
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
