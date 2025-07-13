<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'parish' => 'in:St Andrew,Kingston,Clarendon,Hanover,St Elizabeth,Portland,St Thomas,St James,St Ann,St Catherine,Westmoreland,Manchester,St Mary,Trelawny',
        ];
    }
}
