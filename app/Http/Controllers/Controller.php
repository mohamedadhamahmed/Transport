<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // AuthorizesRequests بيوفر $this->authorize('permission.key') لأي
    // كنترولر وارث من الكلاس ده - بيستخدمها نظام الصلاحيات (شوفي
    // Gate::before في AppServiceProvider) عشان يمنع/يسمح بالوصول لأي
    // action حتى لو المستخدم دخل على الرابط مباشرة من غير ما يشوف الزرار.
    use AuthorizesRequests;
}
