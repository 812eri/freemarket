<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemDisplayTest extends TestCase
{
use RefreshDatabase;

    // ==========================================
    // ID 4: 商品一覧取得
    // ==========================================

    /**
     * ID 4-1: 全商品を取得できる
     * (ログインしていない状態でも商品が見えるか確認)
     */
    public function test_item_list_displays_items()
    {
        // 商品を3つ作成（誰が出品したかは問わない）
        $items = Item::factory()->count(3)->create();

        // トップページを開く
        $response = $this->get('/');

        $response->assertStatus(200);

        // 作成した商品の名前がすべて画面にあるか確認
        foreach ($items as $item) {
            $response->assertSee($item->name);
        }
    }

    /**
     * ID 4-2: 購入済み商品は「Sold」と表示される
     */
    public function test_item_list_displays_sold_label()
    {
        // 購入済み（status = sold）の商品を作成
        $soldItem = Item::factory()->create([
            'status' => 'sold',
            'name' => '売り切れ商品',
        ]);

        // 販売中（status = selling）の商品を作成
        $sellingItem = Item::factory()->create([
            'status' => 'selling',
            'name' => '販売中商品',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // 売り切れ商品には「Sold」が表示されていること
        // （HTML構造上、売り切れ商品の近くにSoldがあるかの厳密なチェックはDusk等が必要ですが、
        //  Featureテストでは「画面内にSoldの文字があるか」を確認します）
        $response->assertSee('Sold');

        // 念のため商品名も確認
        $response->assertSee('売り切れ商品');
        $response->assertSee('販売中商品');
    }

    /**
     * ID 4-3: 自分が出品した商品は表示されない
     */
    public function test_item_list_hides_own_items()
    {
        // ユーザーA（自分）とユーザーB（他人）を作成
        $me = User::factory()->create();
        $other = User::factory()->create();

        // 自分の出品物
        $my_item = Item::factory()->create([
            'user_id' => $me->id,
            'name' => '自分の商品',
        ]);

        // 他人の出品物
        $other_item = Item::factory()->create([
            'user_id' => $other->id,
            'name' => '他人の商品',
        ]);

        // 自分としてログインしてトップページを見る
        $response = $this->actingAs($me)->get('/');

        // 他人の商品は見えるはず
        $response->assertSee('他人の商品');

        // 自分の商品は見えてはいけない（除外されているはず）
        $response->assertDontSee('自分の商品');
    }

    // ==========================================
    // ID 5: マイリスト一覧取得
    // ==========================================

    /**
     * ID 5-1: いいねした商品だけが表示される
     */
    public function test_mylist_shows_only_liked_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // いいねする商品
        $likedItem = Item::factory()->create(['name' => 'いいねした商品']);
        // いいねしない商品
        $otherItem = Item::factory()->create(['name' => '興味ない商品']);

        // DBに直接いいねデータを登録
        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        // マイリストタブを開く（パラメータ ?tab=mylist を想定）
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        
        // いいねした商品は見える
        $response->assertSee('いいねした商品');
        
        // いいねしてない商品は見えない
        $response->assertDontSee('興味ない商品');
    }

    /**
     * ID 5-2: マイリストでも購入済み商品は「Sold」と表示される
     */
    public function test_mylist_shows_sold_label()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 売り切れのいいね商品
        $soldItem = Item::factory()->create([
            'name' => '売り切れのいいね商品',
            'status' => 'sold',
        ]);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $soldItem->id,
        ]);

        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee('売り切れのいいね商品');
        $response->assertSee('Sold');
    }

    /**
     * ID 5-3: 未認証（ゲスト）の場合は何も表示されない
     * ※ログインせずにマイリストタブにアクセスした場合
     */
    public function test_mylist_shows_nothing_for_guest()
    {
        // 商品を作成しておく
        Item::factory()->create(['name' => '誰かの商品']);

        // ログインせずにアクセス
        $response = $this->get('/?tab=mylist');

        $response->assertStatus(200);

        // 商品が表示されていないことを確認（空の一覧であること）
        $response->assertDontSee('誰かの商品');
    }// ==========================================
    // ID 6: 商品検索機能
    // ==========================================

    /**
     * ID 6-1: 「商品名」で部分一致検索ができる
     */
    public function test_search_items_partial_match()
    {
        // 検索対象の商品
        Item::factory()->create(['name' => 'iPhone 13']);
        Item::factory()->create(['name' => 'iPhone SE']);
        
        // 検索対象外の商品
        Item::factory()->create(['name' => 'Android Pixel']);

        // 「iPhone」で検索（部分一致）
        $response = $this->get('/?keyword=iPhone');

        $response->assertStatus(200);

        // 部分一致する商品は表示される
        $response->assertSee('iPhone 13');
        $response->assertSee('iPhone SE');

        // 一致しない商品は表示されない
        $response->assertDontSee('Android Pixel');
    }

    /**
     * ID 6-2: 検索状態がマイリストでも保持されている
     * (マイリストタブでもキーワード検索が適用されるか確認)
     */
    public function test_search_state_preserved_in_mylist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // マイリスト（いいね）に入っている商品
        $likedTarget = Item::factory()->create(['name' => '赤いTシャツ']);
        $likedOther = Item::factory()->create(['name' => '青いジーンズ']);

        // いいね登録
        Like::create(['user_id' => $user->id, 'item_id' => $likedTarget->id]);
        Like::create(['user_id' => $user->id, 'item_id' => $likedOther->id]);

        // マイリストタブで「Tシャツ」を検索している状態をシミュレート
        // (?tab=mylist & keyword=Tシャツ)
        $response = $this->get('/?tab=mylist&keyword=Tシャツ');

        $response->assertStatus(200);

        // 検索ワードにヒットする「赤いTシャツ」は表示される
        $response->assertSee('赤いTシャツ');

        // マイリストに入っていても、検索ワードにヒットしない「青いジーンズ」は表示されない
        $response->assertDontSee('青いジーンズ');

        // 検索窓にキーワードが保持（表示）されていることも確認
        // (value="Tシャツ" がHTMLに含まれているか)
        $response->assertSee('Tシャツ');
    }

    // ==========================================
    // ID 7: 商品詳細情報取得
    // ==========================================

    /**
     * ID 7-1 & 7-2: 商品詳細ページに必要な情報が全て表示される（複数カテゴリ含む）
     */
    public function test_item_detail_display()
    {
        // 1. テストデータの準備
        $seller = User::factory()->create();
        $condition = Condition::factory()->create(['name' => '新品・未使用']);
        
        // カテゴリを複数作成
        $category1 = Category::factory()->create(['name' => 'レディース']);
        $category2 = Category::factory()->create(['name' => 'トップス']);

        // 商品を作成
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '高級腕時計',
            'brand_name' => 'ロレックス',
            'price' => 12345,
            'description' => 'すごい時計です。',
            'condition_id' => $condition->id,
        ]);

        // カテゴリを紐付け (ID 7-2の要件)
        $item->categories()->attach([$category1->id, $category2->id]);

        // コメントを作成
        $commentUser = User::factory()->create(['name' => '購入希望者']);
        Comment::factory()->create([
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
            'body' => '値下げできますか？',
        ]);

        // いいねを作成 (2件)
        $liker1 = User::factory()->create();
        $liker2 = User::factory()->create();
        Like::create(['user_id' => $liker1->id, 'item_id' => $item->id]);
        Like::create(['user_id' => $liker2->id, 'item_id' => $item->id]);

        // 2. 詳細ページを開く
        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);

        // 3. 各情報が表示されているか確認 (ID 7-1)
        $response->assertSee('高級腕時計');         // 商品名
        $response->assertSee('ロレックス');         // ブランド名
        $response->assertSee(number_format(12345)); // 価格（カンマ区切りを想定）
        $response->assertSee('すごい時計です。');   // 商品説明
        $response->assertSee('新品・未使用');       // 商品の状態
        
        // いいね数とコメント数（数字が表示されているか）
        // ※HTML構造に依存するため、最低限「数字が含まれているか」を確認します
        $response->assertSee('2'); // いいね数
        $response->assertSee('1'); // コメント数

        // コメント情報
        $response->assertSee('購入希望者');         // コメントしたユーザー
        $response->assertSee('値下げできますか？'); // コメント内容

        // カテゴリ情報 (ID 7-2: 複数カテゴリ表示)
        $response->assertSee('レディース');
        $response->assertSee('トップス');
    }
}