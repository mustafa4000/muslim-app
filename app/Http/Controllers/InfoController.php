<?php

namespace App\Http\Controllers;

use App\Exceptions\PostNotFoundExceprion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InfoController extends Controller
{
    private $key_kota = 'daftar_kota';

    public function index()
    {
        $daftarkota = cache($this->key_kota);

        if ($daftarkota == null) {
            $daftarkota = $this->getDaftarkota();
            cache([$this->key_kota => $daftarkota], 60 * 60 );
            return redirect()->back()->with('insert','Data Success');
        }

        return response($daftarkota);           
    }

    public function getDaftarkota()
    {
        return Http::get('https://api.banghasan.com/sholat/format/json/kota')->json();
    }
    
    public function jadwalsolat($kode_kota ,$time)
    {
        $daftar_kota = cache($this->key_kota);

        if ($daftar_kota == null) {
            $daftar_kota = $this->getDaftarkota();
            cache([$this->key_kota => $daftar_kota], 60 * 60 );
        }

        $collection = collect($daftar_kota['kota']);

        $filtered = $collection->filter(function ($value, $key) use($kode_kota) {
            return $value['id'] == $kode_kota;
        });

        $tanggal = $this->getWaktu($kode_kota ,$time);
    
        $tanggal['kota'] = $filtered->first()['nama'];
        return response($tanggal, 200);
    }

    public function getWaktu($kode_kota ,$time)
    {
        return Http::get('https://api.banghasan.com/sholat/format/json/jadwal/kota/'. $kode_kota .'/tanggal/'.$time)->json();
    }

    public function namakota($nama)
    {        
        $cacheKey = "asal_kota_{$nama}";
        $asalkota = cache($cacheKey);

        if (!$asalkota) {
            $asalkota = $this->getNamakota($nama);
            cache([$cacheKey => $asalkota], 60 * 60);
        }

        return response($asalkota); 
    }

    public function getNamakota($nama)
    {
        return Http::get('https://api.banghasan.com/sholat/format/json/kota/nama/'. $nama)->json();
    }

}
