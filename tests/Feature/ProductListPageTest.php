<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Konekt\AppShell\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Vanilo\Product\Models\ProductState;

class ProductListPageTest extends DuskTestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        $this->user = User::create([
            'name' => 'Awesome Web User',
            'password' => bcrypt('whatapassword'),
            'email' => 'awesome@vanilo.com'
        ]);
    }

    /** @test */
    public function it_can_list_active_products()
    {
        $this->browse(function (Browser $browser) {

            $productA = Product::create([
                'name' => 'Audi A4',
                'sku' => 'AUD-A4',
                'state' => ProductState::ACTIVE(),
                'price' => 11500
            ]);

            $productB = Product::create([
                'name' => 'BMW M3',
                'sku' => 'BMW-F31',
                'state' => ProductState::ACTIVE(),
                'price' => 14500
            ]);

            $productC = Product::create([
                'name' => 'Daewoo Tico',
                'sku' => 'DWO-TICO',
                'state' => ProductState::ACTIVE(),
                'price' => 1500
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('product.index'))
                ->assertSee('Audi A4')
                ->assertSee('BMW M3')
                ->assertSee('Daewoo Tico')
                ->assertSee(format_price($productA->price))
                ->assertSee(format_price($productB->price))
                ->assertSee(format_price($productC->price));
        });
    }

    /** @test */
    public function it_can_list_only_active_products()
    {
        $this->browse(function (Browser $browser) {

            Product::create([
                'name' => 'Audi A3',
                'sku' => 'AUD-A3',
                'state' => ProductState::ACTIVE(),
                'price' => 15500
            ]);

            Product::create([
                'name' => 'BMW x6',
                'sku' => 'BMW-F31',
                'price' => 22000
            ]);

            $browser
                ->loginAs($this->user)
                ->visit(route('product.index'))
                ->assertSee('Audi A3')
                ->assertDontSee('BMW X6');
        });
    }
}
