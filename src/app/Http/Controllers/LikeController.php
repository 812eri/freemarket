<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    // storeメソッドを「登録/削除」の切り替え機能にします
   public function store(Request $request, $item_id)
    {
        $user_id = Auth::id();

        $existingLike = Like::where('user_id', $user_id)
                            ->where('item_id', $item_id)
                            ->first();

        if ($existingLike) {
            // ▼▼▼ 修正: IDを使わず、条件指定で削除します ▼▼▼
            Like::where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->delete();
        } else {
            Like::create([
                'user_id' => $user_id,
                'item_id' => $item_id,
            ]);
        }

        return back();
    }

    // destroyメソッドはそのままでも、使わなくてもOKです
    public function destroy(Request $request, $item_id)
    {
        Like::where('user_id', Auth::id())
            ->where('item_id', $item_id)
            ->delete();
        return back();
    }
}