<?php

namespace App\Providers;

use App\Models\Bidang;
use App\Models\LampiranCrmc;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Navigasi utama dipakai bersama oleh halaman Beranda dan dashboard
        // CRMC, sehingga datanya disiapkan satu kali di sini lewat view
        // composer. Bentuknya sengaja dibuat datar (array), bukan Collection
        // Eloquent, supaya halaman lain tidak ikut menarik data yang tidak
        // dipakainya.
        View::composer('layouts.app', function ($view) {
            // Urutan sub-bidang sama dengan dashboard: ikut id (urutan database),
                // bukan alfabetis, supaya daftar di navbar sejajar dengan
                // urutan kartu pada tab bidang.
                $bidang = Bidang::query()
                ->with(['subMenus' => fn ($q) => $q->orderBy('id')])
                ->withCount(['subMenus', 'subMenus as jumlah_dokumen' => fn ($q) => $q->has('dokumen')])
                // Sama seperti dashboard: urutan ikut id, bukan nama.
                ->orderBy('id')
                ->get();

            $view->with([
                'navBidang' => $bidang->map(fn (Bidang $b) => [
                    'id' => $b->id,
                    'nama' => $b->nama_bidang,
                    // Tab pada dashboard memakai id, bukan nama, supaya
                    // nama bidang bebas diubah tanpa merusak tautan lama.
                    'tab' => 'bidang-' . $b->id,
                    'ikon' => $b->ikon(),
                    'jumlah' => $b->sub_menus_count,
                    'jumlah_dokumen' => $b->jumlah_dokumen,
                ])->values(),
                // Dipakai oleh kotak pencarian di navbar.
                'navSubBidang' => $bidang->flatMap(fn (Bidang $b) => $b->subMenus->map(fn ($s) => [
                    'judul' => $s->nama_sub_menu,
                    'parent' => $b->nama_bidang,
                    'slug' => $s->slug,
                ]))->values(),
                'navSopJumlah' => LampiranCrmc::where('kategori_komponen', 'sop')->count(),
            ]);
        });
    }

    }
