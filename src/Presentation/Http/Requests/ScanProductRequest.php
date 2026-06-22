<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Presentation\Http\Requests\Concerns\ResolvesWorkspace;

final class ScanProductRequest extends FormRequest
{
    use ResolvesWorkspace;

    public function authorize(): bool
    {
        $workspace = $this->activeWorkspace();

        return $workspace !== null
            && (bool) $this->user()?->can('view', [ProductModel::class, $workspace]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
        ];
    }
}
