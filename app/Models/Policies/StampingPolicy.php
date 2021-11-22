<?php

namespace hDSSolutions\Laravel\Models\Policies;

use hDSSolutions\Laravel\Models\Stamping as Resource;
use HDSSolutions\Laravel\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StampingPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->can('stampings.crud.index');
    }

    public function view(User $user, Resource $resource) {
        return $user->can('stampings.crud.show');
    }

    public function create(User $user) {
        return $user->can('stampings.crud.create');
    }

    public function update(User $user, Resource $resource) {
        return $user->can('stampings.crud.update');
    }

    public function delete(User $user, Resource $resource) {
        return $user->can('stampings.crud.destroy');
    }

    public function restore(User $user, Resource $resource) {
        return $user->can('stampings.crud.destroy');
    }

    public function forceDelete(User $user, Resource $resource) {
        return $user->can('stampings.crud.destroy');
    }
}
