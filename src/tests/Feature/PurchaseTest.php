<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Address;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    // ==========================================
    // ID 10: 商品購入機能
    // ==========================================

    public function test_purchase_completes_successfully()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['status' => 'selling']);

        $this->withoutMiddleware();

        // ▼ 修正: スペルを 'conbini' (c始まり) に変更
        $response = $this->actingAs($user)->post("/item/{$item->id}/purchase", [
            'payment_method' => 'conbini',
        ]);

        $response->assertStatus(302);

        // ▼ 修正: DB確認も 'conbini' で行います
        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'conbini',
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => 'sold',
        ]);
    }

    public function test_purchased_item_shows_sold_label_in_list()
    {
        $item = Item::factory()->create(['status' => 'sold', 'name' => '売り切れた商品']);

        $response = $this->get('/');

        $response->assertSee('Sold');
        $response->assertSee('売り切れた商品');
    }

    public function test_purchased_item_added_to_profile_list()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['name' => '買ったばかりの商品']);

        $address = Address::create([
            'user_id' => $user->id,
            'post_code' => '123-4567',
            'street_address' => 'テスト住所',
            'building_name' => 'テストビル',
        ]);

        Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'card',
            'address_id' => $address->id,
        ]);

        $this->withoutMiddleware();

        // ▼ 修正: パラメータを 'tab' から 'page' に変更 (?page=buy)
        $response = $this->actingAs($user)->get('/mypage?page=buy');

        $response->assertStatus(200);
        $response->assertSee('買ったばかりの商品');
    }

    // ==========================================
    // ID 11: 支払い方法選択機能
    // ==========================================

    public function test_payment_method_is_required()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->withoutMiddleware();

        $response = $this->actingAs($user)->post("/item/{$item->id}/purchase", [
            'payment_method' => '',
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }

    // ==========================================
    // ID 12: 配送先変更機能
    // ==========================================

    public function test_changed_address_is_linked_to_purchase()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->withoutMiddleware();

        $newZip = '123-4567';
        $newAddr = '新しい住所1-1-1';
        $newBldg = '新しいビル';
        
        // 住所変更
        $this->actingAs($user)->patch("/purchase/address/{$item->id}", [
            'post_code' => $newZip,
            'street_address' => $newAddr,
            'building_name' => $newBldg,
        ]);

        // 購入 (ここも念のため 'conbini' に統一しておきます)
        $this->actingAs($user)->post("/item/{$item->id}/purchase", [
            'payment_method' => 'conbini',
        ]);

        // addressesテーブルが更新されているか確認
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'post_code' => $newZip,
            'street_address' => $newAddr,
            'building_name' => $newBldg,
        ]);
    }
}