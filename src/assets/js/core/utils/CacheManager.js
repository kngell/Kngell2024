import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("ValidatorCacheManager");

export default class CacheManager {
  constructor(options = {}) {
    this.cache = new Map();
    this.maxSize = options.maxSize || 1000;
    this.ttl = options.ttl || 0; // Time to live in ms (0 = no expiry)
    this.enableStats = options.enableStats || false;

    if (this.enableStats) {
      this.stats = {
        hits: 0,
        misses: 0,
        sets: 0,
        evictions: 0,
      };
    }
  }

  /**
   * Generate cache key from field and rule
   */
  generateKey(fieldName, ruleName, ruleValue, context = {}) {
    const ruleValueStr = this.serializeRuleValue(ruleValue);
    const contextStr = Object.keys(context).length ? `:${JSON.stringify(context)}` : "";

    return `${fieldName}:${ruleName}:${ruleValueStr}${contextStr}`;
  }

  /**
   * Serialize rule value for cache key
   */
  serializeRuleValue(ruleValue) {
    if (ruleValue === null) return "null";
    if (ruleValue === undefined) return "undefined";

    switch (typeof ruleValue) {
      case "object":
        return JSON.stringify(ruleValue);
      case "boolean":
        return ruleValue ? "true" : "false";
      case "number":
      case "string":
        return String(ruleValue);
      default:
        return String(ruleValue);
    }
  }

  /**
   * Get validator from cache
   */
  get(key) {
    if (!this.cache.has(key)) {
      if (this.enableStats) this.stats.misses++;
      return null;
    }

    const entry = this.cache.get(key);

    // Check TTL expiry
    if (this.ttl > 0 && Date.now() - entry.timestamp > this.ttl) {
      this.cache.delete(key);
      if (this.enableStats) this.stats.evictions++;
      return null;
    }

    if (this.enableStats) this.stats.hits++;
    return entry.validator;
  }

  /**
   * Set validator in cache
   */
  set(key, validator) {
    // Enforce max size
    if (this.cache.size >= this.maxSize) {
      this.evictOldest();
    }

    this.cache.set(key, {
      validator,
      timestamp: Date.now(),
    });

    if (this.enableStats) this.stats.sets++;

    return this;
  }

  /**
   * Check if key exists in cache
   */
  has(key) {
    if (!this.cache.has(key)) return false;

    // Check TTL expiry
    if (this.ttl > 0) {
      const entry = this.cache.get(key);
      if (Date.now() - entry.timestamp > this.ttl) {
        this.cache.delete(key);
        if (this.enableStats) this.stats.evictions++;
        return false;
      }
    }

    return true;
  }

  /**
   * Remove entry from cache
   */
  delete(key) {
    return this.cache.delete(key);
  }

  /**
   * Clear cache entries for a specific field
   */
  clearField(fieldName) {
    let count = 0;
    for (const key of this.cache.keys()) {
      if (key.startsWith(`${fieldName}:`)) {
        this.cache.delete(key);
        count++;
      }
    }
    logger.debug(`Cleared ${count} cache entries for field: ${fieldName}`);
    return count;
  }

  /**
   * Clear cache entries matching pattern
   */
  clearPattern(pattern) {
    let count = 0;
    const regex = new RegExp(pattern);
    for (const key of this.cache.keys()) {
      if (regex.test(key)) {
        this.cache.delete(key);
        count++;
      }
    }
    logger.debug(`Cleared ${count} cache entries matching pattern: ${pattern}`);
    return count;
  }

  /**
   * Clear entire cache
   */
  clear() {
    const size = this.cache.size;
    this.cache.clear();
    logger.debug(`Cleared entire cache (${size} entries)`);
    return size;
  }

  /**
   * Evict oldest entry when cache is full
   */
  evictOldest() {
    let oldestKey = null;
    let oldestTimestamp = Infinity;

    for (const [key, entry] of this.cache.entries()) {
      if (entry.timestamp < oldestTimestamp) {
        oldestTimestamp = entry.timestamp;
        oldestKey = key;
      }
    }

    if (oldestKey) {
      this.cache.delete(oldestKey);
      if (this.enableStats) this.stats.evictions++;
      logger.debug(`Evicted oldest cache entry: ${oldestKey}`);
    }
  }

  /**
   * Get cache statistics
   */
  getStats() {
    if (!this.enableStats) {
      return {
        size: this.cache.size,
        keys: Array.from(this.cache.keys()),
      };
    }

    return {
      size: this.cache.size,
      hits: this.stats.hits,
      misses: this.stats.misses,
      sets: this.stats.sets,
      evictions: this.stats.evictions,
      hitRate:
        this.stats.hits + this.stats.misses > 0
          ? ((this.stats.hits / (this.stats.hits + this.stats.misses)) * 100).toFixed(2) + "%"
          : "0%",
      keys: Array.from(this.cache.keys()),
    };
  }

  /**
   * Get all cached validators for debugging
   */
  getAll() {
    const result = {};
    for (const [key, entry] of this.cache.entries()) {
      result[key] = {
        validator: entry.validator.constructor.name,
        cached: new Date(entry.timestamp).toISOString(),
      };
    }
    return result;
  }
}
