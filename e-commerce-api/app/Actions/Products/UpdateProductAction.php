<?php

namespace App\Actions\Products;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProductAction
{
    use AsAction;

    public function handle(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }
}
