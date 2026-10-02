<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peta nilai skala lama (Rendah/Sedang/Tinggi) ke kunci skala baru.
     *
     * Kedua skala punya arah yang sama: makin rendah residu, makin baik
     * kondisi pengendaliannya. Karena itu nilai lama dipetakan ke tingkat
     * terdekat dengan posisi yang setara, supaya makna yang sudah tercatat
     * tidak berubah sendiri.
     */
    private const PETA_LAMA = [
        'rendah' => 'terkendali',
        'sedang' => 'siaga',
        'tinggi' => 'bahaya',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('dokumen_crmc') || !Schema::hasColumn('dokumen_crmc', 'status_residu_risiko')) {
            return;
        }

        foreach (self::PETA_LAMA as $lama => $baru) {
            DB::table('dokumen_crmc')
                ->whereRaw('LOWER(TRIM(status_residu_risiko)) = ?', [$lama])
                ->update(['status_residu_risiko' => $baru]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dokumen_crmc') || !Schema::hasColumn('dokumen_crmc', 'status_residu_risiko')) {
            return;
        }

        // Balik arah peta: kunci baru -> nama lama aslinya.
        $kembali = array_flip(self::PETA_LAMA);

        foreach ($kembali as $kunciBaru => $lama) {
            DB::table('dokumen_crmc')
                ->where('status_residu_risiko', $kunciBaru)
                ->update(['status_residu_risiko' => $lama]);
        }
    }
};
