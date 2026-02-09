<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Wrapper para el trait HasPOConfirmation del módulo PO Confirmation
 * Este trait siempre está disponible y verifica si el módulo está activo
 */
trait HasPOConfirmationWrapper
{
    /**
     * Verifica si el módulo PO Confirmation está disponible y activo
     */
    protected function isPOConfirmationAvailable(): bool
    {
        return class_exists('RagaOrders\POConfirmation\Traits\HasPOConfirmation') &&
               config('po-confirmation.enabled', false);
    }

    /**
     * Obtiene el trait real si está disponible
     */
    protected function getPOConfirmationTrait()
    {
        if ($this->isPOConfirmationAvailable()) {
            return new class {
                use \RagaOrders\POConfirmation\Traits\HasPOConfirmation;
            };
        }
        return null;
    }

    // Métodos que delegan al trait real si está disponible

    public function scopePendingConfirmation(Builder $query): Builder
    {
        // Verificar si el módulo está activo
        if (!config('po-confirmation.enabled', false)) {
            return $query->whereRaw('1 = 0'); // Retorna resultados vacíos
        }

        // Implementar el scope directamente
        return $query->where(function($q) {
            $q->whereNull('confirmation_hash')
              ->orWhere('confirmation_email_sent', false)
              ->orWhere('confirm_update_date_po', false);
        });
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->scopeConfirmed($query);
        }
        return $query->whereRaw('1 = 0'); // Retorna resultados vacíos
    }

    public function scopeRejected(Builder $query): Builder
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->scopeRejected($query);
        }
        return $query->whereRaw('1 = 0'); // Retorna resultados vacíos
    }

    public function scopeNeedingEmailType(Builder $query, int $emailType): Builder
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->scopeNeedingEmailType($query, $emailType);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            // Get the configured days for this email type
            $emailDays = $this->getTimingSetting("email{$emailType}_days", [1 => 5, 2 => 3, 3 => 1, 4 => 0][$emailType]);

            // Calculate the cutoff date based on date_theorical_load
            $cutoffDate = now()->addDays($emailDays);

            return $query->where('confirm_update_date_po', false)
                        ->whereNotNull('date_theorical_load')
                        ->where('date_theorical_load', '<=', $cutoffDate)
                        ->where('date_theorical_load', '>=', now())
                        ->where(function ($q) use ($emailType) {
                            // Check if this specific email type hasn't been sent yet
                            $q->whereRaw("NOT EXISTS (
                                SELECT 1 FROM jsonb_array_elements(COALESCE(email_sent_history::jsonb, '[]'::jsonb)) AS elem
                                WHERE elem->>'type' = ?
                            )", [$emailType]);
                        });
        }

        return $query->whereRaw('1 = 0'); // Retorna resultados vacíos
    }

    public function isPendingConfirmation(): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->isPendingConfirmation();
        }
        return false;
    }

    public function isConfirmed(): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->isConfirmed();
        }
        return false;
    }

    public function isRejected(): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->isRejected();
        }
        return false;
    }

    public function getConfirmationStatus(): ?string
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->getConfirmationStatus();
        }
        return null;
    }

    public function getConfirmationDate(): ?string
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->getConfirmationDate();
        }
        return null;
    }

    public function getRejectionReason(): ?string
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->getRejectionReason();
        }
        return null;
    }

    public function generateConfirmationHash(): ?string
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->generateConfirmationHash();
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            $hash = \Illuminate\Support\Str::random(64);
            $expiryHours = (int) config('po-confirmation.hash_expiry_hours', 72);

            $this->update([
                'confirmation_hash' => $hash,
                'hash_expires_at' => now()->addHours($expiryHours),
            ]);

            return $hash;
        }

        return null;
    }

    public function isConfirmationHashValid(string $hash): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->isConfirmationHashValid($hash);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            return $this->confirmation_hash === $hash &&
                   $this->hash_expires_at &&
                   $this->hash_expires_at->isFuture();
        }

        return false;
    }

    public function confirmPO(?string $newDeliveryDate = null): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->confirmPO($newDeliveryDate);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            try {
                // Update delivery date if provided
                if ($newDeliveryDate) {
                    $this->updateDeliveryDate($newDeliveryDate);
                }

                // Mark as confirmed
                $this->update([
                    'confirm_update_date_po' => true,
                    'confirmation_hash' => null,
                    'hash_expires_at' => null,
                ]);

                // Dispatch webhook event for confirmed purchase order
                if (function_exists('dispatch_webhook')) {
                    try {
                        $this->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        
                        \Log::info('po_confirmation:dispatching_webhook', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                            'new_delivery_date' => $newDeliveryDate,
                        ]);
                        
                        dispatch_webhook('purchase_order.updated', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                            'action' => 'po_confirmed_by_vendor',
                            'confirmed_date' => $newDeliveryDate,
                            'data' => $this->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user'])->toArray(),
                        ]);
                        
                        \Log::info('po_confirmation:webhook_dispatched', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('po_confirmation:webhook_error', [
                            'purchase_order_id' => $this->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return true;
            } catch (\Exception $e) {
                \Log::error("Error confirming PO {$this->id}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    public function updateDeliveryDate(string $newDate): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->updateDeliveryDate($newDate);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            try {
                $this->update([
                    'date_variable_date' => $newDate,       // Fecha validada (confirmada por proveedor)
                    'carga_lista_validada' => true,         // Marcar como validada
                    'update_date_po' => $newDate,
                ]);

                // Dispatch webhook event for delivery date update
                if (function_exists('dispatch_webhook')) {
                    try {
                        $this->load(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user']);
                        
                        \Log::info('po_confirmation:dispatching_webhook_date_update', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                            'new_date' => $newDate,
                        ]);
                        
                        dispatch_webhook('purchase_order.updated', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                            'action' => 'delivery_date_updated_by_vendor',
                            'new_delivery_date' => $newDate,
                            'data' => $this->fresh(['products', 'vendor', 'shipTo', 'kanbanStatus', 'comments', 'comments.user'])->toArray(),
                        ]);
                        
                        \Log::info('po_confirmation:webhook_dispatched_date_update', [
                            'purchase_order_id' => $this->id,
                            'order_number' => $this->order_number,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('po_confirmation:webhook_error_date_update', [
                            'purchase_order_id' => $this->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                return true;
            } catch (\Exception $e) {
                \Log::error("Error updating delivery date for PO {$this->id}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

        /**
     * Marcar email como enviado
     */
    public function markEmailAsSent(): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->markEmailAsSent();
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            $this->update([
                'confirmation_email_sent' => true,
                'confirmation_email_sent_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Obtener URL de confirmación
     */
    public function getConfirmationUrl(): ?string
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->getConfirmationUrl();
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            if (!$this->confirmation_hash || !$this->isConfirmationHashValid($this->confirmation_hash)) {
                return null;
            }

            return route('po-confirmation.show', ['hash' => $this->confirmation_hash]);
        }

        return null;
    }

    /**
     * Marcar tipo específico de email como enviado
     */
    public function markEmailTypeAsSent(int $emailType): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->markEmailTypeAsSent($emailType);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            try {
                // Get current email history
                $emailHistory = $this->email_sent_history ? json_decode($this->email_sent_history, true) : [];

                // Add new email to history
                $emailHistory[] = [
                    'type' => $emailType,
                    'sent_at' => now()->toISOString(),
                    'days_until_delivery' => $this->date_theorical_load ? now()->diffInDays($this->date_theorical_load, false) : null
                ];

                $this->update([
                    'last_email_type_sent' => $emailType,
                    'last_email_sent_at' => now(),
                    'email_sent_history' => json_encode($emailHistory),
                    'confirmation_email_sent' => true,
                    'confirmation_email_sent_at' => now(),
                ]);

                \Log::info("Email type {$emailType} marked as sent for PO {$this->order_number}");
                return true;
            } catch (\Exception $e) {
                \Log::error("Error marking email type {$emailType} as sent for PO {$this->id}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Verificar si un tipo específico de email ya se envió
     */
    public function hasEmailTypeBeenSent(int $emailType): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->hasEmailTypeBeenSent($emailType);
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            if (!$this->email_sent_history) {
                return false;
            }

            $emailHistory = json_decode($this->email_sent_history, true);

            foreach ($emailHistory as $email) {
                if ($email['type'] == $emailType) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Obtener el siguiente tipo de email que se debe enviar
     */
    public function getNextEmailTypeToSend(): ?int
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->getNextEmailTypeToSend();
        }

        // Implementación directa si el módulo está activo
        if (config('po-confirmation.enabled', false)) {
            if (!$this->date_theorical_load) {
                return null;
            }

            $daysUntilDelivery = now()->diffInDays($this->date_theorical_load, false);

            // Get email settings
            $email1Days = $this->getTimingSetting('email1_days', 5);
            $email2Days = $this->getTimingSetting('email2_days', 3);
            $email3Days = $this->getTimingSetting('email3_days', 1);
            $email4Days = $this->getTimingSetting('email4_days', 0);

            // Check which emails should be sent based on days until delivery
            $emailsToSend = [];

            if ($daysUntilDelivery <= $email1Days && !$this->hasEmailTypeBeenSent(1)) {
                $emailsToSend[] = 1;
            }
            if ($daysUntilDelivery <= $email2Days && !$this->hasEmailTypeBeenSent(2)) {
                $emailsToSend[] = 2;
            }
            if ($daysUntilDelivery <= $email3Days && !$this->hasEmailTypeBeenSent(3)) {
                $emailsToSend[] = 3;
            }
            if ($daysUntilDelivery <= $email4Days && !$this->hasEmailTypeBeenSent(4)) {
                $emailsToSend[] = 4;
            }

            // Return the highest priority email (lowest number) that should be sent
            return !empty($emailsToSend) ? min($emailsToSend) : null;
        }

        return null;
    }

    /**
     * Obtener configuración de timing
     */
    protected function getTimingSetting(string $key, $default = null)
    {
        if (class_exists('RagaOrders\POConfirmation\Models\POConfirmationSetting')) {
            return \RagaOrders\POConfirmation\Models\POConfirmationSetting::getValue($key, $default);
        }
        return $default;
    }

    /**
     * Determinar qué tipo de email se debe enviar
     */
    public function getEmailTypeToSend(): ?int
    {
        return $this->getNextEmailTypeToSend();
    }

    // Métodos estáticos que necesita el servicio
    public static function withValidHash()
    {
        if (config('po-confirmation.enabled', false) &&
            class_exists('RagaOrders\POConfirmation\Traits\HasPOConfirmation')) {
            return static::whereNotNull('confirmation_hash')
                        ->where('hash_expires_at', '>', now());
        }
        return static::whereRaw('1 = 0'); // Retorna resultados vacíos
    }

    public static function withExpiredHash()
    {
        if (config('po-confirmation.enabled', false) &&
            class_exists('RagaOrders\POConfirmation\Traits\HasPOConfirmation')) {
            return static::whereNotNull('confirmation_hash')
                        ->where('hash_expires_at', '<=', now());
        }
        return static::whereRaw('1 = 0'); // Retorna resultados vacíos
    }
}
