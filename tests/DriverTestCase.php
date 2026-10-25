<?php

namespace Onurkacmaz\LaravelModelTranslate\Tests;

use Illuminate\Support\Facades\DB;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslate;
use Onurkacmaz\LaravelModelTranslate\LaravelModelTranslateFacade;
use Onurkacmaz\LaravelModelTranslate\Tests\Fixtures\MethodPost;
use Onurkacmaz\LaravelModelTranslate\Tests\Fixtures\Post;
use Onurkacmaz\LaravelModelTranslate\Tests\Fixtures\SoftDeletePost;

/**
 * Behaviour every driver must have. Each driver gets a subclass.
 */
abstract class DriverTestCase extends TestCase
{
    abstract protected function driverName(): string;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useDriver($this->driverName());
    }

    public function test_creating_a_model_seeds_translations_for_the_other_locales(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);

        $this->assertSame(['content' => 'İçerik', 'title' => 'Merhaba'], $this->sorted($post->getTranslations('en')));
        $this->assertSame(['content' => 'İçerik', 'title' => 'Merhaba'], $this->sorted($post->getTranslations('de')));
        $this->assertSame([], $post->getTranslations('tr'));
    }

    public function test_a_retrieved_model_is_translated_into_the_current_locale(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);
        $post->saveTranslation('en', ['title' => 'Hello', 'content' => 'Content']);

        $this->app->setLocale('en');
        $translated = Post::findOrFail($post->id);

        $this->assertSame('Hello', $translated->title);
        $this->assertSame('Content', $translated->content);
        $this->assertFalse($translated->isDirty());

        $this->app->setLocale('tr');
        $this->assertSame('Merhaba', Post::findOrFail($post->id)->title);
    }

    public function test_updating_in_a_translated_locale_only_writes_the_translation(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);

        $this->app->setLocale('en');
        $post = Post::findOrFail($post->id);
        $post->title = 'Hello';
        $post->views = 10;

        $this->assertTrue($post->save());
        $this->assertSame('Hello', $post->title);

        $row = DB::table('posts')->find($post->id);
        $this->assertSame('Merhaba', $row->title);
        $this->assertEquals(10, $row->views);

        $this->assertSame('Hello', Post::findOrFail($post->id)->title);
        $this->assertSame('Merhaba', $post->getTranslations('de')['title']);
    }

    public function test_updating_in_the_original_locale_writes_the_model_table(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);

        $post->update(['title' => 'Selam']);

        $this->assertSame('Selam', DB::table('posts')->find($post->id)->title);
        $this->assertSame('Merhaba', $post->getTranslations('en')['title']);
    }

    public function test_save_translation_does_nothing_for_the_original_locale(): void
    {
        $post = Post::create(['title' => 'Merhaba']);

        $this->assertFalse($post->saveTranslation('tr', ['title' => 'Selam']));
        $this->assertSame([], $post->getTranslations('tr'));
        $this->assertTrue($post->saveTranslation('en', ['title' => 'Hello', 'views' => 5]));
        $this->assertSame(['title' => 'Hello'], $post->getTranslations('en'));
    }

    public function test_translate_to_switches_between_locales(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);
        $post->saveTranslation('en', ['title' => 'Hello']);

        $this->assertSame('Hello', $post->translateTo('en')->title);
        $this->assertSame('Merhaba', $post->translateTo('tr')->title);
        $this->assertFalse($post->isDirty());
    }

    public function test_null_values_are_kept(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => null]);

        $this->assertSame(['content' => null, 'title' => 'Merhaba'], $this->sorted($post->getTranslations('en')));
    }

    public function test_translatable_columns_can_be_defined_with_a_method(): void
    {
        $post = MethodPost::create(['title' => 'Merhaba', 'content' => 'İçerik']);

        $this->assertSame(['title' => 'Merhaba'], $post->getTranslations('en'));
    }

    public function test_deleting_a_model_deletes_its_translations(): void
    {
        $post = Post::create(['title' => 'Merhaba']);
        $post->delete();

        $this->assertSame([], $post->getTranslations('en'));
    }

    public function test_soft_deleting_keeps_translations_until_force_deleted(): void
    {
        $post = SoftDeletePost::create(['title' => 'Merhaba']);

        $post->delete();
        $this->assertSame(['title' => 'Merhaba'], $post->getTranslations('en'));

        $post->forceDelete();
        $this->assertSame([], $post->getTranslations('en'));
    }

    public function test_the_class_can_be_used_without_the_trait(): void
    {
        $post = Post::create(['title' => 'Merhaba', 'content' => 'İçerik']);
        $post->saveTranslation('en', ['title' => 'Hello']);

        $model = Post::findOrFail($post->id);
        (new LaravelModelTranslate($model, ['title'], 'en'))->translate();
        $this->assertSame('Hello', $model->title);

        $model = Post::findOrFail($post->id);
        LaravelModelTranslate::make()->setModel($model)->setColumns(['title'])->setLocale('en')->translate();
        $this->assertSame('Hello', $model->title);

        $model = Post::findOrFail($post->id);
        LaravelModelTranslateFacade::setModel($model)->setColumns(['title'])->setLocale('en')->translate();
        $this->assertSame('Hello', $model->title);
    }

    private function sorted(array $values): array
    {
        ksort($values);

        return $values;
    }
}
