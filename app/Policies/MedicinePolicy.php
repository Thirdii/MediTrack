<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;

class MedicinePolicy
{
    /**
     * Admin can view any medicine. Staff can only view their pharmacy's medicines.
     */
    public function view(User $user, Medicine $medicine): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->pharmacy_id === $medicine->pharmacy_id;
    }

    /**
     * Staff can create medicines for their pharmacy. Admin can create for any.
     */
    public function create(User $user): bool
    {
        return true; // pharmacy_id is enforced at creation time
    }

    /**
     * Admin can update any. Staff only their pharmacy's medicines.
     */
    public function update(User $user, Medicine $medicine): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->pharmacy_id === $medicine->pharmacy_id;
    }

    /**
     * Archive/restore — same as update.
     */
    public function archive(User $user, Medicine $medicine): bool
    {
        return $this->update($user, $medicine);
    }

    public function restore(User $user, Medicine $medicine): bool
    {
        return $this->update($user, $medicine);
    }
}
