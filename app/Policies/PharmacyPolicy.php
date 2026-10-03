<?php

namespace App\Policies;

use App\Models\Pharmacy;
use App\Models\User;

class PharmacyPolicy
{
    /**
     * Only Admin can view pharmacy management list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin can view any pharmacy. Staff can view their own.
     */
    public function view(User $user, Pharmacy $pharmacy): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->pharmacy_id === $pharmacy->id;
    }

    /**
     * Only Admin can create pharmacies.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Admin can update pharmacies.
     */
    public function update(User $user, Pharmacy $pharmacy): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only Admin can manage pharmacy users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->isAdmin();
    }
}
