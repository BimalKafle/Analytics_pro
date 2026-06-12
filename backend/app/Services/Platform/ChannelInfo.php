<?php

namespace App\Services\Platform;

/**
 * Minimal channel identity used when connecting a platform account.
 */
final readonly class ChannelInfo
{
    public function __construct(
        public string $id,
        public ?string $name,
    ) {}
}
