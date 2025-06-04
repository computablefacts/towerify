<?php

namespace App\Events;

use App\Check\AssetsDiscoverCheck;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartAssetsDiscover
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public AssetsDiscoverCheck $check;

    public function __construct(AssetsDiscoverCheck $check)
    {
        $this->check = $check;
    }
}
