<?php

namespace App\Jobs;

use App\Models\PageView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ResolveCountry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 10;

    public function __construct(public int $pageViewId) {}

    public function handle(): void
    {
        $view = PageView::find($this->pageViewId);

        if (! $view || ! $view->ip || $view->country) {
            return;
        }

        $response = Http::timeout(8)->get("http://ip-api.com/json/{$view->ip}", [
            'fields' => 'status,country,countryCode',
        ]);

        if ($response->ok() && $response->json('status') === 'success') {
            $view->update(['country' => $response->json('country')]);
        }
    }
}
