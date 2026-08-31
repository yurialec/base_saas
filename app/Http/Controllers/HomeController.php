<?php

namespace App\Http\Controllers;

use App\Models\SidebarMenu;
use App\Services\SidebarMenuService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $sidebarMenuService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SidebarMenuService $sidebarMenuService)
    {
        $this->sidebarMenuService = $sidebarMenuService;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getSideBar()
    {
        $menus = $this->sidebarMenuService->getSidebar();
        return response()->json($menus);
    }
}
