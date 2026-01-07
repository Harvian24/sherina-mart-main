<?php
declare(strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Variety;
use App\Models\Category;
use App\Models\SaleTransaction;

class Product extends Model
{
    use HasUuids;
    protected $fillable = ["code", "name"];
    protected $casts = ["code" => "integer", "name" => "string", "price" => "integer"];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(Variety::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sale_transactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class);
    }
}
