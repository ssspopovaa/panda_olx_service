<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Repositories\AdvertRepositoryInterface;

class PriceChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $priceHistory;

    public function __construct(
        private readonly int $advertId,
        private readonly ?float $oldPrice,
        private readonly ?float $newPrice,
        private readonly AdvertRepositoryInterface $advertRepo
    ) {
        $this->priceHistory = $advertRepo->getPriceHistory($advertId);
    }

    public function build()
    {
        return $this->subject('Price changed for advert')
            ->view('emails.price_changed')
            ->with([
                'advertId' => $this->advertId,
                'old' => $this->oldPrice,
                'new' => $this->newPrice,
                'priceHistory' => $this->priceHistory
            ]);
    }
}
