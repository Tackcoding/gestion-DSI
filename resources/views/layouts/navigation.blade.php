<nav x-data="{ ouvert: false }" class="border-b border-[var(--midsp-gris-bord)] bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">

            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('tableau-de-bord') }}" class="inline-flex items-center">
                        <x-application-logo />
                    </a>
                </div>

                {{-- Menu principal (écrans larges) --}}
                <div class="hidden sm:ms-10 sm:flex sm:space-x-6">
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

                        <x-nav-link :href="route('registre.index')" :active="request()->routeIs('registre.*')">
                            Registre
                        </x-nav-link>

                        <x-nav-link :href="route('signalements.index')" :active="request()->routeIs('signalements.*')">
                            Signalements
                        </x-nav-link>
                    @endcan

                    @can('gerer-agents')
                        <x-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.*')">
                            Agents
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            {{-- Menu utilisateur (écrans larges) : même hauteur que les liens, pour rester sur la même ligne --}}
            <div class="hidden sm:ms-6 sm:flex sm:items-stretch">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button type="button"
                                class="inline-flex h-16 items-center gap-2 border-b-4 border-transparent px-2 text-base text-[var(--midsp-gris)] transition hover:text-[var(--midsp-noir)]">
                            {{ Auth::user()->name }}
                            <x-ui.icone nom="chevron" class="h-4 w-4" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-[var(--midsp-gris-bord)] px-4 py-2">
                            <div class="text-xs text-[var(--midsp-gris)]">
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

            {{-- Bouton du menu mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="ouvert = ! ouvert"
                        :aria-expanded="ouvert.toString()"
                        aria-controls="menu-mobile"
                        class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-md p-2 text-[var(--midsp-gris)] transition hover:bg-[var(--midsp-gris-fond)]">
                    <span class="masque-visuel">Ouvrir le menu</span>
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': ouvert, 'inline-flex': ! ouvert }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! ouvert, 'inline-flex': ouvert }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile : mêmes entrées que le menu principal --}}
    <div id="menu-mobile" :class="{'block': ouvert, 'hidden': ! ouvert}" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('tableau-de-bord')" :active="request()->routeIs('tableau-de-bord')">
                Accueil
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('evenements.index')" :active="request()->routeIs('evenements.*')">
                Événements
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('absences.index')" :active="request()->routeIs('absences.*')">
                Absences
            </x-responsive-nav-link>

            @can('gerer-materiel')
                <x-responsive-nav-link :href="route('materiels.index')" :active="request()->routeIs('materiels.*')">
                    Matériel
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('registre.index')" :active="request()->routeIs('registre.*')">
                    Registre
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('signalements.index')" :active="request()->routeIs('signalements.*')">
                    Signalements
                </x-responsive-nav-link>
            @endcan

            @can('gerer-agents')
                <x-responsive-nav-link :href="route('agents.index')" :active="request()->routeIs('agents.*')">
                    Agents
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="border-t border-[var(--midsp-gris-bord)] pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-[var(--midsp-noir)]">{{ Auth::user()->name }}</div>
                <div class="text-sm text-[var(--midsp-gris)]">{{ Auth::user()->email }}</div>
                <div class="mt-1 text-xs text-[var(--midsp-gris)]">{{ Auth::user()->role->libelle() }}</div>
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
