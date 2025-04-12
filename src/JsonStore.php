<?php

namespace FlexibleLabs\JsonStore;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class JsonStore
{
    protected string $path;
    protected array $data = [];
    protected bool $dirty = false;
    protected bool $autoSave = true;
    protected string $disk;
    protected string $base;
    protected bool $loaded = false;
    protected string $filename;
    protected object|array $default;

    public function __construct(
        string $filename,
        object|array $default = [],
        ?string $disk = null,
        ?string $base = null
    ) {
        $this->filename = $filename;
        $this->default = $default;

        $this->disk = $disk ?? config('jsonstore.disk', 'local');
        $this->base = $base ?? config('jsonstore.base_path', '');
        $this->path = trim("{$this->base}/{$this->filename}", '/');
    }

    public function __destruct()
    {
        if ($this->autoSave && $this->dirty && $this->loaded) {
            $this->save();
        }
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function base(string $base): static
    {
        $this->base = $base;
        return $this;
    }

    public function load(): static
    {
        if ($this->loaded) return $this;

        $this->path = trim("{$this->base}/{$this->filename}", '/');

        $defaultArray = is_object($this->default)
            ? json_decode(json_encode($this->default), true)
            : $this->default;

        if (!Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->put($this->path, json_encode($defaultArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->data = $defaultArray;
        } else {
            $existing = json_decode(Storage::disk($this->disk)->get($this->path), true) ?? [];
            $this->data = array_replace_recursive($defaultArray, $existing);
        }

        $this->loaded = true;

        return $this;
    }

    protected function ensureLoaded(): void
    {
        if (!$this->loaded) {
            $this->load();
        }
    }

    public function save(): void
    {
        Storage::disk($this->disk)->put($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->dirty = false;
    }

    public function set(string|array $key, mixed $value = null): void
    {
        $this->ensureLoaded();

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Arr::set($this->data, $k, $v);
            }
        } else {
            Arr::set($this->data, $key, $value);
        }

        $this->dirty = true;
    }

    public function get(string $key = null, $default = null, bool $asObject = false): mixed
    {
        $this->ensureLoaded();

        if (is_null($key)) {
            return $asObject
                ? json_decode(json_encode($this->data))
                : $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }

    public function forget(string $key): void
    {
        $this->ensureLoaded();
        Arr::forget($this->data, $key);
        $this->dirty = true;
    }

    public function getOrSet(string $key, $default): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $default instanceof \Closure ? $default() : $default;
        $this->set($key, $value);
        return $value;
    }

    public function replace(array $newData): void
    {
        $this->data = $newData;
        $this->dirty = true;
    }

    public function update(array $updates): void
    {
        $this->data = array_replace_recursive($this->data, $updates);
        $this->dirty = true;
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        $this->ensureLoaded();
        $now = time();
        $cached = $this->get($key);

        if (
            is_array($cached) &&
            array_key_exists('value', $cached) &&
            array_key_exists('expires_at', $cached) &&
            $cached['expires_at'] > $now
        ) {
            return $cached['value'];
        }

        $value = $callback();

        $this->set($key, [
            'value' => $value,
            'expires_at' => $now + $ttlSeconds,
        ]);

        return $value;
    }

    public function withLock(callable $callback, bool $deleteLockAfter = true): mixed
    {
        $lockFile = Storage::disk($this->disk)->path($this->path . '.lock');

        $fp = fopen($lockFile, 'c+');

        if (!$fp) {
            throw new \RuntimeException("Could not open lock file: {$lockFile}");
        }

        try {
            if (flock($fp, LOCK_EX)) {
                $result = $callback();
                flock($fp, LOCK_UN);
                fclose($fp);

                if ($deleteLockAfter && file_exists($lockFile)) {
                    unlink($lockFile);
                }

                return $result;
            } else {
                fclose($fp);
                throw new \RuntimeException("Could not acquire lock: {$lockFile}");
            }
        } catch (\Throwable $e) {
            fclose($fp);
            throw $e;
        }
    }

    public function insert(string $key, $value): void
    {
        $this->ensureLoaded();

        $array = Arr::get($this->data, $key, []);
        if (!is_array($array)) {
            throw new \InvalidArgumentException("Value at [$key] is not an array.");
        }

        $array[] = $value;
        Arr::set($this->data, $key, $array);
        $this->dirty = true;
    }

    public function delete(string $key, $default = null): mixed
    {
        $this->ensureLoaded();
        $value = Arr::pull($this->data, $key, $default);
        $this->dirty = true;
        return $value;
    }

    public function deleteFrom(string $key, $valueToRemove): void
    {
        $this->ensureLoaded();

        $array = Arr::get($this->data, $key, []);
        if (!is_array($array)) {
            throw new \InvalidArgumentException("Value at [$key] is not an array.");
        }

        $filtered = array_filter($array, fn($item) => $item !== $valueToRemove);
        Arr::set($this->data, $key, array_values($filtered));
        $this->dirty = true;
    }

    public function has(string $key): bool
    {
        $this->ensureLoaded();
        return Arr::has($this->data, $key);
    }

    public function exists(): bool
    {
        $this->path = trim("{$this->base}/{$this->filename}", '/');
        return Storage::disk($this->disk)->exists($this->path);
    }
}
