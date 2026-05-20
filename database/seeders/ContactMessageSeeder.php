<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            [
                'name'       => 'Alex van der Berg',
                'email'      => 'alex@example.nl',
                'message'    => 'Hi Florian, I came across your portfolio and I\'m really impressed by the BingoMC project. We\'re a small studio looking for a Laravel developer for a 3-month contract. Would you be open to a quick call this week?',
                'read_at'    => now()->subDays(2),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(2),
            ],
            [
                'name'       => 'Sarah Mitchell',
                'email'      => 'sarah.mitchell@startup.io',
                'message'    => 'Hey! I\'m building a SaaS product and need help architecting the Laravel backend. Your experience with APIs and Docker looks like a great fit. Let me know if you have availability in the coming weeks.',
                'read_at'    => null,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'name'       => 'Tom Dijkstra',
                'email'      => 'tom@agency.nl',
                'message'    => 'Florian, we have a client project that needs a senior PHP developer for about 6 weeks. It\'s a full rewrite of a legacy application into Laravel. Are you interested? Happy to share more details.',
                'read_at'    => null,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
        ];

        foreach ($messages as $message) {
            $record = new ContactMessage();
            $record->name       = $message['name'];
            $record->email      = $message['email'];
            $record->message    = $message['message'];
            $record->read_at    = $message['read_at'];
            $record->created_at = $message['created_at'];
            $record->updated_at = $message['updated_at'];
            $record->saveQuietly();
        }
    }
}
