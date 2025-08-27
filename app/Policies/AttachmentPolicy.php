<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return $user->hasRole('admin')
            || $attachment->uploader->is($user);
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function update(User $user, Attachment $attachment): bool
    {
        return $user->hasRole('admin')
            || $attachment->uploader->is($user);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->hasRole('admin')
            || $attachment->uploader->is($user);
    }

    public function download(User $user, Attachment $attachment): bool
    {
        return $this->view($user, $attachment);
    }

    public function restore(User $user, Attachment $attachment): bool
    {
        return $user->hasRole('admin')
            || $attachment->uploader->is($user);
    }

    public function forceDelete(User $user, Attachment $attachment): bool
    {
        return $user->hasRole('admin');
    }
}