<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;
use Vanilo\Cart\Contracts\CartItem;
use Vanilo\Cart\Facades\Cart;
use Vanilo\Foundation\Models\MasterProductVariant;
use Vanilo\Product\Contracts\Product;

class CartController extends Controller
{
    public function __construct()
    {
    }

    public function add(Product $product)
    {
        Cart::addItem($product);
        flash()->success($product->name . ' has been added to cart');

        return redirect()->route('cart.show', [
            'euVat' => TaxRate::updateEuVat(Cart::model()),
        ]);
    }

    public function addVariant(MasterProductVariant $masterProductVariant)
    {
        Cart::addItem($masterProductVariant);
        flash()->success($masterProductVariant->name . ' has been added to cart');

        return redirect()->route('cart.show', [
            'euVat' => TaxRate::updateEuVat(Cart::model()),
        ]);
    }

    public function remove(CartItem $cart_item)
    {
        Cart::removeItem($cart_item);
        flash()->info($cart_item->getBuyable()->getName() . ' has been removed from cart');

        return redirect()->route('cart.show', [
            'euVat' => TaxRate::updateEuVat(Cart::model()),
        ]);
    }

    public function update(CartItem $cart_item, Request $request)
    {
        $isItemInCurrentCart = false;
        foreach (Cart::getItems() as $item) {
            if ($item->id == $cart_item->id) {
                $isItemInCurrentCart = true;
                break;
            }
        }

        if (!$isItemInCurrentCart) {
            flash()->warning('Meeh!');
            return redirect()->route('cart.show', [
                'euVat' => TaxRate::updateEuVat(Cart::model()),
            ]);
        }

        $qty = (int)$request->get('qty', $cart_item->getQuantity());
        $cart_item->quantity = $qty;
        $cart_item->save();

        flash()->info(__(':cart_item has been updated', ['cart_item' => $cart_item->getBuyable()->getName()]));

        return redirect()->route('cart.show', [
            'euVat' => TaxRate::updateEuVat(Cart::model()),
        ]);
    }

    public function show()
    {
        return view('cart.show', [
            'euVat' => TaxRate::updateEuVat(Cart::model()),
        ]);
    }
}
