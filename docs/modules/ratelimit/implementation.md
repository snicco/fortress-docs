# Rate Limiting implementation

<!-- TOC -->
* [Token Bucket Rate Limiter](#token-bucket-rate-limiter)
* [Storage backends](#storage-backends)
<!-- TOC -->

---

Before we dig deeper, it's necessary to clarify one thing:

`Rate limiting ≠ Login throttling`.

Rate Limiting is a general-purpose concept where access to a resource is limited based on an arbitrary key.

Login Throttling is ONE application of rate limiting. Other applications might include:

- Password reset throttling.
- Preventing CC testing.
- Limiting access to APIs
- etc.

Fortress contains a custom [Token-Bucket](https://en.wikipedia.org/wiki/Token_bucket) Rate Limiter and currently uses it for the following concrete applications:

- [Login Throttling](login-throttling.md)
- [Password reset throttling](password-reset-throttling.md)

In the future, we might open up the PHP API to the rate limiter, so you can implement your applications.

## Token Bucket Rate Limiter

There are many documents available for all the technical and mathematical details, but to keep things simple, this is how it works:

- A bucket is created with an initial set of tokens (`burst`);
- A new token is added to the bucket with a predefined `refill frequency` (e.g., every second);
- Allowing an event consumes one or more tokens;
- If the bucket still contains tokens, the event is allowed; otherwise, it's denied;
- If the bucket is at total capacity, new tokens are discarded;

A bucket with `five` initial tokens (`burst`) and a `refill frequency` of `one` token per second would allow the following request flow:

- Request 1 => OK => 4 tokens left.
- Request 2 => OK => 3 tokens left.
- Request 3 => OK => 2 tokens left.
- Request 4 => OK => 1 token left.
- Request 5 => OK => 0 tokens left.
- Request 6 => DENIED.
- `Wait two seconds` => 2 tokens are available again.
- Request 7 => OK => 1 token left.
- Request 8 => OK => 0 tokens left.
- Request 9 => DENIED => 0 tokens lefts.
- `Wait 5 seconds` => 5 tokens available again.
- `Wait 5 more seconds` => Still 5 tokens available, the bucket "overflows."
- Request 10 => OK => 4 tokens left.

Over the long term, this bucket allows one request per second, which happens to be the (`refill frequency`).

## Storage backends

The only information that needs to be persisted is the number of available tokens and the last access timestamp.

Naturally, an in-memory cache like Redis is the perfect candidate for storing rate limit information because
it can be stored with a cache lifetime of `cache_lifetime = burst / refill frequency`).
If no rate limit information exists (anymore) in the cache, the bucket is at total capacity.

Fortress will use the site's [object cache](https://developer.wordpress.org/reference/classes/wp_object_cache/#persistent-caching) if it is persistent.
If no persistent object cache is installed, Fortress will fall back to using a custom database table.

Every object cache plugin will work since Fortress makes no assumptions about the actual cache implementation. Instead, it only calls the `wp_cache_XXX` functions.

**Important:**

Flushing the WordPress object cache will reset all rate limits if the object cache backend is used.

---

Next: [Login throttling](login-throttling.md).
