<x-mail::message>
# Estoque baixo detectado

O produto **{{ $sku }}** atingiu o nível de reposição.

- Estoque atual: **{{ $currentStock }}**
- Nível de reposição: **{{ $reorderLevel }}**

Recomendamos registrar uma entrada (movimento `in`) o quanto antes.

<x-mail::button :url="config('app.url')">
Abrir Stockr
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
