<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->where('is_active', 1);

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Lọc theo giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['created_at', 'price', 'name', 'views'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json([
            'status' => 1,
            'data'   => $products,
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images'])
            ->where('is_active', 1)
            ->findOrFail($id);

        // Tăng lượt xem
        $product->increment('views');

        // SP liên quan
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', 1)
            ->limit(4)
            ->get();

        return response()->json([
            'status'  => 1,
            'data'    => $product,
            'related' => $related,
        ]);
    }

    public function featured()
    {
        $products = Product::with(['category'])
            ->where('is_active', 1)
            ->where('is_featured', 1)
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 1,
            'data'   => $products,
        ]);
    }

    public function newArrivals()
    {
        $products = Product::with(['category'])
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 1,
            'data'   => $products,
        ]);
    }
}
