<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;

class ProductTest extends TestCase
{

    /**
     * Test retrieving all products.
     */
    public function test_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                        '*' => [
                            'id',
                            'name',
                            'price',
                            'stock'
                    ]
                ]
            ]);
    }

    /**
     * Test creating a new product.
     */
    public function test_can_create_product()
    {
        $category = Category::factory()->create();

        $payload = [
            'name' => 'Test Product',
            'description' => 'A sample product description',
            'price' => 99.99,
            'stock' => 10,
            'category_id' => $category->id
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'Test Product',
                    'price' => 99.99
                ]
            ]);

        $this->assertDatabaseHas('products', $payload);
    }

    /**
     * Test retrieving a single product.
     */
    public function test_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                ],
            ]);
    }

    /**
     * Test updating a product.
     */
    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 199.99,
            'stock' => 50
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData
    );

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => [
                'id' => $product->id,
                'name' => 'Updated Product Name',
                'price' => 199.99
            ]
        ]);

    $this->assertDatabaseHas('products', $updateData);
}

/**
 *  Test deleting a product.
 */
public function test_can_delete_product()
{
    $product = Product::factory()->create();

    $response = $this->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
}

/**
 *  Test filtering products by price range.
 */
public function test_can_filter_products_by_price_range()
{
    Product::factory()->create(['price' => 50]);
    Product::factory()->create(['price' => 100]);
    Product::factory()->create(['price' => 200]);

    $response = $this->getJson('/api/products?price_min=50&price_max=150');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ])
        ->assertJsonCount(2, 'data.data'); // 2 products should be returned
}

/**
 *  Test filtering products by category.
 */
public function test_can_filter_products_by_category()
{
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->create(['category_id' => $category1->id]);
    Product::factory()->create(['category_id' => $category2->id]);

    $response = $this->getJson("/api/products?category_id={$category1->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ])
        ->assertJsonCount(1, 'data.data'); // Only 1 product should be returned
}

/**
 *  Test sorting products by price descending.
 */
public function test_can_sort_products_by_price_desc()
{
    Product::factory()->create(['price' => 50]);
    Product::factory()->create(['price' => 150]);
    Product::factory()->create(['price' => 100]);

    $response = $this->getJson('/api/products?sort_by=price&sort_order=desc');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ])
        ->assertJsonPath('data.0.price', 100)
        ->assertJsonPath('data.1.price', 50)
        ->assertJsonPath('data.2.price', 25);
}

}
