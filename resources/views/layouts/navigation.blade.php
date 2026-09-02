<nav x-data="{ ouvert: false }" class="border-b border-[var(--trait)] bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">

            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('tableau-de-bord') }}">
                        <x-application-logo />
                    </a>
                </div>

                <div class="hidden sm:-my-px sm:ms-10 sm:flex sm:space-x-8">
                    <x-nav-link :href="route('tableau-de-bord')" :active="request()->routeIs('tableau-de-bord')">
                        Accueil
                    </x-nav-link>

                    <x-nav-link :href="route('evenements.index')" :active="request()->routeIs('evenements.*')">
                        Événements
                    </x-nav-link>

                     <x-nav-link :href="route('absences.index')" :active="request()->routeIs('absences.*')">
                    Absences
                    </x-nav-link>

                    @can('gerer-materiel')
                        <x-nav-link :href="route('materiels.index')" :active="request()->routeIs('materiels.*')">
                            Matériel
                        </x-nav-link>
                    @endcan

                    @can('gerer-agents')
                        <x-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.*')">
                            Agents
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            {{-- Compte --}}
            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-[var(--gris)] transition hover:text-[var(--encre)]">
                            {{ Auth::user()->name }}
                            <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-[var(--trait)] px-4 py-2">
                            <div class="text-xs text-[var(--gris)]">
                                {{ Auth::user()->role->libelle() }}
                            </div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            Mon compte
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Se déconnecter
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Menu mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="ouvert = ! ouvert"
                        class="inline-flex items-center justify-center rounded-md p-2 text-[var(--gris)] transition hover:bg-[var(--fond)]">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': ouvert, 'inline-flex': ! ouvert }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! ouvert, 'inline-flex': ouvert }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Navigation mobile --}}
    <div :class="{'block': ouvert, 'hidden': ! ouvert}" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('tableau-de-bord')" :active="request()->routeIs('tableau-de-bord')">
                Accueil
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('evenements.index')" :active="request()->routeIs('evenements.*')">
                Événements
            </x-responsive-nav-link>

            @can('gerer-materiel')
                <x-responsive-nav-link :href="route('materiels.index')" :active="request()->routeIs('materiels.*')">
                    Matériel
                </x-responsive-nav-link>
            @endcan

             @can('gerer-materiel')
                <x-nav-link :href="route('registre.index')" :active="request()->routeIs('registre.*')">
                    Registre
                </x-nav-link>
            @endcan


            @can('gerer-agents')
                <x-responsive-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.*')">
                    Agents
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="border-t border-[var(--trait)] pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-[var(--encre)]">{{ Auth::user()->name }}</div>
                <div class="text-sm text-[var(--gris)]">{{ Auth::user()->email }}</div>
                <div class="mt-1 text-xs text-[var(--gris)]">{{ Auth::user()->role->libelle() }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Mon compte
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Se déconnecter
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
