<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\ManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/up', function () {
    return response('OK', 200);
});

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/berita/{article:slug}', [LandingController::class, 'showArticle'])->name('landing.article');

Route::get('/login', [ManagementController::class, 'showLogin'])->name('login');
Route::post('/login', [ManagementController::class, 'login'])->name('login.attempt');

Route::middleware('permission')->group(function (): void {
    Route::post('/logout', [ManagementController::class, 'logout'])->name('logout');
    Route::get('/management', [ManagementController::class, 'dashboard'])->name('management.dashboard');
    Route::get('/management/profile', [ManagementController::class, 'editProfile'])->name('management.profile.edit');
    Route::put('/management/profile', [ManagementController::class, 'updateProfile'])->name('management.profile.update');
    Route::post('/management/categories', [ManagementController::class, 'storeCategory'])->name('management.categories.store');
    Route::delete('/management/categories/{category}', [ManagementController::class, 'destroyCategory'])->name('management.categories.destroy');

    Route::middleware('permission:manage_receipts')->group(function (): void {
        Route::get('/management/receipts/create', [ManagementController::class, 'createReceipt'])->name('management.receipts.create');
        Route::post('/management/receipts', [ManagementController::class, 'storeReceipt'])->name('management.receipts.store');
        Route::get('/management/receipts/template-editor', [ManagementController::class, 'editReceiptTemplate'])->name('management.receipts.template.edit');
        Route::put('/management/receipts/template-editor', [ManagementController::class, 'updateReceiptTemplate'])->name('management.receipts.template.update');
        Route::post('/management/receipts/template-editor/upload-docx', [ManagementController::class, 'uploadReceiptDocxTemplate'])->name('management.receipts.template.upload-docx');
        Route::post('/management/receipts/template-editor/reset', [ManagementController::class, 'resetReceiptTemplate'])->name('management.receipts.template.reset');
        Route::get('/management/receipts/{receipt}/edit', [ManagementController::class, 'editReceipt'])->name('management.receipts.edit');
        Route::put('/management/receipts/{receipt}', [ManagementController::class, 'updateReceipt'])->name('management.receipts.update');
        Route::delete('/management/receipts/{receipt}', [ManagementController::class, 'destroyReceipt'])->name('management.receipts.destroy');
    });

    Route::middleware('permission:manage_documents')->group(function (): void {
        Route::get('/management/documents/create', [ManagementController::class, 'createDocument'])->name('management.documents.create');
        Route::post('/management/documents', [ManagementController::class, 'storeDocument'])->name('management.documents.store');
        Route::get('/management/documents/export-excel', [ManagementController::class, 'exportDocumentsExcel'])->name('management.documents.export');
        Route::post('/management/documents/import-excel', [ManagementController::class, 'importDocumentsExcel'])->name('management.documents.import');
        Route::get('/management/documents/template-excel', [ManagementController::class, 'downloadDocumentTemplate'])->name('management.documents.template');
        Route::get('/management/documents/{document}/file', [ManagementController::class, 'showDocumentFile'])->name('management.documents.file');
        Route::get('/management/documents/{document}/edit', [ManagementController::class, 'editDocument'])->name('management.documents.edit');
        Route::put('/management/documents/{document}', [ManagementController::class, 'updateDocument'])->name('management.documents.update');
        Route::delete('/management/documents/{document}', [ManagementController::class, 'destroyDocument'])->name('management.documents.destroy');
    });

    Route::middleware('permission:manage_marriages')->group(function (): void {
        Route::get('/management/marriages/create', [ManagementController::class, 'createMarriage'])->name('management.marriages.create');
        Route::post('/management/marriages', [ManagementController::class, 'storeMarriage'])->name('management.marriages.store');
        Route::get('/management/marriages/export-excel', [ManagementController::class, 'exportMarriagesExcel'])->name('management.marriages.export');
        Route::post('/management/marriages/import-excel', [ManagementController::class, 'importMarriagesExcel'])->name('management.marriages.import');
        Route::get('/management/marriages/template-excel', [ManagementController::class, 'downloadMarriageTemplate'])->name('management.marriages.template');
        Route::get('/management/marriages/{marriage}/file', [ManagementController::class, 'showMarriageFile'])->name('management.marriages.file');
        Route::get('/management/marriages/{marriage}/edit', [ManagementController::class, 'editMarriage'])->name('management.marriages.edit');
        Route::put('/management/marriages/{marriage}', [ManagementController::class, 'updateMarriage'])->name('management.marriages.update');
        Route::delete('/management/marriages/{marriage}', [ManagementController::class, 'destroyMarriage'])->name('management.marriages.destroy');
    });

    Route::middleware('permission:manage_profile')->group(function (): void {
        Route::put('/management/vihara-profile', [ManagementController::class, 'updateViharaProfile'])->name('management.vihara-profile.update');
        Route::get('/management/vihara-profile/facilities/create', [ManagementController::class, 'createViharaFacility'])->name('management.vihara-profile.facilities.create');
        Route::post('/management/vihara-profile/facilities', [ManagementController::class, 'storeViharaFacility'])->name('management.vihara-profile.facilities.store');
        Route::get('/management/vihara-profile/facilities/{facilityId}/edit', [ManagementController::class, 'editViharaFacility'])->name('management.vihara-profile.facilities.edit');
        Route::put('/management/vihara-profile/facilities/{facilityId}', [ManagementController::class, 'updateViharaFacility'])->name('management.vihara-profile.facilities.update');
        Route::delete('/management/vihara-profile/facilities/{facilityId}', [ManagementController::class, 'destroyViharaFacility'])->name('management.vihara-profile.facilities.destroy');
    });

    Route::middleware('permission:manage_schedules')->group(function (): void {
        Route::get('/management/schedules/create', [ManagementController::class, 'createSchedule'])->name('management.schedules.create');
        Route::post('/management/schedules', [ManagementController::class, 'storeSchedule'])->name('management.schedules.store');
        Route::get('/management/schedules/{schedule}/edit', [ManagementController::class, 'editSchedule'])->name('management.schedules.edit');
        Route::put('/management/schedules/{schedule}', [ManagementController::class, 'updateSchedule'])->name('management.schedules.update');
        Route::delete('/management/schedules/{schedule}', [ManagementController::class, 'destroySchedule'])->name('management.schedules.destroy');
    });

    Route::middleware('permission:manage_galleries')->group(function (): void {
        Route::get('/management/galleries/create', [ManagementController::class, 'createGallery'])->name('management.galleries.create');
        Route::post('/management/galleries', [ManagementController::class, 'storeGallery'])->name('management.galleries.store');
        Route::get('/management/galleries/{gallery}/edit', [ManagementController::class, 'editGallery'])->name('management.galleries.edit');
        Route::put('/management/galleries/{gallery}', [ManagementController::class, 'updateGallery'])->name('management.galleries.update');
        Route::delete('/management/galleries/{gallery}', [ManagementController::class, 'destroyGallery'])->name('management.galleries.destroy');
    });

    Route::middleware('permission:manage_articles')->group(function (): void {
        Route::get('/management/articles/create', [ManagementController::class, 'createArticle'])->name('management.articles.create');
        Route::post('/management/articles', [ManagementController::class, 'storeArticle'])->name('management.articles.store');
        Route::get('/management/articles/{article}/edit', [ManagementController::class, 'editArticle'])->name('management.articles.edit');
        Route::put('/management/articles/{article}', [ManagementController::class, 'updateArticle'])->name('management.articles.update');
        Route::delete('/management/articles/{article}', [ManagementController::class, 'destroyArticle'])->name('management.articles.destroy');
    });

    Route::middleware('permission:manage_roles')->group(function (): void {
        Route::get('/management/roles/create', [ManagementController::class, 'createRole'])->name('management.roles.create');
        Route::post('/management/roles', [ManagementController::class, 'storeRole'])->name('management.roles.store');
        Route::get('/management/roles/{role}/edit', [ManagementController::class, 'editRole'])->name('management.roles.edit');
        Route::put('/management/roles/{role}', [ManagementController::class, 'updateRole'])->name('management.roles.update');
        Route::delete('/management/roles/{role}', [ManagementController::class, 'destroyRole'])->name('management.roles.destroy');

        Route::get('/management/users/create', [ManagementController::class, 'createUser'])->name('management.users.create');
        Route::post('/management/users', [ManagementController::class, 'storeUser'])->name('management.users.store');
        Route::get('/management/users/{user}/edit', [ManagementController::class, 'editUser'])->name('management.users.edit');
        Route::put('/management/users/{user}', [ManagementController::class, 'updateUser'])->name('management.users.update');
        Route::delete('/management/users/{user}', [ManagementController::class, 'destroyUser'])->name('management.users.destroy');
    });
});
