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
        return false;
    }

    public function updateDeliveryDate(string $newDate): bool
    {
        if ($this->isPOConfirmationAvailable()) {
            return $this->getPOConfirmationTrait()->updateDeliveryDate($newDate);
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

            return route('po.confirm', ['hash' => $this->confirmation_hash]);
        }

        return null;
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
