<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Registration is a public endpoint.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', 'string', 'min:8', 'max:120'],
            'workspace_name' => ['required', 'string', 'max:120'],
        ];
    }
}
