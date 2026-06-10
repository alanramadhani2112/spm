<?php

namespace App\Events;

use App\Models\Akreditasi;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AkreditasiTransitioned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Akreditasi $akreditasi,
        public readonly string $fromStatus,
        public readonly string $toStatus,
        public readonly User $actor,
    ) {}
}
