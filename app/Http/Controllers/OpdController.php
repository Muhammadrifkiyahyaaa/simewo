<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $query = Opd::with(['websites.monitoringLogs' => function ($q) {
            $q->latest('checked_at')->limit(1);
        }]);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $opds = $query->paginate(10)->withQueryString();

        if ($request->filled('status')) {
            $opds->setCollection(
                $opds->getCollection()->filter(function ($opd) use ($request) {
                    $isUp = $opd->websites->contains(function ($website) {
                        $lastLog = $website->monitoringLogs->first();
                        return $lastLog && $lastLog->is_up;
                    });

                    return $request->status === 'hidup' ? $isUp : !$isUp;
                })
            );
        }

        return view('opd.index', compact('opds'));
    }

    public function create()
    {
        return view('opd.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_opd' => 'required|string|max:255',
            'kategori' => 'required|in:Dinas,Badan,Kecamatan,Sekretariat',
            'pic_name' => 'required|string|max:255',
            'pic_kontak' => 'required|string|max:255',
        ]);

        Opd::create($validated);

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil ditambahkan.');
    }

    public function edit(Opd $opd)
    {
        return view('opd.edit', compact('opd'));
    }

    public function update(Request $request, Opd $opd)
    {
        $validated = $request->validate([
            'nama_opd' => 'required|string|max:255',
            'kategori' => 'required|in:Dinas,Badan,Kecamatan,Sekretariat',
            'pic_name' => 'required|string|max:255',
            'pic_kontak' => 'required|string|max:255',
        ]);

        $opd->update($validated);

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd)
    {
        $opd->delete();

        return redirect()->route('opd.index')->with('success', 'Data OPD berhasil dihapus.');
    }
}
