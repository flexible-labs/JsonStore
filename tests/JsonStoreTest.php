<?php
// tests/JsonStoreTest.php

namespace FlexibleLabs\JsonStore\Tests;

use FlexibleLabs\JsonStore\JsonStore;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class JsonStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake the local disk so tests never touch real files
        Storage::fake('local');
    }

    public function test_sets_and_gets_single_and_multiple_values(): void
    {
        $store = JsonStore::make('demo.json');

        // single
        $store->set('profile.name', 'John');
        $this->assertSame('John', $store->get('profile.name'));

        // multiple
        $store->set([
            'profile.age'  => 30,
            'profile.city' => 'Paris',
        ]);
        $this->assertSame(30,  $store->get('profile.age'));
        $this->assertSame('Paris', $store->get('profile.city'));
    }

    public function test_gets_entire_payload_and_as_object(): void
    {
        $store = JsonStore::make('all.json', ['a' => 1]);
        $this->assertSame(['a' => 1], $store->get());

        $object = $store->get(asObject: true);
        $this->assertEquals(1, $object->a);
    }

    public function test_gets_or_sets_default_values(): void
    {
        $store = JsonStore::make('gos.json');
        $first  = $store->getOrSet('counter', 1);   // sets
        $second = $store->getOrSet('counter', 99);  // gets

        $this->assertSame(1, $first);
        $this->assertSame(1, $second);
    }

    public function test_forgets_keys(): void
    {
        $store = JsonStore::make('forget.json', ['x' => 1]);
        $store->forget('x');

        $this->assertFalse($store->has('x'));
    }

    public function test_inserts_into_root_and_nested_arrays(): void
    {
        // root
        $root = JsonStore::make('root.json', [1, 2]);
        $root->insert(3);
        $this->assertSame([1, 2, 3], $root->get());

        // nested
        $nested = JsonStore::make('nested.json', ['tags' => ['php']]);
        $nested->insert('tags', 'laravel');
        $this->assertSame(['php', 'laravel'], $nested->get('tags'));
    }

    public function test_deletes_from_nested_array(): void
    {
        $store = JsonStore::make('del.json', ['tags' => ['php', 'laravel']]);
        $store->deleteFrom('tags', 'php');

        $this->assertSame(['laravel'], $store->get('tags'));
    }

    public function test_remembers_values_with_ttl(): void
    {
        $store = JsonStore::make('cache.json');

        $value = $store->remember('expensive', 60, fn () => 'fresh');
        $this->assertSame('fresh', $value);

        // second call should hit cache
        $cached = $store->remember('expensive', 60, fn () => 'new');
        $this->assertSame('fresh', $cached);
    }

    public function test_handles_with_lock_safely(): void
    {
        $store = JsonStore::make('lock.json', ['hits' => 0]);

        $store->withLock(function () use ($store) {
            $hits = $store->get('hits');
            $store->set('hits', $hits + 1);
        });

        $this->assertSame(1, $store->get('hits'));
    }

    public function test_checks_file_existence(): void
    {
        $store = JsonStore::make('exists.json');
        $this->assertFalse($store->exists());

        $store->set('x', 1); // triggers auto‑save on destruct
        unset($store);

        $this->assertTrue(JsonStore::make('exists.json')->exists());
    }

    public function test_replaces_and_updates_data(): void
    {
        $store = JsonStore::make('replace.json', ['a' => 1]);

        $store->replace(['b' => 2]);
        $this->assertSame(['b' => 2], $store->get());

        $store->update(['b' => 3, 'c' => 4]);
        $this->assertSame(['b' => 3, 'c' => 4], $store->get());
    }
}
