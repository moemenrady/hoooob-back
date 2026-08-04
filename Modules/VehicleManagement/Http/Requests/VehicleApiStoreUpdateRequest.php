<?php

namespace Modules\VehicleManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleApiStoreUpdateRequest extends FormRequest
{
    public function rules()
    {


        return [
            'brand_id' => 'required',
            'model_id' => 'required',
            'category_id' => 'required',
            'ownership' => Rule::requiredIf(empty($id)),
            'licence_plate_number' => 'required',
            'licence_expire_date' => 'required|date',
            'vin_number' => 'sometimes',
            'transmission' => 'sometimes',
           'parcel_weight_capacity' => 'sometimes|numeric|min:0|max:999999',
			'fuel_type' => 'nullable|sometimes',
          'color' => 'nullable|string|max:50',
            'front_car_licence_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'back_car_licence_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'front_driver_licence_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp,gif,svg|max:2048',
            'back_driver_licence_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp,gif,svg|max:2048',
			'other_documents' => 'nullable|sometimes',
        ];
    }

    public function authorize()
    {
        return Auth::check();
    }
  
  
      public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $front = $this->file('front_car_licence_image');
            $back = $this->file('back_car_licence_image');

            // Ensure both are present (rules already require), and not the same file content
            if ($front && $back) {
                try {
                    $frontHash = md5_file($front->getPathname());
                    $backHash = md5_file($back->getPathname());
                    if ($frontHash === $backHash) {
                        $validator->errors()->add('back_car_licence_image', 'Back license image must be different from the front image.');
                    }
                } catch (\Exception $e) {
                    // If hashing fails, do nothing; base validations still apply
                }
            }
        });
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    throw new \Illuminate\Http\Exceptions\HttpResponseException(
        response()->json([
            'errors' => $validator->errors(),
            'message' => 'Validation failed'
        ], 422)
    );
}
}
