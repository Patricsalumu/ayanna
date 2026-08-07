<?php

namespace App\Services;

class PermissionService
{
    public const ROLE_ADMINISTRATEUR = 'Administrateur';
    public const ROLE_CAISSIER = 'Caissier';
    public const ROLE_SERVEUSE = 'Serveuse';

    public function canAccessTable(?object $user, ?object $table): bool
    {
        if (!$user || !$table) {
            return false;
        }

        if ($this->isCashier($user)) {
            return true;
        }

        if ($this->isWaitress($user)) {
            return (int) ($table->serveuse_id ?? 0) === (int) ($user->id ?? 0);
        }

        return true;
    }

    public function canOpenTable(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user) || $this->isWaitress($user);
    }

    public function canViewInvoices(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canValidatePayment(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canPrintBill(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user) || $this->isWaitress($user);
    }

    public function canPrintReceipt(?object $user): bool
    {
        return $this->isCashier($user);
    }

    public function canApplyDiscount(?object $user): bool
    {
        return $this->isCashier($user);
    }

    public function canEditPayment(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canManageProductQuantity(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }
    
    public function canAddProductsToTable(?object $user): bool
    {
        return !$this->isCashier($user);
    }

    public function resolveServeuseId(?object $user, ?string $currentServeuseId): ?string
    {
        if ($this->isWaitress($user)) {
            $currentValue = trim((string) ($currentServeuseId ?? ''));

            if ($currentValue !== '') {
                return $currentValue;
            }

            return (string) ($user->id ?? '');
        }

        return $currentServeuseId;
    }

    public function isAdmin(?object $user): bool
    {
        return $this->normalizeRole($user?->role) === self::ROLE_ADMINISTRATEUR;
    }

    public function isCashier(?object $user): bool
    {
        return $this->normalizeRole($user?->role) === self::ROLE_CAISSIER;
    }

    public function isWaitress(?object $user): bool
    {
        return $this->normalizeRole($user?->role) === self::ROLE_SERVEUSE;
    }

    protected function normalizeRole(?string $role): string
    {
        $normalized = strtolower(trim((string) $role));

        return match ($normalized) {
            'administrateur', 'admin', 'super_admin' => self::ROLE_ADMINISTRATEUR,
            'caissier', 'cashier', 'comptoiriste' => self::ROLE_CAISSIER,
            'serveuse', 'waitress', 'cuisinière' => self::ROLE_SERVEUSE,
            default => (string) $role,
        };
    }
}
