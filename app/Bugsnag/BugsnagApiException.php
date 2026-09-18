<?php

declare(strict_types=1);

namespace App\Bugsnag;

use RuntimeException;

/**
 * Raised when the Bugsnag Data Access API is unreachable or answers with a
 * status we can't interpret. Callers record the message against the site
 * rather than letting a third-party outage fail the whole sync.
 */
final class BugsnagApiException extends RuntimeException {}
