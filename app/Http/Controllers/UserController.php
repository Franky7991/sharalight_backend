<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("user.index");
    }

    /**
     * Return datatable JSON for users list.
     */
    public function listDataTable(Request $request)
    {
        $query = User::query()->get();
        return datatables($query)->toJson();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("user.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::query()->create([
            "name"     => $request->name,
            "email"    => $request->email,
            "password" => bcrypt($request->password),
        ]);

        return redirect(route('users.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function show(string $id)
    {
        $user = User::query()->findOrFail($id);
        return view("user.show", compact("user"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::query()->findOrFail($id);

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
        ]);

        $fields = [
            "name"  => $request->name,
            "email" => $request->email,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8']]);
            $fields["password"] = bcrypt($request->password);
        }

        $user->update($fields);

        return redirect(route('users.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::query()->findOrFail($id);
        $user->delete();
        return redirect(route('users.index'));
    }

    /**
     * Bulk delete users by array of IDs.
     */
    public function delete(Request $request)
    {
        foreach ($request->ids as $id) {
            $user = User::find($id);
            if ($user === null) {
                continue;
            }
            $user->delete();
        }
        return response()->json(['success' => true]);
    }
}
