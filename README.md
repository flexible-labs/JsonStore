# JsonStore

A Laravel-friendly, dot-accessible JSON storage solution designed to streamline your application's data persistence, handling of user preferences, feature toggles, configuration snapshots, and lightweight caching—all in the familiar "Laravel Way".

---

## Why Use JsonStore?

Laravel developers frequently handle dynamic settings, user preferences, and simple data storage requirements. JsonStore makes this task effortless by providing:

- **Simplicity & Readability:** Easy dot notation for nested data.
- **Automatic Persistence:** Automatically saves changes without explicit calls.
- **Safe Concurrent Access:** Built-in locking to prevent race conditions.
- **Lightweight Caching:** Integrated TTL-based caching.
- **Flexible Storage:** Easily configurable disks and paths.

---

## Package Features

- ✅ **Automatic Saving:** Persist data automatically on object destruction.
- ✅ **Dot Notation Access:** Easily access nested data using dot syntax.
- ✅ **File Locking:** Prevent conflicts with concurrent requests.
- ✅ **TTL Caching:** Store and retrieve cached data efficiently.
- ✅ **Array Helpers:** Convenient methods to manipulate array data.
- ✅ **Customizable Paths:** Supports various disks and directories out-of-the-box.

---

## Quick Start

Get started quickly using JsonStore:

```bash
composer require flexible-labs/json-store
```

### Basic Example

Here's a quick and easy example to get you started:

```php
use FlexibleLabs\JsonStore\JsonStore;

Route::get('/settings', function () {
    $store = JsonStore::make('settings.json');

    $store->set('theme', 'dark');

    return $store->get();
});
```

---

## Detailed Documentation (Laravel Style)

### Creating a JsonStore instance

You can quickly create and load a JSON store with initial data:

```php
// Default disk and path
$store = JsonStore::make('config.json', ['theme' => 'light']);

// Specifying disk and base path
$store = JsonStore::make('config.json')->disk('public')->base('configs');
```

---

### Setting Data (`set` Method)

Set one or multiple keys easily:

```php
// Single value
$store->set('site.title', 'Laravel App');

// Multiple values
$store->set([
    'site.name' => 'Laravel',
    'site.version' => '11.0'
]);
```

---

### Retrieving Data (`get` Method)

Retrieve stored data using dot notation:

```php
// Single value
$title = $store->get('site.title');

// With default fallback
$theme = $store->get('theme', 'default');

// Retrieve all data
$allData = $store->get();
```

---

### Checking Data Existence (`has` Method)

Check if a key exists in your store:

```php
if ($store->has('site.version')) {
    // Key exists, perform action
}
```

---

### Removing Data (`forget` Method)

Easily remove keys:

```php
$store->forget('site.version');
```

---

### Conditional Retrieval or Set (`getOrSet` Method)

Retrieve a key or set it if it doesn't exist:

```php
$theme = $store->getOrSet('theme', 'light');
```

---

### Caching with TTL (`remember` Method)

Easily cache results temporarily:

```php
$apiResponse = $store->remember('api.response', 600, function () {
    return Http::get('https://api.example.com')->json();
});
```

---

### Concurrency Safety (`withLock` Method)

Handle concurrent modifications safely:

```php
$store->withLock(function () use ($store) {
    $counter = $store->get('counter', 0);
    $store->set('counter', $counter + 1);
});
```

---

### Array Manipulation Helpers

#### Insert into Array (`insert` Method)

Append easily to arrays:

```php
$store->insert('tags', 'laravel');
```

#### Delete from Array (`deleteFrom` Method)

Remove values quickly:

```php
$store->deleteFrom('tags', 'laravel');
```

---

### Check if File Exists (`exists` Method)

Check existence without loading:

```php
if ($store->exists()) {
    return $store->get();
}

abort(404);
```

---

## Real-World Example

Here's a practical example using dynamic route handling:

```php
Route::get('/articles/{id}', function($id) {
    $store = JsonStore::make("{$id}.json")->disk('public')->base('articles');

    return $store->exists() ? $store->get() : abort(404);
});
```

---

## Conclusion

JsonStore simplifies handling JSON data with Laravel-friendly methods, automatic persistence, and built-in safety features. It's the perfect choice for maintaining lightweight data stores effortlessly in your Laravel apps.