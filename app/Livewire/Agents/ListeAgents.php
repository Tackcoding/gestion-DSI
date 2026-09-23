<?php

namespace App\Livewire\Agents;

use App\Enums\RoleUtilisateur;
use App\Models\Agent;
use App\Models\Fonction;
use App\Models\Service;
use App\Services\CompteService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

class ListeAgents extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $recherche = '';

    #[Url]
    public string $filtreFonction = '';

    // --- Etat du formulaire (modale) ---
    public bool $modaleOuverte = false;
    public ?int $agentId = null;
    public string $im = '';
    public string $nom = '';
    public string $prenom = '';
    public ?int $fonction_id = null;
    public ?int $service_id = null;
    public string $telephone = '';
    public bool $actif = true;

    // --- Carriere : reprise sur le formulaire officiel de conge / permission ---
    public string $grade = '';
    public string $classe = '';
    public string $echelon = '';
    public string $indice = '';
    public string $chapitre = '';
    public string $date_entree_administration = '';

    public ?int $suppressionId = null;

    // --- Compte de connexion (directeur et administrateur) ---
    public ?int $compteAgentId = null;

    /** Role du futur compte, choisi au moment de generer le code. */
    public string $roleCode = 'agent';

    /** Nouveau role d'un compte existant. */
    public string $nouveauRole = '';

    /** Code en clair : affiche une seule fois, jamais stocke. */
    public ?string $codeGenere = null;
    public ?string $codeExpireLe = null;

    /** Champs de carriere : facultatifs, une chaine vide est enregistree comme null. */
    private const CHAMPS_CARRIERE = [
        'grade', 'classe', 'echelon', 'indice', 'chapitre', 'date_entree_administration',
    ];

    protected function rules(): array
    {
        return [
            'im'          => ['nullable', 'string', 'max:20',
                              Rule::unique('agents', 'im')->ignore($this->agentId)],
            'nom'         => 'required|string|max:100',
            'prenom'      => 'required|string|max:150',
            'fonction_id' => 'required|exists:fonctions,id',
            'service_id'  => 'required|exists:services,id',
            'telephone'   => 'nullable|string|max:30',
            'actif'       => 'boolean',

            'grade'                      => 'nullable|string|max:100',
            'classe'                     => 'nullable|string|max:30',
            'echelon'                    => 'nullable|string|max:30',
            'indice'                     => 'nullable|string|max:20',
            'chapitre'                   => 'nullable|string|max:30',
            'date_entree_administration' => 'nullable|date|before_or_equal:today',
        ];
    }

    protected array $messages = [
        'nom.required'         => 'Le nom est obligatoire.',
        'prenom.required'      => 'Le prénom est obligatoire.',
        'fonction_id.required' => 'La fonction est obligatoire.',
        'service_id.required'  => 'Le service est obligatoire.',
        'im.unique'            => 'Cet IM est déjà attribué à un autre agent.',
        'date_entree_administration.before_or_equal' => 'La date d\'entrée ne peut pas être dans le futur.',
    ];

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function updatingFiltreFonction(): void
    {
        $this->resetPage();
    }

    public function ouvrirCreation(): void
    {
        $this->reinitialiserFormulaire();

        // Une seule direction pour l'instant : on la pre-selectionne.
        $services = Service::pluck('id');
        $this->service_id = $services->count() === 1 ? $services->first() : null;

        $this->modaleOuverte = true;
    }

    public function ouvrirEdition(int $id): void
    {
        $agent = Agent::findOrFail($id);

        $this->agentId     = $agent->id;
        $this->im          = $agent->im ?? '';
        $this->nom         = $agent->nom;
        $this->prenom      = $agent->prenom;
        $this->fonction_id = $agent->fonction_id;
        $this->service_id  = $agent->service_id;
        $this->telephone   = $agent->telephone ?? '';
        $this->actif       = $agent->actif;

        $this->grade    = $agent->grade ?? '';
        $this->classe   = $agent->classe ?? '';
        $this->echelon  = $agent->echelon ?? '';
        $this->indice   = $agent->indice ?? '';
        $this->chapitre = $agent->chapitre ?? '';
        $this->date_entree_administration = $agent->date_entree_administration?->format('Y-m-d') ?? '';

        $this->resetValidation();
        $this->modaleOuverte = true;
    }

    public function enregistrer(): void
    {
        $data = $this->validate();

        // Chaine vide -> null, sinon l'unicite de l'IM casse au 2e agent sans IM.
        $data['im'] = $data['im'] ?: null;
        $data['telephone'] = $data['telephone'] ?: null;

        foreach (self::CHAMPS_CARRIERE as $champ) {
            $data[$champ] = filled($data[$champ] ?? null) ? trim($data[$champ]) : null;
        }

        $modification = (bool) $this->agentId;

        Agent::updateOrCreate(['id' => $this->agentId], $data);

        $this->modaleOuverte = false;
        $this->reinitialiserFormulaire();

        session()->flash('message', $modification ? 'Agent modifié.' : 'Agent ajouté.');
    }

    public function confirmerSuppression(int $id): void
    {
        $this->suppressionId = $id;
    }

    // ------------------------------------------------------------------
    // Compte de connexion : directeur et administrateur uniquement
    // ------------------------------------------------------------------

    public function ouvrirCompte(int $id): void
    {
        $this->authorize('gerer-comptes');

        $agent = Agent::with('user')->findOrFail($id);

        $this->compteAgentId = $agent->id;
        $this->roleCode      = RoleUtilisateur::Agent->value;
        $this->nouveauRole   = $agent->user?->role->value ?? '';
        $this->codeGenere    = null;
        $this->codeExpireLe  = null;
        $this->resetValidation();
    }

    public function fermerCompte(): void
    {
        $this->reset(['compteAgentId', 'roleCode', 'nouveauRole', 'codeGenere', 'codeExpireLe']);
        $this->resetValidation();
    }

    /**
     * Sans compte : code de premiere connexion, avec le role choisi.
     * Avec compte : code de reinitialisation, le role actuel est conserve.
     */
    public function genererCode(): void
    {
        $this->authorize('gerer-comptes');

        $agent = Agent::with('user')->findOrFail($this->compteAgentId);
        $role  = $agent->user?->role ?? RoleUtilisateur::tryFrom($this->roleCode);

        if (! $role) {
            $this->addError('roleCode', 'Choisissez un rôle.');
            return;
        }

        try {
            [$code, $expire] = app(CompteService::class)->genererCode($agent, $role, auth()->user());
        } catch (RuntimeException $e) {
            $this->addError('roleCode', $e->getMessage());
            return;
        }

        $this->codeGenere   = $code;
        $this->codeExpireLe = $expire->format('d/m/Y à H:i');
    }

    public function annulerCode(): void
    {
        $this->authorize('gerer-comptes');

        app(CompteService::class)->annulerCodes(Agent::findOrFail($this->compteAgentId));
        $this->codeGenere = null;
    }

    public function changerRole(): void
    {
        $this->authorize('gerer-comptes');

        $agent = Agent::with('user')->findOrFail($this->compteAgentId);
        $role  = RoleUtilisateur::tryFrom($this->nouveauRole);

        if (! $agent->user || ! $role) {
            $this->addError('nouveauRole', 'Choisissez un rôle.');
            return;
        }

        try {
            app(CompteService::class)->changerRole($agent->user, $role, auth()->user());
        } catch (RuntimeException $e) {
            $this->addError('nouveauRole', $e->getMessage());
            return;
        }

        $this->fermerCompte();
        session()->flash('message', "Rôle de {$agent->nom} : {$role->libelle()}.");
    }

    public function basculerActivation(): void
    {
        $this->authorize('gerer-comptes');

        $agent = Agent::with('user')->findOrFail($this->compteAgentId);

        if (! $agent->user) {
            return;
        }

        try {
            $actif = app(CompteService::class)->basculerActivation($agent->user, auth()->user());
        } catch (RuntimeException $e) {
            $this->addError('nouveauRole', $e->getMessage());
            return;
        }

        $this->fermerCompte();
        session()->flash('message', $actif
            ? "Le compte de {$agent->nom} est réactivé."
            : "Le compte de {$agent->nom} est désactivé : il ne peut plus se connecter.");
    }

    public function supprimer(): void
    {
        $agent = Agent::findOrFail($this->suppressionId);

        if ($agent->couvertures()->exists() || $agent->demandesAbsence()->exists()) {
            $this->suppressionId = null;
            session()->flash('erreur',
                'Cet agent a un historique : désactivez-le plutôt que de le supprimer.');
            return;
        }

        $agent->delete();
        $this->suppressionId = null;

        session()->flash('message', 'Agent supprimé.');
    }

    private function reinitialiserFormulaire(): void
    {
        $this->reset(array_merge(
            ['agentId', 'im', 'nom', 'prenom', 'fonction_id', 'service_id', 'telephone'],
            self::CHAMPS_CARRIERE
        ));
        $this->actif = true;
        $this->resetValidation();
    }

    public function render()
    {
        $agents = Agent::query()
            ->with([
                'fonction', 'service', 'user',
                'codesActivation' => fn ($q) => $q->enAttente(),
            ])
            ->when($this->recherche, fn ($q) => $q->where(function ($sq) {
                $sq->where('nom', 'like', "%{$this->recherche}%")
                   ->orWhere('prenom', 'like', "%{$this->recherche}%")
                   ->orWhere('im', 'like', "%{$this->recherche}%");
            }))
            ->when($this->filtreFonction,
                   fn ($q) => $q->where('fonction_id', $this->filtreFonction))
            ->orderBy('nom')
            ->paginate(10);

        // Fenetre « Compte » ouverte
        $compteAgent = $this->compteAgentId
            ? Agent::with(['user', 'codesActivation' => fn ($q) => $q->enAttente()])->find($this->compteAgentId)
            : null;

        $utilisateur = auth()->user();

        return view('livewire.agents.liste-agents', [
            'agents'          => $agents,
            'fonctions'       => Fonction::orderBy('libelle')->get(),
            'services'        => Service::orderBy('libelle')->get(),
            'compteAgent'     => $compteAgent,
            'codeEnAttente'   => $compteAgent?->codesActivation->first(),
            'peutGererCompte' => ! $compteAgent?->user
                || app(CompteService::class)->peutGerer($utilisateur, $compteAgent->user),
            'rolesAttribuables' => $utilisateur->role->rolesAttribuables(),
        ]);
    }
}
