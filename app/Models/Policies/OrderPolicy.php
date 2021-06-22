<?php

namespace hDSSolutions\Finpar\Models\Policies;

use hDSSolutions\Finpar\Models\Order as Resource;
use HDSSolutions\Finpar\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->can('orders.crud.index');
    }

    public function view(User $user, Resource $resource) {
        return $user->can('orders.crud.show');
    }

    public function create(User $user) {
        return $user->can('orders.crud.create');
    }

    public function update(User $user, Resource $resource) {
        return $user->can('orders.crud.update');
    }

    public function delete(User $user, Resource $resource) {
        return $user->can('orders.crud.destroy');
    }

    public function restore(User $user, Resource $resource) {
        return $user->can('orders.crud.destroy');
    }

    public function forceDelete(User $user, Resource $resource) {
        return $user->can('orders.crud.destroy');
    }
}
