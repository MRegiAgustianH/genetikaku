<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Drop-in replacement for {@see RefreshDatabase} that guarantees the database is
 * never auto-seeded when it is refreshed.
 *
 * Laravel's RefreshDatabase decides whether to run `migrate:fresh --seed` via
 * `shouldSeed()`, which falls back to reading a truthy `$seed` property on the
 * test case. Eris's `Eris\TestTrait` also declares a `protected $seed` property
 * (its random-number-generator seed, a large microtime-based integer). When a
 * property-based (Eris) test happens to be the first RefreshDatabase test in a
 * run, that truthy `$seed` is misread as "seed the database", so the full
 * DatabaseSeeder runs once during migrate:fresh and its rows (e.g.
 * test@example.com) are committed before the per-test rollback transaction
 * begins. Those rows then leak into every later test and break suites that
 * assume a clean database.
 *
 * Because a method declared directly on a trait takes precedence over a method
 * pulled in from a trait it `use`s, this override reliably wins over
 * RefreshDatabase's own `shouldSeed()`, keeping every refreshed database empty.
 */
trait RefreshDatabaseWithoutSeeding
{
    use RefreshDatabase;

    /**
     * Never auto-seed the database when refreshing it.
     */
    protected function shouldSeed()
    {
        return false;
    }
}
