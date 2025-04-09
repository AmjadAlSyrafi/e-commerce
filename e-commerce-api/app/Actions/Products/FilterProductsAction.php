<?php

namespace App\Actions\Products;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class FilterProductsAction
{
    use AsAction;

    /**
     * Filters products dynamically based on provided parameters.
     *
     * @param array $filters Associative array of filters (e.g., ['price_min' => 100, 'sort_by' => 'price']).
     * @return $query
     */
    public function handle(array $filters = [])
    {
        $query = Product::query();

        foreach ($filters as $key => $value) {
            match ($key) {
                'category_id'      => $query->where('category_id', $value),
                'price_min'        => $query->where('price', '>=', $value),
                'price_max'        => $query->where('price', '<=', $value),
                'name'             => $query->where('name', 'like', "%{$value}%"),
                'sort_by'          => $query->orderBy($value, $filters['sort_order'] ?? 'asc'),
                default            => null,
            };
        }

        return $query;
    }
}
