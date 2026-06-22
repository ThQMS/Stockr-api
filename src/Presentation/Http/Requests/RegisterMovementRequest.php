<?php

declare(strict_types=1);

namespace Stockr\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stockr\Domain\Inventory\ValueObjects\MovementType;
use Stockr\Infrastructure\Persistence\Eloquent\Models\MovementModel;
use Stockr\Presentation\Http\Requests\Concerns\ResolvesWorkspace;

final class RegisterMovementRequest extends FormRequest
{
    use ResolvesWorkspace;

    public function authorize(): bool
    {
        $workspace = $this->activeWorkspace();

        return $workspace !== null
            && (bool) $this->user()?->can('create', [MovementModel::class, $workspace]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(MovementType::class)],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'reference_code' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'O tipo de movimento é obrigatório.',
            'type.Illuminate\Validation\Rules\Enum' => 'Tipo inválido. Use: in, out, adjustment ou transfer.',
            'quantity.min' => 'A quantidade deve ser de pelo menos 1.',
        ];
    }
}
