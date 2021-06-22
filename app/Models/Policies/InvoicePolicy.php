<?php

namespace hDSSolutions\Finpar\Models\Policies;

use hDSSolutions\Finpar\Models\Invoice as Resource;
use HDSSolutions\Finpar\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy {
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->can('invoices.crud.index');
    }

    public function view(User $user, Resource $resource) {
        return $user->can('invoices.crud.show');
    }

    public function create(User $user) {
        return $user->can('invoices.crud.create');
    }

    public function update(User $user, Resource $resource) {
        return $user->can('invoices.crud.update');
    }

    public function delete(User $user, Resource $resource) {
        return $user->can('invoices.crud.destroy');
    }

    public function restore(User $user, Resource $resource) {
        return $user->can('invoices.crud.destroy');
    }

    public function forceDelete(User $user, Resource $resource) {
        return $user->can('invoices.crud.destroy');
    }
}
