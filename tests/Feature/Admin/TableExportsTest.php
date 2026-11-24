<?php

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Support\TableExports;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('pdf export renders arabic rows into a pdf', function () {
    User::factory()->create(['name' => 'سعاد الصباح']);

    $response = TableExports::pdf(UserExporter::class, User::all());

    ob_start();
    $response->sendContent();
    $pdf = ob_get_clean();

    expect($pdf)->toStartWith('%PDF')
        ->and($response->headers->get('Content-Disposition'))->toContain('user-');
});

test('pdf export refuses more rows than the limit', function () {
    $records = User::factory()->count(2)->make();

    $records = collect(array_fill(0, TableExports::PDF_MAX_ROWS + 1, $records->first()));

    expect(TableExports::pdf(UserExporter::class, $records))->toBeNull();
});

test('users table pdf header action downloads the filtered rows', function () {
    $this->actingAs(superAdmin(), 'admin');
    User::factory()->count(3)->create();

    Livewire::test(ListUsers::class)
        ->callAction([['name' => 'exportPdf', 'context' => ['table' => true]]])
        ->assertFileDownloaded();
});

test('users table excel export is queued for the admin', function () {
    $admin = superAdmin();
    $this->actingAs($admin, 'admin');
    User::factory()->count(2)->create();

    Livewire::test(ListUsers::class)
        ->callAction([['name' => 'export', 'context' => ['table' => true]]], data: ['formats' => ['xlsx', 'csv']])
        ->assertHasNoActionErrors();

    expect(Export::first())
        ->user->is($admin)->toBeTrue()
        ->total_rows->toBe(2)
        ->file_disk->toBe('local');
});

test('users table date filter narrows rows', function () {
    $this->actingAs(superAdmin(), 'admin');
    $old = User::factory()->create(['created_at' => now()->subYear()]);
    $new = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->filterTable('created_at_range', ['from' => now()->subDay()->toDateString()])
        ->assertCanSeeTableRecords([$new])
        ->assertCanNotSeeTableRecords([$old]);
});
