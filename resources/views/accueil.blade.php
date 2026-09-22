{{-- Page d'accueil des agents, chefs de service et dépositaire.
     Le directeur et l'administrateur arrivent sur le tableau de bord.
     Image de fond : public/img/accueil-fond.jpg (1920 px de large, JPEG, 400 Ko maximum). --}}
<x-app-layout>
    <x-slot name="titre">Accueil</x-slot>

    <section class="accueil" style="--accueil-fond: url('{{ asset('img/accueil-fond.jpg') }}')">
        <div class="accueil-interieur">
            <div class="accueil-bloc">
                <p class="accueil-sigle">MIDSP</p>

                <h1 class="accueil-titre">
                    <span>Ministère de l'Industrialisation</span>
                    <span>et du Développement</span>
                    <span>du Secteur Privé</span>
                </h1>

                <p class="accueil-direction">Direction de la Veille et de la Communication</p>
            </div>
        </div>
    </section>
</x-app-layout>
