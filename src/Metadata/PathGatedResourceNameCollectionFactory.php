<?php

namespace Webkul\BagistoApi\Metadata;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

/**
 * Skips the API resource enumeration for non-API HTTP requests.
 *
 * When Laravel's route cache is off, API Platform's route file iterates every
 * #[ApiResource] on every request to register ~700 routes — a cost that plain
 * admin/shop web pages (e.g. /admin/cms) pay too, and that cold-builds all
 * resource metadata after a cache clear (seconds, enough to trip the 30s limit).
 *
 * A non-API request has no API route to register, so return an empty collection
 * and skip the whole build. Console (route:cache / warm-cache / route:list) and
 * real /api requests always get the full set — the cached route table and the
 * live API are unaffected. With route cache on, the route file never runs, so
 * this gate is inert.
 */
final class PathGatedResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $inner,
    ) {}

    public function create(): ResourceNameCollection
    {
        return $this->shouldEnumerate()
            ? $this->inner->create()
            : new ResourceNameCollection([]);
    }

    private function shouldEnumerate(): bool
    {
        // Only console commands that explicitly need API schema/routes should
        // enumerate resources. Commands such as package:discover, migrate,
        // config:cache, queue workers, and schedulers must not require a live
        // catalog schema just to bootstrap Laravel.
        if (app()->runningInConsole()) {
            return $this->consoleCommandRequiresResources();
        }

        // No HTTP request context — never gate.
        if (! app()->bound('request')) {
            return true;
        }

        $path = trim(request()->getPathInfo(), '/');

        return $path === 'api' || str_starts_with($path, 'api/');
    }

    private function consoleCommandRequiresResources(): bool
    {
        $command = $_SERVER['argv'][1] ?? null;

        return in_array($command, [
            'route:cache',
            'route:list',
            'bagisto-api-platform:warm-cache',
            'bagisto-api-platform:optimize',
            'bagisto-api-platform:export-schema',
        ], true);
    }
}
