<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [];
        foreach (config('app.locales') as $locale) {
            $rules["{$locale}.title"] = 'required|string|max:255';
            $rules["{$locale}.slug"] = 'required|string|max:255|unique:page_translations,slug';
            $rules["{$locale}.desc"] = 'required|string';
            $rules["{$locale}.keywords"] = 'nullable|string';
        }

        // Get valid type IDs from config
        $validTypeIds = collect(config('pageTypes'))->pluck('id')->toArray();
        $rules['type_id'] = 'required|in:'.implode(',', $validTypeIds);
        $rules['active'] = 'nullable|boolean';

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => __(':attribute is required.'),
            'string' => __(':attribute must be a string.'),
            'max' => __(':attribute must not exceed :max characters.'),
            'unique' => __('The :attribute has already been taken.'),
            'in' => __('The selected :attribute is invalid.'),
        ];
    }

    public function attributes()
    {
        $attributes = [
            'type_id' => __('Page type'),
            'active' => __('Active status'),
        ];

        foreach (config('app.locales') as $locale) {
            $languageName = __('admin.locale_'.$locale);
            $attributes["{$locale}.title"] = __('Title (:language)', ['language' => $languageName]);
            $attributes["{$locale}.slug"] = __('URL keyword (:language)', ['language' => $languageName]);
            $attributes["{$locale}.desc"] = __('Description (:language)', ['language' => $languageName]);
            $attributes["{$locale}.keywords"] = __('Keywords (:language)', ['language' => $languageName]);
        }

        return $attributes;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $seenSlugs = [];

            foreach (config('app.locales') as $locale) {
                $slug = $this->input("{$locale}.slug");

                if ($slug === null || $slug === '') {
                    continue;
                }

                $normalized = Str::lower(trim($slug));

                if (isset($seenSlugs[$normalized])) {
                    $validator->errors()->add("{$locale}.slug", __('URL keyword must be unique across all languages.'));
                    $validator->errors()->add($seenSlugs[$normalized].'.slug', __('URL keyword must be unique across all languages.'));
                } else {
                    $seenSlugs[$normalized] = $locale;
                }
            }
        });
    }
}
