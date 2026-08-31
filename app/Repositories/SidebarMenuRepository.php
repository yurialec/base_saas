<?php

namespace App\Repositories;

use App\Models\SidebarMenu;

class SidebarMenuRepository
{
    protected $sidebar;

    public function __construct(SidebarMenu $sidebar)
    {
        $this->sidebar = $sidebar;
    }

    public function getSidebar()
    {
        return $this->sidebar->query()
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->toTree()
            ->toArray();
    }

    public function all()
    {
        return $this->sidebar
            ->query()
            ->orderBy('order')
            ->get()
            ->toTree();
    }

    public function listToCreate()
    {
        return $this->sidebar->query()
            ->defaultOrder()
            ->get();
    }

    public function find($id)
    {
        return $this->sidebar
            ->descendantsAndSelf($id)
            ->toTree();
    }

    public function findOrFail($id)
    {
        return $this->sidebar->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->sidebar->create($data);
    }

    public function delete($id)
    {
        return $this->sidebar->destroy($id);
    }

    public function update($data, $id)
    {
        $menu = $this->findOrFail($id);

        return $this->updateModel($menu, $data);
    }

    public function updateModel(SidebarMenu $menu, array $data)
    {
        $menu->update($data);

        return $menu;
    }

    public function deleteChildrenExcept(SidebarMenu $parent, array $ids): void
    {
        $parent->children()
            ->when(!empty($ids), function ($query) use ($ids) {
                $query->whereNotIn('id', $ids);
            })
            ->delete();
    }

    public function createChild(SidebarMenu $parent, array $data)
    {
        return $parent->children()->create($data);
    }

    public function appendToParent(SidebarMenu $child, SidebarMenu $parent)
    {
        $child->appendToNode($parent)->save();

        return $child;
    }

    public function findByParentAndOrder($parentId, $order)
    {
        return $this->sidebar
            ->query()
            ->where('parent_id', $parentId)
            ->where('order', $order)
            ->first();
    }

    public function treeFromMenu($id)
    {
        return $this->sidebar
            ->defaultOrder()
            ->descendantsAndSelf($id)
            ->toTree()
            ->first();
    }
}
