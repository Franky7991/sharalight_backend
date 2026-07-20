<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitConversion;
use App\Models\UnitOfMeasure;

class UnitConversionController extends Controller
{
    public function index()
    {
        return view('unit_conversion.index');
    }

    public function listDataTable(Request $request)
    {
        $query = UnitConversion::query()
            ->with(['fromUnitOfMeasure', 'toUnitOfMeasure'])
            ->get();

        return datatables($query)
            ->addColumn('from_uom_label', fn($row) => $row->fromUnitOfMeasure
                ? $row->fromUnitOfMeasure->symbol . ' (' . $row->fromUnitOfMeasure->name . ')'
                : '-')
            ->addColumn('to_uom_label', fn($row) => $row->toUnitOfMeasure
                ? $row->toUnitOfMeasure->symbol . ' (' . $row->toUnitOfMeasure->name . ')'
                : '-')
            ->toJson();
    }

    public function create()
    {
        $unitOfMeasures = UnitOfMeasure::query()->orderBy('name')->get();
        return view('unit_conversion.create', compact('unitOfMeasures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
            'from_quantity'           => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
            'to_unit_of_measure_id'   => ['required', 'exists:unit_of_measures,id', 'different:from_unit_of_measure_id'],
            'to_quantity'             => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
        ]);

        UnitConversion::query()->create([
            'from_unit_of_measure_id' => $request->from_unit_of_measure_id,
            'from_quantity'           => $this->parseDecimal($request->from_quantity),
            'to_unit_of_measure_id'   => $request->to_unit_of_measure_id,
            'to_quantity'             => $this->parseDecimal($request->to_quantity),
        ]);

        return redirect(route('unit-conversions.index'));
    }

    public function show(string $id)
    {
        $unitConversion = UnitConversion::query()->findOrFail($id);
        $unitOfMeasures = UnitOfMeasure::query()->orderBy('name')->get();
        return view('unit_conversion.show', compact('unitConversion', 'unitOfMeasures'));
    }

    public function update(Request $request, string $id)
    {
        $unitConversion = UnitConversion::query()->findOrFail($id);

        $request->validate([
            'from_unit_of_measure_id' => ['required', 'exists:unit_of_measures,id'],
            'from_quantity'           => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
            'to_unit_of_measure_id'   => ['required', 'exists:unit_of_measures,id', 'different:from_unit_of_measure_id'],
            'to_quantity'             => ['required', 'regex:/^\d{1,15}([.,]\d{1,2})?$/'],
        ]);

        $unitConversion->update([
            'from_unit_of_measure_id' => $request->from_unit_of_measure_id,
            'from_quantity'           => $this->parseDecimal($request->from_quantity),
            'to_unit_of_measure_id'   => $request->to_unit_of_measure_id,
            'to_quantity'             => $this->parseDecimal($request->to_quantity),
        ]);

        return redirect(route('unit-conversions.index'));
    }

    public function destroy(string $id)
    {
        UnitConversion::query()->findOrFail($id)->delete();
        return redirect(route('unit-conversions.index'));
    }

    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $item = UnitConversion::find($id);
            if ($item === null) {
                continue;
            }
            $item->delete();
        }
        return response()->json(['success' => true]);
    }

    private function parseDecimal(string $value): float
    {
        // Accetta sia formato italiano (1.234,56) che internazionale (1234.56)
        return (float) str_replace(',', '.', str_replace('.', '', $value));
    }
}
