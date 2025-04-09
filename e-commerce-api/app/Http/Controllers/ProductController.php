<?php

namespace App\Http\Controllers;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Actions\ProductCrudAction;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    // List all products using ProductCrudAction
    public function index(Request $request)
    {
        // Gather filters (including per_page)
        $filters = [
            'category_id' => $request->query('category_id'),
            'price_min'   => $request->query('price_min'),
            'price_max'   => $request->query('price_max'),
            'name'        => $request->query('name'),
            'sort_by'     => $request->query('sort_by'),
        ];

        // Remove null values so we only act on actual filter values
        $filters = array_filter($filters, fn($v) => !is_null($v));

        $action = count($filters) > 0 ? 'filter' : 'index';

        // Run the action
        $products = ProductCrudAction::run([], null, $action, $filters);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data'    => $products,
        ], 200);
    }


    // Retrieve a specific product using ProductCrudAction
    public function show(Product $product)
    {
        $productData = ProductCrudAction::run(new Request(), $product->id , 'show');

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $productData
        ], 200);
    }

    // Create a new product using CreateProductAction
    public function store(ProductRequest $request)
    {
        $product = CreateProductAction::run($request->validated() , 'store');

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product)
        ], 201);
    }

    // Update a product using UpdateProductAction
    public function update(ProductRequest $request, Product $product)
    {
        $updatedProduct = UpdateProductAction::run($product, $request->validated(), 'update');

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($updatedProduct)
        ], 200);
    }

    // Delete a product using ProductCrudAction
    public function destroy(Product $product)
    {
        $deleteRequest = new Request(['_method' => 'delete']);
        ProductCrudAction::run($deleteRequest, $product->id, 'destroy');

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }


/*
    public function __invoke(Request $request, $id = null)
    {
        return ProductCrudAction::run($request, $id);
    }
*/

}
