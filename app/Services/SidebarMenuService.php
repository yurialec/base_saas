<?php

namespace App\Services;

use App\Models\SidebarMenu;
use App\Repositories\SidebarMenuRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SidebarMenuService
{
    protected $sidebarMenuRepository;

    public function __construct(SidebarMenuRepository $sidebarMenuRepository)
    {
        $this->sidebarMenuRepository = $sidebarMenuRepository;
    }

    public function getSidebar()
    {
        try {
            if (!session()->has('sidebar')) {
                $this->refreshSidebarSession();
            }

            return session('sidebar');
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar sidebar menus', [
                'message' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    public function all()
    {
        try {
            return $this->sidebarMenuRepository->all();
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar sidebar menus', [
                'message' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    public function listToCreate()
    {
        try {
            return $this->sidebarMenuRepository->listToCreate();
        } catch (\Throwable $e) {
            Log::error('Erro ao carregar sidebar menus para criação', [
                'message' => $e->getMessage(),
            ]);
            return collect();
        }
    }

    public function find($id)
    {
        try {
            return $this->sidebarMenuRepository->find($id);
        } catch (\Throwable $e) {
            Log::error('Erro ao buscar menu', [
                'message' => $e->getMessage(),
                'menu_id' => $id,
            ]);
            return null;
        }
    }


    public function create(array $data)
    {
        $data['order'] = $this->defineOrder($data['parent_id']);

        try {
            $menu = $this->sidebarMenuRepository->create($data);

            if ($menu) {
                $this->refreshSidebarSession();
            }

            return $menu;
        } catch (\Throwable $e) {
            Log::error('Erro ao criar menu', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    private function defineOrder($parent)
    {
        if (!$parent) {
            $menu = SidebarMenu::select('order')
                ->whereNull('parent_id')
                ->orderByDesc('order')
                ->pluck('order')
                ->first();
        } else {
            $menu = SidebarMenu::where('parent_id', $parent)
                ->orderByDesc('order')
                ->pluck('order')
                ->first();
        }

        $order = $menu + 1;

        return $order;
    }

    public function delete($id)
    {
        try {
            $deleted = $this->sidebarMenuRepository->delete($id);

            if ($deleted) {
                $this->refreshSidebarSession();
            }

            return $deleted;
        } catch (\Throwable $e) {
            Log::error('Erro ao excluir menu', [
                'message' => $e->getMessage(),
                'menu_id' => $id,
            ]);
            return null;
        }
    }

    public function update(array $data, $id)
    {
        try {
            $menu = DB::transaction(function () use ($data, $id) {
                $menu = $this->sidebarMenuRepository->findOrFail($id);
                $children = $data['children'] ?? [];

                unset($data['children']);

                $this->sidebarMenuRepository->updateModel($menu, $data);
                $this->syncChildren($menu, $children);

                return $this->sidebarMenuRepository->treeFromMenu($menu->id);
            });

            if ($menu) {
                $this->refreshSidebarSession();
            }

            return $menu;
        } catch (\Throwable $e) {
            Log::error('Erro ao editar menu', [
                'message' => $e->getMessage(),
                'menu_id' => $id,
            ]);
            return null;
        }
    }

    public function changeMenuOrder($id)
    {
        try {
            $menu = DB::transaction(function () use ($id) {
                $menu = $this->sidebarMenuRepository->findOrFail($id);

                if ($menu->order <= 1) {
                    return $menu;
                }

                $newOrder = $menu->order - 1;
                $menuToReplace = $this->sidebarMenuRepository->findByParentAndOrder($menu->parent_id, $newOrder);

                if (!$menuToReplace) {
                    return $menu;
                }

                $oldOrder = $menu->order;

                $this->sidebarMenuRepository->updateModel($menu, [
                    'order' => $newOrder,
                ]);

                $this->sidebarMenuRepository->updateModel($menuToReplace, [
                    'order' => $oldOrder,
                ]);

                return $menu->fresh();
            });

            if ($menu) {
                $this->refreshSidebarSession();
            }

            return $menu;
        } catch (\Throwable $e) {
            Log::error('Erro ao alterar ordem do menu', [
                'message' => $e->getMessage(),
                'menu_id' => $id,
            ]);
            return null;
        }
    }

    private function syncChildren(SidebarMenu $parent, array $children): void
    {
        $sentIds = collect($children)
            ->pluck('id')
            ->filter()
            ->values()
            ->toArray();

        $this->sidebarMenuRepository->deleteChildrenExcept($parent, $sentIds);

        foreach ($children as $childData) {
            $grandChildren = $childData['children'] ?? [];

            unset($childData['children']);

            if (!empty($childData['id'])) {
                $child = $this->sidebarMenuRepository->findOrFail($childData['id']);
                $this->sidebarMenuRepository->updateModel($child, $childData);

                if (!$child->isChildOf($parent)) {
                    $this->sidebarMenuRepository->appendToParent($child, $parent);
                }
            } else {
                $child = $this->sidebarMenuRepository->createChild($parent, $childData);
            }

            $this->syncChildren($child, $grandChildren);
        }
    }

    private function refreshSidebarSession(): void
    {
        $menus = $this->sidebarMenuRepository->getSidebar();
        $menus = $this->validatePermissionSidebar($menus);
        session()->put('sidebar', $menus);
    }

    private function validatePermissionSidebar($data)
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        $role = $user->role;
        $permissions = [];

        if ($role && $role->permissions) {
            $permissions = $role->permissions
                ->pluck('slug')
                ->toArray();
        }

        return $this->filterMenusByPermission($data, $permissions);
    }

    private function filterMenusByPermission($menus, array $permissions): array
    {
        return collect($menus)
            ->map(function ($menu) use ($permissions) {

                $menu = is_array($menu) ? $menu : $menu->toArray();

                if (!empty($menu['children'])) {
                    $menu['children'] = $this->filterMenusByPermission(
                        $menu['children'],
                        $permissions
                    );
                }

                if (!empty($menu['children'])) {
                    return $menu;
                }

                if (($menu['route'] ?? null) === '/') {
                    return $menu;
                }

                if (($menu['route'] ?? null) === '#' || ($menu['url'] ?? null) === '#') {
                    return null;
                }

                $slug = trim($menu['route'] ?? '', '/');

                if (in_array($slug, $permissions, true)) {
                    return $menu;
                }

                return null;
            })
            ->filter()
            ->values()
            ->toArray();
    }
}
