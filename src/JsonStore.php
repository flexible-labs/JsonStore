<?php

namespace FlexibleLabs\JsonStore;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class JsonStore
{
    protected string $path;
    protected array $data = [];
    protected bool $dirty = false;
    protected bool $autoSave = true;

    public function __construct(string $filename, object|array $default = [])
    {
        $this->path = "json/{$filename}";

        // Convert object to array if needed
        $defaultArray = is_object($default) ? json_decode(json_encode($default), true) : $default;

        if (!Storage::disk('local')->exists($this->path)) {
            Storage::disk('local')->put($this->path, json_encode($defaultArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->data = $defaultArray;
        } else {
            $existing = json_decode(Storage::disk('local')->get($this->path), true) ?? [];
            $this->data = array_replace_recursive($defaultArray, $existing);
        }
    }

    public function __destruct()
    {
        if ($this->autoSave && $this->dirty) {
            $this->save();
        }
    }

    public function save(): void
    {
        Storage::disk('local')->put($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->dirty = false;
    }

    public function set(string|array $key, mixed $value = null): void
    {
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
        if (is_null($key)) {
            return $asObject
                ? json_decode(json_encode($this->data))
                : $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }

    public function forget(string $key): void
    {
        Arr::forget($this->data, $key);
        $this->dirty = true;
    }

    public function getOrSet(string $key, $default): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        // If $default is a closure, call it
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

    public function remember(string $key, int $ttlSeconds, callable $callback)
    {
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
        $lockFile = Storage::disk('local')->path($this->path . '.lock');

        // OPTIONAL: ensure lock directory exists
        // $dir = dirname($lockFile);
        // if (!is_dir($dir)) {
        //     mkdir($dir, 0755, true);
        // }

        $fp = fopen($lockFile, 'c+');

        if (!$fp) {
            throw new \RuntimeException("Could not open lock file: {$lockFile}");
        }

        try {
            if (flock($fp, LOCK_EX)) {
                $result = $callback();
                flock($fp, LOCK_UN);
                fclose($fp);

                // ✅ Delete lock file if requested
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
        $array = Arr::get($this->data, $key, []);
        if (!is_array($array)) {
            throw new \InvalidArgumentException("Value at [$key] is not an array.");
        }

        $array[] = $value;
        Arr::set($this->data, $key, $array);
        $this->dirty = true;
    }

    public function delete(string $key, $default = null)
    {
        $value = Arr::pull($this->data, $key, $default);
        $this->dirty = true;
        return $value;
    }

    public function deleteFrom(string $key, $valueToRemove): void
    {
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
        return Arr::has($this->data, $key);
    }
}