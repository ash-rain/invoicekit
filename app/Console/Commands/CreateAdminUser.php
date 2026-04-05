<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {--name= : The admin\'s name}
                            {--email= : The admin\'s email address}
                            {--password= : The admin\'s password}';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:admins,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Admin [{$admin->email}] created successfully.");

        return self::SUCCESS;
    }
}
