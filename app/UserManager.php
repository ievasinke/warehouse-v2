<?php

namespace App;

class UserManager
{
    private string $userFile;

    public function __construct($userFile = 'data/users.json')
    {
        $this->userFile = $userFile;
    }

    public function loadUsers(): array
    {
        $users = [];
        $usersData = json_decode(file_get_contents($this->userFile));

        foreach ($usersData->users as $userData) {
            $users[] = new User(
                $userData->username,
                $userData->accessCode
            );
        }
        return $users;
    }

    public function findUserByAccessCode(string $accessCode): ?User
    {
        $users = $this->loadUsers();
        $filtered = array_filter($users, function ($user) use ($accessCode): bool {
            return $user->getAccessCode() === $accessCode;
        });
        return reset($filtered) ?? null;
    }
}