<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

test('Completing a task in one browser can be seen in another', function () {
    $this->browse(function (Browser $browserA, Browser $browserB) {
        $user = createUser();
        $base = $user->firstPersonalBase();
        $todoList = createList($base, 'todoList', [], 1);
        $todo = $todoList->children->first();

        $browserA->loginAs($user);
        $browserB->loginAs($user);

        $browserA->visitAndWait('/todos')
            ->assertSee($todo->name);
        $browserB->visitAndWait('/todos')
            ->assertSee($todo->name);

        $browserA->press('.o-todo-check')
            ->waitUntilMissingText($todo->name, 10);

        $browserB->waitUntilMissingText($todo->name, 20);
    });
});

test('Deleting a base redirects other tabs', function () {
    $this->browse(function (Browser $browserA, Browser $browserB) {
        $user = createCollabUser();
        $base = $user->firstPersonalBase();
        $collabBase = $user->bases->last();

        $browserA->loginAs($user);
        $browserB->loginAs($user);

        $browserA->visitAndWait("/$collabBase->global_id/settings/general");
        $browserB->visitAndWait("/$collabBase->global_id/home");

        $browserA->press('Delete base')
            ->waitForText('Proceed')
            ->press('Proceed');

        $browserB->waitForLocation("/$base->global_id/home");
    });
});
