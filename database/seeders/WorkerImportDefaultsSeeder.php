<?php

namespace Database\Seeders;

use App\Models\ImportMapping;
use App\Models\JobCategory;
use App\Models\Pertanian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkerImportDefaultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $categoriesData = [
            'Panen' => 'Kegiatan memanen hasil panen cabai/komoditas',
            'Penyiraman' => 'Kegiatan ngocor air atau pompa desel',
            'Pemupukan' => 'Kegiatan ngocor mes / aplikasi pupuk',
            'Penyemprotan (Spraying)' => 'Kegiatan ngobat / spraying pestisida / fungisida',
            'Pasang Lanjaran' => 'Pemasangan lanjaran / tiang rambatan',
            'Penyiangan (Babat)' => 'Pembersihan gulma / rumput liar di lahan',
        ];

        $categoryModels = [];
        foreach ($categoriesData as $name => $desc) {
            $categoryModels[$name] = JobCategory::firstOrCreate(
                ['name' => $name],
                ['description' => $desc]
            );
        }

        // 2. Workers (Pekerja)
        $workersData = [
            ['name' => 'Cak Kusnul', 'email' => 'kusnul@pengentani.my.id'],
            ['name' => 'Rina', 'email' => 'rina@pengentani.my.id'],
        ];

        $workerModels = [];
        foreach ($workersData as $w) {
            $workerModels[$w['name']] = User::firstOrCreate(
                ['email' => $w['email']],
                [
                    'name' => $w['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'pekerja',
                    'is_active' => true,
                ]
            );
        }

        // Add existing workers to models lookup
        foreach (User::where('role', 'pekerja')->get() as $existingWorker) {
            $workerModels[$existingWorker->name] = $existingWorker;
        }

        // 3. Pertanian lookup
        $firstPertanian = Pertanian::first();

        // 4. Seed Import Mappings
        // Kategori mappings
        $catMaps = [
            'ngocor' => 'Penyiraman',
            'ngocor air' => 'Penyiraman',
            'ngocor desel' => 'Penyiraman',
            'ngocor mes' => 'Pemupukan',
            'pupuk' => 'Pemupukan',
            'mes' => 'Pemupukan',
            'ngobat' => 'Penyemprotan (Spraying)',
            'ngobat cabai' => 'Penyemprotan (Spraying)',
            'spray' => 'Penyemprotan (Spraying)',
            'petik' => 'Panen',
            'petik cabai' => 'Panen',
            'pasang lanjaran' => 'Pasang Lanjaran',
            'babat' => 'Penyiangan (Babat)',
        ];

        foreach ($catMaps as $raw => $targetName) {
            if (isset($categoryModels[$targetName])) {
                ImportMapping::register('kategori', $raw, $categoryModels[$targetName]->id, $targetName);
            }
        }

        // Pekerja mappings
        $pekerjaMaps = [
            'cak kusnul' => 'Cak Kusnul',
            'kusnul' => 'Cak Kusnul',
            'rina' => 'Rina',
            'cak anas' => 'Cak Anas',
            'anas' => 'Cak Anas',
            'didik' => 'Didik',
        ];

        foreach ($pekerjaMaps as $raw => $workerName) {
            if (isset($workerModels[$workerName])) {
                ImportMapping::register('pekerja', $raw, $workerModels[$workerName]->id, $workerName);
            }
        }

        // Pertanian mappings (if a pertanian exists)
        if ($firstPertanian) {
            $pertanianMaps = [
                'spl' => $firstPertanian->id,
                'simpang lima' => $firstPertanian->id,
                'simpang l' => $firstPertanian->id,
                'm.jum' => $firstPertanian->id,
                'ma\'jum' => $firstPertanian->id,
                'jj6' => $firstPertanian->id,
                'wonorejo' => $firstPertanian->id,
            ];

            foreach ($pertanianMaps as $raw => $pid) {
                ImportMapping::register('pertanian', $raw, $pid, $firstPertanian->name);
            }
        }
    }
}
