<?php
declare(strict_types=1);

namespace App\Repositories;

use Laravel\Sanctum\PersonalAccessToken;

class TokenRepository
{
    /**
     * Revoke (delete) a bunch of token records by IDs.
     *
     * @param  int[]  $tokenIds
     */
    public function revoke(array $tokenIds): void
    {
        if (empty($tokenIds)) {
            return;
        }

        PersonalAccessToken::whereIn('id', $tokenIds)->delete();
    }
}