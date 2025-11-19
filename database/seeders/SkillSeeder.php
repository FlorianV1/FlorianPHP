<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // Languages
            ['name' => 'PHP', 'icon' => 'devicon-php-plain', 'url' => 'https://php.net', 'category' => 'languages', 'order' => 1],
            ['name' => 'JavaScript', 'icon' => 'devicon-javascript-plain', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/JavaScript', 'category' => 'languages', 'order' => 2],
            ['name' => 'TypeScript', 'icon' => 'devicon-typescript-plain', 'url' => 'https://www.typescriptlang.org', 'category' => 'languages', 'order' => 3],
            ['name' => 'Python', 'icon' => 'devicon-python-plain', 'url' => 'https://python.org', 'category' => 'languages', 'order' => 4],
            ['name' => 'HTML5', 'icon' => 'devicon-html5-plain', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML', 'category' => 'languages', 'order' => 5],
            ['name' => 'CSS3', 'icon' => 'devicon-css3-plain', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/CSS', 'category' => 'languages', 'order' => 6],

            // Backend
            ['name' => 'Laravel', 'icon' => 'devicon-laravel-plain', 'url' => 'https://laravel.com', 'category' => 'backend', 'order' => 1],
            ['name' => 'Symfony', 'icon' => 'devicon-symfony-original', 'url' => 'https://symfony.com', 'category' => 'backend', 'order' => 2],
            ['name' => 'Node.js', 'icon' => 'devicon-nodejs-plain', 'url' => 'https://nodejs.org', 'category' => 'backend', 'order' => 3],
            ['name' => 'Express', 'icon' => 'devicon-express-original', 'url' => 'https://expressjs.com', 'category' => 'backend', 'order' => 4],
            ['name' => 'NestJS', 'icon' => 'devicon-nestjs-plain', 'url' => 'https://nestjs.com', 'category' => 'backend', 'order' => 5],

            // Frontend
            ['name' => 'Vue.js', 'icon' => 'devicon-vuejs-plain', 'url' => 'https://vuejs.org', 'category' => 'frontend', 'order' => 1],
            ['name' => 'React', 'icon' => 'devicon-react-original', 'url' => 'https://react.dev', 'category' => 'frontend', 'order' => 2],
            ['name' => 'Nuxt', 'icon' => 'devicon-nuxtjs-plain', 'url' => 'https://nuxt.com', 'category' => 'frontend', 'order' => 3],
            ['name' => 'Next.js', 'icon' => 'devicon-nextjs-original', 'url' => 'https://nextjs.org', 'category' => 'frontend', 'order' => 4],
            ['name' => 'Tailwind CSS', 'icon' => 'devicon-tailwindcss-plain', 'url' => 'https://tailwindcss.com', 'category' => 'frontend', 'order' => 5],
            ['name' => 'Alpine.js', 'icon' => 'devicon-alpinejs-original', 'url' => 'https://alpinejs.dev', 'category' => 'frontend', 'order' => 6],

            // Database
            ['name' => 'MySQL', 'icon' => 'devicon-mysql-plain', 'url' => 'https://www.mysql.com', 'category' => 'database', 'order' => 1],
            ['name' => 'PostgreSQL', 'icon' => 'devicon-postgresql-plain', 'url' => 'https://www.postgresql.org', 'category' => 'database', 'order' => 2],
            ['name' => 'MongoDB', 'icon' => 'devicon-mongodb-plain', 'url' => 'https://www.mongodb.com', 'category' => 'database', 'order' => 3],
            ['name' => 'Redis', 'icon' => 'devicon-redis-plain', 'url' => 'https://redis.io', 'category' => 'database', 'order' => 4],

            // DevOps
            ['name' => 'Docker', 'icon' => 'devicon-docker-plain', 'url' => 'https://www.docker.com', 'category' => 'devops', 'order' => 1],
            ['name' => 'Git', 'icon' => 'devicon-git-plain', 'url' => 'https://git-scm.com', 'category' => 'devops', 'order' => 2],
            ['name' => 'GitHub', 'icon' => 'devicon-github-original', 'url' => 'https://github.com', 'category' => 'devops', 'order' => 3],
            ['name' => 'AWS', 'icon' => 'devicon-amazonwebservices-original', 'url' => 'https://aws.amazon.com', 'category' => 'devops', 'order' => 4],
            ['name' => 'Nginx', 'icon' => 'devicon-nginx-original', 'url' => 'https://nginx.org', 'category' => 'devops', 'order' => 5],
            ['name' => 'Linux', 'icon' => 'devicon-linux-plain', 'url' => 'https://www.linux.org', 'category' => 'devops', 'order' => 6],

            // Tools
            ['name' => 'Composer', 'icon' => 'devicon-composer-line', 'url' => 'https://getcomposer.org', 'category' => 'tools', 'order' => 1],
            ['name' => 'NPM', 'icon' => 'devicon-npm-original-wordmark', 'url' => 'https://www.npmjs.com', 'category' => 'tools', 'order' => 2],
            ['name' => 'Vite', 'icon' => 'devicon-vitejs-plain', 'url' => 'https://vitejs.dev', 'category' => 'tools', 'order' => 3],
            ['name' => 'VS Code', 'icon' => 'devicon-vscode-plain', 'url' => 'https://code.visualstudio.com', 'category' => 'tools', 'order' => 4],
            ['name' => 'PhpStorm', 'icon' => 'devicon-phpstorm-plain', 'url' => 'https://www.jetbrains.com/phpstorm', 'category' => 'tools', 'order' => 5],

            // CMS
            ['name' => 'WordPress', 'icon' => 'devicon-wordpress-plain', 'url' => 'https://wordpress.org', 'category' => 'cms', 'order' => 1],
            ['name' => 'Filament', 'icon' => 'devicon-laravel-plain', 'url' => 'https://filamentphp.com', 'category' => 'cms', 'order' => 2],

            // Testing
            ['name' => 'PHPUnit', 'icon' => 'devicon-phpunit-plain', 'url' => 'https://phpunit.de', 'category' => 'testing', 'order' => 1],
            ['name' => 'Jest', 'icon' => 'devicon-jest-plain', 'url' => 'https://jestjs.io', 'category' => 'testing', 'order' => 2],
        ];

        foreach ($skills as $skill) {
            Skill::create(array_merge($skill, ['is_active' => true]));
        }
    }
}
