<?php
declare(strict_types=1);
namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\select;
use App\Models\Category;
use App\Models\Variety;
use App\Models\Product;
use App\Models\SaleTransaction;

class MenuCommand extends Command
{
    public Category $category;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:menu-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'menampilkan Menu pada pengguna';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        while (true) {
            $this->line("");
            $this->line("========================================");
            $this->info("       SHERINA MART - MENU UTAMA");
            $this->line("========================================");
            $this->line("1.  Transaksi Pembelian Barang");
            $this->line("2.  Daftar Kategori Barang");
            $this->line("3.  Tambah Kategori Barang");
            $this->line("4.  Ubah Kategori Barang");
            $this->line("5.  Hapus Kategori Barang");
            $this->line("6.  Daftar Jenis Barang");
            $this->line("7.  Tambah Jenis Barang");
            $this->line("8.  Ubah Jenis Barang");
            $this->line("9.  Hapus Jenis Barang");
            $this->line("10. Daftar Barang");
            $this->line("11. Tambah Barang");
            $this->line("12. Ubah Barang");
            $this->line("13. Hapus Barang");
            $this->line("14. Daftar Penjualan Barang");
            $this->line("15. Cetak Ulang Struk Transaksi");
            $this->line("0.  Keluar");
            $this->line("========================================");
            
            $option = $this->ask("Pilih menu (0-15)");
            
            if ($option === null || $option === '0' || $option === 0) {
                break;
            }
            
            $option = (int)$option;

            // $this->info("Anda Memilih Pilihan : {$option}");
            if ($option == 1) {
                $this->info("\n=== TRANSAKSI PEMBELIAN BARANG ===");
                $products = Product::all();
                
                if ($products->isEmpty()) {
                    $this->error("Belum ada barang. Silakan tambah barang terlebih dahulu.");
                    $this->ask("Tekan Enter untuk kembali");
                    continue;
                }
                
                // Keranjang belanja untuk menyimpan multiple items
                $cart = [];
                $saleId = \Illuminate\Support\Str::uuid()->toString();
                
                while (true) {
                    $this->line("\n--- KERANJANG BELANJA ---");
                    if (!empty($cart)) {
                        $this->table(['Kode', 'Nama', 'Qty', 'Harga', 'Subtotal'], 
                            array_map(function($item) {
                                return [
                                    $item['code'],
                                    $item['name'],
                                    $item['quantity'],
                                    number_format($item['price'], 0, ',', '.'),
                                    number_format($item['price'] * $item['quantity'], 0, ',', '.')
                                ];
                            }, $cart)
                        );
                        $total = array_sum(array_map(function($item) {
                            return $item['price'] * $item['quantity'];
                        }, $cart));
                        $this->info("TOTAL: Rp " . number_format($total, 0, ',', '.'));
                    } else {
                        $this->warn("Keranjang masih kosong");
                    }
                    
                    $this->line("\n--- DAFTAR BARANG ---");
                    $this->table(['Kode', 'Nama', 'Harga'], $products->map(function($p) {
                        return [$p->code, $p->name, number_format($p->price, 0, ',', '.')];
                    })->toArray());
                    
                    $this->line("\nPilihan:");
                    $this->line("1. Tambah barang ke keranjang");
                    $this->line("2. Checkout (selesai belanja)");
                    $this->line("0. Batal transaksi");
                    
                    $choice = $this->ask("Pilih aksi (0-2)");
                    
                    if ($choice === '0' || $choice === null) {
                        $this->warn("Transaksi dibatalkan");
                        break;
                    } else if ($choice === '2') {
                        if (empty($cart)) {
                            $this->error("Keranjang masih kosong! Tambahkan barang terlebih dahulu.");
                            continue;
                        }
                        
                        // Simpan semua item di keranjang sebagai transaksi
                        try {
                            $allSaved = true;
                            $transactions = [];
                            
                            foreach ($cart as $item) {
                                $sale = new SaleTransaction;
                                $sale->sale_id = $saleId;
                                $sale->product_id = $item['product_id'];
                                $sale->price = $item['price'];
                                $sale->quantity = $item['quantity'];
                                
                                if ($sale->save()) {
                                    $transactions[] = $sale;
                                } else {
                                    $allSaved = false;
                                    break;
                                }
                            }
                            
                            if ($allSaved) {
                                $total = array_sum(array_map(function($item) {
                                    return $item['price'] * $item['quantity'];
                                }, $cart));
                                
                                $this->info("\n✅ Transaksi berhasil disimpan!");
                                $this->info("Total Bayar: Rp " . number_format($total, 0, ',', '.'));
                                
                                // Cetak struk untuk semua items
                                $this->printReceiptMultiple($transactions, $saleId);
                            } else {
                                $this->error("❌ Transaksi gagal disimpan!");
                                $this->ask("Tekan Enter untuk kembali");
                            }
                        } catch (\Exception $e) {
                            $this->error("❌ ERROR: " . $e->getMessage());
                            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
                            $this->ask("Tekan Enter untuk kembali");
                        }
                        break;
                        
                    } else if ($choice === '1') {
                        $productCode = $this->ask('Masukkan Kode Barang');
                        $choosen_product = Product::where('code', $productCode)->first();
                        
                        if (!$choosen_product) {
                            $this->error("Barang dengan kode tersebut tidak ditemukan!");
                            $this->ask("Tekan Enter untuk melanjutkan");
                            continue;
                        }
                        
                        $quantity = (int)$this->ask("Masukkan Jumlah Barang");
                        
                        if ($quantity <= 0) {
                            $this->error("Jumlah barang harus lebih dari 0!");
                            continue;
                        }
                        
                        // Tambahkan ke keranjang
                        $cart[] = [
                            'product_id' => $choosen_product->id,
                            'code' => $choosen_product->code,
                            'name' => $choosen_product->name,
                            'price' => $choosen_product->price,
                            'quantity' => $quantity
                        ];
                        
                        $this->info("✅ Barang berhasil ditambahkan ke keranjang!");
                    } else {
                        $this->error("Pilihan tidak valid!");
                    }
                }

            } else if ($option == 2) {
                $headers = ['kode', 'nama', 'dibuat', 'diubah'];
                $data = Category::all()->map(function ($item) {
                    return [
                        'kode' => $item->code,
                        'nama' => $item->name,
                        'dibuat' => $item->created_at,
                        'diubah' => $item->updated_at,
                    ];
                })->toArray();
                $this->table($headers, $data);
                $this->ask("Tekan Enter untuk kembali ke menu utama");
            } else if ($option == 3) {
                $this->info("Anda Memilih Pilihan : {$option} Tambah Kategori Barang");
                $category = new Category();
                $category->code = (int)$this->ask("Masukkan Kode Kategori : ");
                $category->name = $this->ask("Masukkan Nama Kategori : ");
                if ($category->save()) {
                    $this->notify("Success", "data berhasil disimpan");
                } else {
                    $this->notify("Failed", "data gagal disimpan");
                }

            } else if ($option == 4) {
                $this->info("Anda Memilih Pilihan : {$option} Ubah Kategori Barang");
                $code = (int)$this->ask("Masukkan Kode Kategori yang akan diubah : ");
                $category = Category::where('code', $code)->first();
                $category->code = (int)$this->ask("Masukkan Kode Kategori : ");
                $category->name = $this->ask("Masukkan Nama Kategori : ");
                if ($category->save()) {
                    $this->notify("Success", "data berhasil diubah");
                } else {
                    $this->notify("Failed", "data gagal diubah");
                }
            } else if ($option == 5) {
                $this->info("Anda Memilih Pilihan : {$option} Hapus Kategori Barang");
                $code = (int)$this->ask("Masukkan Kode Kategori yang akan dihapus : ");
                $category = Category::where('code', $code)->first();
                if ($category->delete()) {
                    $this->notify("Success", "data berhasil dihapus");
                } else {
                    $this->notify("Failed", "data gagal dihapus");
                }
            } else if ($option == 6) {
                $this->info("Anda Memilih Pilihan : {$option} Daftar Jenis Barang");
                $headers = ['kode', 'nama', 'dibuat', 'diubah'];
                $data = Variety::all()->map(function ($item) {
                    return [
                        'kode' => $item->code,
                        'nama' => $item->name,
                        'dibuat' => $item->created_at,
                        'diubah' => $item->updated_at,
                    ];
                })->toArray();
                $this->table($headers, $data);
                $this->ask("Tekan Enter untuk kembali ke menu utama");
            } else if ($option == 7) {
                $this->info("Anda Memilih Pilihan : {$option} Tambah Jenis Barang");
                $variety = new Variety();
                $variety->code = (int)$this->ask("Masukkan Kode Jenis : ");
                $variety->name = $this->ask("Masukkan Nama Jenis : ");
                if ($variety->save()) {
                    $this->notify("Success", "data berhasil disimpan");
                } else {
                    $this->notify("Failed", "data gagal disimpan");
                }
            } else if ($option == 8) {
                $this->info("Anda Memilih Pilihan : {$option} Ubah Jenis Barang");
                $code = (int)$this->ask("Masukkan Kode Jenis yang akan diubah : ");
                $variety = Variety::where('code', $code)->first();
                $variety->code = (int)$this->ask("Masukkan Kode Jenis : ");
                $variety->name = $this->ask("Masukkan Nama Jenis : ");
                if ($variety->save()) {
                    $this->notify("Success", "data berhasil diubah");
                } else {
                    $this->notify("Failed", "data gagal diubah");
                }
            } else if ($option == 9) {
                $this->info("Anda Memilih Pilihan : {$option} Hapus Jenis Barang");
                $code = (int)$this->ask("Masukkan Kode Jenis yang akan dihapus : ");
                $variety = Variety::where('code', $code)->first();
                if ($variety->delete()) {
                    $this->notify("Success", "data berhasil dihapus");
                } else {
                    $this->notify("Failed", "data gagal dihapus");
                }

            } else if ($option == 10) {
                $this->info("Anda Memilih Pilihan : {$option} Daftar Barang");
                $headers = ['kode', 'nama', 'harga', 'kategori', 'jenis','dibuat', 'diubah'];
                $data = Product::with(['category', 'variety'])->get()->map(function ($item) {
                    return [
                        'kode' => $item->code,
                        'nama' => $item->name,
                        'harga' => number_format($item->price, 0, ',', '.'),
                        'kategori' => $item->category ? $item->category->name : '(Dihapus)',
                        'jenis' => $item->variety ? $item->variety->name : '(Dihapus)',
                        'dibuat' => $item->created_at->format('d-m-Y H:i'),
                        'diubah' => $item->updated_at->format('d-m-Y H:i'),
                    ];
                })->toArray();
                $this->table($headers, $data);
                $this->ask("Tekan Enter untuk kembali ke menu utama");
            } else if ($option == 11) {
                $product = new Product();
                $this->info("\n=== TAMBAH BARANG ===");
                
                $categories = Category::all();
                if ($categories->isEmpty()) {
                    $this->error("Belum ada kategori. Silakan tambah kategori terlebih dahulu.");
                    $this->ask("Tekan Enter untuk kembali");
                    continue;
                }
                $this->table(['ID', 'Kode', 'Nama'], $categories->map(function($c) {
                    return [$c->id, $c->code, $c->name];
                })->toArray());
                $product->category_id = $this->ask('Masukkan ID Kategori');

                $varieties = Variety::all();
                if ($varieties->isEmpty()) {
                    $this->error("Belum ada jenis. Silakan tambah jenis terlebih dahulu.");
                    $this->ask("Tekan Enter untuk kembali");
                    continue;
                }
                $this->table(['ID', 'Kode', 'Nama'], $varieties->map(function($v) {
                    return [$v->id, $v->code, $v->name];
                })->toArray());
                $product->variety_id = $this->ask('Masukkan ID Jenis');

                // Validasi kode barang unique
                while (true) {
                    $code = (int)$this->ask("Masukkan Kode Barang : ");
                    $existingProduct = Product::where('code', $code)->first();
                    
                    if ($existingProduct) {
                        $this->error("❌ Kode barang {$code} sudah digunakan oleh '{$existingProduct->name}'!");
                        $this->warn("Silakan gunakan kode yang berbeda.");
                    } else {
                        $product->code = $code;
                        break;
                    }
                }
                
                $product->name = $this->ask("Masukkan Nama Barang : ");
                $product->price = (int)$this->ask("Masukkan Harga Barang : ");
                if ($product->save()) {
                    $this->notify("Success", "data berhasil disimpan");
                } else {
                    $this->notify("Failed", "data gagal disimpan");
                }

            } else if ($option == 12) {
                $this->info("Anda Memilih Pilihan : {$option} Ubah Barang");
                $code = (int)$this->ask("Masukkan Kode Barang yang akan diubah : ");
                $product = Product::where('code', $code)->first();
                
                if (!$product) {
                    $this->error("Barang dengan kode tersebut tidak ditemukan");
                    $this->ask("Tekan Enter untuk kembali");
                } else {
                    $categories = Category::all();
                    $this->table(['ID', 'Kode', 'Nama'], $categories->map(function($c) {
                        return [$c->id, $c->code, $c->name];
                    })->toArray());
                    $product->category_id = $this->ask('Masukkan ID Kategori Baru', $product->category_id);

                    $varieties = Variety::all();
                    $this->table(['ID', 'Kode', 'Nama'], $varieties->map(function($v) {
                        return [$v->id, $v->code, $v->name];
                    })->toArray());
                    $product->variety_id = $this->ask('Masukkan ID Jenis Baru', $product->variety_id);

                    // Validasi kode barang unique
                    while (true) {
                        $code = (int)$this->ask("Masukkan Kode Barang : ", (string)$product->code);
                        $existingProduct = Product::where('code', $code)
                            ->where('id', '!=', $product->id)
                            ->first();
                        
                        if ($existingProduct) {
                            $this->error("❌ Kode barang {$code} sudah digunakan oleh '{$existingProduct->name}'!");
                            $this->warn("Silakan gunakan kode yang berbeda.");
                        } else {
                            $product->code = $code;
                            break;
                        }
                    }
                    
                    $product->name = $this->ask("Masukkan Nama Barang : ", $product->name);
                    $product->price = (int)$this->ask("Masukkan Harga Barang : ", (string)$product->price);
                    
                    if ($product->save()) {
                        $this->notify("Success", "data berhasil diubah");
                    } else {
                        $this->notify("Failed", "data gagal diubah");
                    }
                }
            } else if ($option == 13) {
                $this->info("Anda Memilih Pilihan : {$option} Hapus Barang");
                $code = (int)$this->ask("Masukkan Kode Barang yang akan dihapus : ");
                $product = Product::where('code', $code)->first();
                
                if (!$product) {
                    $this->notify("Failed", "Barang dengan kode tersebut tidak ditemukan");
                } else {
                    if ($product->delete()) {
                        $this->notify("Success", "data berhasil dihapus");
                    } else {
                        $this->notify("Failed", "data gagal dihapus");
                    }
                }
            } else if ($option == 14) {
                $this->info("\n=== DAFTAR PENJUALAN BARANG ===");
                $headers = ['kode', 'nama', 'harga', 'jumlah', 'bayar', 'dibuat', 'diubah'];
                
                // Gunakan eager loading dan filter hanya transaksi yang produknya masih ada
                $data = SaleTransaction::with('product')
                    ->get()
                    ->filter(function ($item) {
                        return $item->product !== null; // Filter hanya yang produknya masih ada
                    })
                    ->map(function ($item) {
                        return [
                            'kode' => $item->product->code,
                            'nama' => $item->product->name,
                            'harga' => number_format($item->product->price, 0, ',', '.'),
                            'jumlah' => $item->quantity,
                            'jumlah bayar' => number_format($item->quantity * $item->product->price, 0, ',', '.'),
                            'dibuat' => $item->created_at->format('d-m-Y H:i'),
                            'diubah' => $item->updated_at->format('d-m-Y H:i'),
                        ];
                    })->toArray();
                
                if (empty($data)) {
                    $this->warn("Belum ada transaksi penjualan.");
                } else {
                    $this->table($headers, $data);
                }
                $this->ask("Tekan Enter untuk kembali ke menu utama");

            } else if ($option == 15) {
                $this->info("\n=== CETAK ULANG STRUK TRANSAKSI ===");
                $transactions = SaleTransaction::with('product')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->filter(function ($item) {
                        return $item->product !== null; // Filter hanya yang produknya masih ada
                    });
                
                if ($transactions->isEmpty()) {
                    $this->error("Belum ada transaksi");
                    $this->ask("Tekan Enter untuk kembali");
                } else {
                    $this->table(['ID', 'Barang', 'Qty', 'Harga', 'Total', 'Tanggal'], 
                        $transactions->map(function ($item) {
                            return [
                                $item->id,
                                $item->product->name,
                                $item->quantity,
                                number_format($item->price, 0, ',', '.'),
                                number_format($item->price * $item->quantity, 0, ',', '.'),
                                $item->created_at->format('d-m-Y H:i')
                            ];
                        })->toArray()
                    );
                    
                    $selectedId = $this->ask('Masukkan ID Transaksi');
                    
                    $selectedTransaction = SaleTransaction::with('product')->find($selectedId);
                    if ($selectedTransaction && $selectedTransaction->product) {
                        $this->printReceipt($selectedTransaction, true);
                    } else {
                        $this->error("Transaksi tidak ditemukan atau produk sudah dihapus");
                        $this->ask("Tekan Enter untuk kembali");
                    }
                }
            }
        }
        
        $this->info("Terimakasih telah menggunakan applikasi kami.");

    }

    /**
     * Print receipt for a sale transaction
     */
    private function printReceipt(SaleTransaction $sale, bool $reprint = false)
    {
        $product = $sale->product;
        
        // Cek apakah produk masih ada
        if (!$product) {
            $this->error("Tidak dapat mencetak struk: Produk sudah dihapus dari sistem");
            $this->ask("Tekan Enter untuk kembali");
            return;
        }
        
        $subtotal = $sale->price * $sale->quantity;
        $transactionId = str_pad((string)$sale->id, 3, '0', STR_PAD_LEFT);
        $date = $sale->created_at->format('d-m-Y H:i:s');
        
        $receipt = "\n";
        $receipt .= "================================================\n";
        $receipt .= "              SHERINA MART\n";
        $receipt .= "          Jalan Mantan No 24\n";
        $receipt .= "         Telp: (021) 1234-5678\n";
        $receipt .= "================================================\n";
        $receipt .= "Tanggal  : {$date}\n";
        $receipt .= "No. Nota : TRX-{$transactionId}\n";
        $receipt .= "------------------------------------------------\n";
        $receipt .= sprintf("%-20s %3s %10s %12s\n", "ITEM", "QTY", "HARGA", "SUBTOTAL");
        $receipt .= "------------------------------------------------\n";
        $receipt .= sprintf("%-20s %3d %10s %12s\n", 
            substr($product->name, 0, 20), 
            $sale->quantity, 
            number_format($sale->price, 0, ',', '.'),
            number_format($subtotal, 0, ',', '.')
        );
        $receipt .= "------------------------------------------------\n";
        $receipt .= sprintf("%36s %12s\n", "TOTAL:", number_format($subtotal, 0, ',', '.'));
        $receipt .= "================================================\n";
        $receipt .= "       Terima Kasih Atas Kunjungan Anda\n";
        $receipt .= "            Selamat Berbelanja Kembali\n";
        $receipt .= "================================================\n";
        
        // Display receipt in terminal
        $this->line($receipt);
        
        // Save to file
        if (!$reprint) {
            $this->saveReceiptToFile($receipt, $sale);
        }
        
        $this->ask("Tekan Enter untuk kembali ke menu utama");
    }
    
    /**
     * Print receipt for multiple items in one transaction
     */
    private function printReceiptMultiple(array $transactions, string $saleId)
    {
        if (empty($transactions)) {
            $this->error("Tidak ada transaksi untuk dicetak");
            return;
        }
        
        $firstTransaction = $transactions[0];
        $date = $firstTransaction->created_at->format('d-m-Y H:i:s');
        $transactionId = substr($saleId, 0, 8);
        
        $receipt = "\n";
        $receipt .= "================================================\n";
        $receipt .= "              SHERINA MART\n";
        $receipt .= "          Jalan  Mantan No 24\n";
        $receipt .= "         Telp: (021) 1234-5678\n";
        $receipt .= "================================================\n";
        $receipt .= "Tanggal  : {$date}\n";
        $receipt .= "No. Nota : TRX-{$transactionId}\n";
        $receipt .= "------------------------------------------------\n";
        $receipt .= sprintf("%-20s %3s %10s %12s\n", "ITEM", "QTY", "HARGA", "SUBTOTAL");
        $receipt .= "------------------------------------------------\n";
        
        $grandTotal = 0;
        foreach ($transactions as $sale) {
            $product = $sale->product;
            if ($product) {
                $subtotal = $sale->price * $sale->quantity;
                $grandTotal += $subtotal;
                $receipt .= sprintf("%-20s %3d %10s %12s\n", 
                    substr($product->name, 0, 20), 
                    $sale->quantity, 
                    number_format($sale->price, 0, ',', '.'),
                    number_format($subtotal, 0, ',', '.')
                );
            }
        }
        
        $receipt .= "------------------------------------------------\n";
        $receipt .= sprintf("%36s %12s\n", "TOTAL:", number_format($grandTotal, 0, ',', '.'));
        $receipt .= "================================================\n";
        $receipt .= "       Terima Kasih Atas Kunjungan Anda\n";
        $receipt .= "            Selamat Berbelanja Kembali\n";
        $receipt .= "================================================\n";
        
        // Display receipt in terminal
        $this->line($receipt);
        
        // Save to file
        $this->saveReceiptToFileMultiple($receipt, $saleId, $firstTransaction);
        
        $this->ask("Tekan Enter untuk kembali ke menu utama");
    }

    
    /**
     * Save receipt to file
     */
    private function saveReceiptToFile(string $receipt, SaleTransaction $sale)
    {
        $receiptsDir = base_path('receipts');
        
        // Create receipts directory if not exists
        if (!is_dir($receiptsDir)) {
            mkdir($receiptsDir, 0755, true);
        }
        
        $transactionId = str_pad((string)$sale->id, 3, '0', STR_PAD_LEFT);
        $date = $sale->created_at->format('Y-m-d');
        $filename = "{$date}_TRX-{$transactionId}.txt";
        $filepath = $receiptsDir . DIRECTORY_SEPARATOR . $filename;
        
        file_put_contents($filepath, $receipt);
        
        $this->info("Struk berhasil disimpan di: receipts/{$filename}");
    }
    
    /**
     * Save receipt to file for multiple items
     */
    private function saveReceiptToFileMultiple(string $receipt, string $saleId, SaleTransaction $firstTransaction)
    {
        $receiptsDir = base_path('receipts');
        
        // Create receipts directory if not exists
        if (!is_dir($receiptsDir)) {
            mkdir($receiptsDir, 0755, true);
        }
        
        $transactionId = substr($saleId, 0, 8);
        $date = $firstTransaction->created_at->format('Y-m-d');
        $filename = "{$date}_TRX-{$transactionId}.txt";
        $filepath = $receiptsDir . DIRECTORY_SEPARATOR . $filename;
        
        file_put_contents($filepath, $receipt);
        
        $this->info("Struk berhasil disimpan di: receipts/{$filename}");
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
