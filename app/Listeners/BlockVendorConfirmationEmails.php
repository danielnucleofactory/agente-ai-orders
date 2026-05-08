<?php

namespace App\Listeners;

use App\Models\Vendor;
use App\Support\EmailList;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

class BlockVendorConfirmationEmails
{
    /**
     * Cancel supplier-facing confirmation emails unless explicitly enabled.
     */
    public function handle(MessageSending $event): bool
    {
        if (config('po-confirmation.vendor_emails_enabled', false)) {
            return true;
        }

        $recipientEmails = $this->recipientEmails($event);
        if ($recipientEmails === []) {
            return true;
        }

        $vendorEmails = Vendor::query()
            ->whereNotNull('email')
            ->pluck('email')
            ->flatMap(fn ($email) => EmailList::parse($email))
            ->intersect($recipientEmails)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($vendorEmails === []) {
            return true;
        }

        Log::warning('vendor_email_blocked', [
            'subject' => method_exists($event->message, 'getSubject')
                ? $event->message->getSubject()
                : null,
            'recipients' => $vendorEmails,
            'reason' => 'PO_CONFIRMATION_VENDOR_EMAILS_ENABLED=false',
        ]);

        return false;
    }

    private function recipientEmails(MessageSending $event): array
    {
        $addresses = [
            ...$event->message->getTo(),
            ...$event->message->getCc(),
            ...$event->message->getBcc(),
        ];

        return collect($addresses)
            ->map(fn ($address) => strtolower(trim($address->getAddress())))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
