<?php

use App\Filament\Resources\CollaboratorGroups\CollaboratorGroupResource;
use App\Filament\Resources\CollaboratorGroups\Pages\CreateCollaboratorGroup;
use App\Models\CollaboratorGroup;
use App\Models\User;
use Database\Seeders\SgciRolesSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(SgciRolesSeeder::class);
});

test('shared groups can be viewed by training administrators but not edited without an explicit permission', function () {
    $creator = User::factory()->create();
    $creator->assignRole('administrador_capacitaciones');
    $viewer = User::factory()->create();
    $viewer->assignRole('administrador_capacitaciones');
    $group = CollaboratorGroup::create([
        'name' => 'Grupo compartido',
        'created_by' => $creator->id,
    ]);

    expect($viewer->can('viewAny', CollaboratorGroup::class))->toBeTrue()
        ->and($viewer->can('view', $group))->toBeTrue()
        ->and($viewer->can('update', $group))->toBeFalse()
        ->and($viewer->can('delete', $group))->toBeFalse();
});

test('a user with update permission can only edit groups created by that user', function () {
    $creator = User::factory()->create();
    $creator->givePermissionTo(Permission::findByName('Update:CollaboratorGroup'));
    $otherUser = User::factory()->create();
    $otherUser->givePermissionTo(Permission::findByName('Update:CollaboratorGroup'));

    $ownGroup = CollaboratorGroup::create([
        'name' => 'Grupo propio editable',
        'created_by' => $creator->id,
    ]);
    $otherGroup = CollaboratorGroup::create([
        'name' => 'Grupo de otro usuario',
        'created_by' => $otherUser->id,
    ]);

    expect($creator->can('update', $ownGroup))->toBeTrue()
        ->and($creator->can('update', $otherGroup))->toBeFalse()
        ->and($otherUser->can('update', $ownGroup))->toBeFalse();
});

test('the super administrator has full control and group creation records the authenticated creator and members', function () {
    $superAdministrator = User::factory()->create();
    $superAdministrator->assignRole('super_administrador');
    $member = User::factory()->create();
    $group = CollaboratorGroup::create([
        'name' => 'Grupo administrado globalmente',
        'created_by' => User::factory()->create()->id,
    ]);

    expect($superAdministrator->can('view', $group))->toBeTrue()
        ->and($superAdministrator->can('update', $group))->toBeTrue()
        ->and($superAdministrator->can('delete', $group))->toBeTrue();

    Filament::setCurrentPanel('admin');

    Livewire::actingAs($superAdministrator)
        ->test(CreateCollaboratorGroup::class)
        ->fillForm([
            'name' => 'Grupo creado desde el panel',
            'description' => 'Integrantes seleccionados desde el formulario.',
            'members' => [$member->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdGroup = CollaboratorGroup::query()
        ->where('name', 'Grupo creado desde el panel')
        ->firstOrFail();

    expect($createdGroup->created_by)->toBe($superAdministrator->id)
        ->and($createdGroup->members()->pluck('users.id')->all())->toBe([$member->id]);
});

test('a viewer cannot access the edit URL of a shared group', function () {
    $creator = User::factory()->create();
    $group = CollaboratorGroup::create([
        'name' => 'Grupo solo lectura',
        'created_by' => $creator->id,
    ]);
    $viewer = User::factory()->create();
    $viewer->assignRole('administrador_capacitaciones');

    Filament::setCurrentPanel('admin');

    $this->actingAs($viewer)
        ->get(CollaboratorGroupResource::getUrl('edit', ['record' => $group], panel: 'admin'))
        ->assertForbidden();
});
