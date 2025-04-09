<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ProductResource;
use App\Actions\Products\FilterProductsAction;

class ProductCrudAction
{
    use AsAction;

    /**
     * Handles product CRUD operations dynamically.
     *
     * @param mixed $Product - Can be a Request or an array.
     * @param int|null $id - Product ID (for update/delete).
     * @param string $action - The action to perform (index, store, show, update, destroy, filter).
     * @param array $filters - Filtering parameters.
     * @return mixed
     */
    public function handle(mixed $Product, ?int $id = null, string $action = 'index', array $filters = []): mixed
    {
        // Determine if the input is a Request or an array
        $isApiRequest = $Product instanceof Request;
        $validatedData = $isApiRequest ? $Product->all() : $Product;

        // Perform the requested action
        return match ($action) {
            'index' => empty($filters) ? $this->index() : $this->filter($filters),
            'store' => $this->store($validatedData),
            'show' => $this->show($id),
            'update' => $this->update($validatedData, $id),
            'destroy' => $this->destroy($id),
            'filter' => $this->filter($filters),
            default => throw new \InvalidArgumentException("Invalid action: $action"),
        };
    }

    /**
     * List all products if no filtering parameters are present.
     */
    public function index(): mixed
    {
        $products = Product::all();
        return ProductResource::collection($products);
    }

    /**
     * Store a new product.
     */
    public function store(array $data): mixed
    {
        $product = Product::create($data);
        return new ProductResource($product);
    }

    /**
     * Retrieve a specific product.
     */
    public function show(?int $id): mixed
    {
        if (!$id) {
            throw new \InvalidArgumentException("Product ID is required for 'show' action.");
        }

        return new ProductResource(Product::findOrFail($id));
    }

    /**
     * Update an existing product.
     */
    public function update(array $data, ?int $id): mixed
    {
        if (!$id) {
            throw new \InvalidArgumentException("Product ID is required for 'update' action.");
        }

        $product = Product::findOrFail($id);
        $product->update($data);

        return new ProductResource($product);
    }

    /**
     * Delete a product.
     */
    public function destroy(?int $id): void
    {
        if (!$id) {
            throw new \InvalidArgumentException("Product ID is required for 'destroy' action.");
        }

        $product = Product::findOrFail($id);
        $product->delete();
    }

    /**
     * Apply filtering only if query parameters are present.
     */
    public function filter(array $filters = [])
    {
        $query = FilterProductsAction::run($filters);

        $products = $query->get();

        return ProductResource::collection($products);
    }

    /**
     * Handle as a Controller.
     */
    public function asController(Request $request, ?int $id = null)
    {
        $action = $request->method() === 'GET'
            ? ($id ? 'show' : 'index')
            : match ($request->method()) {
                'POST' => 'store',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'destroy',
                default => throw new \InvalidArgumentException("Invalid HTTP method."),
            };

        return $this->handle($request, $id, $action);
    }
}
