<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\{
    AddCartItemRequest,
    UpdateCartItemRequest
};
use App\Http\Resources\{CartResource, CartItemResource};
use App\Models\{CartItem, Cart};
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
        $this->middleware('auth:sanctum');
        $this->authorizeResource(Cart::class, 'cart');
    }

    public function show(): CartResource
    {
        $user = auth()->user();
        $cart = $this->cartService->getActiveCartForUser($user->id);

        return new CartResource($cart->load('items.product'));
    }

    public function add(AddCartItemRequest $request): CartItemResource
    {
        $user   = $request->user();
        $cart   = $this->cartService->getActiveCartForUser($user->id);
        $item   = $this->cartService->addItem(
            $cart,
            $request->product_id,
            $request->quantity,
            $request->price,
            $request->metadata ?? []
        );

        return new CartItemResource($item);
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): CartItemResource
    {
        $this->authorize('updateItem', $item->cart);
        $updated = $this->cartService->updateItemQuantity($item, $request->quantity);

        return new CartItemResource($updated);
    }

    public function destroy(CartItem $item): JsonResponse
    {
        $this->authorize('removeItem', $item->cart);
        $this->cartService->removeItem($item);

        return response()->json(null, 204);
    }

    public function clear(): JsonResponse
    {
        $user = auth()->user();
        $cart = $this->cartService->getActiveCartForUser($user->id);
        $this->authorize('clear', $cart);
        $this->cartService->clearCart($cart);

        return response()->json(null, 204);
    }

    public function checkout(): JsonResponse
    {
        $user = auth()->user();
        $cart = $this->cartService->getActiveCartForUser($user->id);
        $this->authorize('checkout', $cart);

        $order = $this->cartService->checkout($cart, request()->only(['shipping_address', 'notes']));

        return response()->json([
            'message' => 'Order created successfully',
            'order'   => new \App\Http\Resources\OrderResource($order),
        ], 201);
    }
}