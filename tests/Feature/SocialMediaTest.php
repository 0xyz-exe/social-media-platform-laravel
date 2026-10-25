<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Hashtag;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard_timeline(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Social Home & Feed');
        $response->assertSee($user->name);
    }

    public function test_user_can_publish_post_and_hashtags_extracted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'content' => 'Building microservices with #laravel and #php is great!',
            'visibility' => 'public',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'content' => 'Building microservices with #laravel and #php is great!',
        ]);

        $this->assertDatabaseHas('hashtags', ['name' => 'laravel', 'posts_count' => 1]);
        $this->assertDatabaseHas('hashtags', ['name' => 'php', 'posts_count' => 1]);
    }

    public function test_user_can_delete_own_post_and_decrements_hashtag_count(): void
    {
        $user = User::factory()->create();
        $hashtag = Hashtag::create(['name' => 'devops', 'posts_count' => 1]);

        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'Deploying to cloud with #devops',
        ]);
        $post->hashtags()->attach($hashtag->id);

        $response = $this->actingAs($user)->delete("/posts/{$post->id}");
        $response->assertRedirect('/feed');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertEquals(0, $hashtag->fresh()->posts_count);
    }

    public function test_user_cannot_delete_other_users_post(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $post = Post::create([
            'user_id' => $userA->id,
            'content' => 'Alice public post',
        ]);

        $response = $this->actingAs($userB)->delete("/posts/{$post->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_user_can_like_and_unlike_post(): void
    {
        $user = User::factory()->create();
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'Awesome tech release',
            'likes_count' => 0,
        ]);

        // Like
        $this->actingAs($user)->post("/posts/{$post->id}/like");
        $this->assertEquals(1, $post->fresh()->likes_count);
        $this->assertDatabaseHas('likes', ['user_id' => $user->id, 'post_id' => $post->id]);

        // Unlike
        $this->actingAs($user)->post("/posts/{$post->id}/like");
        $this->assertEquals(0, $post->fresh()->likes_count);
        $this->assertDatabaseMissing('likes', ['user_id' => $user->id, 'post_id' => $post->id]);
    }

    public function test_user_can_comment_on_post(): void
    {
        $user = User::factory()->create(['name' => 'Commenter']);
        $post = Post::create([
            'user_id' => $user->id,
            'content' => 'Initial post content',
            'comments_count' => 0,
        ]);

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", [
            'content' => 'Great insight!',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, $post->fresh()->comments_count);
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'Great insight!',
        ]);
    }

    public function test_user_can_follow_and_unfollow_another_user(): void
    {
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);

        // Alice follows Bob
        $responseFollow = $this->actingAs($alice)->post("/users/{$bob->id}/follow");
        $responseFollow->assertSessionHas('success');
        $this->assertTrue($alice->isFollowing($bob));

        // Alice unfollows Bob
        $responseUnfollow = $this->actingAs($alice)->post("/users/{$bob->id}/follow");
        $responseUnfollow->assertSessionHas('success');
        $this->assertFalse($alice->isFollowing($bob));
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post("/users/{$user->id}/follow");
        $response->assertSessionHas('error');
        $this->assertFalse($user->isFollowing($user));
    }

    public function test_user_can_filter_posts_by_hashtag(): void
    {
        $user = User::factory()->create();
        $tag = Hashtag::create(['name' => 'ai']);

        $postWithTag = Post::create([
            'user_id' => $user->id,
            'content' => 'AI is advancing fast',
        ]);
        $postWithTag->hashtags()->attach($tag->id);

        $postWithoutTag = Post::create([
            'user_id' => $user->id,
            'content' => 'Cooking pasta tonight',
        ]);

        $response = $this->actingAs($user)->get('/feed?tag=ai');
        $response->assertStatus(200);
        $response->assertSee('AI is advancing fast');
        $response->assertDontSee('Cooking pasta tonight');
    }

    public function test_user_can_search_posts_by_keyword(): void
    {
        $user = User::factory()->create();

        Post::create([
            'user_id' => $user->id,
            'content' => 'Quantum computing breakthroughs',
        ]);

        Post::create([
            'user_id' => $user->id,
            'content' => 'Gardening tips for spring',
        ]);

        $response = $this->actingAs($user)->get('/feed?search=quantum');
        $response->assertStatus(200);
        $response->assertSee('Quantum computing breakthroughs');
        $response->assertDontSee('Gardening tips for spring');
    }
}
