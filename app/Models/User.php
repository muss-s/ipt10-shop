<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['name', 'email', 'password_hash', 'role'];

    public static function register(string $name, string $email, string $password, string $role = 'customer'): int
    {
        return self::create([
            'name' => $name,
            'email' => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    /** Returns the user row if the email + password match, else null. */
    public static function attempt(string $email, string $password): ?array
    {
        $user = self::findBy('email', strtolower($email));
        return $user && password_verify($password, $user['password_hash']) ? $user : null;
    }
}
