<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'username',
        'company',
        'rfc',
        'account_type',
        'tipo_cliente',
        'giro_industrial',
        'volumen_mensual',
        'ciudad',
        'csf_path',
        'b2b_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Only meaningful when role === 'admin'. Null = full/legacy admin with no
     * module restriction; set = scoped to that Role's allowed module_keys
     * (see ModuleAccessMiddleware).
     */
    public function customRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function canAccessModule(string $moduleKey): bool
    {
        if ($this->role !== 'admin' || !$this->role_id) {
            return true; // legacy/full admin, or not an admin-panel account at all
        }
        $role = $this->customRole;
        if (!$role || $role->is_system) {
            return true;
        }
        return in_array($moduleKey, $role->allowedModuleKeys(), true);
    }
}
