<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // roles: admin, delivery_man, customer

        $roles = ['admin', 'delivery_man', 'customer'];

        // create roles

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }


        // create admin user or store admin user in database
        $adminRole = Role::where('name', 'admin')->first();

        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'latitude' => '23.8103',
            'longitude' => '90.4125',
        ]);

        $adminUser->roles()->attach($adminRole);


        // create delivery man user
        $deliveryManRole = Role::where('name', 'delivery_man')->first();
        $deliveryManUser = User::create([
            'name' => 'Delivery Man',
            'email' => 'deliveryman@test.com',
            'password' => bcrypt('password'),
            'role_id' => $deliveryManRole->id,
            'latitude' => '23.8103',
            'longitude' => '90.4125',
        ]);
        $deliveryManUser->roles()->attach($deliveryManRole);

        // create user
        
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $customers = [
            [
                'name' => 'Customer One',
                'email' => 'customer1@test.com',
                'address' => 'Dhanmondi, Dhaka',
                'latitude' => '23.7465',
                'longitude' => '90.3760',
            ],
            [
                'name' => 'Customer Two',
                'email' => 'customer2@test.com',
                'address' => 'Gulshan, Dhaka',
                'latitude' => '23.7925',
                'longitude' => '90.4078',
            ],
            [
                'name' => 'Customer Three',
                'email' => 'customer3@test.com',
                'address' => 'Mirpur, Dhaka',
                'latitude' => '23.8223',
                'longitude' => '90.3654',
            ],
            [
                'name' => 'Customer Four',
                'email' => 'customer4@test.com',
                'address' => 'Uttara, Dhaka',
                'latitude' => '23.8759',
                'longitude' => '90.3795',
            ],
            [
                'name' => 'Customer Five',
                'email' => 'customer5@test.com',
                'address' => 'Motijheel, Dhaka',
                'latitude' => '23.7333',
                'longitude' => '90.4172',
            ],
        ];

        foreach ($customers as $customer) {
            $customerUser = User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'password' => bcrypt('password'),
                'role_id' => $customerRole->id,
                'address' => $customer['address'],
                'latitude' => $customer['latitude'],
                'longitude' => $customer['longitude'],
            ]);

            $customerUser->roles()->attach($customerRole);
        }
    }
}
