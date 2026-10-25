<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use Illuminate\Support\Facades\DB;
use Onurkacmaz\LaravelModelTranslate\Tests\Fixtures\Post;
use Onurkacmaz\LaravelModelTranslate\Tests\Fixtures\UuidPost;

class DatabaseDriverTest extends DriverTestCase
{
    protected function driverName(): string
    {
        return 'database';
    }

    public function test_translations_are_stored_in_the_translations_table(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);

        $this->assertSame(4, DB::table('translations')->count());
        $this->assertDatabaseHas('translations', [
            'namespace' => Post::class,
            'foreign_id' => (string) $post->id,
            'locale' => 'en',
            'key' => 'title',
            'value' => 'Merhaba',
        ]);
    }

    public function test_saving_again_does_not_duplicate_translations(): void
    {
        $post = Post::create(['title' => 'Merhaba']);
        $post->update(['views' => 3]);
        $post->update(['title' => 'Selam']);

        $this->assertSame(2, DB::table('translations')->count());
    }

    public function test_models_with_uuid_keys_are_supported(): void
    {
        $post = UuidPost::create(['title' => 'Merhaba']);
        $post->saveTranslation('en', ['title' => 'Hello']);

        $this->app->setLocale('en');

        $this->assertSame('Hello', UuidPost::findOrFail($post->id)->title);
    }
}
