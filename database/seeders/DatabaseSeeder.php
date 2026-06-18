<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'cranam21@gmail.com'],
            [
                'name' => 'Choirul Anam',
                'password' => '$2y$12$lQgV26PAdDeYartQpU31nelPVQr96OSfIzXVXsMQndfJETYIdyB32',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'dunga@hacktani'],
            [
                'name' => 'Dunga',
                'password' => '$2y$12$ALGUrdLSbXRqv22zsphbgevmKhe5R/KA4HuhN7AM1Wn8eT0FN9BUi',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'huda@hacktani'],
            [
                'name' => 'Pak Huda',
                'password' => '$2y$12$t/3b..UUYgaaukQhcolSVuzWhP98hERk1uVX77UQIkJvPNDOt49km',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'yanti@hacktani'],
            [
                'name' => 'Bu Yanti',
                'password' => '$2y$12$sk6VydhFvmjEzDIKOxCGxuAEBysSVwDBDCZVXNtwlEP4kOLdqtEGK',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cakanas821@pengentani.my.id'],
            [
                'name' => 'Cak Anas',
                'password' => '$2y$12$18YJuK236mlHn82gdfioD.SmlVea.k.IYKMj8sZ6asUZ3GVwncoly',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'didik454@pengentani.my.id'],
            [
                'name' => 'Didik',
                'password' => '$2y$12$o4StXAnvYe9P6AleB0tPC.IDdcBsBvpiw9Km.EpYa39WjuS91H5Nu',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'pakjoni388@pengentani.my.id'],
            [
                'name' => 'Pak Joni',
                'password' => '$2y$12$Og6pCzyzCeHNGUoxRK9YPudhljvfV0zJPMaVM4C1HN1iB5Ob.zL5O',
                'is_active' => true,
            ]
        );

    }
}
