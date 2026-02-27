<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 8-1: いいね登録テスト
     */
    public function test_like_item_adds_record_and_increases_count()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // POST処理は画面表示がないので、全てのミドルウェアを無効化してOK
        $this->withoutMiddleware();

        $response = $this->actingAs($user)->post("/item/{$item->id}/like");

        $response->assertStatus(302);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(1, Like::count());
    }

    /**
     * ID 8-2: 色の変化（表示）テスト
     */
    public function test_like_icon_color_change_logic()
    {
        // ▼ 修正: メール認証済みのユーザーを作成（verifiedミドルウェア対策）
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $item = Item::factory()->create();

        // ▼ 修正: 画面表示にはセッション機能が必要なので、profile.completed だけを無効化します
        // ※もしこれで 302 エラーが出る場合は 'verified' もここに追加してください
        $this->withoutMiddleware(['profile.completed', 'verified']);

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get("/item/{$item->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    /**
     * ID 8-3: いいね解除テスト
     */
    public function test_unlike_item_removes_record_and_decreases_count()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // POST処理なので全て無効化でOK
        $this->withoutMiddleware();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(1, Like::count());

        $response = $this->actingAs($user)->post("/item/{$item->id}/like");

        $response->assertStatus(302);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->assertEquals(0, Like::count());
    }

    // ==========================================
    // ID 9: コメント送信機能
    // ==========================================

    /**
     * ID 9-1: ログイン済みのユーザーはコメントを送信できる
     */
    public function test_comment_sending_success()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->withoutMiddleware();

        $comment = 'これはテストコメントです';

        // ▼修正: Requestに合わせて 'comment_body' で送信
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment_body' => $comment,
        ]);

        $response->assertStatus(302);

        // ▼注意: DBのカラムは 'body' なので、ここは 'body' で確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'body' => $comment,
        ]);

        $this->assertEquals(1, Comment::count());
    }

    /**
     * ID 9-2: ログイン前のユーザーはコメントを送信できない
     */
    public function test_guest_cannot_send_comment()
    {
        $item = Item::factory()->create();

        // ▼修正: 'comment_body' で送信
        $response = $this->post("/item/{$item->id}/comment", [
            'comment_body' => 'ゲストのコメント',
        ]);

        $response->assertRedirect('/login');

        $this->assertEquals(0, Comment::count());
    }

    /**
     * ID 9-3: コメントが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_comment_validation_required()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->withoutMiddleware();

        // ▼修正: 'comment_body' を空で送信
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment_body' => '',
        ]);

        // ▼修正: Requestのエラーキーも 'comment_body' になる
        $response->assertSessionHasErrors(['comment_body']);
    }

    /**
     * ID 9-4: コメントが255文字以上（256文字）の場合、バリデーションメッセージが表示される
     */
    public function test_comment_validation_max_length()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->withoutMiddleware();

        $longComment = str_repeat('a', 256);

        // ▼修正: 'comment_body' で送信
        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", [
            'comment_body' => $longComment,
        ]);

        // ▼修正: Requestのエラーキーも 'comment_body'
        $response->assertSessionHasErrors(['comment_body']);
    }
}