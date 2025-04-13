# 📦 JsonStore

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/flexible-labs/json-store.svg)](https://packagist.org/packages/flexible-labs/json-store)
![Laravel](https://img.shields.io/badge/Laravel-10%2B-red)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![Downloads](https://img.shields.io/packagist/dt/flexible-labs/json-store)

## Table of Contents

- [Introduction](#introduction)
- [Key Features](#key-features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [API Reference](#api-reference)
- [Key Features](#key-features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Detailed Usage](#detailed-usage)
  - [Creating a Store](#creating-a-store)
  - [Setting and Retrieving Data](#setting-and-retrieving-data)
  - [Managing Arrays](#managing-arrays)
  - [Caching with TTL](#caching-with-ttl)
  - [Concurrency Handling](#concurrency-handling)
  - [Additional API Methods](#additional-api-methods)
- [Practical Examples](#practical-examples)
- [Testing](#testing)
- [Advanced Tips](#advanced-tips)
- [Comparison with Other Tools](#comparison-with-other-tools)
- [License](#license)
- [Author](#author)

## Introduction

**JsonStore** is a Laravel-friendly package that simplifies JSON-based storage. It enables you to store, retrieve, and manage structured data with ease using dot-notation, automatic persistence, caching, and file-locking mechanisms.

---

## Key Features

- **Dot Notation:** Intuitively access and modify deeply nested JSON keys.
- **Automatic Saving:** Changes are persisted without manual `save()` calls.
- **Safe Concurrency:** File-level locking prevents race conditions.
- **TTL Caching:** Built-in `remember()` helper for temporary data storage.
- **Flexible Storage:** Works with any disk, base path, or multi-tenancy setup.

---

## Installation

```bash
composer require flexible-labs/json-store
```

---

## Configuration

```bash
php artisan vendor:publish --provider="FlexibleLabs\JsonStore\JsonStoreServiceProvider"
```

```php
return [
    'disk'      => env('JSONSTORE_DISK', 'local'),
    'base_path' => env('JSONSTORE_BASE', ''),
];
```

---

## Quick Start

```php
use FlexibleLabs\JsonStore\JsonStore;

Route::get('/quick-start', function () {
    $store = JsonStore::make('settings.json');
    $store->set('theme', 'dark');
    return $store->get();
});
```

---

## API Reference

| Method                                                   | Description                                  |
| -------------------------------------------------------- | -------------------------------------------- |
| `make(filename, default = [], disk = null, base = null)` | Create a new JsonStore instance.             |
| `disk(disk)`                                             | Set the storage disk.                        |
| `base(path)`                                             | Set the base path inside the disk.           |
| `load()`                                                 | Load the JSON file (auto-called on access).  |
| `save()`                                                 | Manually save changes to the JSON file.      |
| `set(key, value)`                                        | Set a value by key (dot notation supported). |
| `get(key = null, default = null, asObject = false)`      | Get a value or all data.                     |
| `getOrSet(key, default)`                                 | Get value or set it if missing.              |
| `has(key)`                                               | Check if a key exists.                       |
| `forget(key)`                                            | Remove a key.                                |
| `delete(key, default = null)`                            | Delete and return a value.                   |
| `deleteFrom(key, valueToRemove)`                         | Remove an item from an array.                |
| `insert(keyOrValue, value = null)`                       | Append to array or to root.                  |
| `replace(array)`                                         | Replace all data.                            |
| `update(array)`                                          | Merge updates into current data.             |
| `remember(key, ttl, callback)`                           | Cache value with TTL.                        |
| `withLock(callback, deleteLockAfter = true)`             | Safely run operations with a file lock.      |
| `exists()`                                               | Check if the file exists.                    |

---

## Detailed Usage


### Creating a Store

```php
$store = JsonStore::make('config.json', ['theme' => 'light']);
$store = JsonStore::make('config.json')->disk('public')->base('configs');
```


### Setting and Retrieving Data

```php
$store->set('app.name', 'LaravelApp');
$store->set('app.version', '11.0');
$appName = $store->get('app.name');
$full = $store->get();
```


### Managing Arrays

```php
// Append a user to an array
$store->insert('users', 'John Doe');

// Remove a specific value from an array
$store->deleteFrom('users', 'John Doe');

// Replace the entire store content (use with caution)
$store->replace([
    'theme' => 'blue',
    'notifications' => ['email' => true, 'sms' => false]
]);

// Recursively update part of the data without overriding the rest
$store->update([
    'notifications' => ['push' => true],
    'app' => ['name' => 'Json Manager']
]);
```


### Caching with TTL

```php
$cached = $store->remember('api.response', 3600, fn () => Http::get(...)->json());
```


### Concurrency Handling

```php
$store->withLock(function () use ($store) {
    $views = $store->get('views', 0);
    $store->set('views', $views + 1);
});
```


### Additional API Methods

#### `getOrSet()`

```php
$token = $store->getOrSet('auth.token', fn () => Str::uuid());
```

#### `forget()`

```php
$store->forget('deprecated.key');
```

#### `delete()`

```php
$value = $store->delete('temp.value');
```

#### `has()`

```php
if ($store->has('env.debug')) {
    // Do something
}
```

#### `exists()`

```php
if ($store->exists()) {
    // File exists in storage
}
```

#### `all()` via `get()`

```php
$data = $store->get();
```

#### `set()` with array

```php
$store->set([
    'key1' => 'value1',
    'nested.key' => 'value2'
]);
```

#### `insert()` directly to root

```php
$store->insert('a new root item');
```

---

## Practical Examples


### Dynamic Routes

```php
Route::get('/articles/{id}', function ($id) {
    $store = JsonStore::make("{$id}.json")->disk('public')->base('articles');
    return $store->exists() ? $store->get() : abort(404);
});
```


### Nested JSON Manipulation

```php
$store = JsonStore::make('colors.json');
$store->set('1.name', 'red');
$store->set('1.code', '#ff0000');
return $store->get();
```

---

## Testing

```bash
composer install
composer test
```

---

## Advanced Tips

- Integrate with Laravel Echo for real-time sync.
- Use environment-specific `disk`/`base` for separation.
- Schedule regular exports via Laravel scheduler.

---

## Comparison with Other Tools

| Feature             | JsonStore | Laravel Settings | Laravel JSON Settings |
| ------------------- | --------- | ---------------- | --------------------- |
| Dot notation        | ✅         | ✅                | ✅                     |
| Auto persistence    | ✅         | ❌                | ❌                     |
| File locking        | ✅         | ❌                | ❌                     |
| TTL caching         | ✅         | ❌                | ❌                     |
| Flexible paths      | ✅         | ❌                | ✅                     |
| Native Laravel feel | ✅         | ✅                | ✅                     |

---

## License

JsonStore is open-source software licensed under the **MIT license**.

---

## Author

Maintained by [Suleiman Shahbari](https://github.com/suliemandev).