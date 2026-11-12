<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VentanillaRequest;
use App\Models\Area;
use App\Models\Ventanilla;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class VentanillaController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $areaId = $request->integer('area_id');

        $ventanillas = Ventanilla::query()
            ->with('area')
            ->withCount('users')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo', 'like', "%{$buscar}%");
                });
            })
            ->when($areaId > 0, fn ($query) => $query->where('area_id', $areaId))
            ->orderBy('codigo')
            ->paginate(12)
            ->withQueryString();

        $areas = Area::query()->orderBy('nombre')->get();

        return view('admin.ventanillas.index', compact('ventanillas', 'areas', 'buscar', 'areaId'));
    }

    public function create(): View
    {
        return view('admin.ventanillas.create', [
            'ventanilla' => new Ventanilla(['activo' => true]),
            'areas' => $this->areas(),
        ]);
    }

    public function store(VentanillaRequest $request): RedirectResponse
    {
        $ventanilla = Ventanilla::create($request->validated());

        Log::info('Ventanilla creada', ['ventanilla_id' => $ventanilla->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.ventanillas.index')
            ->with('success', "Ventanilla {$ventanilla->codigo} creada correctamente.");
    }

    public function edit(Ventanilla $ventanilla): View
    {
        return view('admin.ventanillas.edit', [
            'ventanilla' => $ventanilla->load('area'),
            'areas' => $this->areas(),
        ]);
    }

    public function update(VentanillaRequest $request, Ventanilla $ventanilla): RedirectResponse
    {
        $ventanilla->update($request->validated());

        Log::info('Ventanilla actualizada', ['ventanilla_id' => $ventanilla->id, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.ventanillas.index')
            ->with('success', "Ventanilla {$ventanilla->codigo} actualizada correctamente.");
    }

    public function destroy(Request $request, Ventanilla $ventanilla): RedirectResponse
    {
        if ($ventanilla->users()->exists() || $ventanilla->turnos()->exists()) {
            return back()->with('error', 'No se puede eliminar la ventanilla porque tiene empleados o turnos asociados. Puedes desactivarla.');
        }

        $codigo = $ventanilla->codigo;
        $ventanilla->delete();

        Log::info('Ventanilla eliminada', ['codigo' => $codigo, 'user_id' => $request->user()->id]);

        return redirect()->route('admin.ventanillas.index')
            ->with('success', "Ventanilla {$codigo} eliminada.");
    }

    private function areas()
    {
        return Area::query()->orderBy('nombre')->get();
    }
}
