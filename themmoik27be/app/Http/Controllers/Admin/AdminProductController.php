<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category'])->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(15);
        return response()->json(['status' => 1, 'data' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|min:2|max:200',
            'description' => 'required|string|max:500',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'image'       => 'required|string|max:500',
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'name.required'        => 'Vui lòng nhập tên sản phẩm.',
            'description.required' => 'Vui lòng nhập mô tả sản phẩm.',
            'price.required'       => 'Vui lòng nhập giá sản phẩm.',
            'quantity.required'    => 'Vui lòng nhập số lượng.',
            'image.required'       => 'Vui lòng nhập URL hình ảnh.',
        ]);

        $product = Product::create([
            'category_id'  => $request->category_id,
            'name'         => $request->name,
            'slug'         => Str::slug($request->name) . '-' . Str::random(6),
            'description'  => $request->description,
            'content'      => $request->content,
            'price'        => $request->price,
            'sale_price'   => $request->sale_price,
            'quantity'     => $request->quantity,
            'image'        => $request->image,
            'is_featured'  => $request->get('is_featured', 0),
            'is_active'    => $request->get('is_active', 1),
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm sản phẩm thành công!',
            'data'    => $product->load('category'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'          => 'required|exists:products,id',
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|min:2|max:200',
            'description' => 'required|string|max:500',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'image'       => 'required|string|max:500',
        ]);

        $product = Product::findOrFail($request->id);
        $product->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'description' => $request->description,
            'content'     => $request->content,
            'price'       => $request->price,
            'sale_price'  => $request->sale_price,
            'quantity'    => $request->quantity,
            'image'       => $request->image,
            'is_featured' => $request->get('is_featured', $product->is_featured),
            'is_active'   => $request->get('is_active', $product->is_active),
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật sản phẩm thành công!',
            'data'    => $product->load('category'),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:products,id']);
        $product = Product::findOrFail($request->id);
        $product->delete();
        return response()->json(['status' => 1, 'message' => 'Xoá sản phẩm thành công!']);
    }
}
