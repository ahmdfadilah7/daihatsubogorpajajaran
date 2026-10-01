<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkDestroy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MarqueeItemRequest;
use App\Models\MarqueeItem;

class MarqueeItemController extends Controller
{
    use HandlesBulkDestroy;

    protected function bulkModelClass(): string
    {
        return MarqueeItem::class;
    }

    protected function bulkRouteName(): string
    {
        return 'admin.marquee-items.index';
    }

    public function index()
    {
        $items = MarqueeItem::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.marquee-items.index', compact('items'));
    }

    public function create()
    {
        $item = new MarqueeItem;

        return view('admin.marquee-items.create', compact('item'));
    }

    public function store(MarqueeItemRequest $request)
    {
        MarqueeItem::create($request->validated());

        return redirect()->route('admin.marquee-items.index')
            ->with('sukses', 'Teks berjalan berhasil ditambahkan.');
    }

    public function edit(MarqueeItem $marqueeItem)
    {
        return view('admin.marquee-items.edit', ['item' => $marqueeItem]);
    }

    public function update(MarqueeItemRequest $request, MarqueeItem $marqueeItem)
    {
        $marqueeItem->update($request->validated());

        return redirect()->route('admin.marquee-items.index')
            ->with('sukses', 'Teks berjalan berhasil diperbarui.');
    }

    public function destroy(MarqueeItem $marqueeItem)
    {
        $marqueeItem->delete();

        return redirect()->route('admin.marquee-items.index')
            ->with('sukses', 'Teks berjalan berhasil dihapus.');
    }
}
