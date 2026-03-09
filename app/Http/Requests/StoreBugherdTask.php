<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBugherdTask extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required','string','max:10000'],
            'priority'    => ['nullable','in:critical,high,normal,low'],
            'status'      => ['nullable','in:backlog,doing,done'],
            'tag_names'   => ['nullable','array'],
            'tag_names.*' => ['string','max:50'],
        ];
    }
    public function authorize(): bool { return true; }
}
