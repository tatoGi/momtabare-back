<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Assuming anyone can make a product request; you may adjust this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // Base attributes
            'category_id' => 'nullable|exists:categories,id',
            'active' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048', // Assuming images are optional but must be of certain type and size
            'price' => 'required|string|min:0', // Ensure price is required and is a valid number
        ];
    
        // Validation rules for translatable attributes
        foreach (config('app.locales') as $locale) {
            $rules["{$locale}.title"] = 'required|max:255';
            $rules["{$locale}.description"] = 'nullable|string|max:1000';
            $rules["{$locale}.style"] = 'nullable|string|max:255';
        }
    
        return $rules;
    }
}
