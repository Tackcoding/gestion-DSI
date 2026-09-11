{{--
| Variante horizontale du logotype.
|
| La version courte de la charte (symbole + acronyme empiles) a un
| rapport de 1,09 : pour atteindre les 100 px de large exiges, il lui
| faudrait 92 px de haut, ce qu'une barre de navigation de 64 px ne
| permet pas. La composition horizontale porte le rapport a 3,68 :
| a 40 px de haut, le logo mesure 147 px de large et respecte le seuil.
--}}
<img src="{{ asset('img/midsp-logo-horizontal-240.png') }}"
     alt="MIDSP — Ministère de l'Industrialisation et du Développement du Secteur Privé"
     {{ $attributes->merge(['class' => 'h-10 w-auto']) }}>
