<?php

namespace App\Clients;

use Illuminate\Support\Facades\Redis;
use Closure;

class SingleflightClient
{
    public static function run(string $key, Closure $fn, int $ttl = 300)
    {
        // Check if the key is currently being processed by another request.
        $lockKey = "singleflight:{$key}:lock";
        if (Redis::exists($lockKey)) {
            // Wait for up to 5 seconds for the lock to be released.
            Redis::blpop($lockKey, 5);
        }

        // Check if the key is already cached in Redis.
        $cacheKey = "singleflight:{$key}:cache";
        $cachedResult = Redis::get($cacheKey);
        if ($cachedResult !== false) {
            return unserialize($cachedResult);
        }

        // Acquire a lock on the key to prevent concurrent processing.
        Redis::setex($lockKey, 5, 1);

        // Execute the function and cache the result.
        $result = $fn();
        Redis::setex($cacheKey, $ttl, serialize($result));

        // Release the lock on the key.
        Redis::del($lockKey);
        Redis::lpush($lockKey, 1);

        return $result;
    }
}
