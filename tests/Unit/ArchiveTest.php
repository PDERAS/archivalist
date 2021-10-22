<?php

use Pderas\Archivalist\Archivalist;
use Pderas\Archivalist\Tests\Models\Post;

it('Creates model without archives', function () {
    $post = tap(new Post)->save();

    assertEquals(Post::count(), 1);
    assertEquals($post->archives()->count(), 0);
});

it('Updates to model saves archive', function () {
    $post = tap(new Post)->save();
    $post->title = 'Test';
    $post->save();

    assertEquals(Post::count(), 1);
    assertEquals($post->archives()->count(), 1);
});

it('First archive saves original model value', function () {
    $post = new Post;
    $post->title = 'first';
    $post->save();

    $post->title = 'second';
    $post->save();

    assertEquals(Post::first()->title, 'second');
    assertEquals($post->archives()->first()->data->title, 'first');
});

it("retrieves the full history when asked", function () {
    $post = new Post;
    $post->title = 'first';
    $post->content = 'no content';
    $post->save();

    $post->title = 'second';
    $post->content = 'some content';
    $post->save();

    $post->title = 'third';
    $post->content = 'a lot of content';
    $post->save();

    $history = $post->getHistory();

    assertInstanceOf(Post::class, $history[0]);
    assertEquals($history[0]->title, 'first');
    assertEquals($history[0]->content, 'no content');

    assertInstanceOf(Post::class, $history[1]);
    assertEquals($history[1]->title, 'second');
    assertEquals($history[1]->content, 'some content');

    assertInstanceOf(Post::class, $history[2]);
    assertEquals($history[2]->title, 'third');
    assertEquals($history[2]->content, 'a lot of content');
});

it("Follows a complex history tree", function () {
    $post = new Post;
    $post->title = 'first';
    $post->content = '';
    $post->save();

    $post->content = 'some content';
    $post->save();

    $post->title = 'third';
    $post->content = 'a lot of content';
    $post->save();

    $post->title = 'forth';
    $post->save();

    $history = $post->getHistory();

    assertInstanceOf(Post::class, $history[0]);
    assertEquals($history[0]->title, 'first');
    assertEquals($history[0]->content, '');

    assertInstanceOf(Post::class, $history[1]);
    assertEquals($history[1]->title, 'first');
    assertEquals($history[1]->content, 'some content');

    assertInstanceOf(Post::class, $history[2]);
    assertEquals($history[2]->title, 'third');
    assertEquals($history[2]->content, 'a lot of content');

    assertInstanceOf(Post::class, $history[3]);
    assertEquals($history[3]->title, 'forth');
    assertEquals($history[3]->content, 'a lot of content');
});

it("exposes a mass assignment update proxy on the facade", function () {
    for ($i = 0; $i < 5; $i++) {
        $p = new Post;
        $p->title = "Post: " . ($i + 1);
        $p->save();
    }

    Archivalist::proxy(Post::query())
        ->where('title', '!=', 'blah')
        ->where(function ($query) {
            return $query->where('content', '!=', 'doesnt exist')
                ->orWhereNull('content');
        })
        ->whereNull('content')
        ->whereIn('id', [1, 2, 3, 4, 5])
        ->update(['title' => 'New Title']);

    $history = Post::find(2)->getHistory();
    assertInstanceOf(Post::class, $history[0]);
    assertEquals($history[0]->title, 'Post: 2');
    assertInstanceOf(Post::class, $history[1]);
    assertEquals($history[1]->title, 'New Title');


    $history = Post::find(3)->getHistory();
    assertInstanceOf(Post::class, $history[0]);
    assertEquals($history[0]->title, 'Post: 3');
    assertInstanceOf(Post::class, $history[1]);
    assertEquals($history[1]->title, 'New Title');
});

it('doesnt archive $hidden attributes', function () {
    $post = new Post;
    $post->title = 'first';
    $post->secret = 'secrets';
    $post->save();

    $post->title = 'second';
    $post->secret = 'ssshhhh';
    $post->save();

    $post->title = 'third';
    $post->secret = 'not here';
    $post->save();

    $history = Post::first()->getHistory();

    assertEquals($history[0]->title, 'first');
    assertEquals($history[0]->secret, null);

    assertEquals($history[1]->title, 'second');
    assertEquals($history[1]->secret, null);

    // The final model in history is the current 'active' model
    assertEquals($history[2]->title, 'third');
    assertEquals($history[2]->secret, 'not here');
});


it('never archives when only hidden attributes change', function () {
    $post = new Post;
    $post->title = 'first';
    $post->content = '';
    $post->secret = 'this';
    $post->save();

    // first archive created
    $post->content = 'some content';
    $post->secret = 'shouldnt';
    $post->save();

    // second archive created
    $post->title = 'third';
    $post->content = 'a lot of content';
    $post->secret = 'be';
    $post->save();

    // No archivable changes detected...
    $post->secret = 'archived';
    $post->save();

    assertEquals(Post::count(), 1);
    assertEquals($post->archives()->count(), 2);
});
