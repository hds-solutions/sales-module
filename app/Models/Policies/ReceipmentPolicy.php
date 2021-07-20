<?php

namespace hDSSolutions\Laravel\Models\Policies;

use hDSSolutions\Laravel\Models\Receipment as Resource;
use HDSSolutions\Laravel\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceipmentPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->can('receipments.crud.index');
    }

    public function view(User $user, Resource $resource) {
        return $user->can('receipments.crud.show');
    }

    public function create(User $user) {
        return $user->can('receipments.crud.create');
    }

    public function update(User $user, Resource $resource) {
        return $user->can('receipments.crud.update');
    }

    public function delete(User $user, Resource $resource) {
        return $user->can('receipments.crud.destroy');
    }

    public function restore(User $user, Resource $resource) {
        return $user->can('receipments.crud.destroy');
    }

    public function forceDelete(User $user, Resource $resource) {
        return $user->can('receipments.crud.destroy');
    }
}
