<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SelectWorkspaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'workspace_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
