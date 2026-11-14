<?php

use App\Models\CollaboratorGroup;
use App\Models\User;
use Illuminate\Database\QueryException;

test('a collaborator group keeps its creator and selected members', function () {
    $creator = User::factory()->create();
    $firstMember = User::factory()->create();
    $secondMember = User::factory()->create();

    $group = CollaboratorGroup::create([
        'name' => 'Supervisores de agencia',
        'description' => 'Grupo compartido para capacitaciones de supervisión.',
        'created_by' => $creator->id,
    ]);
    $group->members()->attach([$firstMember->id, $secondMember->id]);

    expect($group->createdBy->is($creator))->toBeTrue()
        ->and($group->isCreatedBy($creator))->toBeTrue()
        ->and($group->members()->pluck('users.id')->all())->toEqualCanonicalizing([$firstMember->id, $secondMember->id])
        ->and($firstMember->collaboratorGroups()->pluck('collaborator_groups.id')->all())->toBe([$group->id])
        ->and($creator->createdCollaboratorGroups()->pluck('id')->all())->toBe([$group->id]);
});

test('a collaborator cannot be added twice to the same group', function () {
    $group = CollaboratorGroup::create([
        'name' => 'Personal operativo',
        'created_by' => User::factory()->create()->id,
    ]);
    $member = User::factory()->create();

    $group->members()->attach($member->id);

    expect(fn () => $group->members()->attach($member->id))
        ->toThrow(QueryException::class);
});

test('deleting a group removes only its membership records', function () {
    $group = CollaboratorGroup::create([
        'name' => 'Equipo de inducción',
        'created_by' => User::factory()->create()->id,
    ]);
    $member = User::factory()->create();
    $group->members()->attach($member->id);

    $group->delete();

    $this->assertDatabaseMissing('collaborator_groups', ['id' => $group->id]);
    $this->assertDatabaseMissing('collaborator_group_user', [
        'collaborator_group_id' => $group->id,
        'user_id' => $member->id,
    ]);
    $this->assertDatabaseHas('users', ['id' => $member->id]);
});
