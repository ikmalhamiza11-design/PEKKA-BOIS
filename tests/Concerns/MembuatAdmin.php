<?php

namespace Tests\Concerns;

use App\Models\Kelompok;
use App\Models\Shop;
use App\Models\User;

trait MembuatAdmin
{
    use MembuatDataContoh;

    protected function buatAdminDinas(array $atribut = []): User
    {
        $user = User::factory()->create($atribut);
        $user->role = User::ADMIN_DINAS;
        $user->save();

        return $user->fresh();
    }

    protected function buatAdminKelompok(?Kelompok $kelompok = null, array $atribut = []): User
    {
        $kelompok ??= $this->buatKelompok(['nama' => 'Kelompok Admin '.uniqid()]);

        $user = User::factory()->create($atribut);
        $user->role = User::ADMIN_KELOMPOK;
        $user->kelompok_id = $kelompok->id;
        $user->save();

        return $user->fresh();
    }

    protected function buatPenjualDiKelompok(Kelompok $kelompok, string $status = Shop::MENUNGGU): User
    {
        $penjual = $this->buatPenjual($status);
        $penjual->shop->update(['kelompok_id' => $kelompok->id]);

        return $penjual->fresh('shop');
    }
}
