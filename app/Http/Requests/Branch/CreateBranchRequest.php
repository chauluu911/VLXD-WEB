<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\ValidatorHelper;

class CreateBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shopId' => 'required|numeric',
            'name' => 'required|string',
            'phone1' => 'required',
            'areaProvince' => 'required|numeric',
            'areaDistrict' => 'required|numeric',
            'areaWard' => 'required|numeric',
        ];
    }
}
