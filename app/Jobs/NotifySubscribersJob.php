<?php

namespace App\Jobs;

use App\Infrastructure\Repositories\Subscription\SubscriptionRepositoryInterface;
use App\Mail\PriceChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifySubscribersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $advertId,
        private readonly ?float $oldPrice,
        private readonly ?float $newPrice,
        private readonly array $priceHistory
    ) {
    }

    public function handle(SubscriptionRepositoryInterface $subsRepo)
    {
        $emails = $subsRepo->getVerifiedEmailsByAdvertId($this->advertId);

        foreach ($emails as $email) {
            Mail::to($email)->queue(
                new PriceChangedMail(
                    $this->advertId,
                    $this->oldPrice,
                    $this->newPrice,
                    $this->priceHistory
                )
            );
        }
    }
}
