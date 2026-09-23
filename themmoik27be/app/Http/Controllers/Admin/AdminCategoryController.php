<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return response()->json(['status' => 1, 'data' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|min:2|max:100',
            'image' => 'nullable|string|max:255',
        ], ['name.required' => 'Vui lòng nhập tên danh mục.']);

        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . '-' . Str::random(4),
            'description' => $request->description,
            'image'       => $request->image,
            'is_active'   => $request->get('is_active', 1),
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm danh mục thành công!',
            'data'    => $category,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'   => 'required|exists:categories,id',
            'name' => 'required|string|min:2|max:100',
        ]);

        $category = Category::findOrFail($request->id);
        $category->update([
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $request->image ?? $category->image,
            'is_active'   => $request->get('is_active', $category->is_active),
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật danh mục thành công!',
            'data'    => $category,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:categories,id']);
        $category = Category::findOrFail($request->id);

        if ($category->products()->count() > 0) {
            return response()->json(['status' => 0, 'message' => 'Không thể xoá danh mục còn sản phẩm.']);
        }

        $category->delete();
        return response()->json(['status' => 1, 'message' => 'Xoá danh mục thành công!']);
    }
}
