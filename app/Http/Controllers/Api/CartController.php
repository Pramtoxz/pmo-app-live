<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PublicSchema\Part;
use App\Helpers\ApiResponse;
use App\Helpers\PartHelper;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = $user->activeCart()->with(['items.part', 'items.product'])->first();

        if (!$cart) {
            return ApiResponse::success([
                'items' => [],
                'summary' => [
                    'totalItems' => 0,
                    'totalPrice' => 0
                ]
            ]);
        }

        return ApiResponse::success([
            'items' => $cart->items->map(function($item) {
                // Get stock info untuk isReady
                $stock = $item->part ? $item->part->getCurrentStock() : null;
                $isReady = $stock ? $stock->is_available : false;

                return [
                    'id' => (string) $item->id,
                    'partId' => (string) $item->kode_part,
                    'partNumber' => $item->kode_part,
                    'name' => PartHelper::getPartName($item->part, $item->product),
                    'image' => PartHelper::getPartImage($item->kode_part, $item->product, $item->part),
                    'price' => (float) $item->harga,
                    'quantity' => $item->qty,
                    'subtotal' => (float) $item->subtotal,
                    'isReady' => $isReady,
                ];
            }),
            'summary' => [
                'totalItems' => $cart->totalItems,
                'totalPrice' => (float) $cart->total
            ]
        ]);
    }

    public function add(AddToCartRequest $request)
    {
        $user = $request->user();
        $part = Part::where('kd_part', $request->partNumber)->firstOrFail();

        if (!$part->part_active) {
            return ApiResponse::error('Part ini sudah tidak diproduksi (discontinued) dan tidak bisa dipesan. Hanya untuk referensi harga.', 400);
        }

        $cart = $user->activeCart()->firstOrCreate([
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        $cartItem = $cart->items()->where('kode_part', $part->kd_part)->first();

        if ($cartItem) {
            return ApiResponse::error('Part sudah ada di keranjang', 400);
        }

        $cartItem = $cart->items()->create([
            'kode_part' => $part->kd_part,
            'qty' => $request->quantity,
            'harga' => $part->het,
            'diskon' => 0,
        ]);

        // Get stock info untuk response
        $stock = $part->getCurrentStock();
        $isReady = $stock ? $stock->is_available : false;

        return ApiResponse::success([
            'cartItemId' => (string) $cartItem->id,
            'totalItems' => $cart->fresh()->totalItems,
            'isReady' => $isReady,
            'message' => $isReady ? 'Item added to cart' : 'Pre-order item added to cart'
        ], $isReady ? 'Item added to cart' : 'Pre-order item added to cart');
    }

    public function update(UpdateCartRequest $request, $id)
    {
        $user = $request->user();
        $cartItem = CartItem::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'active');
        })->findOrFail($id);

        $cartItem->qty = $request->quantity;
        $cartItem->save();

        return ApiResponse::success(null, 'Cart updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $cartItem = CartItem::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'active');
        })->findOrFail($id);

        $cartItem->delete();

        return ApiResponse::success(null, 'Item removed from cart');
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = $user->activeCart()->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return ApiResponse::success(null, 'Cart cleared');
    }
}
