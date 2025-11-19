<x-layouts.portfolio :profile="$profile">
    <x-portfolio.navigation :profile="$profile" />
    <x-portfolio.hero :profile="$profile" />
    <x-portfolio.now :items="$nowItems" />
    <x-portfolio.projects :projects="$projects" />
    <x-portfolio.experience :experiences="$experiences" />
    <x-portfolio.skills-marquee :skills="$skills" />
    <x-portfolio.about :profile="$profile" />
    <x-portfolio.contact :profile="$profile" />
    <x-portfolio.footer :profile="$profile" />
</x-layouts.portfolio>
