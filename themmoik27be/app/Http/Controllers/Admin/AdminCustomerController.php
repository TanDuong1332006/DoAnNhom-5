<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('orders')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $customers = $query->paginate(15);
        return response()->json(['status' => 1, 'data' => $customers]);
    }

    public function toggleBlock(Request $request)
    {
        $request->validate(['id' => 'required|exists:customers,id']);
        $customer = Customer::findOrFail($request->id);
        $customer->update(['is_block' => !$customer->is_block]);

        $msg = $customer->is_block ? 'Đã khoá tài khoản.' : 'Đã mở khoá tài khoản.';
        return response()->json(['status' => 1, 'message' => $msg, 'data' => $customer]);
    }
}
