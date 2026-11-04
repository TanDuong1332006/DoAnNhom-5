<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SanPhamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Route đã kiểm tra auth:sanctum và admin.
    }

    public function rules(): array
    {
        $id = $this->is('api/admin/san-pham/update') ? (int) $this->input('id') : null;

        return [
            'id' => $id !== null ? ['required', 'integer', 'exists:san_phams,id'] : ['prohibited'],
            'ten_san_pham' => ['required', 'string', 'max:255'],
            'slug_san_pham' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('san_phams')->ignore($id)],
            'ma_san_pham' => ['nullable', 'string', 'max:255', Rule::unique('san_phams')->ignore($id)],
            'id_danh_muc' => ['required', 'integer', 'exists:danh_mucs,id'],
            'id_thuong_hieu' => ['nullable', 'integer', 'exists:thuong_hieus,id'],
            'gia_ban' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'gia_khuyen_mai' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'lte:gia_ban'],
            'so_luong_ton' => ['sometimes', 'integer', 'min:0', 'max:2147483647'],
            'trang_thai' => ['sometimes', 'integer', Rule::in([0, 1, 2])],
            'is_noi_bat' => ['sometimes', 'boolean'],
            'is_flash_sale' => ['sometimes', 'boolean'],
            'mo_ta_ngan' => ['nullable', 'string', 'max:16000'],
            'mo_ta_chi_tiet' => ['nullable', 'string', 'max:100000'],
            'hinh_anh' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                $scheme = parse_url($value, PHP_URL_SCHEME);
                if (($scheme && !in_array(strtolower($scheme), ['http', 'https'], true)) || preg_match('/[<>\x00-\x1F]/', $value)) {
                    $fail('Đường dẫn ảnh không hợp lệ.');
                }
            }],
        ];
    }
}
