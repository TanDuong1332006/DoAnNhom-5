<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['status' => 1, 'data' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'    => 'required|string|min:2|max:100',
            'phone'   => 'nullable|regex:/^[0-9]{9,11}$/',
            'address' => 'nullable|string|max:255',
            'birthday'=> 'nullable|date',
            'gender'  => 'nullable|in:1,2',
        ]);

        $user->update($request->only(['name', 'phone', 'address', 'birthday', 'gender']));

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật thông tin thành công!',
            'data'    => $user,
        ]);
    }
}
