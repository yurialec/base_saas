<?php

namespace App\Http\Controllers;

use App\Services\SidebarMenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $sidebarMenuService;

    public function __construct(SidebarMenuService $sidebarMenuService)
    {
        $this->sidebarMenuService = $sidebarMenuService;
    }

    public function index()
    {
        $menus = $this->sidebarMenuService->all();
        return response()->json($menus);
    }

    public function listToCreate()
    {
        $menus = $this->sidebarMenuService->listToCreate();
        return response()->json($menus);
    }

    public function show($id)
    {
        $menu = $this->sidebarMenuService->find($id);
        if ($menu) {
            return response()->json($menu);
        } else {
            return response()->json(['message' => 'Menu not found'], 404);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:sidebar_menus,id',
            'children' => 'nullable|array',
            'children.*.title' => 'required|string|max:255',
            'children.*.icon' => 'nullable|string|max:255',
            'children.*.url' => 'nullable|string|max:255',
            'children.*.parent_id' => 'nullable|exists:sidebar_menus,id',
        ]);

        $menu = $this->sidebarMenuService->create($validatedData);

        return response()->json($menu, 201);
    }

    public function destroy($id)
    {
        $menu = $this->sidebarMenuService->find($id);
        if ($menu) {
            $this->sidebarMenuService->delete($id);
            return response()->json(['message' => 'Menu deleted successfully']);
        } else {
            return response()->json(['message' => 'Menu not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:sidebar_menus,id',
            'children' => 'nullable|array',
            'children.*.title' => 'required|string|max:255',
            'children.*.icon' => 'nullable|string|max:255',
            'children.*.url' => 'nullable|string|max:255',
            'children.*.parent_id' => 'nullable|exists:sidebar_menus,id',
        ]);

        $menu = $this->sidebarMenuService->update($validatedData, $id);

        return response()->json($menu);
    }

    public function changeMenuOrder($id)
    {
        $menu = $this->sidebarMenuService->changeMenuOrder($id);
        return response()->json($menu);
    }
}
