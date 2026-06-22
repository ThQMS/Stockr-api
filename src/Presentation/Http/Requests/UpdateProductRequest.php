<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Presentation\Http\Requests\Concerns\ResolvesWorkspace;

final class UpdateProductRequest extends FormRequest
{
    use ResolvesWorkspace;

    public function authorize(): bool
    {
        $workspace = $this->activeWorkspace();

        return $workspace !== null
            && (bool) $this->user()?->can('update', [ProductModel::class, $workspace]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
