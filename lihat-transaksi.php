<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\SaleTransaction;
use App\Models\Product;

echo "\n";
echo "========================================\n";
echo "   DATA TRANSAKSI SHERINA MART\n";
echo "========================================\n\n";

// Hitung total transaksi
$totalTransactions = SaleTransaction::count();
echo "Total Transaksi: {$totalTransactions}\n\n";

if ($totalTransactions == 0) {
    echo "Belum ada transaksi.\n";
    exit;
}

// Ambil semua transaksi dengan detail
$transactions = SaleTransaction::with('product')
    ->orderBy('created_at', 'desc')
    ->get();

// Kelompokkan berdasarkan sale_id
$groupedTransactions = $transactions->groupBy('sale_id');

echo "========================================\n";
echo "DAFTAR TRANSAKSI (Dikelompokkan)\n";
echo "========================================\n\n";

$no = 1;
foreach ($groupedTransactions as $saleId => $items) {
    $firstItem = $items->first();
    $tanggal = $firstItem->created_at->format('d-m-Y H:i:s');
    $notaId = $saleId ? substr($saleId, 0, 8) : str_pad((string)$firstItem->id, 3, '0', STR_PAD_LEFT);
    
    echo "Transaksi #{$no}\n";
    echo "No. Nota  : TRX-{$notaId}\n";
    echo "Tanggal   : {$tanggal}\n";
    echo "Items     : {$items->count()} barang\n";
    echo "----------------------------------------\n";
    
    $grandTotal = 0;
    foreach ($items as $item) {
        $product = $item->product;
        if ($product) {
            $subtotal = $item->price * $item->quantity;
            $grandTotal += $subtotal;
            
            printf("  %-20s x%-3d @Rp%10s = Rp%12s\n",
                substr($product->name, 0, 20),
                $item->quantity,
                number_format($item->price, 0, ',', '.'),
                number_format($subtotal, 0, ',', '.')
            );
        } else {
            echo "  [Produk sudah dihapus]\n";
        }
    }
    
    echo "----------------------------------------\n";
    printf("TOTAL: Rp%12s\n\n", number_format($grandTotal, 0, ',', '.'));
    $no++;
}

echo "========================================\n";
echo "RINGKASAN\n";
echo "========================================\n";

// Hitung total penjualan
$totalPenjualan = 0;
$totalItem = 0;

foreach ($transactions as $item) {
    if ($item->product) {
        $totalPenjualan += ($item->price * $item->quantity);
        $totalItem += $item->quantity;
    }
}

echo "Total Transaksi  : {$groupedTransactions->count()}\n";
echo "Total Item Terjual: {$totalItem}\n";
echo "Total Penjualan  : Rp " . number_format($totalPenjualan, 0, ',', '.') . "\n";
echo "========================================\n\n";
