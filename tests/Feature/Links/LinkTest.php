<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('A link is validated to a certain number of characters', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $linkList = createList($user, 'linkList');
    $longURL = 'http://example.com?query='.str_pad('', 1500, 'a');

    $this->be($user)->assertFailedGraphQLMutation(
        'createLink(input: $input)',
        ['input: CreateLinkInput!' => [
            'name' => 'test',
            'linkListId' => $linkList->global_id,
            'url' => $longURL,
        ]]
    )->assertGraphQLValidationError('input.url', 'The URL must not be greater than 1000 characters.');
}
);

test('Updating a link is validated to a certain number of characters', function () {
    $this->withGraphQLExceptionHandling();
    $user = createUser();

    $linkList = createList($user, 'linkList', [], 1);
    $link = $linkList->links->first();
    $longURL = 'http://example.com?query='.str_pad('', 1500, 'a');

    $this->be($user)->assertFailedGraphQLMutation(
        'updateLink(input: $input)',
        ['input: UpdateLinkInput!' => [
            'name' => 'test',
            'id' => $link->global_id,
            'url' => $longURL,
        ]]
    )->assertGraphQLValidationError('input.url', 'The URL must not be greater than 1000 characters.');
}
);
