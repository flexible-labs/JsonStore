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
- [Detailed Usage](#detailed-usage)  
  - [Creating a Store](#creating-a-store)  
  - [Setting and Retrieving Data](#setting-and-retrieving-data)  
  - [Managing Arrays](#managing-arrays)  
  - [Caching with TTL](#caching-with-ttl)  
  - [Concurrency Handling](#concurrency-handling)  
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

Install the package using Composer:  

```bash  
composer require flexible-labs/json-store  
```  

---

## Configuration  

Publish the configuration file if you need to customize the defaults:  

```bash  
php artisan vendor:publish --provider="FlexibleLabs\JsonStore\JsonStoreServiceProvider"  
```  

This creates a `config/jsonstore.php` file:  

```php  
return [  
    'disk'      => env('JSONSTORE_DISK', 'local'),  
    'base_path' => env('JSONSTORE_BASE', ''),  
];  
```  

---

## Quick Start  

Here’s how to quickly get started:  

```php  
use FlexibleLabs\JsonStore\JsonStore;  

Route::get('/quick-start', function () {  
    $store = JsonStore::make('settings.json');  
    $store->set('theme', 'dark');  
    return $store->get();  // Output: [ 'theme' => 'dark' ]  
});  
```  

---

## Detailed Usage  

### Creating a Store  

```php  
$store = JsonStore::make('config.json', ['theme' => 'light']);  // Default data  
$store = JsonStore::make('config.json')->disk('public')->base('configs');  // Custom disk and path  
```  

### Setting and Retrieving Data  

```php  
$store->set('app.name', 'LaravelApp');  
$store->set('app.version', '11.0');  
$appName = $store->get('app.name');  // LaravelApp  
```  

### Managing Arrays  

```php  
$store->insert('users', 'John Doe');  // Append to an array  
$store->deleteFrom('users', 'John Doe');  // Remove from an array  
```  

### Caching with TTL  

```php  
$response = $store->remember('api.response', 3600, function () {  
    return Http::get('https://api.example.com/data')->json();  
});  
```  

### Concurrency Handling  

```php  
$store->withLock(function () use ($store) {  
    $views = $store->get('views', 0);  
    $store->set('views', $views + 1);  
});  
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
Route::get('colors', function () {  
    $store = JsonStore::make('colors.json');  
    $store->set('1.name', 'red');  
    $store->set('1.code', '#ff0000');  
    return $store->get();  
});  
```  

---

## Testing  

Run the tests to ensure the package works as expected:  

1. Install dependencies:  

   ```bash  
   composer install  
   ```  

2. Run the test suite:  

   ```bash  
   composer test  
   ```  

The suite covers all public methods and uses fake filesystems for safety.  

---

## Advanced Tips  

- **Real-Time Updates:** Integrate with Laravel Echo and WebSockets to broadcast changes.  
- **Environment-Specific Configurations:** Use separate disks or paths for local, staging, and production environments.  
- **Scheduled Maintenance:** Leverage Laravel’s task scheduler for periodic backups of JSON data.  

---

## Comparison with Other Tools  

| Feature                | JsonStore | Laravel Settings | Laravel JSON Settings |  
|------------------------|-----------|------------------|-----------------------|  
| Dot notation           | ✅        | ✅               | ✅                    |  
| Auto persistence       | ✅        | ❌               | ❌                    |  
| File locking           | ✅        | ❌               | ❌                    |  
| TTL caching            | ✅        | ❌               | ❌                    |  
| Flexible paths         | ✅        | ❌               | ✅                    |  
| Native Laravel feel    | ✅        | ✅               | ✅                    |  

---

## License  

JsonStore is open-source software licensed under the **MIT license**.  

---

## Author  

Maintained by [Sulieman Shahbari](https://github.com/suliemanshahbari).  
