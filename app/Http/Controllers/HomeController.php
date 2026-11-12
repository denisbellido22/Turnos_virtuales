<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($user = $request->user()) {
            return redirect()->to($user->rutaPanel());
        }

        $areas = Area::query()
            ->with(['servicios' => fn ($query) => $query->where('activo', true)->orderBy('nombre')])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('home', compact('areas'));
    }
}
