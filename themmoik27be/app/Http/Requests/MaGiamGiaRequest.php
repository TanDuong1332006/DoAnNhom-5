<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class MaGiamGiaRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Middleware Admin bảo vệ routes.

    protected function prepareForValidation(): void
    {
        if ($this->has('ma_code')) $this->merge(['ma_code' => strtoupper(trim((string) $this->ma_code))]);
    }

    public function rules(): array
    {
        $update = $this->is('api/admin/ma-giam-gia/update');
        return [
            'id' => $update ? 'required|integer|exists:ma_giam_gias,id' : 'prohibited',
            'ma_code' => $update ? 'prohibited' : ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('ma_giam_gias', 'ma_code')],
            'ten_giam_gia' => 'required|string|max:255',
            'loai_giam' => 'required|in:phan_tram,co_dinh',
            'gia_tri' => 'required|numeric|min:0|max:'.($this->loai_giam === 'phan_tram' ? '100' : '9999999999999.99'),
            'gia_tri_toi_thieu' => 'nullable|numeric|min:0|max:9999999999999.99',
            'giam_toi_da' => 'nullable|numeric|min:0|max:9999999999999.99',
            'so_luong' => 'required|integer|min:0|max:2147483647',
            'ngay_bat_dau' => 'nullable|date_format:Y-m-d',
            'ngay_ket_thuc' => ['nullable', 'date_format:Y-m-d', ...($this->ngay_bat_dau ? ['after_or_equal:ngay_bat_dau'] : [])],
            'trang_thai' => 'required|integer|in:0,1',
            'doi_tuong' => Schema::hasColumn('ma_giam_gias', 'doi_tuong') ? 'sometimes|in:all,new,selected' : 'prohibited',
            'gioi_han_moi_khach' => Schema::hasColumn('ma_giam_gias', 'gioi_han_moi_khach') ? 'nullable|integer|min:1|max:2147483647' : 'prohibited',
            'ids_khach_hang' => Schema::hasColumn('ma_giam_gias', 'ids_khach_hang') ? 'required_if:doi_tuong,selected|nullable|array|min:1|max:1000' : 'prohibited',
            'ids_khach_hang.*' => ['integer', 'distinct', Rule::exists('khach_hangs', 'id')->where(fn ($q) => $q->where('role', 'customer')->where('is_khoa', 0)->where('is_kich_hoat', 1))],
        ];
    }
}
