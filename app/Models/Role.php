<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\{
    HasAvatarUrl,
    HasRoles,
    HasSocialLogin,
    UsesUuid,
    RoleRelations,
    RolePermissions,
    RoleScopes,
    RoleHelpers
};


class Role extends Model
{

    use HasFactory, UsesUuid;
    use RoleRelations, RolePermissions, RoleScopes, RoleHelpers , 
        HasAvatarUrl, HasRoles, HasSocialLogin;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_default',
        'level'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_default' => 'boolean',
        'level' => 'integer'
    ];

    protected $attributes = [
        'permissions' => '[]',
        'is_default' => false,
        'level' => 0
    ];
}