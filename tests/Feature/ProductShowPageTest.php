<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vanilo\Product\Models\ProductState;

class ProductShowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function it_can_show_active_product()
    {
        $product = Product::create([
            'name' => 'Dacia Logan',
            'sku' => 'DCA-LOGAN',
            'state' => ProductState::ACTIVE(),
            'price' => 11500
        ]);

        $user = \App\User::create([
            'name' => 'Awesome Web User',
            'password' => bcrypt('whatapassword'),
            'email' => 'awesome@vanilo.com'
        ]);

        $response = $this->actingAs($user)->get(route('product.show', $product->slug));

        $response->assertStatus(200);

        $response->assertSee('Dacia Logan');
        $response->assertSee(format_price($product->price));
        $response->assertSee('Deploy');
        $response->assertSee(route('cart.add', $product));
    }
}
