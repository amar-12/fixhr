<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\MenusController;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Middleware\CheckSuperadmin;



Route::prefix('superadmin')->group(function () {
    Route::get('login', [SuperAdminController::class, 'login'])->name('superadmin.login');
});


Route::middleware([checkSuperadmin::class])->group(function () {
    Route::prefix('superadmin')->group(function () {
        Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard')->middleware(checkSuperadmin::class);
        // Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
        Route::get('masters', [SuperAdminController::class, 'index'])->name('superadmin.masters');
        Route::post('add-master', [SuperAdminController::class, 'createMaster'])->name('superadmin.add-master');
        Route::delete('delete-master/{id}', [SuperAdminController::class, 'deleteMaster']);
        Route::get('masters/data', [SuperAdminController::class, 'getData'])->name('masters.data');
        Route::get('/edit.master/{id}', [SuperAdminController::class, 'editMaster'])->name('master.edit');
        Route::post('/edit.master/{id}', [SuperAdminController::class, 'updateMaster'])->name('master.update');
        // Route::delete('delete-master/{id}', [SuperAdminController::class, 'deleteMaster']);

        Route::get('logout', [SuperAdminController::class, 'logout'])->name('superadmin.logout');

        Route::prefix('module')->group(function () {
            Route::get('dashboard', [SuperAdminController::class, 'moduleDashboard'])->name('module.dashboard');
            Route::post('create', [SuperAdminController::class, 'createModule'])->name('module.create');
            Route::get('get-modules', [SuperAdminController::class, 'getModules'])->name('modules.get-modules');
            Route::get('update/{id}', [SuperAdminController::class, 'getUpdate'])->name('modules.feature.update');
            Route::post('/features/store', [SuperAdminController::class, 'storefeature'])->name('features.store');
            Route::get('get-modules-features', [SuperAdminController::class, 'getModulesFeatures'])->name('modules.get-modules-features');
            Route::get('/edit-feature/{id}', [SuperAdminController::class, 'editfeature'])->name('module.feature.edit');
            Route::post('/edit-feature/{id}', [SuperAdminController::class, 'updateFeature'])->name('module.feature.update');
            Route::delete('delete-feature/{id}', [SuperAdminController::class, 'deleteFeature']);
            Route::delete('delete-module/{id}', [SuperAdminController::class, 'deleteModule']);
            Route::get('/edit-module/{id}', [SuperAdminController::class, 'editModule'])->name('module.edit');
            Route::post('/edit-module/{id}', [SuperAdminController::class, 'updateModule'])->name('module.update');
        });

        Route::resource('/menus', MenusController::class);
        Route::get('privacy-policy-manage', [App\Http\Controllers\SuperAdmin\SuperAdminController::class, 'managePrivacyPolicy'])->name('superadmin.privacy-policy-manage');
        Route::get('feedbacks', [App\Http\Controllers\SuperAdmin\FeedbackController::class, 'index'])->name('superadmin.feedbacks.index');
    });
});




Route::post('createSuperAdmin', [SuperAdminController::class, 'createSuperAdmin'])->name('superadmin.create');
Route::post('superadmin/login', [SuperAdminController::class, 'loginSuperAdmin'])->name('superadmin.login');
