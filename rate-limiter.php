<?php
/*
 *	Usage:
 *
 *	$_30_times = 30;
 *	$_per_hour = 60 * 60.0;
 *	try {
 *		if ((new RateLimiter())->allow("resource-to-rate-limit", $_30_times, $_per_hour)) {
 *			// Access granted
 *		} else {
 *			// Access denied
 *		}
 *	} catch (Exception $e) {
 *		// Something went wrong with Memcache
 *	}
 */

if (! class_exists('Memcache')) {
	throw new Exception("RateLimiter requires Memcache.");
}

class RateLimiter {
	private $quota, $ttl, $time, $stock;

	public function allow($key, $quota = 60, $period = 60.0) {
		$allow = true;

		try {
                        $memcache = new Memcache();
		} catch (Exception $e) {
			throw new Exception('Failed to create memcache object.');
                }

		if (! $memcache->connect('127.0.0.1')) {
			throw new Exception('Failed to connect to memcache server.');
		}

		if (! ($cached = $memcache->get($key))) {
			$quota = max(1, intval($quota));
			$period = max(0.000001, floatval($period));

			$this->quota = $quota;
			$this->ttl = $period / $quota;
			$this->time = microtime(true) - $period - $this->ttl;
			$this->stock = $quota - 1;

			if (! $memcache->add($key, $this)) {
				throw new Exception('Failed to add memcache key.');
			}
		} else {
			$this->quota = $cached->quota;
			$this->ttl = $cached->ttl;
			$this->time = microtime(true);
			$this->stock = min($cached->stock + intval(($this->time - $cached->time) / $this->ttl), $this->quota);

			if ($this->stock > 0) {
				$this->stock--;
			} else {
				$allow = false;
			}

			if (! $memcache->replace($key, $this)) {
				throw new Exception('Failed to replace memcache key.');
			}
		}

		if (! $memcache->close()) {
			throw new Exception('Failed to close memcache connection.');
		}

		unset($memcache);

		return $allow;
	}
}

