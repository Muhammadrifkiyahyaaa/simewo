<?php

namespace App\Http\Controllers;

use App\Models\SuspiciousKeyword;
use Illuminate\Http\Request;

class SuspiciousKeywordController extends Controller
{
    public function index()
    {
        $keywords = SuspiciousKeyword::orderBy('keyword')->get();

        return view('suspicious-keywords.index', compact('keywords'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
        ]);

        $keyword = strtolower(trim($validated['keyword']));

        if (SuspiciousKeyword::whereRaw('LOWER(keyword) = ?', [$keyword])->exists()) {
            return back()
                ->withErrors(['keyword' => 'Kata kunci ini sudah ada di daftar.'])
                ->withInput();
        }

        SuspiciousKeyword::create(['keyword' => $keyword]);

        return redirect()->route('suspicious-keywords.index')
            ->with('success', 'Kata kunci "' . $keyword . '" berhasil ditambahkan.');
    }

    public function destroy(SuspiciousKeyword $suspiciousKeyword)
    {
        $keyword = $suspiciousKeyword->keyword;
        $suspiciousKeyword->delete();

        return redirect()->route('suspicious-keywords.index')
            ->with('success', 'Kata kunci "' . $keyword . '" dihapus dari daftar.');
    }
}
