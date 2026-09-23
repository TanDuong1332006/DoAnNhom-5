<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DangKyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ho_va_ten'     => 'required|string|min:3|max:255',
            'email'         => 'required|email|max:255|unique:khach_hangs,email',
            'so_dien_thoai' => 'required|string|max:20',
            'mat_khau'      => 'required|string|min:6|max:255',
            'xac_nhan_mk'   => 'required|string|same:mat_khau',
        ];
    }

    public function messages(): array
    {
        return [
            'ho_va_ten.required'     => 'Vui lòng nhập họ và tên',
            'ho_va_ten.min'          => 'Họ và tên tối thiểu 3 ký tự',
            'ho_va_ten.max'          => 'Họ và tên tối đa 255 ký tự',
            'email.required'         => 'Vui lòng nhập email',
            'email.email'            => 'Email không đúng định dạng',
            'email.max'              => 'Email tối đa 255 ký tự',
            'email.unique'           => 'Email này đã được sử dụng',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại',
            'so_dien_thoai.max'      => 'Số điện thoại tối đa 20 ký tự',
            'mat_khau.required'      => 'Vui lòng nhập mật khẩu',
            'mat_khau.min'           => 'Mật khẩu tối thiểu 6 ký tự',
            'mat_khau.max'           => 'Mật khẩu tối đa 255 ký tự',
            'xac_nhan_mk.required'   => 'Vui lòng nhập xác nhận mật khẩu',
            'xac_nhan_mk.same'       => 'Xác nhận mật khẩu không khớp',
        ];
    }
}
