## 📦 `JsonStore` — Laravel-Friendly JSON Store

A flexible, dot-accessible JSON store for Laravel with auto-save, file locking, TTL cache, and array helpers. Perfect for storing user settings, config data, feature flags, or any small structured state — no database needed.

---

### 🚀 Features

- ✅ Dot notation access (`$store->get('user.name')`)
- ✅ Auto-saving on destruct or manually
- ✅ File-locking for concurrency safety (`withLock`)
- ✅ TTL-based cache with `remember()`
- ✅ Per-user/per-file scoped storage
- ✅ Array helpers like `insert()`, `deleteFrom()`
- ✅ Supports default structure + object or array access

---

### 📦 Installation

```bash
composer require flexible-labs/json-store
```

If you're developing locally, link it using a path repository:

```json
"repositories": [
    {
        "type": "path",
        "url": "../json-store",
        "options": {
            "symlink": true
        }
    }
]
```

---

### 🥪 Usage Example

```php
use FlexibleLabs\JsonStore\JsonStore;

// Create or load store
$store = new JsonStore("users/{$user->id}.json", [
    'settings' => [
        'theme' => 'dark',
    ],
]);

// Access values
$theme = $store->get('settings.theme');

// Set values
$store->set('profile.name', 'Sulieman');

// Auto-saves on destruct, or call manually:
$store->save();
```

---

### 🔒 Safe Concurrent Updates

```php
$store->withLock(function () use ($store) {
    $views = $store->get('views', 0);
    $store->set('views', $views + 1);
});
```

---

### 🤠 Smart Cache

```php
$result = $store->remember('api.response', 3600, function () {
    return Http::get('https://api.example.com/data')->json();
});
```

---

### 🧰 Array Helpers

```php
$store->insert('tags', 'laravel');
$store->deleteFrom('tags', 'vue');
```

---

### 🔧 API Overview

| Method             | Description                          |
|--------------------|--------------------------------------|
| `get($key)`        | Get a value or the entire store      |
| `set($key, $value)`| Set value(s)                         |
| `delete($key)`     | Delete a key                         |
| `has($key)`        | Check if key exists                  |
| `getOrSet()`       | Set default if missing               |
| `remember()`       | Get value with TTL fallback          |
| `insert()`         | Add to array                         |
| `deleteFrom()`     | Remove from array                    |
| `withLock()`       | Lock file, run closure safely        |
| `save()`           | Save changes manually                |

---

### 📄 License

MIT © Suleiman

