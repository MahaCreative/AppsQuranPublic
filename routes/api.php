<?php

use App\Models\Kategori;
use App\Models\Kisah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('web-token');
        return response()->json(['token' => $token->plainTextToken], 200);
    } else {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
});

Route::post('create-kisah', function (Request $request) {
    $request->validate([
        'kategori_id' => 'required',
        "judul" => 'required|string|min:10',
        "kontent" => 'required|string|min:20',
        "gambar" => 'required|image|mimes:jpg,jpeg,png',
    ]);
    $gambar = $request->file('gambar')->store('kisah');
    $kisah = Kisah::create([
        'kategori_id' => $request->kategori_id,
        'judul' => $request->judul,
        'kontent' => $request->kontent,
        'gambar' => "/storage/" . $gambar,
        'slug' => \Str::slug($request->judul),
    ]);
    return response()->json('success');
});
Route::post('delete-kisah/{slug}', function (Request $request, $slug) {
    $kisah = Kisah::where('slug', $slug)->first();
    $kisah->delete();
    return response()->json('success');
});


Route::get('get-kategori', function (Request $request) {
    $kategori = Kategori::latest()->get();
    return response()->json($kategori);
});
Route::get('get-kisah', function (Request $request) {
    $query = Kisah::query();
    if ($request->kategori_id) {
        $query->where('kategori_id', $request->kategori_id);
    }
    if ($request->cari) {
        $query->where('judul', 'like', '%' . $request->cari . '%');
    }
    $kisah = $query->latest()->get();
    return response()->json($kisah);
});



Route::get('show-kisah/{slug}', function (Request $request, $slug) {
    $kisah = Kisah::with('kategori')->where('slug', $slug)->first();

    if ($kisah && !empty($kisah->kontent)) {
        // Load the content into DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($kisah->kontent, 'HTML-ENTITIES', 'UTF-8'));

        // Get all span elements
        $spans = $dom->getElementsByTagName('span');

        foreach ($spans as $span) {
            // Remove the font-size style if it exists
            if ($span->hasAttribute('style')) {
                $style = $span->getAttribute('style');
                $updatedStyle = preg_replace('/font-size\s*:\s*[^;]+;?/', '', $style);

                if (empty(trim($updatedStyle))) {
                    $span->removeAttribute('style');
                } else {
                    $span->setAttribute('style', $updatedStyle);
                }
            }

            // Add the 'setFont' class to the span
            if ($span->hasAttribute('class')) {
                $existingClass = $span->getAttribute('class');
                $span->setAttribute('class', $existingClass . ' setFont');
            } else {
                $span->setAttribute('class', 'setFont');
            }
        }

        // Save the updated HTML back to the kontent
        $kisah->kontent = $dom->saveHTML($dom->getElementsByTagName('body')->item(0)->firstChild);
    }

    return response()->json($kisah);
});

Route::post('create-kategori', function (Request $request) {
    $request->validate(['nama' => 'required|min:3']);
    $kategori = Kategori::create(['nama_kategori' => $request->nama]);
    return response()->json('Berhasil menambahkan 1 kategori baru');
});
Route::post('delete-kategori', function (Request $request) {
    $kategori = Kategori::find($request->id);
    $kategori->delete();
    return response()->json('success');
});
