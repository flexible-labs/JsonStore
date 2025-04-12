# 📦 JsonStore

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/flexible-labs/json-store.svg)](https://packagist.org/packages/flexible-labs/json-store)
![Laravel](https://img.shields.io/badge/Laravel-10%2B-red)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)
![Downloads](https://img.shields.io/packagist/dt/flexible-labs/json-store)

## Table of Contents

- [Introduction](#introduction)
- [Why Use JsonStore](#why-use-jsonstore)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [Creating a Store](#creating-a-store)
  - [Setting Data](#setting-data)
  - [Retrieving Data](#retrieving-data)
  - [Conditional Retrieval or Setting](#conditional-retrieval-or-setting)
  - [Removing Data](#removing-data)
  - [Array Helpers](#array-helpers)
  - [Checking Data Existence](#checking-data-existence)
  - [Caching with TTL](#caching-with-ttl)
  - [Concurrency with Locks](#concurrency-with-locks)
  - [Existence Check](#existence-check)
- [Practical Route Examples](#practical-route-examples)
- [Advanced Usage Tips](#advanced-usage-tips)
- [Comparison with Other Packages](#comparison-with-other-packages)
- [License](#license)

---

## ✨ Introduction

**JsonStore** is a Laravel‑friendly package for effortless JSON‑based storage. It lets you store, retrieve, and manage structured data with dot‑notation ease, automatic persistence, and built‑in safety features—_the Laravel way_.

---

## 🚀 Why Use JsonStore

* **Laravel‑first design** – integrates seamlessly with the filesystem and service container.
* **Dot notation everywhere** – intuitive nested access like `profile.name`.
* **Automatic saving** – no manual `save()` calls needed (unless you disable auto‑save).
* **Safe concurrency** – file‑level locking to avoid race conditions.
* **TTL caching** – lightweight `remember()` helper for transient data.
* **Flexible storage** – any disk, any base path, per‑tenant or per‑user.

---

## 🛠️ Features

| Feature | Description |
|---------|-------------|
| **Dot Notation** | Read & write deeply‑nested JSON keys with ease |
| **Automatic Saving** | Persists changes on object destruction |
| **File Locking** | `withLock()` ensures safe concurrent writes |
| **TTL Caching** | `remember()` caches values for N seconds |
| **Array Helpers** | `insert()` / `deleteFrom()` for array fields |
| **Flexible Paths** | `disk()` & `base()` fluent setters |

---

## 🔧 Installation

```bash
composer require flexible-labs/json-store
```

---

## ⚙️ Configuration

Publish the config file if you need to customise defaults:

```bash
php artisan vendor:publish --provider="FlexibleLabs\JsonStore\JsonStoreServiceProvider"
```

`config/jsonstore.php`:

```php
return [
    'disk'      => env('JSONSTORE_DISK', 'local'),
    'base_path' => env('JSONSTORE_BASE', ''),
];
```

---

## ⚡ Quick Start

```php
use FlexibleLabs\JsonStore\JsonStore;

Route::get('/quick-start', function () {
    $store = JsonStore::make('settings.json');
    $store->set('theme', 'dark');
    return $store->get();          // [ 'theme' => 'dark' ]
});
```

---

## 📚 Usage

### Creating a Store

```php
// Create with default data on the default disk
$store = JsonStore::make('config.json', ['theme' => 'light']);

// Specify disk & base folder
$store = JsonStore::make('config.json')
    ->disk('public')
    ->base('configs');
```

### Setting Data

```php
// Single value
$store->set('app.name', 'LaravelApp');

// Multiple values at once
$store->set([
    'app.version' => '11.0',
    'app.author'  => 'Flexible Labs',
]);

// Deeply‑nested array element
$store->set('data.colors.0.name', 'red');
```

### Retrieving Data

```php
$title   = $store->get('app.name');
$version = $store->get('app.version', '1.0'); // default fallback
$all     = $store->get();                     // entire payload
```

### Conditional Retrieval or Setting

```php
$theme = $store->getOrSet('theme', 'light');
```

### Removing Data

```php
$store->forget('app.author');
```

### Array Helpers

```php
// Append to root array
a$store->insert('new‑item');

// Append to nested array
$store->insert('users', 'Jane Doe');

// Remove from nested array
$store->deleteFrom('users', 'Jane Doe');
```

### Checking Data Existence

```php
if ($store->has('app.version')) {
    // key exists
}
```

### Caching with TTL

```php
$response = $store->remember('api.response', 3600, function () {
    return Http::get('https://api.example.com/data')->json();
});
```

### Concurrency with Locks

```php
$store->withLock(function () use ($store) {
    $views = $store->get('views', 0);
    $store->set('views', $views + 1);
});
```

### Existence Check

```php
if ($store->exists()) {
    return $store->get();
}
abort(404);
```

---

## 📝 Practical Route Examples

```php
// 1. Dynamic article route with exists()
Route::get('/articles/{id}', function ($id) {
    $store = JsonStore::make("{$id}.json")->disk('public')->base('articles');
    return $store->exists() ? $store->get() : abort(404);
});

// 2. Nested colors example
Route::get('colors', function () {
    $store = JsonStore::make('colors.json');
    $store->set('1.name', 'red');
    $store->set('1.code', '#ff000');
    $store->getOrSet('2.name', 'green');
    return $store->get();
});

// 3. Array append example
Route::get('array', function () {
    $store = JsonStore::make('array.json', [1, 2, 3]);
    $store->insert(4);
    return $store->get(); // [1,2,3,4]
});
```

---

## 💡 Advanced Usage Tips

* **Real‑time broadcasting** – pair JsonStore updates with Laravel Echo & WebSockets to notify clients when data changes.
* **Environment isolation** – point each environment (local/stage/prod) to its own disk or base path.
* **Scheduled snapshots** – use Laravel schedules to back up JsonStore files or rotate archives.

---

## 🔍 Comparison with Other Packages

| Feature                | JsonStore | Laravel Settings | Laravel JSON Settings |
|------------------------|-----------|------------------|-----------------------|
| Dot notation           | ✅        | ✅               | ✅                    |
| Auto persistence       | ✅        | ❌               | ❌                    |
| File locking           | ✅        | ❌               | ❌                    |
| TTL caching            | ✅        | ❌               | ❌                    |
| Flexible paths         | ✅        | ❌               | ✅                    |
| Native Laravel feel    | ✅        | ✅               | ✅                    |

---

## 📄 License

JsonStore is open‑sourced software licensed under the **MIT license**.

---

## 🙋‍♂️ Author

Maintained by [Suleiman Shahbari](https://github.com/suliemandev)