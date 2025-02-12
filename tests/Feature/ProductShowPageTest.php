<?php

namespace Tests\Feature;

use App\Models\Product;
use Tests\TestCase;
use Vanilo\Product\Models\ProductState;

class ProductShowPageTest extends TestCase
{
    /** @test */
    public function it_can_show_active_product()
    {
        $product = Product::create([
            'name' => 'Dacia Logan',
            'sku' => 'DCA-LOGAN',
            'state' => ProductState::ACTIVE(),
            'price' => 11500
        ]);

        $user = \App\User::updateOrCreate([
            'email' => 'awesome@vanilo.com'
        ], [
            'name' => 'Awesome Web User',
            'password' => bcrypt('whatapassword'),
            'email' => 'awesome@vanilo.com'
        ]);

        $response = $this->actingAs($user)->get(route('product.show', $product->slug));

        $response->assertStatus(200);

        $response->assertSee('Dacia Logan');
        $response->assertSee(format_price($product->price));
        // $response->assertSee('Add To Cart');
        $response->assertSee(route('cart.add', $product));
    }
}
