<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WheelPrizeRequest;
use App\Models\WheelPrize;

class WheelPrizeController extends Controller
{
    public function index()
    {
        $prizes = WheelPrize::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.wheel-prizes.index', compact('prizes'));
    }

    public function create()
    {
        $prize = new WheelPrize;

        return view('admin.wheel-prizes.create', compact('prize'));
    }

    public function store(WheelPrizeRequest $request)
    {
        WheelPrize::create($request->validated());

        return redirect()->route('admin.wheel-prizes.index')
            ->with('sukses', 'Hadiah roda berhasil ditambahkan.');
    }

    public function edit(WheelPrize $wheelPrize)
    {
        return view('admin.wheel-prizes.edit', ['prize' => $wheelPrize]);
    }

    public function update(WheelPrizeRequest $request, WheelPrize $wheelPrize)
    {
        $wheelPrize->update($request->validated());

        return redirect()->route('admin.wheel-prizes.index')
            ->with('sukses', 'Hadiah roda berhasil diperbarui.');
    }

    public function destroy(WheelPrize $wheelPrize)
    {
        $wheelPrize->delete();

        return redirect()->route('admin.wheel-prizes.index')
            ->with('sukses', 'Hadiah roda berhasil dihapus.');
    }
}
