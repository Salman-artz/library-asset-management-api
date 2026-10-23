<?php

namespace Database\Seeders;

use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Fine;
use App\Models\LibraryCollection;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            User::factory()->admin()->create([
                'name' => 'Admin Library',
                'email' => 'admin@library.test',
            ]);

            foreach ([
                ['name' => 'Staff Satu', 'email' => 'staff1@library.test'],
                ['name' => 'Staff Dua', 'email' => 'staff2@library.test'],
            ] as $staffData) {
                User::factory()->staff()->create($staffData);
            }

            $members = collect([
                ['name' => 'Member Satu', 'email' => 'member1@library.test'],
                ['name' => 'Member Dua', 'email' => 'member2@library.test'],
                ['name' => 'Member Tiga', 'email' => 'member3@library.test'],
                ['name' => 'Member Empat', 'email' => 'member4@library.test'],
                ['name' => 'Member Lima', 'email' => 'member5@library.test'],
                ['name' => 'Member Enam', 'email' => 'member6@library.test'],
            ])->map(fn (array $memberData) => User::factory()->member()->create($memberData));

            $categories = collect([
                'Teknologi Informasi',
                'Bisnis dan Manajemen',
                'Fiksi',
                'Referensi',
                'Aset Kantor',
            ])->map(fn (string $name) => Category::create(['name' => $name]));

            $collections = collect([
                ['code' => 'COL-IT-001', 'title' => 'Laravel dari Dasar ke Produksi', 'category' => 'Teknologi Informasi', 'total_stock' => 8, 'available_stock' => 6, 'condition' => 'good', 'status' => 'available'],
                ['code' => 'COL-IT-002', 'title' => 'Modern PHP Patterns', 'category' => 'Teknologi Informasi', 'total_stock' => 5, 'available_stock' => 5, 'condition' => 'good', 'status' => 'available'],
                ['code' => 'COL-BIZ-001', 'title' => 'Dasar-dasar Manajemen Aset', 'category' => 'Bisnis dan Manajemen', 'total_stock' => 4, 'available_stock' => 4, 'condition' => 'good', 'status' => 'available'],
                ['code' => 'COL-FIC-001', 'title' => 'Senja di Ruang Baca', 'category' => 'Fiksi', 'total_stock' => 3, 'available_stock' => 2, 'condition' => 'fair', 'status' => 'available'],
                ['code' => 'COL-REF-001', 'title' => 'Kamus Istilah Perpustakaan', 'category' => 'Referensi', 'total_stock' => 2, 'available_stock' => 0, 'condition' => 'good', 'status' => 'borrowed'],
                ['code' => 'COL-ASSET-001', 'title' => 'Printer Laser Gedung A', 'category' => 'Aset Kantor', 'total_stock' => 1, 'available_stock' => 1, 'condition' => 'good', 'status' => 'available'],
                ['code' => 'COL-ASSET-002', 'title' => 'Scanner Dokumen Cepat', 'category' => 'Aset Kantor', 'total_stock' => 1, 'available_stock' => 0, 'condition' => 'damaged', 'status' => 'maintenance'],
            ])->map(function (array $item) use ($categories): LibraryCollection {
                return LibraryCollection::create([
                    'code' => $item['code'],
                    'title' => $item['title'],
                    'category_id' => $categories->firstWhere('name', $item['category'])->id,
                    'cover_image_path' => null,
                    'total_stock' => $item['total_stock'],
                    'available_stock' => $item['available_stock'],
                    'condition' => $item['condition'],
                    'status' => $item['status'],
                ]);
            });

            $loanOne = Borrowing::create([
                'user_id' => $members[0]->id,
                'collection_id' => $collections[0]->id,
                'borrowed_at' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(4)->toDateString(),
                'returned_at' => null,
                'status' => 'borrowed',
            ]);

            $loanTwo = Borrowing::create([
                'user_id' => $members[1]->id,
                'collection_id' => $collections[3]->id,
                'borrowed_at' => Carbon::now()->subDays(14),
                'due_date' => Carbon::now()->subDays(7)->toDateString(),
                'returned_at' => Carbon::now()->subDays(2),
                'status' => 'returned',
            ]);

            Fine::create([
                'borrowing_id' => $loanTwo->id,
                'amount' => 15000,
                'days_late' => 5,
                'daily_rate' => 3000,
                'status' => 'unpaid',
                'paid_at' => null,
            ]);

            $collections[0]->update([
                'available_stock' => $collections[0]->available_stock - 1,
            ]);

            $collections[3]->update([
                'available_stock' => $collections[3]->available_stock - 1,
                'status' => ($collections[3]->available_stock - 1) === 0 ? 'borrowed' : 'available',
            ]);
        });
    }
}
