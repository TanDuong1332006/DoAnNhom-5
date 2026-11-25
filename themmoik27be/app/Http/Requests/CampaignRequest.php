<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'title'=>'required|string|max:160','body'=>'required|string|max:5000',
            'kind'=>'required|in:general,promotion,thanks,voucher','voucher_id'=>'nullable|integer|exists:ma_giam_gias,id',
            'send_at'=>'nullable|date_format:Y-m-d\TH:i','recheck'=>'sometimes|boolean',
            'filters'=>'sometimes|array:period,orders,spend','filters.period'=>'sometimes|in:week,month,year,all',
            'filters.orders'=>'sometimes|array:min,max','filters.orders.min'=>'nullable|integer|min:0|max:1000000',
            'filters.orders.max'=>'nullable|integer|min:0|max:1000000',
            'filters.spend'=>'sometimes|array:min,max','filters.spend.min'=>'nullable|numeric|min:0|max:9999999999999',
            'filters.spend.max'=>'nullable|numeric|min:0|max:9999999999999',
            'preview_hash'=>'sometimes|string|size:64','request_key'=>'sometimes|uuid',
        ];
    }
    public function after(): array
    {
        return [function ($validator) {
            foreach (['orders','spend'] as $key) {
                $min=$this->input("filters.$key.min"); $max=$this->input("filters.$key.max");
                if (is_numeric($min) && is_numeric($max) && $min > $max) $validator->errors()->add("filters.$key.max",'Giá trị tối đa phải lớn hơn hoặc bằng tối thiểu.');
            }
            if ($this->kind === 'voucher' && !$this->voucher_id) $validator->errors()->add('voucher_id','Vui lòng chọn voucher.');
            if ($this->kind === 'general' && $this->voucher_id) $validator->errors()->add('voucher_id','Thông báo có voucher phải thuộc nhóm khuyến mãi/tri ân/voucher.');
        }];
    }
}
