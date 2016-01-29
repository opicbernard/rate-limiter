# rate-limiter
[![GNU General Public License v3.0](https://img.shields.io/badge/license-GNU%20GPL%20v3.0-green.png)](https://raw.githubusercontent.com/opicbernard/git-diffview/master/LICENSE.md)

A simple PHP class implementing a Memcache-based version of the [Token Bucket](https://en.wikipedia.org/wiki/Token_bucket)  algorithm to perform rate limiting on resources.

## Installation

Copy the rate-limiter.php file in your prefered includes location.

## Usage

```code
require_once('includes/rate-limiter.php');

$_30_times = 30;
$_per_hour = 60 * 60.0;
try {
	if ((new RateLimiter())->allow("resource-to-rate-limit", $_30_times, $_per_hour)) {
		// Access granted
	} else {
		// Access denied
	}
} catch (Exception $e) {
	// Something went wrong with Memcache
}

```

## Suggestion

Use multiple parameters to compose the resource name.

For example, use "api-auth-$_SERVER[REMOTE_ADDR]" to perform a per client IP rate limiting on a resource named "auth-api".
