<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductImageController extends Controller
{
    private function result(SanPham $product)
    {
        return response()->json(['status' => 1, 'data' => $product->fresh()->load(['anhSanPhams' => fn ($q) => $q->orderBy('thu_tu')->orderBy('id')])]);
    }

    public function upload(Request $request, int $product)
    {
        $request->validate([
            'image_kind' => ['sometimes', 'in:photo,illustration'],
            'images' => ['required', 'array', 'min:1', 'max:3'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ]);
        $stored = [];
        try {
            $record = DB::transaction(function () use ($request, $product, &$stored) {
                $record = SanPham::lockForUpdate()->findOrFail($product);
                if ($record->anhSanPhams()->count() + count($request->file('images')) > 20) {
                    throw ValidationException::withMessages(['images' => 'Mỗi sản phẩm tối đa 20 ảnh gallery.']);
                }
                $order = (int) $record->anhSanPhams()->max('thu_tu');
                foreach ($request->file('images') as $file) {
                    $name = ($request->input('image_kind') === 'illustration' ? 'illustration-' : '').Str::uuid().'.'.$file->extension();
                    $path = $file->storeAs('products/'.$record->id, $name, 'public');
                    if (!$path) throw new \RuntimeException('Không lưu được file ảnh.');
                    $stored[] = $path;
                    // URL theo host API đang chạy, không phụ thuộc APP_URL hoặc symlink local.
                    $url = $request->getSchemeAndHttpHost().'/api/product-images/'.$record->id.'/'.$name;
                    DB::table('anh_san_phams')->insert(['id_san_pham' => $record->id, 'duong_dan_anh' => $url, 'thu_tu' => ++$order]);
                    if (!$record->hinh_anh) { $record->hinh_anh = $url; $record->save(); }
                }
                return $record;
            });
        } catch (\Throwable $error) {
            // Chỉ dọn các file vừa tạo nếu transaction thất bại.
            Storage::disk('public')->delete($stored);
            throw $error;
        }
        return $this->result($record);
    }

    public function arrange(Request $request, int $product)
    {
        $data = $request->validate([
            'ids' => ['present', 'array', 'max:20'], 'ids.*' => ['required', 'integer', 'distinct'],
            'main_id' => ['sometimes', 'nullable', 'integer'],
        ]);
        $record = DB::transaction(function () use ($data, $product) {
            $record = SanPham::lockForUpdate()->findOrFail($product);
            $images = $record->anhSanPhams()->get()->keyBy('id');
            $ids = array_map('intval', $data['ids']);
            if (count($ids) !== $images->count() || array_diff($ids, $images->keys()->all())
                || (isset($data['main_id']) && !$images->has($data['main_id']))) {
                throw ValidationException::withMessages(['ids' => 'Danh sách ảnh đã thay đổi hoặc không thuộc sản phẩm này. Hãy tải lại.']);
            }
            foreach ($ids as $order => $id) DB::table('anh_san_phams')->where('id', $id)->where('id_san_pham', $product)->update(['thu_tu' => $order]);
            if (array_key_exists('main_id', $data)) {
                $record->hinh_anh = isset($data['main_id']) ? $images[$data['main_id']]->duong_dan_anh : null;
                $record->save();
            }
            return $record;
        });
        return $this->result($record);
    }

    public function remove(int $product, int $image)
    {
        $record = DB::transaction(function () use ($product, $image) {
            $record = SanPham::lockForUpdate()->findOrFail($product);
            $photo = $record->anhSanPhams()->findOrFail($image);
            DB::table('anh_san_phams')->where('id', $image)->where('id_san_pham', $product)->delete();
            if ($record->hinh_anh === $photo->duong_dan_anh) {
                $record->hinh_anh = $record->anhSanPhams()->orderBy('thu_tu')->orderBy('id')->value('duong_dan_anh');
                $record->save();
            }
            // Giữ file public để ảnh trong lịch sử đơn hàng không bị mất.
            return $record;
        });
        return $this->result($record);
    }

    public function image(int $product, string $filename)
    {
        abort_unless(preg_match('/^(?:illustration-)?[a-f0-9-]{36}\.(?:jpg|jpeg|png|webp)$/D', $filename), 404);
        $path = 'products/'.$product.'/'.$filename;
        abort_unless(Storage::disk('public')->exists($path), 404);
        return Storage::disk('public')->response($path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=86400']);
    }
}
