<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', 1)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 1,
            'data'   => $categories,
        ]);
    }
}
