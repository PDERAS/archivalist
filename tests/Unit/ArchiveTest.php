<?php

use PDERAS\Archivalist\Tests\Models\Post;

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

it("Archive hydrates into original model", function () {

    $post = new Post;
    $post->title = 'first';
    $post->content = 'no content';
    $post->save();

    $post->title = 'second';
    $post->content = 'some content';
    $post->save();

    assertInstanceOf(Post::class, $post->archives()->first()->rehydrate());
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
