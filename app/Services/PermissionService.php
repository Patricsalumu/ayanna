<?php

namespace App\Services;

class PermissionService
{
    public const ROLE_ADMINISTRATEUR = 'Administrateur';
    public const ROLE_CAISSIER = 'Caissier';
    public const ROLE_CAISSIER_1 = 'Caissier1';
    public const ROLE_CAISSIER_2 = 'Caissier2';
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
        return $this->isCashier($user) || $this->isWaitress($user);
    }

    public function canManageSalesSession(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canEditPayment(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canManageProductQuantity(?object $user): bool
    {
        return $this->isAdmin($user) || $this->isCashier($user);
    }

    public function canEditServeuseAssignment(?object $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isAdmin($user) || $this->isCashierType1($user);
    }

    public function canAccessPointDeVente(?object $user, ?int $pointDeVenteId): bool
    {
        if (!$user || !$pointDeVenteId) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (!method_exists($user, 'pointsDeVente')) {
            return true;
        }

        $assignedIds = $user->pointsDeVente()->pluck('points_de_vente.id');

        if ($assignedIds->isEmpty()) {
            // Compatibilité ascendante: pas d'assignation explicite => accès entreprise inchangé.
            return true;
        }

        return $assignedIds->contains((int) $pointDeVenteId);
    }
    
    public function canAddProductsToTable(?object $user): bool
    {
        if ($this->isCashierType1($user)) {
            return true;
        }

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

    public function isSuperAdmin(?object $user): bool
    {
        return strtolower(trim((string) ($user?->role ?? ''))) === 'super_admin';
    }

    public function isCashier(?object $user): bool
    {
        return in_array($this->normalizeRole($user?->role), [
            self::ROLE_CAISSIER,
            self::ROLE_CAISSIER_1,
            self::ROLE_CAISSIER_2,
        ], true);
    }

    public function isCashierType1(?object $user): bool
    {
        return $this->normalizeRole($user?->role) === self::ROLE_CAISSIER_1;
    }

    public function isCashierType2(?object $user): bool
    {
        return $this->normalizeRole($user?->role) === self::ROLE_CAISSIER_2;
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
            'caissier1', 'caissier_1', 'cashier1', 'cashier_1', 'comptoiriste1', 'comptoiriste_1' => self::ROLE_CAISSIER_1,
            'caissier2', 'caissier_2', 'cashier2', 'cashier_2', 'comptoiriste2', 'comptoiriste_2' => self::ROLE_CAISSIER_2,
            'serveuse', 'waitress', 'cuisinière' => self::ROLE_SERVEUSE,
            default => (string) $role,
        };
    }
}
