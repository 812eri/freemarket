<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);

        if ($item->is_sold) {
            return redirect()->route('home')->with('error', 'この商品はすでに購入されています。');
        }

        $address = Address::where('user_id', Auth::id())->first();

        $selectedPaymentMethodCode = request()->query('payment_method_code');
        $selectedPaymentMethod = $this->getPaymentMethodDisplay($selectedPaymentMethodCode);

        return view('purchase.show', compact('item', 'address', 'selectedPaymentMethod', 'selectedPaymentMethodCode'));
    }

    public function store(Request $request, $item_id)
    {
        $request->validate([
            'payment_method' => 'required|in:conbini,credit'
        ]);

        $item = Item::findOrFail($item_id);

        if ($item->is_sold) {
            return redirect()->route('home')->with('error', 'この商品はすでに購入されています。');
        }

        $address = Address::where('user_id', Auth::id())->first();
        if (!$address) {
            return back()->withErrors(['address' => '配送先を設定してください。']);
        }

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'conbini') {
            DB::transaction(function () use ($item, $address, $paymentMethod) {
                $item->update([
                    'is_sold' => true,
                    'buyer_id' => Auth::id(),
                    'status' => 'sold',
                ]);

                Purchase::create([
                    'user_id'        => Auth::id(),
                    'item_id'        => $item->id,
                    'address_id'     => $address->id,
                    'payment_method' => $paymentMethod,
                    'status'         => 'waiting',
                ]);
            });

            return redirect()->route('home')->with('success', '商品の購入手続きが完了しました。コンビニでのお支払いをお願いします。');
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'locale' => 'ja',
            'success_url' => route('purchase.complete') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.show', $item->id),
            'metadata' => [
                'item_id' => $item->id,
                'user_id' => Auth::id(),
            ],
        ]);

        return redirect($session->url, 303);
    }

    public function complete(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('home')->with('error', '決済情報が見つかりませんでした。');
        }

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('home')->with('error', '決済が完了していません。');
            }

            $itemId = $session->metadata->item_id;
            $userId = $session->metadata->user_id;

            $item = Item::findOrFail($itemId);
            $address = Address::where('user_id', $userId)->first();


            DB::transaction(function () use ($item, $userId, $address, $session) {
                if ($item->is_sold) {
                    return;
                }

                $item->update([
                    'is_sold' => true,
                    'buyer_id' => $userId,
                    'status' => 'sold',
                ]);

                Purchase::create([
                    'user_id'        => $userId,
                    'item_id'        => $item->id,
                    'address_id'     => $address->id ?? null,
                    'payment_method' => $session->payment_method_types[0] ?? 'credit',
                    'status'         => 'paid',
                ]);
            });

            return redirect()->route('home')->with('success', '商品の購入が完了しました。');

        } catch (\Exception $e) {
            \Log::error('Stripe購入完了処理エラー: ' . $e->getMessage());
            return redirect()->route('home')->with('error', '購入処理中にエラーが発生しました。');
        }
    }

    private function getPaymentMethodDisplay($code)
    {
        return [
            'conbini' => 'コンビニ支払い',
            'credit'  => 'カード支払い',
        ][$code] ?? '未選択';
    }
}