<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AreaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $areas = Area::query()
            ->withCount(['servicios', 'ventanillas', 'users'])
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.areas.index', compact('areas', 'buscar'));
    }

    public function create(): View
    {
        return view('admin.areas.create', ['area' => new Area(['activo' => true])]);
    }

    public function store(AreaRequest $request): RedirectResponse
    {
        $area = Area::create($request->validated());

        Log::info('Área creada', ['area_id' => $area->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.areas.index')
            ->with('success', "Área {$area->nombre} creada correctamente.");
    }

    public function edit(Area $area): View
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(AreaRequest $request, Area $area): RedirectResponse
    {
        $area->update($request->validated());

        Log::info('Área actualizada', ['area_id' => $area->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.areas.index')
            ->with('success', "Área {$area->nombre} actualizada correctamente.");
    }

    public function destroy(Request $request, Area $area): RedirectResponse
    {
        if ($area->servicios()->exists() || $area->ventanillas()->exists() || $area->users()->exists() || $area->turnos()->exists()) {
            return back()->with('error', 'No se puede eliminar el área porque tiene servicios, ventanillas, usuarios o turnos asociados. Puedes desactivarla.');
        }

        $nombre = $area->nombre;
        $area->delete();

        Log::info('Área eliminada', ['area' => $nombre, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.areas.index')
            ->with('success', "Área {$nombre} eliminada.");
    }
}
