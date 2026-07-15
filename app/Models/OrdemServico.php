<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemServico extends Model
{
    protected $table = 'ordens_servico';

    /**
     * A tabela não tem coluna updated_at (schema define apenas created_at).
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'veiculo_id',
        'valor',
        'status',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function veiculo(): BelongsTo
    {
        return $this->belongsTo(Veiculo::class);
    }
}
