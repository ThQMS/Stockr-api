<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Stockr\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Stockr\Presentation\Http\Requests\Concerns\ResolvesWorkspace;

final class StoreProductRequest extends FormRequest
{
    use ResolvesWorkspace;

    public function authorize(): bool
    {
        $workspace = $this->activeWorkspace();

        return $workspace !== null
            && (bool) $this->user()?->can('create', [ProductModel::class, $workspace]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['sometimes', 'nullable', 'string', 'max:30'],
            'barcode' => ['sometimes', 'nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:200'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:10'],
            'initial_stock' => ['sometimes', 'integer', 'min:0'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'cost_price.required' => 'O preço de custo é obrigatório.',
            'cost_price.min' => 'O preço de custo não pode ser negativo.',
            'sku.max' => 'O SKU deve ter no máximo 30 caracteres.',
        ];
    }
}
