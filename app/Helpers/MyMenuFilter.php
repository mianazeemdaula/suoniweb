<?php
namespace App\Helpers;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;

class MyMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        if (isset($item['permission']) && !Auth::user()->hasRole($item['permission'])) {
            return false;
        }

        return $item;
    }
}
